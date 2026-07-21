<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * The "Leave" module is being broadened into "Filings" (leave + official
 * business + schedule change + time adjustment requests). Rename the
 * underlying module_key everywhere it's already been granted so existing
 * access profiles/employees don't silently lose access.
 */
class RenameLeaveModuleToFilings extends Migration
{
    public function up(): void
    {
        $this->db->table('access_profile_modules')->where('module_key', 'leave')->update(['module_key' => 'filings']);
        $this->db->table('employee_module_access')->where('module_key', 'leave')->update(['module_key' => 'filings']);
    }

    public function down(): void
    {
        $this->db->table('access_profile_modules')->where('module_key', 'filings')->update(['module_key' => 'leave']);
        $this->db->table('employee_module_access')->where('module_key', 'filings')->update(['module_key' => 'leave']);
    }
}
