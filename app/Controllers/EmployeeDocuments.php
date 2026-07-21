<?php

namespace App\Controllers;

use App\Models\DocumentTemplateModel;
use App\Models\DocumentTypeModel;
use App\Models\EmployeeDocumentModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;

class EmployeeDocuments extends BaseController
{
    use CompanyScoped;

    private const ALLOWED_UPLOAD_EXT = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

    private const ALLOWED_UPLOAD_MIME = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/jpeg',
        'image/png',
    ];

    private const STATUSES = ['draft', 'final', 'signed', 'archived'];

    protected EmployeeDocumentModel $documents;
    protected DocumentTypeModel $types;
    protected DocumentTemplateModel $templates;

    public function __construct()
    {
        $this->documents = new EmployeeDocumentModel();
        $this->types     = new DocumentTypeModel();
        $this->templates = new DocumentTemplateModel();
    }

    /**
     * The employee row joined with everything a document might need to
     * display or use as a token value. Kept local to this controller (rather
     * than added to EmployeeModel) to avoid colliding with the Employee
     * Management module being built in parallel.
     */
    private function employeeContext(int $id): ?array
    {
        $row = db_connect()->table('employees e')
            ->select("e.*, u.name AS user_name, u.email AS user_email,
                      c.name AS company_name, d.name AS department_name,
                      p.title AS position_title, b.name AS branch_name")
            ->join('users u', 'u.id = e.user_id')
            ->join('companies c', 'c.id = e.company_id')
            ->join('departments d', 'd.id = e.department_id', 'left')
            ->join('positions p', 'p.id = e.position_id', 'left')
            ->join('branches b', 'b.id = e.branch_id', 'left')
            ->where('e.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    private function tokensFor(array $employee): array
    {
        return [
            'employee_name'   => $employee['user_name'] ?? '',
            'employee_number' => $employee['employee_number'] ?? '',
            'position'        => $employee['position_title'] ?? '',
            'department'      => $employee['department_name'] ?? '',
            'company_name'    => $employee['company_name'] ?? '',
            'hire_date'       => $employee['hire_date'] ?? '',
            'basic_pay'       => $employee['basic_pay'] !== null ? number_format((float) $employee['basic_pay'], 2) : '',
            'today'           => date('F j, Y'),
        ];
    }

    /** Active templates (system-wide + this company's own) grouped by document_type_id, for the "generate" dropdown. */
    private function templatesGroupedByType(int $companyId): array
    {
        $rows = $this->templates
            ->where('is_active', true)
            ->groupStart()
                ->where('company_id', $companyId)
                ->orWhere('company_id', null)
            ->groupEnd()
            ->orderBy('name')
            ->findAll();

        $byType = [];
        foreach ($rows as $row) {
            $byType[$row['document_type_id']][] = $row;
        }

        return $byType;
    }

    public function index(int $employeeId)
    {
        $employee = $this->employeeContext($employeeId);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employee['company_id']);

        return view('employee_documents/index', [
            'title'           => 'Documents — ' . $employee['user_name'],
            'active'          => 'employee-mgmt',
            'employee'        => $employee,
            'documents'       => $this->documents->withDetails($employeeId),
            'types'           => $this->types->orderBy('name')->findAll(),
            'templatesByType' => $this->templatesGroupedByType((int) $employee['company_id']),
        ]);
    }

    public function generate(int $employeeId)
    {
        $employee = $this->employeeContext($employeeId);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employee['company_id']);

        $post           = $this->request->getPost();
        $documentTypeId = (int) ($post['document_type_id'] ?? 0);
        $templateId     = (int) ($post['document_template_id'] ?? 0) ?: null;

        $type = $this->types->find($documentTypeId);
        if (! $type) {
            return redirect()->back()->with('error', 'Choose a valid document type.');
        }

        $body = '';

        if ($templateId !== null) {
            $template = $this->templates->find($templateId);
            if (! $template || (int) $template['document_type_id'] !== $documentTypeId) {
                return redirect()->back()->with('error', 'Choose a valid template for that document type.');
            }
            // Non-superadmin may only use system-wide templates or their own company's.
            if ($template['company_id'] !== null && (int) $template['company_id'] !== (int) $employee['company_id']) {
                throw PageNotFoundException::forPageNotFound();
            }

            $body = $this->templates->renderBody($template['body'], $this->tokensFor($employee));
        }

        $title = trim((string) ($post['title'] ?? '')) ?: ($type['name'] . ' — ' . $employee['user_name']);

        $this->documents->insert([
            'employee_id'          => $employeeId,
            'document_type_id'     => $documentTypeId,
            'document_template_id' => $templateId,
            'title'                => $title,
            'content'              => $body,
            'status'               => 'draft',
            'issued_date'          => date('Y-m-d'),
            'generated_by_user_id' => session()->get('user_id'),
        ]);

        return redirect()->to('/employee-management/' . $employeeId . '/documents')->with('success', 'Document generated as a draft.');
    }

    public function upload(int $employeeId)
    {
        $employee = $this->employeeContext($employeeId);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employee['company_id']);

        $post           = $this->request->getPost();
        $documentTypeId = (int) ($post['document_type_id'] ?? 0);

        $type = $this->types->find($documentTypeId);
        if (! $type) {
            return redirect()->back()->with('error', 'Choose a valid document type.');
        }

        $file = $this->request->getFile('file');
        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'Choose a file to upload.');
        }

        $ext  = strtolower($file->getClientExtension());
        $mime = $file->getMimeType();

        if (! in_array($ext, self::ALLOWED_UPLOAD_EXT, true) || ! in_array($mime, self::ALLOWED_UPLOAD_MIME, true)) {
            return redirect()->back()->with('error', 'Unsupported file type. Allowed: PDF, DOC, DOCX, JPG, PNG.');
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/documents', $newName);

        $title = trim((string) ($post['title'] ?? '')) ?: ($type['name'] . ' — ' . $employee['user_name']);

        $this->documents->insert([
            'employee_id'          => $employeeId,
            'document_type_id'     => $documentTypeId,
            'title'                => $title,
            'file_path'            => 'uploads/documents/' . $newName,
            'status'               => 'final',
            'issued_date'          => date('Y-m-d'),
            'generated_by_user_id' => session()->get('user_id'),
        ]);

        return redirect()->to('/employee-management/' . $employeeId . '/documents')->with('success', 'Document uploaded.');
    }

    public function updateStatus(int $documentId)
    {
        $doc = $this->documents->find($documentId);
        if (! $doc) {
            throw PageNotFoundException::forPageNotFound();
        }

        $employee = $this->employeeContext((int) $doc['employee_id']);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employee['company_id']);

        $status = (string) ($this->request->getPost('status') ?? '');
        if (! in_array($status, self::STATUSES, true)) {
            return redirect()->back()->with('error', 'Invalid status.');
        }

        $this->documents->update($documentId, ['status' => $status]);

        return redirect()->to('/employee-management/' . $doc['employee_id'] . '/documents')->with('success', 'Document status updated.');
    }

    public function delete(int $documentId)
    {
        $doc = $this->documents->find($documentId);
        if (! $doc) {
            throw PageNotFoundException::forPageNotFound();
        }

        $employee = $this->employeeContext((int) $doc['employee_id']);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employee['company_id']);

        if (! empty($doc['file_path']) && is_file(FCPATH . $doc['file_path'])) {
            @unlink(FCPATH . $doc['file_path']);
        }

        $this->documents->delete($documentId);

        return redirect()->to('/employee-management/' . $doc['employee_id'] . '/documents')->with('success', 'Document deleted.');
    }

    public function view(int $documentId)
    {
        $doc = $this->documents->findWithDetails($documentId);
        if (! $doc) {
            throw PageNotFoundException::forPageNotFound();
        }

        $employee = $this->employeeContext((int) $doc['employee_id']);
        if (! $employee) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $employee['company_id']);

        if (! empty($doc['file_path'])) {
            $fullPath = FCPATH . $doc['file_path'];
            if (! is_file($fullPath)) {
                throw PageNotFoundException::forPageNotFound();
            }

            return $this->response->download($fullPath, null, true);
        }

        // No uploaded file: this is a generated document — render its saved
        // content as a clean, printable HTML page (browser print → PDF).
        return view('employee_documents/view', [
            'title'    => $doc['title'],
            'active'   => 'employee-mgmt',
            'document' => $doc,
            'employee' => $employee,
        ]);
    }
}
