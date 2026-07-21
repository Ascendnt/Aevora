<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\DocumentTemplateModel;
use App\Models\DocumentTypeModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;

class DocumentTemplates extends BaseController
{
    use CompanyScoped;

    protected DocumentTemplateModel $templates;
    protected DocumentTypeModel $types;

    public function __construct()
    {
        $this->templates = new DocumentTemplateModel();
        $this->types     = new DocumentTypeModel();
    }

    public function index()
    {
        $this->ensureDefaultTemplates();

        $companyId = scoped_company_id();

        if (is_superadmin()) {
            $templates = $this->templates->allWithTypeName();
        } elseif ($companyId !== null) {
            $templates = $this->templates->visibleToCompany($companyId);
        } else {
            $templates = [];
        }

        $grouped = [];
        foreach ($templates as $row) {
            $row['editable'] = is_superadmin()
                || ($row['company_id'] !== null && (int) $row['company_id'] === $companyId);
            $grouped[$row['type_name']][] = $row;
        }

        return view('document_templates/index', [
            'title'   => 'Document templates',
            'active'  => 'document-templates',
            'grouped' => $grouped,
        ]);
    }

    public function new()
    {
        return view('document_templates/form', [
            'title'     => 'Add document template',
            'active'    => 'document-templates',
            'template'  => null,
            'types'     => $this->types->orderBy('name')->findAll(),
            'companies' => is_superadmin() ? (new CompanyModel())->orderBy('name')->findAll() : [],
        ]);
    }

    public function create()
    {
        $post      = $this->request->getPost();
        $companyId = $this->resolveCompanyIdFromPost($post);

        if (! is_superadmin()) {
            if ($companyId === null) {
                return redirect()->back()->with('error', 'Your account is not linked to a company.');
            }
            $this->assertOwnsCompany($companyId);
        }

        $typeId = (int) ($post['document_type_id'] ?? 0);
        $name   = trim((string) ($post['name'] ?? ''));
        $body   = (string) ($post['body'] ?? '');

        if ($typeId <= 0 || $name === '' || trim($body) === '') {
            return redirect()->back()->withInput()->with('error', 'Document type, name, and body are all required.');
        }

        $inserted = $this->templates->insert([
            'document_type_id' => $typeId,
            'company_id'        => $companyId,
            'name'              => $name,
            'body'              => $body,
            'is_active'         => ! empty($post['is_active']),
        ]);

        if (! $inserted) {
            return redirect()->back()->withInput()->with('errors', $this->templates->errors());
        }

        return redirect()->to('/document-templates')->with('success', 'Template added.');
    }

    public function edit(int $id)
    {
        $template = $this->templates->find($id);
        if (! $template) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertCanEditTemplate($template);

        return view('document_templates/form', [
            'title'     => 'Edit document template',
            'active'    => 'document-templates',
            'template'  => $template,
            'types'     => $this->types->orderBy('name')->findAll(),
            'companies' => is_superadmin() ? (new CompanyModel())->orderBy('name')->findAll() : [],
        ]);
    }

    public function update(int $id)
    {
        $template = $this->templates->find($id);
        if (! $template) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertCanEditTemplate($template);

        $post      = $this->request->getPost();
        $companyId = is_superadmin() ? $this->resolveCompanyIdFromPost($post) : $template['company_id'];

        if (! is_superadmin() && $companyId !== null) {
            $this->assertOwnsCompany((int) $companyId);
        }

        $typeId = (int) ($post['document_type_id'] ?? 0);
        $name   = trim((string) ($post['name'] ?? ''));
        $body   = (string) ($post['body'] ?? '');

        if ($typeId <= 0 || $name === '' || trim($body) === '') {
            return redirect()->back()->withInput()->with('error', 'Document type, name, and body are all required.');
        }

        $this->templates->update($id, [
            'document_type_id' => $typeId,
            'company_id'        => $companyId,
            'name'              => $name,
            'body'              => $body,
            'is_active'         => ! empty($post['is_active']),
        ]);

        return redirect()->to('/document-templates')->with('success', 'Template updated.');
    }

    public function delete(int $id)
    {
        $template = $this->templates->find($id);
        if (! $template) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertCanEditTemplate($template);

        $this->templates->delete($id);

        return redirect()->to('/document-templates')->with('success', 'Template deleted.');
    }

    /** Only a superadmin may touch a system-wide (company_id null) template; otherwise it's company-owned. */
    private function assertCanEditTemplate(array $template): void
    {
        if ($template['company_id'] === null) {
            if (! is_superadmin()) {
                throw PageNotFoundException::forPageNotFound();
            }

            return;
        }

        $this->assertOwnsCompany((int) $template['company_id']);
    }

    /** Superadmin picks a company (or leaves it blank for system-wide); everyone else is forced to their own. */
    private function resolveCompanyIdFromPost(array $post): ?int
    {
        if (! is_superadmin()) {
            return scoped_company_id();
        }

        $raw = trim((string) ($post['company_id'] ?? ''));

        return $raw === '' ? null : (int) $raw;
    }

    /**
     * Seed one system-wide starter template per document type the first time this
     * runs, so the module has usable content out of the box. Idempotent: only
     * inserts for a type that doesn't already have a system-wide (company_id
     * IS NULL) template. Deliberately generic boilerplate — labelled as a
     * starter, not certified legal language.
     */
    private function ensureDefaultTemplates(): void
    {
        $bodies = $this->starterBodies();

        foreach ($this->types->findAll() as $type) {
            $exists = $this->templates
                ->where('document_type_id', $type['id'])
                ->where('company_id', null)
                ->countAllResults() > 0;

            if ($exists) {
                continue;
            }

            $this->templates->insert([
                'document_type_id' => $type['id'],
                'company_id'        => null,
                'name'              => $type['name'] . ' — Starter Template',
                'body'              => $bodies[$type['key']] ?? $this->genericStarterBody($type['name']),
                'is_active'         => true,
            ]);
        }
    }

    private function genericStarterBody(string $typeName): string
    {
        return "STARTER TEMPLATE — {$typeName}\n\n"
            . "Employee: {{employee_name}}\nPosition: {{position}}\nDepartment: {{department}}\n"
            . "Company: {{company_name}}\nHire date: {{hire_date}}\nDate: {{today}}\n\n"
            . '[This is a placeholder starter template. Replace with wording appropriate to this '
            . 'document, reviewed by qualified legal counsel before use — this is not certified legal advice.]';
    }

    /** @return array<string, string> document_types.key => starter body text */
    private function starterBodies(): array
    {
        return [
            'employment_contract' => <<<TXT
                EMPLOYMENT CONTRACT

                This Employment Contract is entered into by and between {{company_name}} ("the Company") and {{employee_name}} ("the Employee").

                1. Position. The Employee is engaged as {{position}} in the {{department}} department, effective {{hire_date}}.
                2. Compensation. The Employee shall receive a basic pay of {{basic_pay}} per pay period, subject to applicable statutory deductions.
                3. Terms. This engagement is subject to the Company's policies and applicable labor laws.

                Signed this {{today}}.

                ___________________________          ___________________________
                {{company_name}}                      {{employee_name}}
                Employer                              Employee

                [STARTER TEMPLATE — replace this boilerplate with language reviewed by qualified legal counsel before use. This is not certified legal advice.]
                TXT,

            'nda' => <<<TXT
                NON-DISCLOSURE AGREEMENT

                This Non-Disclosure Agreement is made on {{today}} between {{company_name}} ("Company") and {{employee_name}}, {{position}}, {{department}} department.

                The Employee agrees to keep confidential all proprietary and sensitive information encountered in the course of employment with the Company, and not to disclose such information during or after employment.

                ___________________________          ___________________________
                {{company_name}}                      {{employee_name}}

                [STARTER TEMPLATE — customize with your legal counsel before use. This is not certified legal advice.]
                TXT,

            'offer_letter' => <<<TXT
                Dear {{employee_name}},

                We are pleased to offer you the position of {{position}} in the {{department}} department at {{company_name}}, with a starting basic pay of {{basic_pay}}, effective {{hire_date}}.

                Please sign and return this letter to confirm your acceptance.

                Sincerely,
                {{company_name}}
                Date: {{today}}

                [STARTER TEMPLATE — customize wording and terms before sending to a candidate.]
                TXT,

            'coe' => <<<TXT
                CERTIFICATE OF EMPLOYMENT

                This is to certify that {{employee_name}} is/was employed with {{company_name}} as {{position}} in the {{department}} department, since {{hire_date}}.

                This certification is issued upon the employee's request for whatever legal purpose it may serve.

                Issued this {{today}}.

                ___________________________
                {{company_name}}
                Authorized Signatory

                [STARTER TEMPLATE — verify this wording matches your company's standard COE format.]
                TXT,

            'quitclaim' => <<<TXT
                RELEASE, WAIVER, AND QUITCLAIM

                I, {{employee_name}}, formerly employed by {{company_name}} as {{position}} in the {{department}} department, hereby acknowledge receipt of all final pay and benefits due to me, and release {{company_name}} from any and all claims arising from my employment, effective {{today}}.

                ___________________________          ___________________________
                {{employee_name}}                     Witness

                [STARTER TEMPLATE — quitclaims carry legal risk if improperly worded; have this reviewed by legal counsel before use.]
                TXT,

            'bir_2316' => <<<TXT
                CERTIFICATE OF COMPENSATION PAYMENT / TAX WITHHELD (BIR FORM 2316) — SUMMARY

                Employee: {{employee_name}}
                Position: {{position}}
                Department: {{department}}
                Employer: {{company_name}}
                Date Hired: {{hire_date}}
                Basic Pay (per period): {{basic_pay}}
                Date Generated: {{today}}

                [STARTER TEMPLATE ONLY — this is a plain-text summary, not the official BIR Form 2316 layout. Use the BIR-prescribed form for actual filing; consult your accountant or the BIR for the certified form.]
                TXT,

            'nte_nod' => <<<TXT
                NOTICE TO EXPLAIN

                Date: {{today}}
                To: {{employee_name}}, {{position}}, {{department}} department
                From: {{company_name}} — Human Resources

                You are directed to submit a written explanation regarding the incident/matter described below, within the timeframe stated in company policy.

                Incident/Matter: ____________________________________________

                Failure to respond within the given period may be construed as a waiver of your right to explain, and the Company may proceed to decide based on available information.

                [STARTER TEMPLATE — disciplinary notices carry legal risk if improperly worded or timed; have your process and this template reviewed by legal counsel before use.]
                TXT,
        ];
    }
}
