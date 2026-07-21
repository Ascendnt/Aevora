<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayrollRuns extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('payroll_runs')) {
            $this->forge->addField([
                'id'                => ['type' => 'BIGINT', 'auto_increment' => true],
                'company_id'        => ['type' => 'BIGINT'],
                'cutoff_schedule_id' => ['type' => 'BIGINT', 'null' => true],
                'period_start'      => ['type' => 'DATE'],
                'period_end'        => ['type' => 'DATE'],
                'pay_date'          => ['type' => 'DATE', 'null' => true],
                // draft = still live-recomputed from attendance; finalized/paid = locked history.
                'status'            => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
                'created_at'        => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'        => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('company_id', 'companies', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('cutoff_schedule_id', 'cutoff_schedules', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('payroll_runs');
        }

        if (! $this->db->tableExists('payslips')) {
            $this->forge->addField([
                'id'                     => ['type' => 'BIGINT', 'auto_increment' => true],
                'payroll_run_id'         => ['type' => 'BIGINT'],
                'employee_id'            => ['type' => 'BIGINT'],
                'basic_pay'              => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'days_worked'            => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
                'late_minutes'           => ['type' => 'INT', 'default' => 0],
                'undertime_minutes'      => ['type' => 'INT', 'default' => 0],
                'absence_days'           => ['type' => 'DECIMAL', 'constraint' => '6,2', 'default' => 0],
                'late_deduction'         => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'undertime_deduction'    => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'absence_deduction'      => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'gross_pay'              => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'taxable_income'         => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'tax_withheld'           => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'statutory_deductions'   => ['type' => 'TEXT', 'null' => true], // JSON breakdown per contribution
                'benefits_total'         => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'loan_deductions_total'  => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'total_deductions'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'net_pay'                => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'computed_at'            => ['type' => 'TIMESTAMP', 'null' => true],
                'created_at'             => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'             => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['payroll_run_id', 'employee_id']);
            $this->forge->addForeignKey('payroll_run_id', 'payroll_runs', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('payslips');
        }

        if (! $this->db->tableExists('employee_benefits')) {
            $this->forge->addField([
                'id'           => ['type' => 'BIGINT', 'auto_increment' => true],
                'employee_id'  => ['type' => 'BIGINT'],
                'benefit_type' => ['type' => 'VARCHAR', 'constraint' => 100],
                'amount'       => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'is_recurring' => ['type' => 'BOOLEAN', 'default' => true],
                'effective_date' => ['type' => 'DATE'],
                'end_date'     => ['type' => 'DATE', 'null' => true],
                'notes'        => ['type' => 'TEXT', 'null' => true],
                'created_at'   => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'   => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('employee_benefits');
        }

        if (! $this->db->tableExists('employee_loans')) {
            $this->forge->addField([
                'id'                    => ['type' => 'BIGINT', 'auto_increment' => true],
                'employee_id'           => ['type' => 'BIGINT'],
                'loan_type'             => ['type' => 'VARCHAR', 'constraint' => 100],
                'principal_amount'      => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'monthly_amortization'  => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'balance_remaining'     => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'start_date'            => ['type' => 'DATE'],
                'end_date'              => ['type' => 'DATE', 'null' => true],
                'status'                => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'active'],
                'notes'                 => ['type' => 'TEXT', 'null' => true],
                'created_at'            => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'            => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('employee_id', 'employees', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('employee_loans');
        }

        if (! $this->db->tableExists('loan_deduction_history')) {
            $this->forge->addField([
                'id'              => ['type' => 'BIGINT', 'auto_increment' => true],
                'loan_id'         => ['type' => 'BIGINT'],
                'payslip_id'      => ['type' => 'BIGINT'],
                'amount_deducted' => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'deducted_at'     => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('loan_id', 'employee_loans', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('payslip_id', 'payslips', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('loan_deduction_history');
        }

        if (! $this->db->tableExists('benefit_application_history')) {
            $this->forge->addField([
                'id'             => ['type' => 'BIGINT', 'auto_increment' => true],
                'benefit_id'     => ['type' => 'BIGINT'],
                'payslip_id'     => ['type' => 'BIGINT'],
                'amount_applied' => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'applied_at'     => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('benefit_id', 'employee_benefits', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('payslip_id', 'payslips', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('benefit_application_history');
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('benefit_application_history', true);
        $this->forge->dropTable('loan_deduction_history', true);
        $this->forge->dropTable('employee_loans', true);
        $this->forge->dropTable('employee_benefits', true);
        $this->forge->dropTable('payslips', true);
        $this->forge->dropTable('payroll_runs', true);
    }
}
