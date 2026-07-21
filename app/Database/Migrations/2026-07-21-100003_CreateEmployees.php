<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEmployees extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('employees')) {
            $this->forge->addField([
                'id'                => ['type' => 'BIGINT', 'auto_increment' => true],
                'user_id'           => ['type' => 'BIGINT'],
                'company_id'        => ['type' => 'BIGINT'],
                'branch_id'         => ['type' => 'BIGINT', 'null' => true],
                'department_id'     => ['type' => 'BIGINT', 'null' => true],
                'position_id'       => ['type' => 'BIGINT', 'null' => true],
                'access_profile_id' => ['type' => 'BIGINT', 'null' => true],
                'employee_number'   => ['type' => 'VARCHAR', 'constraint' => 60, 'null' => true],
                'status'            => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
                'hire_date'         => ['type' => 'DATE', 'null' => true],
                'created_at'        => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'        => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('user_id');
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('branch_id', 'branches', 'id', 'SET NULL', 'CASCADE');
            $this->forge->addForeignKey('department_id', 'departments', 'id', 'SET NULL', 'CASCADE');
            $this->forge->addForeignKey('position_id', 'positions', 'id', 'SET NULL', 'CASCADE');
            $this->forge->addForeignKey('access_profile_id', 'access_profiles', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('employees');
        }

        if (! $this->db->tableExists('employee_module_access')) {
            $this->forge->addField([
                'id'          => ['type' => 'BIGINT', 'auto_increment' => true],
                'employee_id' => ['type' => 'BIGINT'],
                'module_key'  => ['type' => 'VARCHAR', 'constraint' => 40],
                'created_at'  => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['employee_id', 'module_key']);
            $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('employee_module_access');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('employee_module_access', true);
        $this->forge->dropTable('employees', true);
    }
}
