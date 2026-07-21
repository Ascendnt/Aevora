<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Temporary read-only diagnostic: dump users + their linked employee row.
 * Uses the app's own already-configured DB connection, not a raw pg_connect.
 */
class DebugUsers extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'debug:users';
    protected $description = 'Dump users + linked employee rows for debugging.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        $rows = $db->table('users u')
            ->select('u.id, u.name, u.email, u.is_superadmin, e.id AS employee_id, e.access_profile_id, e.company_id, e.status AS employee_status')
            ->join('employees e', 'e.user_id = u.id', 'left')
            ->orderBy('u.id', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            CLI::write(json_encode($row));
        }

        CLI::write('Total: ' . count($rows));
    }
}
