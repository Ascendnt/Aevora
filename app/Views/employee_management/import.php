<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Bulk import employees</h1>
    <p class="sub"><a href="<?= site_url('employee-management') ?>">&larr; Back to employee management</a></p>
  </div>
</div>

<div class="form-card">
  <p class="section-label">Upload CSV</p>
  <p class="muted" style="margin:2px 0 14px;">
    Expected headers: <code>name, email, password, employee_number, department, position, job_level, employee_rank,
    supervisor_email, basic_pay, pay_frequency, is_minimum_wage_earner, date_of_birth, hire_date, branch</code>.
    Only <code>email</code> is required in every row — any other column can be left out of the file entirely.
    If a row's email already belongs to an employee in this company, that employee is <strong>updated</strong>
    (any column you included and filled in is applied; blank or missing columns are left untouched — this is how
    bulk pay-rate updates work too: re-upload with just <code>email</code> and <code>basic_pay</code> filled in).
    Otherwise a new login and employee record are <strong>created</strong> (name and password, min. 8 characters, are required for new rows).
    Department, position, job level, employee rank, and branch are matched by exact name within this company and
    created automatically if they don't exist yet.
  </p>
  <p class="muted" style="margin:0 0 18px;">
    <a href="<?= site_url('employee-management/import/template') ?>">Download a CSV template</a> to start from.
    Only plain CSV is supported — if your data is in Excel, use "Save As &rarr; CSV" first; .xlsx files are not parsed directly.
  </p>

  <form method="post" action="<?= site_url('employee-management/import') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-grid">
      <?php if (count($companies) > 1): ?>
        <div>
          <label for="company_id">Company *</label>
          <select id="company_id" name="company_id" required>
            <option value="">Choose a company…</option>
            <?php foreach ($companies as $c): ?>
              <option value="<?= esc($c['id']) ?>" <?= (int) $preselect === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      <?php elseif (count($companies) === 1): ?>
        <input type="hidden" name="company_id" value="<?= esc($companies[0]['id']) ?>">
      <?php endif; ?>
      <div>
        <label for="csv_file">CSV file *</label>
        <input type="file" id="csv_file" name="csv_file" accept=".csv,text/csv" required>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary">Upload and import</button>
      <a class="btn" href="<?= site_url('employee-management') ?>">Cancel</a>
    </div>
  </form>
</div>

<?php if ($results !== null): ?>
  <div class="form-card" style="margin-top:20px;">
    <p class="section-label">Results</p>
    <p style="margin:6px 0 16px;">
      <?= esc($summary['total']) ?> row<?= $summary['total'] === 1 ? '' : 's' ?> processed —
      <strong><?= esc($summary['created']) ?></strong> created,
      <strong><?= esc($summary['updated']) ?></strong> updated,
      <strong><?= esc($summary['errors']) ?></strong> error<?= $summary['errors'] === 1 ? '' : 's' ?>.
    </p>

    <?php if (empty($results)): ?>
      <div class="empty">No data rows were found in that file.</div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:1%;">Row</th>
              <th>Email</th>
              <th>Status</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $r): ?>
              <tr>
                <td><?= esc($r['row']) ?></td>
                <td><?= esc($r['identifier']) ?></td>
                <td>
                  <?php if ($r['status'] === 'created'): ?>
                    <span class="badge active">Created</span>
                  <?php elseif ($r['status'] === 'updated'): ?>
                    <span class="badge hq">Updated</span>
                  <?php else: ?>
                    <span class="badge inactive" style="color:var(--text-danger);">Error</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (! empty($r['messages'])): ?>
                    <ul style="margin:0; padding-left:1.1rem;">
                      <?php foreach ($r['messages'] as $m): ?>
                        <li class="muted"><?= esc($m) ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php else: ?>
                    <span class="muted">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
