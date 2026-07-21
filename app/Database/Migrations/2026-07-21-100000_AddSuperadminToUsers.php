<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSuperadminToUsers extends Migration
{
    public function up(): void
    {
        if (! in_array('is_superadmin', $this->db->getFieldNames('users'), true)) {
            $this->forge->addColumn('users', [
                'is_superadmin' => ['type' => 'BOOLEAN', 'default' => false, 'null' => false],
            ]);
        }

        // Promote the known seeded admin account so existing logins keep full access.
        $this->db->table('users')
            ->where('email', 'admin@hris.test')
            ->update(['is_superadmin' => true]);
    }

    public function down(): void
    {
        if (in_array('is_superadmin', $this->db->getFieldNames('users'), true)) {
            $this->forge->dropColumn('users', 'is_superadmin');
        }
    }
}
