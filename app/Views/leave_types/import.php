<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Import leave types</h1>
    <p class="sub"><a href="<?= site_url('leave-types') ?>">&larr; Back to leave types</a></p>
  </div>
</div>

<div class="form-card">
  <p class="section-label">CSV format</p>
  <p class="muted" style="margin:2px 0 20px;">
    Header row exactly: <code>name,is_paid,filing_rule,min_days_notice</code><br>
    <code>is_paid</code> accepts 1/0, true/false, yes/no. <code>filing_rule</code> must be <code>before</code> or <code>after</code> (defaults to <code>before</code> if left blank or invalid).
    A row whose <code>name</code> matches an existing leave type for the chosen company updates it in place; otherwise a new leave type is created.
  </p>

  <form method="post" action="<?= site_url('leave-types/import') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div>
        <label for="company_id">Company *</label>
        <?php if (count($companies) === 1): ?>
          <input type="hidden" name="company_id" value="<?= esc($companies[0]['id']) ?>">
          <input type="text" value="<?= esc($companies[0]['name']) ?>" disabled>
        <?php else: ?>
          <select id="company_id" name="company_id" required>
            <option value="">Choose a company…</option>
            <?php foreach ($companies as $c): ?>
              <option value="<?= esc($c['id']) ?>" <?= (int) $preselect === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>
      <div>
        <label for="csv_file">CSV file *</label>
        <input type="file" id="csv_file" name="csv_file" accept=".csv,text/csv" required>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary"><i class="ti ti-upload" aria-hidden="true"></i>Import</button>
      <a class="btn" href="<?= site_url('leave-types') ?>">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
