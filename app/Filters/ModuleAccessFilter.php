<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Route filter: ['filter' => 'module:employee_management'] requires the
 * logged-in user to have that module key (superadmins always pass).
 *
 * Also supports a finer-grained form, ['filter' => 'module:time_attendance:holidays'],
 * which passes if the user has either the full parent module OR just that
 * specific sub-module grant (see Modules::subModules() / can_access_sub()).
 */
class ModuleAccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $moduleKey = $arguments[0] ?? null;
        $subKey    = $arguments[1] ?? null;

        if ($moduleKey === null) {
            return;
        }

        $allowed = $subKey !== null ? can_access_sub($subKey, $moduleKey) : can_access($moduleKey);

        if ($allowed) {
            return;
        }

        return redirect()->to('/dashboard')->with('error', "You don't have access to that section.");
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
