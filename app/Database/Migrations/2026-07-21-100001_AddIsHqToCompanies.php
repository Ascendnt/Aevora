<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsHqToCompanies extends Migration
{
    public function up(): void
    {
        if (! in_array('is_hq', $this->db->getFieldNames('companies'), true)) {
            $this->forge->addColumn('companies', [
                'is_hq' => ['type' => 'BOOLEAN', 'default' => false, 'null' => false],
            ]);
        }

        $hasHq = $this->db->table('companies')->where('is_hq', true)->countAllResults() > 0;

        if (! $hasHq) {
            $first = $this->db->table('companies')->orderBy('id', 'ASC')->get(1)->getRowArray();

            if ($first) {
                $this->db->table('companies')->where('id', $first['id'])->update(['is_hq' => true]);
            }
        }
    }

    public function down(): void
    {
        if (in_array('is_hq', $this->db->getFieldNames('companies'), true)) {
            $this->forge->dropColumn('companies', 'is_hq');
        }
    }
}
