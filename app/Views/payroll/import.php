<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Bulk import benefits &amp; loans</h1>
    <p class="sub"><a href="<?= site_url('payroll') ?>">&larr; Back to payroll dashboard</a></p>
  </div>
</div>

<div class="form-card" style="max-width:none;">
  <form method="post" action="<?= site_url('payroll/import') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div>
        <label for="import_type">What are you importing? *</label>
        <select id="import_type" name="import_type" required>
          <option value="benefit">Benefits</option>
          <option value="loan">Loans</option>
        </select>
      </div>
      <div>
        <label for="csv">CSV file *</label>
        <input type="file" id="csv" name="csv" accept=".csv" required>
      </div>
    </div>
    <p class="muted" style="margin:10px 0 0;">
      Matches rows to employees by <code>employee_email</code>. If a row's employee already has a benefit/loan of the same type, it's updated instead of duplicated — safe to re-upload a corrected file.
    </p>
    <details style="margin-top:14px;">
      <summary style="cursor:pointer; font-weight:500;">Expected CSV columns</summary>
      <div style="display:flex; gap:24px; flex-wrap:wrap; margin-top:10px;">
        <div>
          <p class="section-label" style="margin-top:0;">Benefits template</p>
          <code style="display:block; white-space:pre; background:var(--surface-2, #f4f4f4); padding:10px; border-radius:8px; overflow-x:auto;">employee_email,benefit_type,amount,is_recurring,effective_date,end_date,notes</code>
        </div>
        <div>
          <p class="section-label" style="margin-top:0;">Loans template</p>
          <code style="display:block; white-space:pre; background:var(--surface-2, #f4f4f4); padding:10px; border-radius:8px; overflow-x:auto;">employee_email,loan_type,principal_amount,monthly_amortization,balance_remaining,start_date,end_date,status,notes</code>
        </div>
      </div>
    </details>
    <div class="form-actions">
      <button type="submit" class="btn primary">Upload &amp; import</button>
    </div>
  </form>
</div>

<?php if ($results !== null): ?>
  <p class="section-label">Import results &mdash; <?= esc(ucfirst($results['type'])) ?>s</p>
  <div class="stat-grid" style="margin-bottom:16px;">
    <div class="stat">
      <p class="label">Rows processed</p>
      <p class="value"><?= esc(count($results['rows'])) ?></p>
    </div>
    <div class="stat">
      <p class="label">Succeeded</p>
      <p class="value"><?= esc($results['success_count']) ?></p>
    </div>
    <div class="stat">
      <p class="label">Errors</p>
      <p class="value"><?= esc($results['error_count']) ?></p>
    </div>
  </div>

  <?php if ($results['error_count'] > 0): ?>
    <div class="alert error">This file has <?= esc($results['error_count']) ?> row(s) with errors &mdash; fix them in your spreadsheet and re-upload just the corrected rows.</div>
  <?php endif; ?>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Line</th>
          <th>Status</th>
          <th>Message</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($results['rows'] as $row): ?>
          <tr>
            <td><?= esc($row['line']) ?></td>
            <td><span class="badge <?= $row['status'] === 'success' ? 'active' : 'inactive' ?>"><?= esc(ucfirst($row['status'])) ?></span></td>
            <td><?= esc($row['message']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
