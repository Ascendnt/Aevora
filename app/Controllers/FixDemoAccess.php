<?php

namespace App\Controllers;

use App\Models\EmployeeModel;

/**
 * TEMPORARY one-off fix: the demo employees seeded before this endpoint
 * existed ended up with no module access (the seeder looked up 'HR'/
 * 'Employee' access profiles that no longer exist on this install). This
 * grants the same individual module access the corrected seeder now grants
 * directly, for the 5 already-seeded Southbay demo employees. Delete after use.
 */
class FixDemoAccess extends BaseController
{
    private const MANAGER_MODULES  = ['employees', 'documents', 'time_attendance', 'filings', 'payroll', 'company_settings', 'employee_management'];
    private const EMPLOYEE_MODULES = ['employees', 'time_attendance', 'filings'];

    public function run()
    {
        if (! is_superadmin()) {
            return $this->response->setStatusCode(403)->setBody('Forbidden');
        }

        $employees = new EmployeeModel();
        $emails    = [
            'anna.cruz@southbay-demo.test'     => self::MANAGER_MODULES,
            'miguel.santos@southbay-demo.test' => self::EMPLOYEE_MODULES,
            'liza.fernandez@southbay-demo.test' => self::EMPLOYEE_MODULES,
            'paolo.reyes@southbay-demo.test'   => self::EMPLOYEE_MODULES,
            'carla.mendoza@southbay-demo.test' => self::EMPLOYEE_MODULES,
        ];

        $results = [];

        foreach ($emails as $email => $modules) {
            $user = db_connect()->table('users')->where('email', $email)->get()->getRowArray();
            if (! $user) {
                $results[] = "{$email}: user not found";
                continue;
            }

            $employee = $employees->findByUserId((int) $user['id']);
            if (! $employee) {
                $results[] = "{$email}: employee not found";
                continue;
            }

            $employees->setIndividualModules((int) $employee['id'], $modules);
            $results[] = "{$email}: granted " . implode(', ', $modules);
        }

        return $this->response->setContentType('text/plain')->setBody(implode("\n", $results));
    }
}
