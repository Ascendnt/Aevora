<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Route filter: ['filter' => 'module:employee_management'] requires the
 * logged-in user to have that module key (superadmins always pass).
 */
class ModuleAccessFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $moduleKey = $arguments[0] ?? null;

        if ($moduleKey === null || can_access($moduleKey)) {
            return;
        }

        return redirect()->to('/dashboard')->with('error', "You don't have access to that section.");
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
