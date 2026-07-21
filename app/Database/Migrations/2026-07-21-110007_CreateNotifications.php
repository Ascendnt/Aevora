<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotifications extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('notifications')) {
            $this->forge->addField([
                'id'         => ['type' => 'BIGINT', 'auto_increment' => true],
                'user_id'    => ['type' => 'BIGINT'],
                'message'    => ['type' => 'TEXT'],
                'type'       => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
                'link'       => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'is_read'    => ['type' => 'BOOLEAN', 'default' => false],
                'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('notifications');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('notifications', true);
    }
}
