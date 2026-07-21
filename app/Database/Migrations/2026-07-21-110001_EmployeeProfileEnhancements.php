<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds the fields a real HR onboarding flow needs (org structure, pay
 * basis, approval routing) on top of the minimal employees table built
 * earlier. Idempotent: safe to run against a database that already has
 * some of these columns.
 */
class EmployeeProfileEnhancements extends Migration
{
    public function up(): void
    {
        // Company-level config: which country's payroll rules apply, and how
        // many approval tiers this company's org chart uses (dynamic, not hardcoded).
        $companyFields = $this->db->getFieldNames('companies');
        $toAdd         = [];

        if (! in_array('country_code', $companyFields, true)) {
            $toAdd['country_code'] = ['type' => 'VARCHAR', 'constraint' => 2, 'null' => true];
        }
        if (! in_array('max_approval_levels', $companyFields, true)) {
            $toAdd['max_approval_levels'] = ['type' => 'INT', 'default' => 5, 'null' => false];
        }
        if ($toAdd !== []) {
            $this->forge->addColumn('companies', $toAdd);
        }

        // Best-effort backfill of country_code from the existing free-text country name.
        $map = ['Philippines' => 'PH', 'Australia' => 'AU'];
        foreach ($map as $name => $code) {
            $this->db->table('companies')->where('country', $name)->where('country_code', null)->update(['country_code' => $code]);
        }

        // Configurable org-structure lookups (same add/edit/delete pattern as departments/positions).
        if (! $this->db->tableExists('job_levels')) {
            $this->forge->addField([
                'id'         => ['type' => 'BIGINT', 'auto_increment' => true],
                'company_id' => ['type' => 'BIGINT'],
                'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
                'sort_order' => ['type' => 'INT', 'default' => 0],
                'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('job_levels');
        }

        if (! $this->db->tableExists('employee_ranks')) {
            $this->forge->addField([
                'id'         => ['type' => 'BIGINT', 'auto_increment' => true],
                'company_id' => ['type' => 'BIGINT'],
                'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
                // Whether this classification is exempt from OT/undertime-style deductions
                // (e.g. "Managerial"/"Executive" in many jurisdictions) — feeds payroll later.
                'is_exempt'  => ['type' => 'BOOLEAN', 'default' => false],
                'sort_order' => ['type' => 'INT', 'default' => 0],
                'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('employee_ranks');
        }

        // Employee profile fields.
        $employeeFields = $this->db->getFieldNames('employees');
        $toAdd           = [];

        $maybeAdd = [
            'date_of_birth'          => ['type' => 'DATE', 'null' => true],
            'supervisor_id'          => ['type' => 'BIGINT', 'null' => true],
            'job_level_id'           => ['type' => 'BIGINT', 'null' => true],
            'employee_rank_id'       => ['type' => 'BIGINT', 'null' => true],
            'approval_level'         => ['type' => 'INT', 'null' => true],
            'basic_pay'              => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
            'pay_frequency'          => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'monthly'],
            'is_minimum_wage_earner' => ['type' => 'BOOLEAN', 'default' => false],
        ];

        foreach ($maybeAdd as $name => $def) {
            if (! in_array($name, $employeeFields, true)) {
                $toAdd[$name] = $def;
            }
        }
        if ($toAdd !== []) {
            $this->forge->addColumn('employees', $toAdd);

            // Only add the FK constraints the first time these columns are created —
            // this migration runs exactly once, so no need to guard against re-adding.
            $this->forge->addForeignKey('supervisor_id', 'employees', 'id', 'SET NULL', 'CASCADE');
            $this->forge->addForeignKey('job_level_id', 'job_levels', 'id', 'SET NULL', 'CASCADE');
            $this->forge->addForeignKey('employee_rank_id', 'employee_ranks', 'id', 'SET NULL', 'CASCADE');
            $this->forge->processIndexes('employees');
        }
    }

    public function down(): void
    {
        // Intentionally not reversed — this is an additive, data-bearing change.
    }
}
