<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\LeaveTypeModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;

class LeaveTypes extends BaseController
{
    use CompanyScoped;

    protected LeaveTypeModel $leaveTypes;

    public function __construct()
    {
        $this->leaveTypes = new LeaveTypeModel();
    }

    /** Companies the current user is allowed to pick from (all for superadmin, just their own otherwise). */
    private function selectableCompanies(): array
    {
        $builder = (new CompanyModel())->orderBy('name');
        $scoped  = scoped_company_id();

        if ($scoped !== null) {
            $builder->where('id', $scoped);
        }

        return $builder->findAll();
    }

    public function index()
    {
        $scoped    = scoped_company_id();
        $requested = (int) ($this->request->getGet('company') ?? 0) ?: null;
        $companyId = $scoped ?? $requested;

        return view('leave_types/index', [
            'title'      => 'Leave types',
            'active'     => 'filings',
            'leaveTypes' => $this->leaveTypes->withCompany($companyId),
            'companies'  => $this->selectableCompanies(),
            'filter'     => $companyId,
        ]);
    }

    public function new()
    {
        return view('leave_types/form', [
            'title'     => 'Add leave type',
            'active'    => 'filings',
            'leaveType' => null,
            'companies' => $this->selectableCompanies(),
            'preselect' => scoped_company_id() ?? 0,
        ]);
    }

    public function create()
    {
        $data = $this->leaveTypeFields($this->request->getPost());
        $this->assertOwnsCompany($data['company_id']);

        if (! $this->leaveTypes->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->leaveTypes->errors());
        }

        return redirect()->to('/leave-types')->with('success', 'Leave type added.');
    }

    public function edit(int $id)
    {
        $leaveType = $this->leaveTypes->find($id);
        if (! $leaveType) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $leaveType['company_id']);

        return view('leave_types/form', [
            'title'     => 'Edit leave type',
            'active'    => 'filings',
            'leaveType' => $leaveType,
            'companies' => $this->selectableCompanies(),
            'preselect' => 0,
        ]);
    }

    public function update(int $id)
    {
        $leaveType = $this->leaveTypes->find($id);
        if (! $leaveType) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $leaveType['company_id']);

        $data = $this->leaveTypeFields($this->request->getPost());
        $this->assertOwnsCompany($data['company_id']);

        if (! $this->leaveTypes->update($id, $data)) {
            return redirect()->back()->withInput()->with('errors', $this->leaveTypes->errors());
        }

        return redirect()->to('/leave-types')->with('success', 'Leave type updated.');
    }

    public function delete(int $id)
    {
        $leaveType = $this->leaveTypes->find($id);
        if (! $leaveType) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $leaveType['company_id']);

        $this->leaveTypes->delete($id);

        return redirect()->to('/leave-types')->with('success', 'Leave type deleted.');
    }

    public function importForm()
    {
        return view('leave_types/import', [
            'title'     => 'Import leave types',
            'active'    => 'filings',
            'companies' => $this->selectableCompanies(),
            'preselect' => scoped_company_id() ?? 0,
        ]);
    }

    /**
     * Bulk create-or-update-by-name from an uploaded CSV.
     * Headers (exact, in order): name,is_paid,filing_rule,min_days_notice
     */
    public function import()
    {
        $companyId = (int) $this->request->getPost('company_id');
        $this->assertOwnsCompany($companyId);

        if ($companyId <= 0) {
            return redirect()->back()->with('error', 'Please choose a company.');
        }

        $file = $this->request->getFile('csv_file');
        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'Please choose a CSV file to upload.');
        }

        $handle = fopen($file->getTempName(), 'r');
        if (! $handle) {
            return redirect()->back()->with('error', 'Could not read the uploaded file.');
        }

        $required = ['name', 'is_paid', 'filing_rule', 'min_days_notice'];
        $header   = fgetcsv($handle);

        if (! $header) {
            fclose($handle);

            return redirect()->back()->with('error', 'The CSV file is empty.');
        }

        $header = array_map(static fn ($h) => strtolower(trim((string) $h)), $header);

        if ($header !== $required) {
            fclose($handle);

            return redirect()->back()->with('error', 'CSV headers must be exactly: ' . implode(',', $required));
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count(array_filter($row, static fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // blank line
            }

            $cols = array_combine($header, array_pad(array_slice($row, 0, count($header)), count($header), ''));
            $name = trim((string) ($cols['name'] ?? ''));

            if ($name === '') {
                $skipped++;

                continue;
            }

            $isPaid = in_array(strtolower(trim((string) ($cols['is_paid'] ?? ''))), ['1', 'true', 'yes', 'y'], true);
            $rule   = strtolower(trim((string) ($cols['filing_rule'] ?? '')));
            $rule   = in_array($rule, ['before', 'after'], true) ? $rule : 'before';
            $notice = max(0, (int) ($cols['min_days_notice'] ?? 0));

            $fields = [
                'company_id'      => $companyId,
                'name'            => $name,
                'is_paid'         => $isPaid,
                'filing_rule'     => $rule,
                'min_days_notice' => $notice,
            ];

            $existing = $this->leaveTypes->findByName($companyId, $name);

            if ($existing) {
                $this->leaveTypes->update($existing['id'], $fields);
                $updated++;
            } else {
                $this->leaveTypes->insert($fields);
                $created++;
            }
        }

        fclose($handle);

        return redirect()->to('/leave-types')->with('success', "Import complete: {$created} added, {$updated} updated, {$skipped} skipped.");
    }

    private function leaveTypeFields(array $post): array
    {
        $rule = in_array($post['filing_rule'] ?? '', ['before', 'after'], true) ? $post['filing_rule'] : 'before';

        return [
            'company_id'      => (int) ($post['company_id'] ?? 0),
            'name'            => trim((string) ($post['name'] ?? '')),
            'is_paid'         => ! empty($post['is_paid']),
            'filing_rule'     => $rule,
            'min_days_notice' => max(0, (int) ($post['min_days_notice'] ?? 0)),
        ];
    }
}
