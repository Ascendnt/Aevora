<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocuments extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('document_types')) {
            $this->forge->addField([
                'id'                 => ['type' => 'BIGINT', 'auto_increment' => true],
                'key'                => ['type' => 'VARCHAR', 'constraint' => 60],
                'name'               => ['type' => 'VARCHAR', 'constraint' => 190],
                'requires_signature' => ['type' => 'BOOLEAN', 'default' => true],
                'created_at'         => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'         => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('key');
            $this->forge->createTable('document_types');
        }

        if (! $this->db->tableExists('document_templates')) {
            $this->forge->addField([
                'id'               => ['type' => 'BIGINT', 'auto_increment' => true],
                'document_type_id' => ['type' => 'BIGINT'],
                'company_id'       => ['type' => 'BIGINT', 'null' => true], // null = system-wide default template
                'name'             => ['type' => 'VARCHAR', 'constraint' => 190],
                // Plain-text/HTML body with {{token}} placeholders — deliberately simple
                // (no proprietary format) so a future AI/automation layer can read and
                // rewrite it directly.
                'body'             => ['type' => 'TEXT'],
                'is_active'        => ['type' => 'BOOLEAN', 'default' => true],
                'created_at'       => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'       => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('document_type_id', 'document_types', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('document_templates');
        }

        if (! $this->db->tableExists('employee_documents')) {
            $this->forge->addField([
                'id'                    => ['type' => 'BIGINT', 'auto_increment' => true],
                'employee_id'           => ['type' => 'BIGINT'],
                'document_type_id'      => ['type' => 'BIGINT'],
                'document_template_id'  => ['type' => 'BIGINT', 'null' => true],
                'title'                 => ['type' => 'VARCHAR', 'constraint' => 190],
                'content'               => ['type' => 'TEXT', 'null' => true],
                'file_path'             => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'status'                => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
                'issued_date'           => ['type' => 'DATE', 'null' => true],
                'generated_by_user_id'  => ['type' => 'BIGINT', 'null' => true],
                'created_at'            => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'            => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('document_type_id', 'document_types', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('document_template_id', 'document_templates', 'id', 'SET NULL', 'CASCADE');
            $this->forge->addForeignKey('generated_by_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('employee_documents');
        }

        $this->seedDocumentTypes();
    }

    private function seedDocumentTypes(): void
    {
        $types = [
            ['key' => 'employment_contract', 'name' => 'Employment Contract', 'requires_signature' => true],
            ['key' => 'nda', 'name' => 'Non-Disclosure Agreement', 'requires_signature' => true],
            ['key' => 'offer_letter', 'name' => 'Offer Letter', 'requires_signature' => true],
            ['key' => 'coe', 'name' => 'Certificate of Employment', 'requires_signature' => false],
            ['key' => 'quitclaim', 'name' => 'Quitclaim / Release Document', 'requires_signature' => true],
            ['key' => 'bir_2316', 'name' => 'BIR Form 2316', 'requires_signature' => true],
            ['key' => 'nte_nod', 'name' => 'Notice to Explain / Notice of Decision', 'requires_signature' => true],
        ];

        $now = date('Y-m-d H:i:s');

        foreach ($types as $type) {
            $exists = $this->db->table('document_types')->where('key', $type['key'])->countAllResults() > 0;

            if (! $exists) {
                $this->db->table('document_types')->insert($type + ['created_at' => $now, 'updated_at' => $now]);
            }
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('employee_documents', true);
        $this->forge->dropTable('document_templates', true);
        $this->forge->dropTable('document_types', true);
    }
}
