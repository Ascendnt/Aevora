<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * A fully-connected demo dataset — one non-HQ company built out end-to-end
 * (org structure, schedules, cutoff, attendance policy, holiday, 5 employees
 * with real reporting lines and pay, attendance history, filings including
 * the new overtime type, a pending profile-change request, benefits/loans,
 * and a draft payroll run) so the whole system can be explored with
 * connected, realistic data instead of empty screens.
 *
 * Idempotent: skips entirely if the demo company already exists, so it's
 * safe to run on every container start alongside InitialSeeder.
 */
class DemoDataSeeder extends Seeder
{
    private const DEMO_COMPANY_NAME = 'Southbay Retail Solutions';

    public function run(): void
    {
        if ($this->db->table('companies')->where('name', self::DEMO_COMPANY_NAME)->countAllResults() > 0) {
            echo "Demo data already seeded — skipping.\n";

            return;
        }

        $now = date('Y-m-d H:i:s');

        $companyId = $this->seedCompany($now);
        $this->seedBranch($companyId, $now);
        [$salesDeptId, $opsDeptId] = $this->seedDepartments($companyId, $now);
        [$posSales, $posOps, $posSupervisor] = $this->seedPositions($companyId, $salesDeptId, $opsDeptId, $now);
        [$levelJunior, $levelSenior] = $this->seedJobLevels($companyId, $now);
        [$rankStaff, $rankSupervisor] = $this->seedRanks($companyId, $now);
        [$scheduleDay, $scheduleExec] = $this->seedSchedules($companyId, $now);
        $this->seedCutoff($companyId, $now);
        $this->seedAttendancePolicy($companyId, $now);
        $holidayDate = $this->seedHoliday($companyId, $now);
        [$leaveVacation, $leaveSick] = $this->seedLeaveTypes($companyId, $now);

        $hrProfileId       = $this->accessProfileId('HR');
        $employeeProfileId = $this->accessProfileId('Employee');

        $anna = $this->seedEmployee($companyId, $now, [
            'name' => 'Anna Cruz', 'email' => 'anna.cruz@southbay-demo.test',
            'position_id' => $posSupervisor, 'department_id' => $salesDeptId,
            'job_level_id' => $levelSenior, 'employee_rank_id' => $rankStaff,
            'work_schedule_id' => $scheduleExec, 'access_profile_id' => $hrProfileId,
            'supervisor_id' => null, 'hire_date' => date('Y-m-d', strtotime('-2 years')),
            'basic_pay' => 45000, 'is_minimum_wage_earner' => false, 'approval_level' => 1,
            'employee_number' => 'SB-0001',
        ]);

        $miguel = $this->seedEmployee($companyId, $now, [
            'name' => 'Miguel Santos', 'email' => 'miguel.santos@southbay-demo.test',
            'position_id' => $posSales, 'department_id' => $salesDeptId,
            'job_level_id' => $levelJunior, 'employee_rank_id' => $rankStaff,
            'work_schedule_id' => $scheduleDay, 'access_profile_id' => $employeeProfileId,
            'supervisor_id' => $anna, 'hire_date' => date('Y-m-d', strtotime('-1 year')),
            'basic_pay' => 19000, 'is_minimum_wage_earner' => true, 'approval_level' => null,
            'employee_number' => 'SB-0002',
        ]);

        $liza = $this->seedEmployee($companyId, $now, [
            'name' => 'Liza Fernandez', 'email' => 'liza.fernandez@southbay-demo.test',
            'position_id' => $posOps, 'department_id' => $opsDeptId,
            'job_level_id' => $levelJunior, 'employee_rank_id' => $rankStaff,
            'work_schedule_id' => $scheduleDay, 'access_profile_id' => $employeeProfileId,
            'supervisor_id' => $anna, 'hire_date' => date('Y-m-d', strtotime('-8 months')),
            'basic_pay' => 22000, 'is_minimum_wage_earner' => false, 'approval_level' => null,
            'employee_number' => 'SB-0003',
        ]);

        $paolo = $this->seedEmployee($companyId, $now, [
            'name' => 'Paolo Reyes', 'email' => 'paolo.reyes@southbay-demo.test',
            'position_id' => $posSales, 'department_id' => $salesDeptId,
            'job_level_id' => $levelJunior, 'employee_rank_id' => $rankStaff,
            'work_schedule_id' => $scheduleDay, 'access_profile_id' => $employeeProfileId,
            'supervisor_id' => $anna, 'hire_date' => date('Y-m-d', strtotime('-6 months')),
            'basic_pay' => 21000, 'is_minimum_wage_earner' => false, 'approval_level' => null,
            'employee_number' => 'SB-0004',
        ]);

        $carla = $this->seedEmployee($companyId, $now, [
            'name' => 'Carla Mendoza', 'email' => 'carla.mendoza@southbay-demo.test',
            'position_id' => $posOps, 'department_id' => $opsDeptId,
            'job_level_id' => $levelJunior, 'employee_rank_id' => $rankSupervisor,
            'work_schedule_id' => $scheduleDay, 'access_profile_id' => $employeeProfileId,
            'supervisor_id' => $anna, 'hire_date' => date('Y-m-d', strtotime('-3 months')),
            'basic_pay' => 23000, 'is_minimum_wage_earner' => false, 'approval_level' => null,
            'employee_number' => 'SB-0005',
        ]);

        $this->seedAttendance($miguel, $holidayDate, $now);
        $this->seedAttendance($liza, $holidayDate, $now);
        $this->seedAttendance($paolo, $holidayDate, $now);
        $this->seedAttendance($carla, $holidayDate, $now);
        $this->seedAttendance($anna, $holidayDate, $now);

        $this->seedLeaveBalance($miguel, $leaveVacation, $now);
        $this->seedLeaveFiling($miguel, $anna, $leaveVacation, $now);
        $this->seedOfficialBusinessFiling($liza, $anna, $now);
        $this->seedOvertimeFiling($carla, $anna, $now);
        $this->seedProfileChangeRequest($paolo, $now);

        $this->seedBenefit($miguel, $now);
        $this->seedLoan($liza, $now);

        $this->seedPayrollRun($companyId, $now);

        echo "Demo data seeded: {$companyId} / 5 employees.\n";
    }

    private function seedCompany(string $now): int
    {
        $this->db->table('companies')->insert([
            'name' => self::DEMO_COMPANY_NAME, 'legal_name' => 'Southbay Retail Solutions, Inc.',
            'industry' => 'Retail', 'email' => 'hello@southbay-demo.test', 'phone' => '+63 32 234 5678',
            'address_line' => '88 Osmeña Boulevard', 'city' => 'Cebu City', 'province' => 'Cebu',
            'country' => 'Philippines', 'country_code' => 'PH', 'max_approval_levels' => 5,
            'is_hq' => false, 'created_at' => $now, 'updated_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    private function seedBranch(int $companyId, string $now): void
    {
        $this->db->table('branches')->insert([
            'company_id' => $companyId, 'name' => 'Southbay Main Branch', 'code' => 'SB-MAIN',
            'is_hq' => true, 'city' => 'Cebu City', 'province' => 'Cebu', 'status' => 'active',
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    /** @return array{0: int, 1: int} [salesDeptId, opsDeptId] */
    private function seedDepartments(int $companyId, string $now): array
    {
        $this->db->table('departments')->insert(['company_id' => $companyId, 'name' => 'Sales', 'created_at' => $now, 'updated_at' => $now]);
        $sales = (int) $this->db->insertID();
        $this->db->table('departments')->insert(['company_id' => $companyId, 'name' => 'Operations', 'created_at' => $now, 'updated_at' => $now]);
        $ops = (int) $this->db->insertID();

        return [$sales, $ops];
    }

    /** @return array{0: int, 1: int, 2: int} [salesAssociate, opsStaff, storeSupervisor] */
    private function seedPositions(int $companyId, int $salesDeptId, int $opsDeptId, string $now): array
    {
        $this->db->table('positions')->insert(['company_id' => $companyId, 'department_id' => $salesDeptId, 'title' => 'Sales Associate', 'created_at' => $now, 'updated_at' => $now]);
        $sales = (int) $this->db->insertID();
        $this->db->table('positions')->insert(['company_id' => $companyId, 'department_id' => $opsDeptId, 'title' => 'Operations Staff', 'created_at' => $now, 'updated_at' => $now]);
        $ops = (int) $this->db->insertID();
        $this->db->table('positions')->insert(['company_id' => $companyId, 'department_id' => $salesDeptId, 'title' => 'Store Supervisor', 'created_at' => $now, 'updated_at' => $now]);
        $sup = (int) $this->db->insertID();

        return [$sales, $ops, $sup];
    }

    /** @return array{0: int, 1: int} [junior, senior] */
    private function seedJobLevels(int $companyId, string $now): array
    {
        $this->db->table('job_levels')->insert(['company_id' => $companyId, 'name' => 'Junior', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now]);
        $junior = (int) $this->db->insertID();
        $this->db->table('job_levels')->insert(['company_id' => $companyId, 'name' => 'Senior', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now]);
        $senior = (int) $this->db->insertID();

        return [$junior, $senior];
    }

    /** @return array{0: int, 1: int} [staff, supervisor] */
    private function seedRanks(int $companyId, string $now): array
    {
        $this->db->table('employee_ranks')->insert(['company_id' => $companyId, 'name' => 'Staff', 'is_exempt' => false, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now]);
        $staff = (int) $this->db->insertID();
        $this->db->table('employee_ranks')->insert(['company_id' => $companyId, 'name' => 'Supervisor', 'is_exempt' => false, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now]);
        $sup = (int) $this->db->insertID();

        return [$staff, $sup];
    }

    /** @return array{0: int, 1: int} [dayShift, executive] */
    private function seedSchedules(int $companyId, string $now): array
    {
        $this->db->table('work_schedules')->insert([
            'company_id' => $companyId, 'name' => 'Day Shift', 'time_in' => '08:00:00', 'time_out' => '17:00:00',
            'grace_minutes' => 10, 'break_minutes' => 60, 'schedule_type' => 'fixed', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $day = (int) $this->db->insertID();

        $this->db->table('work_schedules')->insert([
            'company_id' => $companyId, 'name' => 'Executive Schedule', 'time_in' => '09:00:00', 'time_out' => '18:00:00',
            'grace_minutes' => 15, 'break_minutes' => 60, 'schedule_type' => 'executive', 'created_at' => $now, 'updated_at' => $now,
        ]);
        $exec = (int) $this->db->insertID();

        return [$day, $exec];
    }

    private function seedCutoff(int $companyId, string $now): void
    {
        $this->db->table('cutoff_schedules')->insert([
            'company_id' => $companyId, 'scope_type' => 'company', 'scope_id' => null,
            'frequency' => 'semi_monthly', 'period_config' => json_encode(['cutoff_days' => [15, 'last']]),
            'pay_date_offset_days' => 5, 'reminder_days_before' => 3, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function seedAttendancePolicy(int $companyId, string $now): void
    {
        $this->db->table('attendance_policies')->insert([
            'company_id' => $companyId, 'name' => 'Standard Attendance Policy',
            'absent_before_holiday_forfeits_pay' => true, 'absent_after_holiday_forfeits_pay' => false,
            'consecutive_absence_alert_days' => 3, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    /** @return string the seeded holiday's date, so attendance can be arranged around it */
    private function seedHoliday(int $companyId, string $now): string
    {
        $date = date('Y-m-d', strtotime('-3 days'));

        $this->db->table('holidays')->insert([
            'company_id' => $companyId, 'name' => 'Company Foundation Day', 'date' => $date,
            'holiday_type' => 'special', 'scope_type' => 'national', 'source' => 'manual',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        return $date;
    }

    /** @return array{0: int, 1: int} [vacation, sick] */
    private function seedLeaveTypes(int $companyId, string $now): array
    {
        $this->db->table('leave_types')->insert(['company_id' => $companyId, 'name' => 'Vacation Leave', 'is_paid' => true, 'filing_rule' => 'before', 'min_days_notice' => 3, 'created_at' => $now, 'updated_at' => $now]);
        $vacation = (int) $this->db->insertID();
        $this->db->table('leave_types')->insert(['company_id' => $companyId, 'name' => 'Sick Leave', 'is_paid' => true, 'filing_rule' => 'after', 'min_days_notice' => 0, 'created_at' => $now, 'updated_at' => $now]);
        $sick = (int) $this->db->insertID();

        return [$vacation, $sick];
    }

    private function accessProfileId(string $name): ?int
    {
        $row = $this->db->table('access_profiles')->where('name', $name)->get()->getRowArray();

        return $row ? (int) $row['id'] : null;
    }

    private function seedEmployee(int $companyId, string $now, array $fields): int
    {
        $this->db->table('users')->insert([
            'name' => $fields['name'], 'email' => $fields['email'],
            'password_hash' => password_hash('Demo@12345', PASSWORD_DEFAULT),
            'is_superadmin' => false, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $userId = (int) $this->db->insertID();

        $this->db->table('employees')->insert([
            'user_id' => $userId, 'company_id' => $companyId,
            'department_id' => $fields['department_id'], 'position_id' => $fields['position_id'],
            'job_level_id' => $fields['job_level_id'], 'employee_rank_id' => $fields['employee_rank_id'],
            'work_schedule_id' => $fields['work_schedule_id'], 'access_profile_id' => $fields['access_profile_id'],
            'supervisor_id' => $fields['supervisor_id'], 'hire_date' => $fields['hire_date'],
            'basic_pay' => $fields['basic_pay'], 'pay_frequency' => 'monthly',
            'is_minimum_wage_earner' => $fields['is_minimum_wage_earner'], 'approval_level' => $fields['approval_level'],
            'employee_number' => $fields['employee_number'], 'status' => 'active',
            'created_at' => $now, 'updated_at' => $now,
        ]);

        return (int) $this->db->insertID();
    }

    /** Recent weekday attendance for one employee, including an unexcused absence the day before the holiday. */
    private function seedAttendance(int $employeeId, string $holidayDate, string $now): void
    {
        $dayBeforeHoliday = date('Y-m-d', strtotime($holidayDate . ' -1 day'));

        for ($i = 10; $i >= 1; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $dow  = (int) date('N', strtotime($date));

            if ($dow >= 6) {
                continue; // skip weekends for this simple fixed-schedule demo history
            }

            // The whole point of this date: leave it with no log at all, to
            // demonstrate the attendance policy's holiday-pay forfeiture.
            if ($date === $dayBeforeHoliday) {
                continue;
            }

            $timeIn  = $i % 4 === 0 ? '08:17:00' : '07:55:00'; // occasional lateness beyond the 10-min grace
            $timeOut = '17:05:00';

            $this->db->table('attendance_logs')->insert([
                'employee_id' => $employeeId, 'log_date' => $date, 'time_in' => $timeIn, 'time_out' => $timeOut,
                'source' => 'clock', 'timezone' => 'Asia/Manila', 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    private function seedLeaveBalance(int $employeeId, int $leaveTypeId, string $now): void
    {
        $this->db->table('leave_balances')->insert([
            'employee_id' => $employeeId, 'leave_type_id' => $leaveTypeId, 'year' => (int) date('Y'),
            'entitled_days' => 15, 'used_days' => 2, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function seedLeaveFiling(int $employeeId, int $approverId, int $leaveTypeId, string $now): void
    {
        $this->db->table('filings')->insert([
            'employee_id' => $employeeId, 'filing_type' => 'leave', 'leave_type_id' => $leaveTypeId,
            'days_count' => 2, 'status' => 'approved', 'approver_employee_id' => $approverId,
            'reason' => 'Family trip', 'filed_at' => date('Y-m-d H:i:s', strtotime('-9 days')),
            'decided_at' => date('Y-m-d H:i:s', strtotime('-9 days')), 'created_at' => $now, 'updated_at' => $now,
        ]);
        $filingId = (int) $this->db->insertID();

        $this->db->table('filing_dates')->insertBatch([
            ['filing_id' => $filingId, 'date' => date('Y-m-d', strtotime('-10 days'))],
            ['filing_id' => $filingId, 'date' => date('Y-m-d', strtotime('-9 days'))],
        ]);
    }

    private function seedOfficialBusinessFiling(int $employeeId, int $approverId, string $now): void
    {
        $this->db->table('filings')->insert([
            'employee_id' => $employeeId, 'filing_type' => 'official_business',
            'requested_time_in' => '09:00:00', 'requested_time_out' => '16:00:00',
            'status' => 'pending', 'approver_employee_id' => $approverId,
            'reason' => 'Supplier site visit', 'filed_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $filingId = (int) $this->db->insertID();

        $this->db->table('filing_dates')->insert(['filing_id' => $filingId, 'date' => date('Y-m-d', strtotime('+2 days'))]);
    }

    private function seedOvertimeFiling(int $employeeId, int $approverId, string $now): void
    {
        $this->db->table('filings')->insert([
            'employee_id' => $employeeId, 'filing_type' => 'overtime', 'overtime_hours' => 3,
            'status' => 'pending', 'approver_employee_id' => $approverId,
            'reason' => 'Month-end inventory count', 'filed_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'created_at' => $now, 'updated_at' => $now,
        ]);
        $filingId = (int) $this->db->insertID();

        $this->db->table('filing_dates')->insert(['filing_id' => $filingId, 'date' => date('Y-m-d', strtotime('-1 day'))]);
    }

    private function seedProfileChangeRequest(int $employeeId, string $now): void
    {
        $this->db->table('employee_profile_change_requests')->insert([
            'employee_id' => $employeeId,
            'requested_changes' => json_encode(['phone' => ['from' => '', 'to' => '+63 917 555 0102']]),
            'employee_note' => 'Updating my contact number.', 'status' => 'pending',
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function seedBenefit(int $employeeId, string $now): void
    {
        $this->db->table('employee_benefits')->insert([
            'employee_id' => $employeeId, 'benefit_type' => 'Rice Allowance', 'amount' => 1500,
            'is_recurring' => true, 'effective_date' => date('Y-m-d', strtotime('-1 year')),
            'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function seedLoan(int $employeeId, string $now): void
    {
        $this->db->table('employee_loans')->insert([
            'employee_id' => $employeeId, 'loan_type' => 'SSS Salary Loan', 'principal_amount' => 10000,
            'monthly_amortization' => 1000, 'balance_remaining' => 8000, 'start_date' => date('Y-m-d', strtotime('-2 months')),
            'status' => 'active', 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function seedPayrollRun(int $companyId, string $now): void
    {
        $day = (int) date('j');
        // Mirrors the semi-monthly cutoff_days [15, 'last'] configured above.
        if ($day <= 15) {
            $periodStart = date('Y-m-01');
            $periodEnd   = date('Y-m-15');
        } else {
            $periodStart = date('Y-m-16');
            $periodEnd   = date('Y-m-t');
        }

        $this->db->table('payroll_runs')->insert([
            'company_id' => $companyId, 'period_start' => $periodStart, 'period_end' => $periodEnd,
            'status' => 'draft', 'created_at' => $now, 'updated_at' => $now,
        ]);
    }
}
