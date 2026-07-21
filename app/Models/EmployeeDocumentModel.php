<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeDocumentModel extends Model
{
    protected $table         = 'employee_documents';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'employee_id', 'document_type_id', 'document_template_id', 'title',
        'content', 'file_path', 'status', 'issued_date', 'generated_by_user_id',
    ];
    protected $useTimestamps = true;

    protected $validationRules = [
        'employee_id'      => 'required|is_natural_no_zero',
        'document_type_id' => 'required|is_natural_no_zero',
        'title'             => 'required|max_length[190]',
        'status'            => 'required|in_list[draft,final,signed,archived]',
    ];

    /** One employee's documents, newest first, joined with type/template names for display. */
    public function withDetails(int $employeeId): array
    {
        return $this->db->table('employee_documents ed')
            ->select('ed.*, t.name AS type_name, tpl.name AS template_name')
            ->join('document_types t', 't.id = ed.document_type_id')
            ->join('document_templates tpl', 'tpl.id = ed.document_template_id', 'left')
            ->where('ed.employee_id', $employeeId)
            ->orderBy('ed.created_at', 'DESC')
            ->get()->getResultArray();
    }

    /** A single document with the same joins, for the view/print page. */
    public function findWithDetails(int $id): ?array
    {
        $row = $this->db->table('employee_documents ed')
            ->select('ed.*, t.name AS type_name, tpl.name AS template_name')
            ->join('document_types t', 't.id = ed.document_type_id')
            ->join('document_templates tpl', 'tpl.id = ed.document_template_id', 'left')
            ->where('ed.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }
}
