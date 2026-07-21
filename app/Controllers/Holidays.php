<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\HolidayModel;
use App\Traits\CompanyScoped;
use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Services;
use Throwable;

class Holidays extends BaseController
{
    use CompanyScoped;

    protected HolidayModel $holidays;

    public function __construct()
    {
        $this->holidays = new HolidayModel();
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
        $year      = (int) ($this->request->getGet('year') ?? date('Y'));

        return view('holidays/index', [
            'title'     => 'Holiday calendar',
            'active'    => 'attendance',
            'holidays'  => $this->holidays->withCompany($companyId, $year),
            'companies' => $this->selectableCompanies(),
            'filter'    => $companyId,
            'year'      => $year,
        ]);
    }

    public function new()
    {
        $scoped = scoped_company_id();

        return view('holidays/form', [
            'title'     => 'Add holiday',
            'active'    => 'attendance',
            'holiday'   => null,
            'companies' => $this->selectableCompanies(),
            'preselect' => $scoped ?? (int) ($this->request->getGet('company') ?? 0),
        ]);
    }

    public function create()
    {
        $post      = $this->request->getPost();
        $companyId = (int) ($post['company_id'] ?? 0);
        $this->assertOwnsCompany($companyId);

        $data                 = $this->fields($post, $companyId);
        $data['source']       = 'manual';
        $data['external_ref'] = null;

        if (! $this->holidays->insert($data)) {
            return redirect()->back()->withInput()->with('errors', $this->holidays->errors());
        }

        return redirect()->to('/holidays')->with('success', 'Holiday added.');
    }

    public function edit(int $id)
    {
        $holiday = $this->holidays->find($id);
        if (! $holiday) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $holiday['company_id']);

        return view('holidays/form', [
            'title'     => 'Edit holiday',
            'active'    => 'attendance',
            'holiday'   => $holiday,
            'companies' => $this->selectableCompanies(),
            'preselect' => 0,
        ]);
    }

    public function update(int $id)
    {
        $holiday = $this->holidays->find($id);
        if (! $holiday) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $holiday['company_id']);

        $post      = $this->request->getPost();
        $companyId = (int) ($post['company_id'] ?? $holiday['company_id']);
        $this->assertOwnsCompany($companyId);

        // source/external_ref are left untouched here — editing an api-imported
        // holiday by hand shouldn't make it look like a fresh manual entry.
        if (! $this->holidays->update($id, $this->fields($post, $companyId))) {
            return redirect()->back()->withInput()->with('errors', $this->holidays->errors());
        }

        return redirect()->to('/holidays')->with('success', 'Holiday updated.');
    }

    public function delete(int $id)
    {
        $holiday = $this->holidays->find($id);
        if (! $holiday) {
            throw PageNotFoundException::forPageNotFound();
        }
        $this->assertOwnsCompany((int) $holiday['company_id']);

        $this->holidays->delete($id);

        return redirect()->to('/holidays')->with('success', 'Holiday deleted.');
    }

    /**
     * Pulls public holidays for a year/country from the free Nager.Date API
     * and inserts any that aren't already recorded (matched by external_ref
     * = the ISO date string, per company) — safe to re-run for the same year.
     * Coverage varies a lot by country and this is a live external call, so
     * failures are caught and flashed rather than allowed to crash the request.
     */
    public function syncFromApi()
    {
        $post      = $this->request->getPost();
        $companyId = (int) ($post['company_id'] ?? 0);
        $this->assertOwnsCompany($companyId);

        $company = (new CompanyModel())->find($companyId);
        if (! $company) {
            throw PageNotFoundException::forPageNotFound();
        }

        $year        = (int) ($post['year'] ?? date('Y'));
        $countryCode = strtoupper(trim((string) ($post['country_code'] ?? '')) ?: (string) ($company['country_code'] ?? ''));

        if ($year < 1900 || $countryCode === '') {
            return redirect()->to('/holidays')->with('error', 'A valid year and country code are required to sync holidays.');
        }

        $url = "https://date.nager.at/api/v3/PublicHolidays/{$year}/{$countryCode}";

        try {
            $client   = Services::curlrequest();
            $response = $client->get($url, ['timeout' => 10]);

            if ($response->getStatusCode() !== 200) {
                return redirect()->to('/holidays')->with('error', "The holiday API returned an unexpected response for {$countryCode} {$year}. Nothing was imported.");
            }

            $items = json_decode((string) $response->getBody(), true);
        } catch (Throwable $e) {
            return redirect()->to('/holidays')->with('error', 'Could not reach the public holiday API right now. Please try again later, or add holidays manually.');
        }

        if (! is_array($items) || $items === []) {
            return redirect()->to('/holidays')->with('error', "No holiday data was returned for {$countryCode} {$year} — this API's coverage varies by country. Add holidays manually if needed.");
        }

        $imported = 0;
        $skipped  = 0;

        foreach ($items as $item) {
            $date = $item['date'] ?? null;
            $name = $item['localName'] ?? ($item['name'] ?? null);

            if (! $date || ! $name || $this->holidays->externalRefExists($companyId, $date)) {
                $skipped++;
                continue;
            }

            $this->holidays->insert([
                'company_id'   => $companyId,
                'name'         => $name,
                'date'         => $date,
                'holiday_type' => 'legal',
                'scope_type'   => 'national',
                'scope_value'  => null,
                'source'       => 'api_import',
                'external_ref' => $date,
            ]);
            $imported++;
        }

        return redirect()->to('/holidays')->with('success', "Imported {$imported} holiday(s) for {$countryCode} {$year}, skipped {$skipped} already-recorded/invalid entr" . ($skipped === 1 ? 'y' : 'ies') . '.');
    }

    private function fields(array $post, int $companyId): array
    {
        $scopeType = in_array($post['scope_type'] ?? 'national', ['national', 'regional', 'local'], true)
            ? $post['scope_type']
            : 'national';

        return [
            'company_id'   => $companyId,
            'name'         => trim((string) ($post['name'] ?? '')),
            'date'         => trim((string) ($post['date'] ?? '')),
            'holiday_type' => in_array($post['holiday_type'] ?? 'legal', ['legal', 'special'], true) ? $post['holiday_type'] : 'legal',
            'scope_type'   => $scopeType,
            'scope_value'  => $scopeType !== 'national' ? (trim((string) ($post['scope_value'] ?? '')) ?: null) : null,
        ];
    }
}
