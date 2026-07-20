<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBranches extends Migration
{
    // public function up(): void
    // {
    //     $this->forge->addField([
    //         'id'           => ['type' => 'BIGINT', 'auto_increment' => true],
    //         'company_id'   => ['type' => 'BIGINT'],
    //         'name'         => ['type' => 'VARCHAR', 'constraint' => 190],
    //         'code'         => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
    //         'is_hq'        => ['type' => 'BOOLEAN', 'default' => false],
    //         'email'        => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
    //         'phone'        => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
    //         'address_line' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
    //         'city'         => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
    //         'province'     => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
    //         'postal_code'  => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
    //         'country'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'default' => 'Philippines'],
    //         'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
    //         'created_at'   => ['type' => 'TIMESTAMP', 'null' => true],
    //         'updated_at'   => ['type' => 'TIMESTAMP', 'null' => true],
    //     ]);
    //     $this->forge->addKey('id', true);
    //     $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
    //     $this->forge->createTable('branches');
    // }

    // public function down(): void
    // {
    //     $this->forge->dropTable('branches');
    // }


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
