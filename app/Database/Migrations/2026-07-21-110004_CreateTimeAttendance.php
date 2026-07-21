<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTimeAttendance extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('work_schedules')) {
            $this->forge->addField([
                'id'             => ['type' => 'BIGINT', 'auto_increment' => true],
                'company_id'     => ['type' => 'BIGINT'],
                'name'           => ['type' => 'VARCHAR', 'constraint' => 100],
                'time_in'        => ['type' => 'VARCHAR', 'constraint' => 5],  // "07:00"
                'time_out'       => ['type' => 'VARCHAR', 'constraint' => 5],  // "16:00"
                'grace_minutes'  => ['type' => 'INT', 'default' => 10],
                'break_minutes'  => ['type' => 'INT', 'null' => true],
                // fixed | shifting | executive — executive logs are still recorded but
                // skip late/undertime payroll deductions (checked in the payroll engine).
                'schedule_type'  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'fixed'],
                'created_at'     => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'     => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('work_schedules');
        }

        if (! $this->db->tableExists('schedule_assignments')) {
            $this->forge->addField([
                'id'                => ['type' => 'BIGINT', 'auto_increment' => true],
                'employee_id'       => ['type' => 'BIGINT'],
                'date'              => ['type' => 'DATE'],
                'work_schedule_id'  => ['type' => 'BIGINT'],
                'created_at'        => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['employee_id', 'date']);
            $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('work_schedule_id', 'work_schedules', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('schedule_assignments');
        }

        if (! $this->db->tableExists('attendance_logs')) {
            $this->forge->addField([
                'id'          => ['type' => 'BIGINT', 'auto_increment' => true],
                'employee_id' => ['type' => 'BIGINT'],
                'log_date'    => ['type' => 'DATE'],
                'time_in'     => ['type' => 'VARCHAR', 'constraint' => 8, 'null' => true],
                'time_out'    => ['type' => 'VARCHAR', 'constraint' => 8, 'null' => true],
                'source'      => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'clock'],
                'created_at'  => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'  => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['employee_id', 'log_date']);
            $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('attendance_logs');
        }

        if (! $this->db->tableExists('holidays')) {
            $this->forge->addField([
                'id'           => ['type' => 'BIGINT', 'auto_increment' => true],
                'company_id'   => ['type' => 'BIGINT'],
                'name'         => ['type' => 'VARCHAR', 'constraint' => 190],
                'date'         => ['type' => 'DATE'],
                'holiday_type' => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'legal'], // legal | special
                'scope_type'   => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'national'], // national | regional | local
                'scope_value'  => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'source'       => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'manual'], // manual | api_import
                'external_ref' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'created_at'   => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'   => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('holidays');
        }

        if (! $this->db->tableExists('cutoff_schedules')) {
            $this->forge->addField([
                'id'                   => ['type' => 'BIGINT', 'auto_increment' => true],
                'company_id'           => ['type' => 'BIGINT'],
                'scope_type'           => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'company'], // company | department | branch | employee
                'scope_id'             => ['type' => 'BIGINT', 'null' => true],
                'frequency'            => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'semi_monthly'], // monthly | semi_monthly | weekly
                'period_config'        => ['type' => 'TEXT', 'null' => true], // JSON period boundaries
                'pay_date_offset_days' => ['type' => 'INT', 'default' => 5],
                'reminder_days_before' => ['type' => 'INT', 'default' => 2],
                'created_at'           => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'           => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('cutoff_schedules');
        }

        // Now that work_schedules exists, wire up the deferred FKs from earlier migrations.
        if (! in_array('work_schedule_id', $this->db->getFieldNames('employees'), true)) {
            $this->forge->addColumn('employees', [
                'work_schedule_id' => ['type' => 'BIGINT', 'null' => true],
            ]);
            $this->forge->addForeignKey('work_schedule_id', 'work_schedules', 'id', 'SET NULL', 'CASCADE');
            $this->forge->processIndexes('employees');
        }

        $this->forge->addForeignKey('requested_work_schedule_id', 'work_schedules', 'id', 'SET NULL', 'CASCADE');
        $this->forge->processIndexes('filings');
    }

    public function down(): void
    {
        $this->forge->dropTable('cutoff_schedules', true);
        $this->forge->dropTable('holidays', true);
        $this->forge->dropTable('attendance_logs', true);
        $this->forge->dropTable('schedule_assignments', true);
        $this->forge->dropTable('work_schedules', true);
    }
}
