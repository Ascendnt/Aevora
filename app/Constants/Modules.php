<?php

namespace App\Constants;

/**
 * Single source of truth for the module keys used by access profiles,
 * the module-access filter, and the sidebar nav.
 */
class Modules
{
    public const EMPLOYEES           = 'employees';
    public const DOCUMENTS           = 'documents';
    public const TIME_ATTENDANCE     = 'time_attendance';
    public const FILINGS             = 'filings';
    public const PAYROLL             = 'payroll';
    public const COMPANY_SETTINGS    = 'company_settings';
    public const EMPLOYEE_MANAGEMENT = 'employee_management';

    /** @return array<string, string> module key => human label */
    public static function all(): array
    {
        return [
            self::EMPLOYEES           => 'Employees',
            self::DOCUMENTS           => 'Documents',
            self::TIME_ATTENDANCE     => 'Time & Attendance',
            self::FILINGS             => 'Filings',
            self::PAYROLL             => 'Payroll',
            self::COMPANY_SETTINGS    => 'Company Settings',
            self::EMPLOYEE_MANAGEMENT => 'Employee Management',
        ];
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function isValid(string $key): bool
    {
        return array_key_exists($key, self::all());
    }

    /**
     * Finer-grained access within a module — lets an access profile or an
     * individual employee grant be scoped to just one part of a module
     * (e.g. "Holidays" within Time & Attendance) instead of all-or-nothing.
     * Stored as plain strings in the same access_profile_modules /
     * employee_module_access tables as top-level module keys — no schema
     * change needed, just a wider set of valid key strings.
     *
     * @return array<string, array<string, string>> parent module key => [sub key => label]
     */
    public static function subModules(): array
    {
        return [
            self::TIME_ATTENDANCE => [
                'time_attendance.schedules' => 'Work Schedules',
                'time_attendance.holidays'  => 'Holidays',
                'time_attendance.cutoff'    => 'Cutoff Schedules',
                'time_attendance.policies'  => 'Attendance Policies',
            ],
            self::FILINGS => [
                'filings.leave_types' => 'Leave Types Setup',
            ],
            self::PAYROLL => [
                'payroll.runs'     => 'Payroll Runs',
                'payroll.benefits' => 'Benefits',
                'payroll.loans'    => 'Loans',
            ],
            self::EMPLOYEE_MANAGEMENT => [
                'employee_management.job_levels' => 'Job Levels',
                'employee_management.ranks'      => 'Employee Ranks',
            ],
            self::DOCUMENTS => [
                'documents.templates' => 'Document Templates',
            ],
        ];
    }

    /** @return string[] every sub-module key across every parent, flattened. */
    public static function allSubKeys(): array
    {
        $keys = [];
        foreach (self::subModules() as $subs) {
            $keys = array_merge($keys, array_keys($subs));
        }

        return $keys;
    }

    /** A top-level module key OR a valid sub-module key. */
    public static function isValidAny(string $key): bool
    {
        return self::isValid($key) || in_array($key, self::allSubKeys(), true);
    }
}
