<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDepartmentsAndPositions extends Migration
{
public function up(): void
{
    $this->forge->addField([
        'id'         => ['type' => 'BIGINT', 'auto_increment' => true],
        'company_id' => ['type' => 'BIGINT'],
        'name'       => ['type' => 'VARCHAR', 'constraint' => 190],
        'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
        'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
    $this->forge->createTable('departments');

    $this->forge->addField([
        'id'            => ['type' => 'BIGINT', 'auto_increment' => true],
        'company_id'    => ['type' => 'BIGINT'],
        'department_id' => ['type' => 'BIGINT', 'null' => true],
        'title'         => ['type' => 'VARCHAR', 'constraint' => 190],
        'created_at'    => ['type' => 'TIMESTAMP', 'null' => true],
        'updated_at'    => ['type' => 'TIMESTAMP', 'null' => true],
    ]);
    $this->forge->addKey('id', true);
    $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
    $this->forge->addForeignKey('department_id', 'departments', 'id', 'SET NULL', 'CASCADE');
    $this->forge->createTable('positions');
}

public function down(): void
{
    $this->forge->dropTable('positions');
    $this->forge->dropTable('departments');
}
}
