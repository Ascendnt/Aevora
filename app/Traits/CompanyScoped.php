<?php

namespace App\Traits;

use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Shared row-level ownership check: non-superadmins may only touch
 * records that belong to their own company.
 */
trait CompanyScoped
{
    private function assertOwnsCompany(int $companyId): void
    {
        $scoped = scoped_company_id();

        if ($scoped !== null && $scoped !== $companyId) {
            throw PageNotFoundException::forPageNotFound();
        }
    }
}
