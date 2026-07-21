<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Generic, data-driven payroll reference tables — adding a new country
 * means inserting rows, not writing new PHP. Seeded with real, sourced
 * figures for PH and AU as of 2026-07-21; ALWAYS reverify against the
 * official BIR/ATO/DOLE/Fair Work publication before relying on these in
 * a live payroll run, since brackets/rates change with legislation.
 *
 * Sources (fetched 2026-07-21):
 *  - PH income tax brackets (TRAIN law, unchanged since 2023): BIR / taxumo.com / netsalaire.com
 *  - PH SSS/PhilHealth/Pag-IBIG 2026 rates: kamiworkforce.com / taxumo.com
 *  - PH NCR minimum wage (Wage Order NCR-26/27): smartsalarytool.com / emerhub.com
 *  - AU resident tax brackets FY2025-26 (Stage 3 cuts): ato.gov.au / superguide.com.au
 *  - AU Superannuation Guarantee 12% (FY2026-27): ato.gov.au / canstar.com.au
 *  - AU national minimum wage from 1 July 2026 (Annual Wage Review 2026): fairworkmate.com.au
 */
class CreatePayrollReferenceData extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('country_tax_rules')) {
            $this->forge->addField([
                'country_code'                  => ['type' => 'VARCHAR', 'constraint' => 2],
                'currency_code'                 => ['type' => 'VARCHAR', 'constraint' => 3],
                'pay_periods_per_year_default'  => ['type' => 'INT', 'default' => 12],
                'exempt_minimum_wage_earners'   => ['type' => 'BOOLEAN', 'default' => false],
                'notes'                         => ['type' => 'TEXT', 'null' => true],
                'created_at'                    => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at'                    => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addPrimaryKey('country_code');
            $this->forge->createTable('country_tax_rules');
        }

        if (! $this->db->tableExists('country_tax_brackets')) {
            $this->forge->addField([
                'id'             => ['type' => 'BIGINT', 'auto_increment' => true],
                'country_code'   => ['type' => 'VARCHAR', 'constraint' => 2],
                'bracket_order'  => ['type' => 'INT'],
                'min_amount'     => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'max_amount'     => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'base_tax'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'default' => 0],
                'rate_percent'   => ['type' => 'DECIMAL', 'constraint' => '6,3', 'default' => 0],
                'effective_year' => ['type' => 'INT'],
                'created_at'     => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('country_tax_brackets');
        }

        if (! $this->db->tableExists('minimum_wage_rates')) {
            $this->forge->addField([
                'id'             => ['type' => 'BIGINT', 'auto_increment' => true],
                'country_code'   => ['type' => 'VARCHAR', 'constraint' => 2],
                'region'         => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'amount'         => ['type' => 'DECIMAL', 'constraint' => '14,2'],
                'period'         => ['type' => 'VARCHAR', 'constraint' => 10, 'default' => 'daily'], // daily | hourly | monthly
                'effective_date' => ['type' => 'DATE'],
                'created_at'     => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('minimum_wage_rates');
        }

        if (! $this->db->tableExists('statutory_contributions')) {
            $this->forge->addField([
                'id'                     => ['type' => 'BIGINT', 'auto_increment' => true],
                'country_code'           => ['type' => 'VARCHAR', 'constraint' => 2],
                'name'                   => ['type' => 'VARCHAR', 'constraint' => 100],
                'employee_share_percent' => ['type' => 'DECIMAL', 'constraint' => '6,3', 'default' => 0],
                'employer_share_percent' => ['type' => 'DECIMAL', 'constraint' => '6,3', 'default' => 0],
                'min_base'               => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'max_base'               => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'min_contribution'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'max_contribution'       => ['type' => 'DECIMAL', 'constraint' => '14,2', 'null' => true],
                'effective_year'         => ['type' => 'INT'],
                'created_at'             => ['type' => 'TIMESTAMP', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('statutory_contributions');
        }

        $this->seed();
    }

    private function seed(): void
    {
        $now = date('Y-m-d H:i:s');

        if ($this->db->table('country_tax_rules')->countAllResults() === 0) {
            $this->db->table('country_tax_rules')->insertBatch([
                ['country_code' => 'PH', 'currency_code' => 'PHP', 'pay_periods_per_year_default' => 12, 'exempt_minimum_wage_earners' => true, 'notes' => 'TRAIN law: minimum wage earners are exempt from income tax withholding.', 'created_at' => $now, 'updated_at' => $now],
                ['country_code' => 'AU', 'currency_code' => 'AUD', 'pay_periods_per_year_default' => 12, 'exempt_minimum_wage_earners' => false, 'notes' => 'AU has no blanket minimum-wage income tax exemption; the tax-free threshold ($18,200/yr) applies to everyone.', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if ($this->db->table('country_tax_brackets')->countAllResults() === 0) {
            $this->db->table('country_tax_brackets')->insertBatch([
                // Philippines — TRAIN law annual brackets (unchanged 2023–2026)
                ['country_code' => 'PH', 'bracket_order' => 1, 'min_amount' => 0, 'max_amount' => 250000, 'base_tax' => 0, 'rate_percent' => 0, 'effective_year' => 2026, 'created_at' => $now],
                ['country_code' => 'PH', 'bracket_order' => 2, 'min_amount' => 250000.01, 'max_amount' => 400000, 'base_tax' => 0, 'rate_percent' => 15, 'effective_year' => 2026, 'created_at' => $now],
                ['country_code' => 'PH', 'bracket_order' => 3, 'min_amount' => 400000.01, 'max_amount' => 800000, 'base_tax' => 22500, 'rate_percent' => 20, 'effective_year' => 2026, 'created_at' => $now],
                ['country_code' => 'PH', 'bracket_order' => 4, 'min_amount' => 800000.01, 'max_amount' => 2000000, 'base_tax' => 102500, 'rate_percent' => 25, 'effective_year' => 2026, 'created_at' => $now],
                ['country_code' => 'PH', 'bracket_order' => 5, 'min_amount' => 2000000.01, 'max_amount' => 8000000, 'base_tax' => 402500, 'rate_percent' => 30, 'effective_year' => 2026, 'created_at' => $now],
                ['country_code' => 'PH', 'bracket_order' => 6, 'min_amount' => 8000000.01, 'max_amount' => null, 'base_tax' => 2202500, 'rate_percent' => 35, 'effective_year' => 2026, 'created_at' => $now],
                // Australia — resident individual annual brackets, FY2025-26 (Medicare levy handled separately, not included here)
                ['country_code' => 'AU', 'bracket_order' => 1, 'min_amount' => 0, 'max_amount' => 18200, 'base_tax' => 0, 'rate_percent' => 0, 'effective_year' => 2026, 'created_at' => $now],
                ['country_code' => 'AU', 'bracket_order' => 2, 'min_amount' => 18200.01, 'max_amount' => 45000, 'base_tax' => 0, 'rate_percent' => 16, 'effective_year' => 2026, 'created_at' => $now],
                ['country_code' => 'AU', 'bracket_order' => 3, 'min_amount' => 45000.01, 'max_amount' => 135000, 'base_tax' => 4288, 'rate_percent' => 30, 'effective_year' => 2026, 'created_at' => $now],
                ['country_code' => 'AU', 'bracket_order' => 4, 'min_amount' => 135000.01, 'max_amount' => 190000, 'base_tax' => 31288, 'rate_percent' => 37, 'effective_year' => 2026, 'created_at' => $now],
                ['country_code' => 'AU', 'bracket_order' => 5, 'min_amount' => 190000.01, 'max_amount' => null, 'base_tax' => 51638, 'rate_percent' => 45, 'effective_year' => 2026, 'created_at' => $now],
            ]);
        }

        if ($this->db->table('minimum_wage_rates')->countAllResults() === 0) {
            $this->db->table('minimum_wage_rates')->insertBatch([
                ['country_code' => 'PH', 'region' => 'NCR', 'amount' => 695.00, 'period' => 'daily', 'effective_date' => '2026-01-01', 'created_at' => $now],
                ['country_code' => 'PH', 'region' => 'NCR', 'amount' => 755.00, 'period' => 'daily', 'effective_date' => '2026-07-25', 'created_at' => $now],
                ['country_code' => 'AU', 'region' => null, 'amount' => 26.44, 'period' => 'hourly', 'effective_date' => '2026-07-01', 'created_at' => $now],
            ]);
        }

        if ($this->db->table('statutory_contributions')->countAllResults() === 0) {
            $this->db->table('statutory_contributions')->insertBatch([
                ['country_code' => 'PH', 'name' => 'SSS', 'employee_share_percent' => 5, 'employer_share_percent' => 10, 'min_base' => 5000, 'max_base' => 35000, 'effective_year' => 2026, 'created_at' => $now],
                ['country_code' => 'PH', 'name' => 'PhilHealth', 'employee_share_percent' => 2.5, 'employer_share_percent' => 2.5, 'min_contribution' => 500, 'max_contribution' => 5000, 'effective_year' => 2026, 'created_at' => $now],
                ['country_code' => 'PH', 'name' => 'Pag-IBIG', 'employee_share_percent' => 2, 'employer_share_percent' => 2, 'max_base' => 10000, 'max_contribution' => 200, 'effective_year' => 2026, 'created_at' => $now],
                ['country_code' => 'AU', 'name' => 'Superannuation Guarantee', 'employee_share_percent' => 0, 'employer_share_percent' => 12, 'effective_year' => 2026, 'created_at' => $now],
            ]);
        }
    }

    public function down(): void
    {
        $this->forge->dropTable('statutory_contributions', true);
        $this->forge->dropTable('minimum_wage_rates', true);
        $this->forge->dropTable('country_tax_brackets', true);
        $this->forge->dropTable('country_tax_rules', true);
    }
}
