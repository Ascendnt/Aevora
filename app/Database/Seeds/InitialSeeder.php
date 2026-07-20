<?php

namespace App\Database\Seeds;

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

        // Default admin — change the password after first login.
        $this->db->table('users')->insert([
            'name'          => 'Maria Reyes',
            'email'         => 'admin@hris.test',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        // Demo company
        $this->db->table('companies')->insert([
            'name'         => 'Acme Corp',
            'legal_name'   => 'Acme Corporation, Inc.',
            'industry'     => 'Retail',
            'email'        => 'hello@acmecorp.ph',
            'phone'        => '+63 2 8123 4567',
            'address_line' => '6789 Ayala Avenue',
            'city'         => 'Makati',
            'province'     => 'Metro Manila',
            'country'      => 'Philippines',
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
    }
}
