<?php

use App\Constants\Modules;
use App\Models\AccessProfileModel;
use App\Models\CompanyModel;
use App\Models\EmployeeModel;

if (! function_exists('db_bool')) {
    /**
     * CodeIgniter's Postgres driver returns BOOLEAN columns as literal
     * 't'/'f' strings, not PHP booleans. PHP's (bool) cast and empty()
     * both treat the string "f" as truthy, so any naive truthy check on a
     * boolean column value is silently wrong. Use this instead.
     */
    function db_bool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower((string) $value), ['1', 't', 'true', 'y', 'yes'], true);
    }
}

if (! function_exists('is_superadmin')) {
    function is_superadmin(): bool
    {
        return (bool) session()->get('is_superadmin');
    }
}

if (! function_exists('current_employee')) {
    /** The logged-in user's employee row, or null for superadmins/accounts with none. */
    function current_employee(): ?array
    {
        static $cached  = null;
        static $resolved = false;

        if ($resolved) {
            return $cached;
        }
        $resolved = true;

        if (is_superadmin()) {
            return $cached = null;
        }

        $userId = session()->get('user_id');

        return $cached = $userId ? (new EmployeeModel())->findByUserId((int) $userId) : null;
    }
}

if (! function_exists('scoped_company_id')) {
    /** Null means "no restriction" (superadmin); otherwise the employee's own company id. */
    function scoped_company_id(): ?int
    {
        if (is_superadmin()) {
            return null;
        }

        $employee = current_employee();

        return $employee ? (int) $employee['company_id'] : null;
    }
}

if (! function_exists('effective_modules')) {
    /** Module keys this user can use: their access profile's modules plus any individual grants. */
    function effective_modules(): array
    {
        static $cached   = null;
        static $resolved = false;

        if ($resolved) {
            return $cached;
        }
        $resolved = true;

        if (is_superadmin()) {
            return $cached = Modules::keys();
        }

        $employee = current_employee();
        if (! $employee) {
            return $cached = [];
        }

        $profileModules = $employee['access_profile_id']
            ? (new AccessProfileModel())->moduleKeys((int) $employee['access_profile_id'])
            : [];
        $individual = (new EmployeeModel())->individualModules((int) $employee['id']);

        return $cached = array_values(array_unique(array_merge($profileModules, $individual)));
    }
}

if (! function_exists('can_access')) {
    function can_access(string $moduleKey): bool
    {
        return is_superadmin() || in_array($moduleKey, effective_modules(), true);
    }
}

if (! function_exists('hq_company_name')) {
    function hq_company_name(): string
    {
        static $cached   = null;
        static $resolved = false;

        if ($resolved) {
            return $cached;
        }
        $resolved = true;

        $hq = (new CompanyModel())->hqCompany();

        return $cached = $hq['name'] ?? 'Aevora';
    }
}
