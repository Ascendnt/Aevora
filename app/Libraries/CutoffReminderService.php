<?php

namespace App\Libraries;

use App\Models\NotificationModel;
use Config\Database;
use DateTimeImmutable;
use Throwable;

/**
 * Computes upcoming payroll cutoff dates from cutoff_schedules and turns
 * the ones that are due soon into notifications.
 *
 * period_config is a free-form JSON TEXT column — nothing else in this app
 * has settled on a fixed shape for it yet, so every read here is
 * defensive: unknown/missing keys fall back to sane defaults instead of
 * throwing, and any malformed JSON is treated as an empty config.
 *
 * Recognised (all optional) period_config keys:
 *   - period_end     : explicit ISO date string, wins over everything else
 *   - day_of_month    (monthly)       : 1-31, or "last"/"eom"/"end"
 *   - cutoff_day      (monthly)       : alias for day_of_month
 *   - cutoff_days     (semi_monthly)  : array of day_of_month-style values,
 *                                       e.g. [15, "last"]
 *   - days            (semi_monthly)  : alias for cutoff_days
 *   - weekday         (weekly)        : 0 (Sun) - 6 (Sat), or a day name
 *   - day_of_week     (weekly)        : alias for weekday
 */
class CutoffReminderService
{
    /**
     * Cutoff schedules for a company whose upcoming period end falls
     * within their own reminder_days_before window.
     *
     * @return array<int, array{schedule: array, period_end: string, days_until: int}>
     */
    public function pendingReminders(int $companyId): array
    {
        $schedules = Database::connect()
            ->table('cutoff_schedules')
            ->where('company_id', $companyId)
            ->get()
            ->getResultArray();

        if ($schedules === []) {
            return [];
        }

        $today   = new DateTimeImmutable('today');
        $pending = [];

        foreach ($schedules as $schedule) {
            $periodEnd = $this->nextPeriodEnd($schedule, $today);
            if ($periodEnd === null) {
                continue; // couldn't make sense of this schedule — skip it, don't crash
            }

            $daysUntil    = (int) $today->diff($periodEnd)->days;
            $reminderDays = (int) ($schedule['reminder_days_before'] ?? 0);

            if ($daysUntil <= $reminderDays) {
                $pending[] = [
                    'schedule'   => $schedule,
                    'period_end' => $periodEnd->format('Y-m-d'),
                    'days_until' => $daysUntil,
                ];
            }
        }

        return $pending;
    }

    /**
     * Creates a notification for every active employee's user in the
     * company for each pending reminder, skipping anyone who already has
     * an unread notification for that same schedule + period. Returns the
     * number of notifications actually inserted.
     *
     * NOTE: cutoff_schedules can be scoped to a department/branch/employee
     * via scope_type/scope_id, but per spec this fans out to every active
     * employee in the company regardless of that scope — a future
     * refinement could narrow the recipient list using scope_type/scope_id.
     */
    public function syncNotifications(int $companyId): int
    {
        $reminders = $this->pendingReminders($companyId);
        if ($reminders === []) {
            return 0;
        }

        $userIds = array_column(
            Database::connect()
                ->table('employees e')
                ->select('u.id AS user_id')
                ->join('users u', 'u.id = e.user_id')
                ->where('e.company_id', $companyId)
                ->where('e.status', 'active')
                ->get()
                ->getResultArray(),
            'user_id',
        );

        if ($userIds === []) {
            return 0;
        }

        $notifications = new NotificationModel();
        $type          = 'cutoff_reminder';
        $created       = 0;

        foreach ($reminders as $reminder) {
            $schedule = $reminder['schedule'];
            $link     = '/payroll?cutoff_schedule=' . $schedule['id'] . '&period_end=' . $reminder['period_end'];
            $message  = $this->reminderMessage($schedule, $reminder);

            foreach ($userIds as $userId) {
                $userId = (int) $userId;

                if ($notifications->existsUnread($userId, $type, $link)) {
                    continue; // already has an unread reminder for this exact schedule + period
                }

                $notifications->insert([
                    'user_id' => $userId,
                    'message' => $message,
                    'type'    => $type,
                    'link'    => $link,
                    'is_read' => false,
                ]);
                $created++;
            }
        }

        return $created;
    }

    private function reminderMessage(array $schedule, array $reminder): string
    {
        $frequencyLabel = ucfirst(str_replace('_', ' ', (string) ($schedule['frequency'] ?? 'payroll')));
        $periodEnd      = $reminder['period_end'];
        $daysUntil      = (int) $reminder['days_until'];

        $when = $daysUntil <= 0 ? 'today' : ($daysUntil === 1 ? 'in 1 day' : "in {$daysUntil} days");

        $payDateNote = '';
        $offset      = (int) ($schedule['pay_date_offset_days'] ?? 0);

        if ($offset > 0) {
            try {
                $payDate     = (new DateTimeImmutable($periodEnd))->modify("+{$offset} days")->format('M j, Y');
                $payDateNote = " Pay date: {$payDate}.";
            } catch (Throwable) {
                // Malformed offset/date — the message is still useful without it.
            }
        }

        return "{$frequencyLabel} payroll cutoff ends {$periodEnd} ({$when}).{$payDateNote}";
    }

    /** @return DateTimeImmutable|null Null means this schedule's config couldn't be interpreted. */
    private function nextPeriodEnd(array $schedule, DateTimeImmutable $today): ?DateTimeImmutable
    {
        try {
            $config    = $this->decodeConfig($schedule['period_config'] ?? null);
            $frequency = (string) ($schedule['frequency'] ?? '');

            // An explicit override always wins, whatever the frequency says.
            if (! empty($config['period_end']) && is_string($config['period_end'])) {
                $explicit = $this->parseDate($config['period_end']);
                if ($explicit !== null && $explicit >= $today) {
                    return $explicit;
                }
            }

            return match ($frequency) {
                'weekly'       => $this->nextWeekly($config, $today),
                'semi_monthly' => $this->nextSemiMonthly($config, $today),
                default        => $this->nextMonthly($config, $today), // 'monthly' and any unrecognised value
            };
        } catch (Throwable) {
            return null;
        }
    }

    private function nextMonthly(array $config, DateTimeImmutable $today): DateTimeImmutable
    {
        $day       = $config['day_of_month'] ?? $config['cutoff_day'] ?? null;
        $candidate = $this->dateForDayInMonth($today, $day);

        if ($candidate < $today) {
            $candidate = $this->dateForDayInMonth($today->modify('first day of next month'), $day);
        }

        return $candidate;
    }

    private function nextSemiMonthly(array $config, DateTimeImmutable $today): DateTimeImmutable
    {
        $rawDays = $config['cutoff_days'] ?? $config['days'] ?? null;
        if (! is_array($rawDays) || $rawDays === []) {
            $rawDays = [15, 'last'];
        }

        $candidates = [];
        foreach ([$today, $today->modify('first day of next month')] as $monthRef) {
            foreach ($rawDays as $day) {
                $candidates[] = $this->dateForDayInMonth($monthRef, $day);
            }
        }

        usort($candidates, static fn (DateTimeImmutable $a, DateTimeImmutable $b) => $a <=> $b);

        foreach ($candidates as $candidate) {
            if ($candidate >= $today) {
                return $candidate;
            }
        }

        return $today; // shouldn't happen (the "next month" pass always yields something upcoming)
    }

    private function nextWeekly(array $config, DateTimeImmutable $today): DateTimeImmutable
    {
        $targetDow = $this->normalizeWeekday($config['weekday'] ?? $config['day_of_week'] ?? 'Friday');
        $todayDow  = (int) $today->format('w');
        $diff      = ($targetDow - $todayDow + 7) % 7;

        return $today->modify("+{$diff} days");
    }

    /** Resolves a day-of-month spec ("last"/"eom"/"end", or 1-31) against a given month, clamped to that month's length. */
    private function dateForDayInMonth(DateTimeImmutable $monthRef, mixed $day): DateTimeImmutable
    {
        $firstOfMonth = $monthRef->modify('first day of this month');
        $lastDay      = (int) $firstOfMonth->format('t');

        if ($day === null || (is_string($day) && in_array(strtolower($day), ['last', 'eom', 'end'], true))) {
            $dayNum = $lastDay;
        } else {
            $dayNum = max(1, min((int) $day, $lastDay));
        }

        return $firstOfMonth->modify('+' . ($dayNum - 1) . ' days');
    }

    private function normalizeWeekday(mixed $value): int
    {
        if (is_numeric($value)) {
            $n = ((int) $value) % 7;

            return $n < 0 ? $n + 7 : $n;
        }

        $names = [
            'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
            'thursday' => 4, 'friday' => 5, 'saturday' => 6,
            'sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6,
        ];

        return $names[strtolower(trim((string) $value))] ?? 5; // default Friday
    }

    private function parseDate(string $value): ?DateTimeImmutable
    {
        try {
            return (new DateTimeImmutable($value))->setTime(0, 0);
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array<string, mixed> */
    private function decodeConfig(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
