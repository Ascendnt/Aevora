<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * "Filings" unifies leave requests, official business, schedule-change
 * requests, and attendance-correction ("time adjustment") requests under
 * one polymorphic table + a shared approval flow (routed to the filer's
 * supervisor) instead of four near-identical tables/controllers — less
 * surface area to keep consistent and bug-free.
 */
class CreateFilings extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('leave_types')) {
            $this->forge->addField([
                'id'              => ['type' => 'BIGINT', 'auto_increment' => true],
                'company_id'      => ['type' => 'BIGINT'],
                'name'            => ['type' => 'VARCHAR', 'constraint' => 100],
                'is_paid'         => ['type' => 'BOOLEAN', 'default' => true],
                // 'before' = must be filed at least min_days_notice days before the leave date;
                // 'after'  = may be filed after the fact (e.g. sick leave), within min_days_notice days.
                'filing_rule'     => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'before'],
                'min_days_notice' => ['type' => 'INT', 'default' => 0],
                'created_at'      => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'      => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('leave_types');
        }

        if (! $this->db->tableExists('leave_balances')) {
            $this->forge->addField([
                'id'            => ['type' => 'BIGINT', 'auto_increment' => true],
                'employee_id'   => ['type' => 'BIGINT'],
                'leave_type_id' => ['type' => 'BIGINT'],
                'year'          => ['type' => 'INT'],
                'entitled_days' => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
                'used_days'     => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
                'created_at'    => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'    => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['employee_id', 'leave_type_id', 'year']);
            $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('leave_type_id', 'leave_types', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('leave_balances');
        }

        if (! $this->db->tableExists('filings')) {
            $this->forge->addField([
                'id'                        => ['type' => 'BIGINT', 'auto_increment' => true],
                'employee_id'               => ['type' => 'BIGINT'],
                // leave | official_business | schedule_change | time_adjustment
                'filing_type'               => ['type' => 'VARCHAR', 'constraint' => 30],
                'leave_type_id'             => ['type' => 'BIGINT', 'null' => true],
                'requested_time_in'         => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
                'requested_time_out'        => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
                // FK added later once work_schedules exists (see time & attendance migration).
                'requested_work_schedule_id' => ['type' => 'BIGINT', 'null' => true],
                'reason'                    => ['type' => 'TEXT', 'null' => true],
                'days_count'                => ['type' => 'DECIMAL', 'constraint' => '6,2', 'null' => true],
                'status'                    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
                'approver_employee_id'      => ['type' => 'BIGINT', 'null' => true],
                'decided_by_user_id'        => ['type' => 'BIGINT', 'null' => true],
                'decision_note'             => ['type' => 'TEXT', 'null' => true],
                'decided_at'                => ['type' => 'TIMESTAMP', 'null' => true],
                'filed_at'                  => ['type' => 'TIMESTAMP', 'null' => true],
                'created_at'                => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'                => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('leave_type_id', 'leave_types', 'id', 'SET NULL', 'CASCADE');
            $this->forge->addForeignKey('approver_employee_id', 'employees', 'id', 'SET NULL', 'CASCADE');
            $this->forge->addForeignKey('decided_by_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('filings');
        }

        if (! $this->db->tableExists('filing_dates')) {
            $this->forge->addField([
                'id'         => ['type' => 'BIGINT', 'auto_increment' => true],
                'filing_id'  => ['type' => 'BIGINT'],
                'date'       => ['type' => 'DATE'],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['filing_id', 'date']);
            $this->forge->addForeignKey('filing_id', 'filings', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('filing_dates');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('filing_dates', true);
        $this->forge->dropTable('filings', true);
        $this->forge->dropTable('leave_balances', true);
        $this->forge->dropTable('leave_types', true);
    }
}
