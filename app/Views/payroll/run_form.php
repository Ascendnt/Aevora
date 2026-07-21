<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>New payroll run</h1>
    <p class="sub"><a href="<?= site_url('payroll/runs') ?>">&larr; Back to payroll runs</a></p>
  </div>
</div>

<div class="form-card">
  <form method="post" action="<?= site_url('payroll/runs') ?>">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div>
        <label for="company_id">Company *</label>
        <?php if (count($companies) === 1): ?>
          <input type="hidden" name="company_id" value="<?= esc($companies[0]['id']) ?>">
          <input type="text" value="<?= esc($companies[0]['name']) ?>" disabled>
        <?php else: ?>
          <select id="company_id" name="company_id" required>
            <option value="">Choose a company&hellip;</option>
            <?php foreach ($companies as $c): ?>
              <option value="<?= esc($c['id']) ?>" <?= (int) old('company_id') === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>
      <div>
        <label for="cutoff_schedule_id">Cutoff schedule</label>
        <select id="cutoff_schedule_id" name="cutoff_schedule_id">
          <option value="">None / manual</option>
          <?php foreach ($cutoffSchedules as $cs): ?>
            <option value="<?= esc($cs['id']) ?>" <?= (int) old('cutoff_schedule_id') === (int) $cs['id'] ? 'selected' : '' ?>>
              <?= esc(ucfirst(str_replace('_', ' ', $cs['frequency']))) ?> &mdash; <?= esc(ucfirst($cs['scope_type'])) ?><?= count($companies) > 1 ? ' (' . esc($cs['company_name']) . ')' : '' ?>
            </option>
          <?php endforeach; ?>
        </select>
        <p class="muted" style="margin-top:6px;">Used to auto-derive the pay date if you leave it blank below.</p>
      </div>
      <div>
        <label for="period_start">Period start *</label>
        <input type="date" id="period_start" name="period_start" value="<?= esc(old('period_start')) ?>" required>
      </div>
      <div>
        <label for="period_end">Period end *</label>
        <input type="date" id="period_end" name="period_end" value="<?= esc(old('period_end')) ?>" required>
      </div>
      <div class="full">
        <label for="pay_date">Pay date</label>
        <input type="date" id="pay_date" name="pay_date" value="<?= esc(old('pay_date')) ?>">
        <p class="muted" style="margin-top:6px;">Leave blank to auto-derive from the cutoff schedule's pay date offset, if one is chosen.</p>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary">Create draft run</button>
      <a class="btn" href="<?= site_url('payroll/runs') ?>">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
