<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Schema for the second follow-up round: configurable attendance policies,
 * branch/employee-specific holiday scoping, employee self-service profile
 * edit requests, an overtime filing type, and a per-punch timezone record
 * for geo-aware clock in/out. All guarded/idempotent — see the pattern
 * established in the first mega-build migrations.
 */
class AttendancePoliciesProfileRequestsOvertime extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('attendance_policies')) {
            $this->forge->addField([
                'id'                                => ['type' => 'BIGINT', 'auto_increment' => true],
                'company_id'                        => ['type' => 'BIGINT'],
                'name'                               => ['type' => 'VARCHAR', 'constraint' => 100],
                // If true, an unexcused absence the working day immediately before a
                // holiday forfeits that holiday's pay (a common policy in several
                // jurisdictions) — enforced by PayrollEngine, not just recorded here.
                'absent_before_holiday_forfeits_pay' => ['type' => 'BOOLEAN', 'default' => false],
                'absent_after_holiday_forfeits_pay'  => ['type' => 'BOOLEAN', 'default' => false],
                // Flags (not blocks) an employee whose consecutive unexcused absences
                // reach this many days, surfaced wherever HR reviews attendance.
                'consecutive_absence_alert_days'    => ['type' => 'INT', 'null' => true],
                'notes'                              => ['type' => 'TEXT', 'null' => true],
                'created_at'                         => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'                         => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('attendance_policies');
        }

        // Holidays: let a specific branch or employee be the scope, alongside the
        // existing free-text scope_value (used for regional/local naming).
        $holidayFields = $this->db->getFieldNames('holidays');
        $toAdd          = [];
        if (! in_array('branch_id', $holidayFields, true)) {
            $toAdd['branch_id'] = ['type' => 'BIGINT', 'null' => true];
        }
        if (! in_array('employee_id', $holidayFields, true)) {
            $toAdd['employee_id'] = ['type' => 'BIGINT', 'null' => true];
        }
        if ($toAdd !== []) {
            $this->forge->addColumn('holidays', $toAdd);
            $this->forge->addForeignKey('branch_id', 'branches', 'id', 'SET NULL', 'CASCADE');
            $this->forge->addForeignKey('employee_id', 'employees', 'id', 'SET NULL', 'CASCADE');
            $this->forge->processIndexes('holidays');
        }

        // Filings: overtime is a new filing_type: this is the only type-specific
        // numeric field it needs beyond what the table already has (reason, dates).
        $filingFields = $this->db->getFieldNames('filings');
        if (! in_array('overtime_hours', $filingFields, true)) {
            $this->forge->addColumn('filings', [
                'overtime_hours' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
            ]);
        }

        // Attendance logs: record which IANA timezone a punch was resolved in
        // (geo-detected from the employee's connection), for audit/display —
        // the actual time_in/time_out stay wall-clock in that zone.
        $logFields = $this->db->getFieldNames('attendance_logs');
        if (! in_array('timezone', $logFields, true)) {
            $this->forge->addColumn('attendance_logs', [
                'timezone' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            ]);
        }

        // Self-service contact details an employee can view on their own profile
        // and propose changes to (subject to HR/superadmin review) — everything
        // else on the profile (department, position, pay, supervisor, etc.)
        // stays HR-managed only, via Employee Management.
        $employeeFields = $this->db->getFieldNames('employees');
        $toAdd           = [];
        foreach ([
            'phone'                   => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'address'                 => ['type' => 'TEXT', 'null' => true],
            'emergency_contact_name'  => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'emergency_contact_phone' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
        ] as $name => $def) {
            if (! in_array($name, $employeeFields, true)) {
                $toAdd[$name] = $def;
            }
        }
        if ($toAdd !== []) {
            $this->forge->addColumn('employees', $toAdd);
        }

        if (! $this->db->tableExists('employee_profile_change_requests')) {
            $this->forge->addField([
                'id'                 => ['type' => 'BIGINT', 'auto_increment' => true],
                'employee_id'        => ['type' => 'BIGINT'],
                // JSON: {"field_name": {"from": "...", "to": "..."}, ...}
                'requested_changes'  => ['type' => 'TEXT'],
                'employee_note'      => ['type' => 'TEXT', 'null' => true],
                'status'             => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
                'reviewed_by_user_id' => ['type' => 'BIGINT', 'null' => true],
                'review_note'        => ['type' => 'TEXT', 'null' => true],
                'reviewed_at'        => ['type' => 'TIMESTAMP', 'null' => true],
                'created_at'         => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'         => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('employee_profile_change_requests');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('employee_profile_change_requests', true);
        $this->forge->dropTable('attendance_policies', true);
        // Column additions to existing tables are intentionally not reversed
        // (additive, data-bearing changes) — same convention as earlier migrations.
    }
}
