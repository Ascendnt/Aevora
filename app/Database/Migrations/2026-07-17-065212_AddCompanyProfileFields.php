<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCompanyProfileFields extends Migration
{
    public function up(): void
    {
        $fields = [
            'logo_path'              => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'date_established'       => ['type' => 'DATE', 'null' => true],
            'company_size'           => ['type' => 'INT', 'null' => true],
            'sec_dti_number'         => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'sss_number'             => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'philhealth_number'      => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'pagibig_number'         => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'business_permit_number' => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
            'rdo_code'               => ['type' => 'VARCHAR', 'constraint' => 10, 'null' => true],
        ];

        // A previous, since-corrected migration already added these columns on
        // some databases - only add whatever is actually still missing.
        $existing = $this->db->getFieldNames('companies');
        $missing  = array_diff_key($fields, array_flip($existing));

        if ($missing !== []) {
            $this->forge->addColumn('companies', $missing);
        }
    }

    public function down(): void
    {
        $columns  = [
            'logo_path', 'date_established', 'company_size', 'sec_dti_number',
            'sss_number', 'philhealth_number', 'pagibig_number',
            'business_permit_number', 'rdo_code',
        ];
        $existing = $this->db->getFieldNames('companies');
        $present  = array_intersect($columns, $existing);

        if ($present !== []) {
            $this->forge->dropColumn('companies', $present);
        }
    }
}
