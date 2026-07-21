<?php

namespace App\Libraries;

use App\Models\CountryTaxBracketModel;
use App\Models\CountryTaxRuleModel;
use App\Models\EmployeeBenefitModel;
use App\Models\EmployeeLoanModel;
use App\Models\StatutoryContributionModel;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

/**
 * Country-agnostic payroll computation for one employee over one period.
 *
 * Deliberately self-contained: rather than depending on the Time & Attendance
 * or Filings modules' own classes for attendance/schedule reads, this walks
 * attendance_logs / work_schedules / schedule_assignments / holidays /
 * filings directly via the query builder and re-implements the same small
 * late/undertime comparison (grace_minutes on time_in, strict on time_out)
 * itself. That keeps this module safe to run even if those modules' internal
 * classes change shape later, at the cost of a little duplication.
 *
 * Side-effect free: computeForEmployee() never writes to the database. It is
 * safe to call repeatedly for a live "draft" preview. Only Payroll::finalizeRun()
 * persists payslips/ledger rows, using the array this method returns.
 */
class PayrollEngine
{
    /**
     * Assumption: with no per-employee "standard hours" field in the schema,
     * a full workday is treated as 8 hours (480 minutes) whenever a specific
     * day's schedule can't tell us otherwise (e.g. no schedule resolved).
     */
    private const DEFAULT_WORK_MINUTES_PER_DAY = 480;

    /**
     * Assumption: for monthly/semi-monthly paid employees, this app has no
     * "working days per year" company setting, so we use the common
     * Philippine payroll convention of 261 working days/year (365 days minus
     * 52 Sundays minus ~12 regular holidays) to derive a daily rate from an
     * annual salary: dailyRate = (basic_pay * periodsPerYearForBasicPay) / 261.
     * This is a simplification — real policies vary (some use 260, 313, or a
     * company-specific divisor) — flagged for the integrator to sanity check.
     */
    private const WORKING_DAYS_PER_YEAR = 261;

    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    /**
     * @return array{
     *   error?: string,
     *   employee_id?: int, employee_name?: string, company_id?: int,
     *   country_code?: string, country_fallback?: bool,
     *   period_start?: string, period_end?: string,
     *   basic_pay?: float, days_worked?: float, late_minutes?: int, undertime_minutes?: int,
     *   absence_days?: float, late_deduction?: float, undertime_deduction?: float, absence_deduction?: float,
     *   gross_pay?: float, taxable_income?: float, tax_withheld?: float, statutory_deductions?: string,
     *   benefits_total?: float, loan_deductions_total?: float, total_deductions?: float, net_pay?: float,
     *   computed_at?: string, details?: array
     * }
     */
    public function computeForEmployee(int $employeeId, string $periodStart, string $periodEnd): array
    {
        $employee = $this->db->table('employees')->where('id', $employeeId)->get()->getRowArray();
        if (! $employee) {
            return ['error' => 'Employee not found.'];
        }

        $user         = $this->db->table('users')->where('id', $employee['user_id'])->get()->getRowArray();
        $employeeName = $user['name'] ?? ('Employee #' . $employeeId);

        $company         = $this->db->table('companies')->where('id', $employee['company_id'])->get()->getRowArray();
        $countryFallback = empty($company['country_code']);
        $countryCode     = $countryFallback ? 'PH' : $company['country_code'];

        if ($employee['basic_pay'] === null || $employee['basic_pay'] === '') {
            return [
                'error'         => 'Payroll cannot be computed until basic pay is set for this employee.',
                'employee_id'   => $employeeId,
                'employee_name' => $employeeName,
            ];
        }

        $basicPay     = (float) $employee['basic_pay'];
        $payFrequency = $employee['pay_frequency'] ?: 'monthly';
        $dailyRate    = $this->computeDailyRate($basicPay, $payFrequency);

        $rankExempt = false;
        if (! empty($employee['employee_rank_id'])) {
            $rank       = $this->db->table('employee_ranks')->where('id', $employee['employee_rank_id'])->get()->getRowArray();
            $rankExempt = $rank ? db_bool($rank['is_exempt']) : false;
        }

        $walk = $this->walkPeriod($employee, $employeeId, $periodStart, $periodEnd, $dailyRate, $rankExempt);

        $absenceDeduction = round($walk['absenceDays'] * $dailyRate, 2);
        $grossPay         = max(0, ($walk['paidDayEquivalents'] * $dailyRate) - $walk['lateDeduction'] - $walk['undertimeDeduction'] - $absenceDeduction);

        [$statutoryTotal, $statutoryBreakdown] = $this->computeStatutory($countryCode, $basicPay);

        $taxableIncome            = max(0, $grossPay - $statutoryTotal);
        [$taxWithheld, $taxNote]  = $this->computeTax($countryCode, $taxableIncome, db_bool($employee['is_minimum_wage_earner'] ?? false));

        [$benefitsTotal, $benefitLines] = $this->activeBenefits($employeeId, $periodStart, $periodEnd);
        [$loanTotal, $loanLines]        = $this->activeLoanDeductions($employeeId);

        $totalDeductions = round($statutoryTotal + $taxWithheld + $loanTotal, 2);
        $netPay          = round($grossPay + $benefitsTotal - $totalDeductions, 2);

        return [
            'error'                 => null,
            'employee_id'           => $employeeId,
            'employee_name'         => $employeeName,
            'company_id'            => (int) $employee['company_id'],
            'country_code'          => $countryCode,
            'country_fallback'      => $countryFallback,
            'period_start'          => $periodStart,
            'period_end'            => $periodEnd,
            'basic_pay'             => $basicPay,
            'days_worked'           => $walk['daysWorked'],
            'late_minutes'          => $walk['lateMinutes'],
            'undertime_minutes'     => $walk['undertimeMinutes'],
            'absence_days'          => $walk['absenceDays'],
            'late_deduction'        => round($walk['lateDeduction'], 2),
            'undertime_deduction'   => round($walk['undertimeDeduction'], 2),
            'absence_deduction'     => $absenceDeduction,
            'gross_pay'             => round($grossPay, 2),
            'taxable_income'        => round($taxableIncome, 2),
            'tax_withheld'          => round($taxWithheld, 2),
            'statutory_deductions'  => json_encode($statutoryBreakdown),
            'benefits_total'        => round($benefitsTotal, 2),
            'loan_deductions_total' => round($loanTotal, 2),
            'total_deductions'      => $totalDeductions,
            'net_pay'               => $netPay,
            'computed_at'           => date('Y-m-d H:i:s'),
            'details'               => [
                'days'                         => $walk['dayDetails'],
                'statutory'                    => $statutoryBreakdown,
                'benefits'                     => $benefitLines,
                'loans'                        => $loanLines,
                'exempt_from_time_deductions'  => $rankExempt,
                'daily_rate'                   => round($dailyRate, 4),
                'pay_frequency'                => $payFrequency,
                'tax_note'                     => $taxNote,
            ],
        ];
    }

    /**
     * Walks every calendar date in the period and classifies it, accumulating
     * the raw minutes/days the payslip needs. Precedence per date: holiday >
     * excused filing (leave/official_business) > actual attendance > absent.
     */
    private function walkPeriod(array $employee, int $employeeId, string $periodStart, string $periodEnd, float $dailyRate, bool $rankExempt): array
    {
        $daysWorked         = 0;
        $lateMinutes         = 0;
        $undertimeMinutes    = 0;
        $absenceDays         = 0;
        $lateDeduction       = 0.0;
        $undertimeDeduction  = 0.0;
        $paidDayEquivalents  = 0.0;
        $dayDetails          = [];

        foreach ($this->datesBetween($periodStart, $periodEnd) as $date) {
            $detail = ['date' => $date];

            $holiday = $this->db->table('holidays')
                ->where('company_id', $employee['company_id'])->where('date', $date)
                ->get()->getRowArray();

            if ($holiday) {
                $detail['classification'] = 'holiday';
                $detail['note']           = $holiday['name'];
                $paidDayEquivalents += 1;
                $dayDetails[]        = $detail;
                continue;
            }

            $filings = $this->db->table('filing_dates fd')
                ->select('f.*')
                ->join('filings f', 'f.id = fd.filing_id')
                ->where('fd.date', $date)
                ->where('f.employee_id', $employeeId)
                ->where('f.status', 'approved')
                ->get()->getResultArray();

            $excused = $this->firstExcusingFiling($filings);
            if ($excused !== null) {
                $isLeave = $excused['filing_type'] === 'leave';
                $paid    = true;
                if ($isLeave && ! empty($excused['leave_type_id'])) {
                    $leaveType = $this->db->table('leave_types')->where('id', $excused['leave_type_id'])->get()->getRowArray();
                    $paid      = $leaveType ? db_bool($leaveType['is_paid']) : true;
                }
                // Assumption: official_business is treated as paid (employee is presumed
                // working off-site), matching the spec's "treat these as excused, not absent".
                $detail['classification'] = $isLeave ? 'on_leave' : 'official_business';
                $detail['paid']           = $paid;
                if ($paid) {
                    $paidDayEquivalents += 1;
                }
                $dayDetails[] = $detail;
                continue;
            }

            // Effective schedule for this date: an approved schedule_change filing's
            // requested_work_schedule_id wins, else a schedule_assignments override,
            // else the employee's default work_schedule_id.
            $scheduleChange = $this->firstOfType($filings, 'schedule_change');
            $timeAdjustment = $this->firstOfType($filings, 'time_adjustment');

            $scheduleId = null;
            if ($scheduleChange !== null && ! empty($scheduleChange['requested_work_schedule_id'])) {
                $scheduleId = (int) $scheduleChange['requested_work_schedule_id'];
            } else {
                $assignment = $this->db->table('schedule_assignments')
                    ->where('employee_id', $employeeId)->where('date', $date)
                    ->get()->getRowArray();
                if ($assignment) {
                    $scheduleId = (int) $assignment['work_schedule_id'];
                } elseif (! empty($employee['work_schedule_id'])) {
                    $scheduleId = (int) $employee['work_schedule_id'];
                }
            }

            $schedule = $scheduleId ? $this->db->table('work_schedules')->where('id', $scheduleId)->get()->getRowArray() : null;

            $log = $this->db->table('attendance_logs')
                ->where('employee_id', $employeeId)->where('log_date', $date)
                ->get()->getRowArray();

            // An approved time_adjustment filing corrects the recorded time for this date.
            $actualTimeIn  = $log['time_in']  ?? null;
            $actualTimeOut = $log['time_out'] ?? null;
            if ($timeAdjustment !== null) {
                $actualTimeIn  = $timeAdjustment['requested_time_in']  ?: $actualTimeIn;
                $actualTimeOut = $timeAdjustment['requested_time_out'] ?: $actualTimeOut;
            }

            if (! $schedule) {
                // No schedule resolvable for this date — can't judge lateness, only presence.
                if ($actualTimeIn) {
                    $detail['classification'] = 'worked';
                    $detail['time_in']        = $actualTimeIn;
                    $detail['time_out']       = $actualTimeOut;
                    $daysWorked++;
                    $paidDayEquivalents += 1;
                } else {
                    $detail['classification'] = 'absent';
                    $absenceDays++;
                }
                $dayDetails[] = $detail;
                continue;
            }

            if (! $actualTimeIn) {
                $detail['classification'] = 'absent';
                $detail['schedule']       = $schedule['name'];
                $absenceDays++;
                $dayDetails[] = $detail;
                continue;
            }

            $graceMinutes = (int) ($schedule['grace_minutes'] ?? 10);
            $scheduledIn  = $this->toMinutes($schedule['time_in']);
            $scheduledOut = $this->toMinutes($schedule['time_out']);
            $actualIn     = $this->toMinutes(substr((string) $actualTimeIn, 0, 5));
            $actualOut    = $actualTimeOut ? $this->toMinutes(substr((string) $actualTimeOut, 0, 5)) : null;

            // Late: arrived after scheduled time_in + grace period. Undertime: left
            // before scheduled time_out — no grace period on the way out.
            $dayLate      = max(0, $actualIn - ($scheduledIn + $graceMinutes));
            $dayUndertime = $actualOut !== null ? max(0, $scheduledOut - $actualOut) : 0;

            $isExecutive = ($schedule['schedule_type'] ?? 'fixed') === 'executive';

            $daysWorked++;
            $paidDayEquivalents += 1;
            $lateMinutes      += $dayLate;
            $undertimeMinutes += $dayUndertime;

            // Exempt ranks/executive schedules: minutes are still recorded above for
            // visibility, but no peso deduction is applied for either.
            if (! $rankExempt && ! $isExecutive) {
                $minuteRate          = $this->minuteRateForSchedule($dailyRate, $schedule);
                $lateDeduction      += $dayLate * $minuteRate;
                $undertimeDeduction += $dayUndertime * $minuteRate;
            }

            $detail['classification']     = $dayLate > 0 ? 'late' : ($dayUndertime > 0 ? 'undertime' : 'on_time');
            $detail['time_in']            = $actualTimeIn;
            $detail['time_out']           = $actualTimeOut;
            $detail['late_minutes']       = $dayLate;
            $detail['undertime_minutes']  = $dayUndertime;
            $dayDetails[] = $detail;
        }

        return [
            'daysWorked'         => $daysWorked,
            'lateMinutes'        => $lateMinutes,
            'undertimeMinutes'   => $undertimeMinutes,
            'absenceDays'        => $absenceDays,
            'lateDeduction'      => $lateDeduction,
            'undertimeDeduction' => $undertimeDeduction,
            'paidDayEquivalents' => $paidDayEquivalents,
            'dayDetails'         => $dayDetails,
        ];
    }

    /** @return array|null The first leave/official_business filing covering the date, if any. */
    private function firstExcusingFiling(array $filings): ?array
    {
        foreach ($filings as $filing) {
            if (in_array($filing['filing_type'], ['leave', 'official_business'], true)) {
                return $filing;
            }
        }

        return null;
    }

    private function firstOfType(array $filings, string $type): ?array
    {
        foreach ($filings as $filing) {
            if ($filing['filing_type'] === $type) {
                return $filing;
            }
        }

        return null;
    }

    /** @return string[] Y-m-d dates, inclusive of both endpoints. */
    private function datesBetween(string $start, string $end): array
    {
        $startDate = new DateTimeImmutable($start);
        $endDate   = (new DateTimeImmutable($end))->modify('+1 day'); // DatePeriod's end is exclusive
        $period    = new DatePeriod($startDate, new DateInterval('P1D'), $endDate);

        $dates = [];
        foreach ($period as $day) {
            $dates[] = $day->format('Y-m-d');
        }

        return $dates;
    }

    private function toMinutes(string $hhmm): int
    {
        [$h, $m] = array_pad(explode(':', $hhmm), 2, '0');

        return ((int) $h * 60) + (int) $m;
    }

    /**
     * Basic-pay-to-daily-rate conversion. Assumption (integrator: please sanity check):
     *  - pay_frequency 'daily'  : basic_pay already IS the daily rate.
     *  - pay_frequency 'hourly' : basic_pay is an hourly rate; daily rate = hourly * 8.
     *  - pay_frequency 'semi_monthly' : basic_pay is a per-cutoff (half-month) amount;
     *    annualized = basic_pay * 24; daily = annualized / 261.
     *  - anything else (default 'monthly') : basic_pay is a full-month amount;
     *    annualized = basic_pay * 12; daily = annualized / 261.
     * 261 = an assumed working-days-per-year constant (see WORKING_DAYS_PER_YEAR docblock).
     */
    private function computeDailyRate(float $basicPay, string $payFrequency): float
    {
        return match ($payFrequency) {
            'daily'  => $basicPay,
            'hourly' => $basicPay * 8,
            'semi_monthly' => ($basicPay * 24) / self::WORKING_DAYS_PER_YEAR,
            default  => ($basicPay * 12) / self::WORKING_DAYS_PER_YEAR,
        };
    }

    /** Per-minute rate for a specific schedule's shift length, falling back to an 8-hour day. */
    private function minuteRateForSchedule(float $dailyRate, array $schedule): float
    {
        $minutes = $this->toMinutes($schedule['time_out']) - $this->toMinutes($schedule['time_in']) - (int) ($schedule['break_minutes'] ?? 0);
        if ($minutes <= 0) {
            $minutes = self::DEFAULT_WORK_MINUTES_PER_DAY;
        }

        return $dailyRate / $minutes;
    }

    private function clamp(float $value, ?float $min, ?float $max): float
    {
        if ($min !== null && $value < $min) {
            $value = $min;
        }
        if ($max !== null && $value > $max) {
            $value = $max;
        }

        return $value;
    }

    /**
     * Every statutory_contributions row for the employee's country, applied against
     * basic_pay: the contribution base is clamped to [min_base, max_base] when those
     * are set (percentage-of-clamped-base style, e.g. SSS), otherwise the raw
     * percentage-of-basic_pay result is clamped to [min_contribution, max_contribution]
     * when those are set instead (flat-cap style, e.g. PhilHealth/Pag-IBIG).
     *
     * @return array{0: float, 1: array} [employeeShareTotal, breakdown]
     */
    private function computeStatutory(string $countryCode, float $basicPay): array
    {
        $rows      = (new StatutoryContributionModel())->forCountry($countryCode, (int) date('Y'));
        $total     = 0.0;
        $breakdown = [];

        foreach ($rows as $row) {
            $minBase = $row['min_base'] !== null ? (float) $row['min_base'] : null;
            $maxBase = $row['max_base'] !== null ? (float) $row['max_base'] : null;

            $base = ($minBase !== null || $maxBase !== null) ? $this->clamp($basicPay, $minBase, $maxBase) : $basicPay;

            $employeeShare = $base * ((float) $row['employee_share_percent'] / 100);
            $employerShare = $base * ((float) $row['employer_share_percent'] / 100);

            $minContribution = $row['min_contribution'] !== null ? (float) $row['min_contribution'] : null;
            $maxContribution = $row['max_contribution'] !== null ? (float) $row['max_contribution'] : null;

            if ($minContribution !== null || $maxContribution !== null) {
                $employeeShare = $this->clamp($employeeShare, $minContribution, $maxContribution);
                $employerShare = $this->clamp($employerShare, $minContribution, $maxContribution);
            }

            $employeeShare = round($employeeShare, 2);
            $employerShare = round($employerShare, 2);

            $total      += $employeeShare;
            $breakdown[] = [
                'name'           => $row['name'],
                'employee_share' => $employeeShare,
                'employer_share' => $employerShare,
            ];
        }

        return [$total, $breakdown];
    }

    /**
     * Annualizes taxable_income using the country's pay_periods_per_year_default,
     * finds the matching bracket, and de-annualizes the resulting tax back to a
     * per-period figure. Skips entirely (0 tax) for minimum-wage earners in a
     * country whose rule marks them exempt.
     *
     * IMPORTANT: the seeded country_tax_rules/country_tax_brackets figures are
     * reference data only and MUST be reverified against the official BIR (PH)
     * / ATO (AU) publications before this is used for a real payroll run.
     *
     * @return array{0: float, 1: ?string} [taxWithheld, note]
     */
    private function computeTax(string $countryCode, float $taxableIncome, bool $isMinimumWageEarner): array
    {
        $rule = (new CountryTaxRuleModel())->findOrDefault($countryCode);

        if ($isMinimumWageEarner && db_bool($rule['exempt_minimum_wage_earners'])) {
            return [0.0, 'Exempt: minimum wage earner in a country that exempts them from withholding.'];
        }

        $periods = (int) ($rule['pay_periods_per_year_default'] ?: 12);
        $annualized = $taxableIncome * $periods;

        $bracket = (new CountryTaxBracketModel())->findBracket($countryCode, $annualized, (int) date('Y'));
        if (! $bracket) {
            return [0.0, "No country_tax_brackets row covers this income for {$countryCode} — tax withheld defaulted to 0. Verify reference data."];
        }

        $tax = (float) $bracket['base_tax'] + (((float) $bracket['rate_percent'] / 100) * ($annualized - (float) $bracket['min_amount']));
        $tax = max(0, $tax);

        return [$tax / $periods, null];
    }

    /**
     * Benefits considered active for this period (recurring, or a one-off whose
     * effective_date falls inside the period) — additions to gross, not deductions.
     *
     * @return array{0: float, 1: array}
     */
    private function activeBenefits(int $employeeId, string $periodStart, string $periodEnd): array
    {
        $rows  = (new EmployeeBenefitModel())->activeForPeriod($employeeId, $periodStart, $periodEnd);
        $total = 0.0;
        $lines = [];

        foreach ($rows as $row) {
            $amount = round((float) $row['amount'], 2);
            $total += $amount;
            $lines[] = ['benefit_id' => (int) $row['id'], 'benefit_type' => $row['benefit_type'], 'amount' => $amount];
        }

        return [$total, $lines];
    }

    /**
     * Active loans, deducted at monthly_amortization capped to whatever balance
     * remains. This method never writes — the ledger (loan_deduction_history) and
     * the balance decrement only happen when Payroll::finalizeRun() persists this
     * same computation for real.
     *
     * @return array{0: float, 1: array}
     */
    private function activeLoanDeductions(int $employeeId): array
    {
        $rows  = (new EmployeeLoanModel())->activeForEmployee($employeeId);
        $total = 0.0;
        $lines = [];

        foreach ($rows as $row) {
            $deduction = round(min((float) $row['monthly_amortization'], (float) $row['balance_remaining']), 2);
            if ($deduction <= 0) {
                continue;
            }

            $total += $deduction;
            $lines[] = [
                'loan_id'        => (int) $row['id'],
                'loan_type'      => $row['loan_type'],
                'amount'         => $deduction,
                'balance_before' => (float) $row['balance_remaining'],
            ];
        }

        return [$total, $lines];
    }
}
