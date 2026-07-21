<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit   = $leaveType !== null;
$val      = static fn (string $key) => esc(old($key, $leaveType[$key] ?? ''));
$selected = (int) old('company_id', $leaveType['company_id'] ?? $preselect ?? 0);
$isPaid   = db_bool(old('is_paid', $leaveType['is_paid'] ?? true));
$rule     = old('filing_rule', $leaveType['filing_rule'] ?? 'before');
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit leave type' : 'Add leave type' ?></h1>
    <p class="sub"><a href="<?= site_url('leave-types') ?>">&larr; Back to leave types</a></p>
  </div>
</div>

<div class="form-card">
  <form method="post" action="<?= $isEdit ? site_url('leave-types/' . $leaveType['id']) : site_url('leave-types') ?>">
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
              <option value="<?= esc($c['id']) ?>" <?= $selected === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>
      <div>
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" value="<?= $val('name') ?>" required placeholder="e.g. Vacation leave">
      </div>
      <div>
        <label for="filing_rule">Filing rule *</label>
        <select id="filing_rule" name="filing_rule" required>
          <option value="before" <?= $rule === 'before' ? 'selected' : '' ?>>Before — must be filed in advance</option>
          <option value="after" <?= $rule === 'after' ? 'selected' : '' ?>>After — may be filed after the fact (e.g. sick leave)</option>
        </select>
      </div>
      <div>
        <label for="min_days_notice">Minimum days notice</label>
        <input type="number" id="min_days_notice" name="min_days_notice" min="0" step="1"
               value="<?= esc((string) old('min_days_notice', $leaveType['min_days_notice'] ?? 0)) ?>">
      </div>
      <div class="full">
        <label class="check">
          <input type="checkbox" name="is_paid" value="1" <?= $isPaid ? 'checked' : '' ?>>
          This leave type is paid
        </label>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add leave type' ?></button>
      <a class="btn" href="<?= site_url('leave-types') ?>">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
