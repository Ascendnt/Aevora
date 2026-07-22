<?php

namespace App\Database\Seeds;

use App\Constants\Modules;
use CodeIgniter\Database\Seeder;

class InitialSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotent: skip if already seeded (safe to run on every container start).
        if ($this->db->table('users')->countAllResults() > 0) {
            echo "Already seeded — skipping.\n";

            return;
        }

        $now = date('Y-m-d H:i:s');

        // Default superadmin — change the password after first login. Not an
        // employee: excluded from employee counts, sees/manages everything.
        $this->db->table('users')->insert([
            'name'          => 'Maria Reyes',
            'email'         => 'admin@hris.test',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'is_superadmin' => true,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // Demo HQ company
        $this->db->table('companies')->insert([
            'name'         => 'Aveora',
            'legal_name'   => 'Aveora Corporation, Inc.',
            'industry'     => 'Retail',
            'email'        => 'hello@aevora.ph',
            'phone'        => '+63 2 8123 4567',
            'address_line' => '6789 Ayala Avenue',
            'city'         => 'Makati',
            'province'     => 'Metro Manila',
            'country'      => 'Philippines',
            'is_hq'        => true,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);
        $companyId = $this->db->insertID();

        $this->db->table('branches')->insertBatch([
            [
                'company_id' => $companyId, 'name' => 'Main Branch', 'code' => 'MKT-01',
                'is_hq' => true, 'city' => 'Makati', 'province' => 'Metro Manila',
                'status' => 'active', 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'company_id' => $companyId, 'name' => 'Cebu Branch', 'code' => 'CEB-01',
                'is_hq' => false, 'city' => 'Cebu City', 'province' => 'Cebu',
                'status' => 'active', 'created_at' => $now, 'updated_at' => $now,
            ],
            [
                'company_id' => $companyId, 'name' => 'Davao Branch', 'code' => 'DVO-01',
                'is_hq' => false, 'city' => 'Davao City', 'province' => 'Davao del Sur',
                'status' => 'active', 'created_at' => $now, 'updated_at' => $now,
            ],
        ]);

        // Default access profiles, matching the examples used while designing this.
        $this->db->table('access_profiles')->insert(['name' => 'HR', 'created_at' => $now, 'updated_at' => $now]);
        $hrId = $this->db->insertID();
        $this->db->table('access_profile_modules')->insertBatch(
            array_map(static fn ($key) => ['access_profile_id' => $hrId, 'module_key' => $key], Modules::keys()),
        );

        $this->db->table('access_profiles')->insert(['name' => 'Employee', 'created_at' => $now, 'updated_at' => $now]);
        $employeeId = $this->db->insertID();
        $this->db->table('access_profile_modules')->insertBatch([
            ['access_profile_id' => $employeeId, 'module_key' => Modules::TIME_ATTENDANCE],
            ['access_profile_id' => $employeeId, 'module_key' => Modules::FILINGS],
        ]);
    }
}
