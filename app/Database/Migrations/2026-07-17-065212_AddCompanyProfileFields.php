<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompanyProfileFields extends Migration
{
    public function up(): void
    {
        $this->forge->addColumn('companies', [
            'logo_path'              => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'date_established'       => ['type' => 'DATE', 'null' => true],
            'company_size'           => ['type' => 'INT', 'null' => true],
            'sec_dti_number'         => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'sss_number'             => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'philhealth_number'      => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'pagibig_number'         => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'business_permit_number' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'rdo_code'               => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('companies', [
            'logo_path', 'date_established', 'company_size', 'sec_dti_number',
            'sss_number', 'philhealth_number', 'pagibig_number',
            'business_permit_number', 'rdo_code',
        ]);
    }
}
