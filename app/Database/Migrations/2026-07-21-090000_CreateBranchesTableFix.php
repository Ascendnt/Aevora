<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * A previous version of CreateBranches (2026-07-17-000003) had its table
 * creation code accidentally replaced by a duplicate of the company profile
 * fields migration, so the "branches" table was never actually created on
 * databases that already ran that migration version. This creates it if
 * still missing; it's a no-op on databases where the corrected
 * CreateBranches migration already created the table.
 */
class CreateBranchesTableFix extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('branches')) {
            return;
        }

        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'auto_increment' => true],
            'company_id'   => ['type' => 'BIGINT'],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 190],
            'code'         => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'is_hq'        => ['type' => 'BOOLEAN', 'default' => false],
            'email'        => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'phone'        => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'address_line' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'city'         => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'province'     => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'postal_code'  => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'country'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'default' => 'Philippines'],
            'status'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
            'created_at'   => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'   => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('branches');
    }

    public function down(): void
    {
        if ($this->db->tableExists('branches')) {
            $this->forge->dropTable('branches');
        }
    }
}
