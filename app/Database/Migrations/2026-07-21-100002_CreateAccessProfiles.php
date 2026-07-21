<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAccessProfiles extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('access_profiles')) {
            $this->forge->addField([
                'id'         => ['type' => 'BIGINT', 'auto_increment' => true],
                'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
                'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('name');
            $this->forge->createTable('access_profiles');
        }

        if (! $this->db->tableExists('access_profile_modules')) {
            $this->forge->addField([
                'id'                => ['type' => 'BIGINT', 'auto_increment' => true],
                'access_profile_id' => ['type' => 'BIGINT'],
                'module_key'        => ['type' => 'VARCHAR', 'constraint' => 40],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['access_profile_id', 'module_key']);
            $this->forge->addForeignKey('access_profile_id', 'access_profiles', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('access_profile_modules');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('access_profile_modules', true);
        $this->forge->dropTable('access_profiles', true);
    }
}
