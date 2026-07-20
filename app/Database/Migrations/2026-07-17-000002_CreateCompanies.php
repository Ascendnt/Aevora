<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCompanies extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'auto_increment' => true],
            'name'         => ['type' => 'VARCHAR', 'constraint' => 190],
            'legal_name'   => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'tin'          => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'industry'     => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'email'        => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'phone'        => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'website'      => ['type' => 'VARCHAR', 'constraint' => 190, 'null' => true],
            'address_line' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'city'         => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'province'     => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
            'postal_code'  => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'country'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true, 'default' => 'Philippines'],
            'created_at'   => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'   => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('companies');
    }

    public function down(): void
    {
        $this->forge->dropTable('companies');
    }
}
