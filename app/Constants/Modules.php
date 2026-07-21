<?php

namespace App\Constants;

/**
 * Single source of truth for the module keys used by access profiles,
 * the module-access filter, and the sidebar nav.
 */
class Modules
{
    public const EMPLOYEES           = 'employees';
    public const TIME_ATTENDANCE     = 'time_attendance';
    public const LEAVE               = 'leave';
    public const PAYROLL             = 'payroll';
    public const COMPANY_SETTINGS    = 'company_settings';
    public const EMPLOYEE_MANAGEMENT = 'employee_management';

    /** @return array<string, string> module key => human label */
    public static function all(): array
    {
        return [
            self::EMPLOYEES           => 'Employees',
            self::TIME_ATTENDANCE     => 'Time & Attendance',
            self::LEAVE               => 'Leave',
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
}
