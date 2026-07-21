<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * document_templates holds plain-text/HTML bodies with {{token}} placeholders.
 * company_id = null means a system-wide default template (visible to every
 * company, editable only by a superadmin); a non-null company_id scopes the
 * template to that one company.
 */
class DocumentTemplateModel extends Model
{
    protected $table         = 'document_templates';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['document_type_id', 'company_id', 'name', 'body', 'is_active'];
    protected $useTimestamps = true;

    protected $validationRules = [
        'document_type_id' => 'required|is_natural_no_zero',
        'name'              => 'required|min_length[2]|max_length[190]',
        'body'              => 'required',
    ];

    protected $validationMessages = [
        'document_type_id' => ['required' => 'Choose a document type.'],
        'name'              => ['required' => 'Name is required.'],
        'body'              => ['required' => 'Template body is required.'],
    ];

    private function baseQuery()
    {
        return $this->db->table('document_templates dt')
            ->select('dt.*, t.name AS type_name, t.key AS type_key, c.name AS company_name')
            ->join('document_types t', 't.id = dt.document_type_id')
            ->join('companies c', 'c.id = dt.company_id', 'left')
            ->orderBy('t.name')
            ->orderBy('dt.name');
    }

    /** Every template system-wide, for the superadmin list view. */
    public function allWithTypeName(): array
    {
        return $this->baseQuery()->get()->getResultArray();
    }

    /** System-wide (company_id null) templates plus one company's own, for a non-superadmin list view. */
    public function visibleToCompany(int $companyId): array
    {
        return $this->baseQuery()
            ->groupStart()
                ->where('dt.company_id', $companyId)
                ->orWhere('dt.company_id', null)
            ->groupEnd()
            ->get()->getResultArray();
    }

    /**
     * Substitute {{token}} placeholders in a template body.
     * $tokens may use plain keys ('employee_name' => 'Jane Doe') or
     * already-bracketed keys ('{{employee_name}}' => 'Jane Doe') — both work.
     */
    public function renderBody(string $body, array $tokens): string
    {
        $replacements = [];

        foreach ($tokens as $key => $value) {
            $key                = (string) $key;
            $placeholder        = str_starts_with($key, '{{') ? $key : '{{' . $key . '}}';
            $replacements[$placeholder] = (string) $value;
        }

        return strtr($body, $replacements);
    }
}
