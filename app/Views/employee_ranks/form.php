<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit    = $employeeRank !== null;
$val       = static fn (string $key) => esc(old($key, $employeeRank[$key] ?? ''));
$selected  = (int) old('company_id', $employeeRank['company_id'] ?? $preselect ?? 0);
$isExempt  = db_bool(old('is_exempt', $employeeRank['is_exempt'] ?? false));
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit employee rank' : 'Add employee rank' ?></h1>
    <p class="sub"><a href="<?= site_url('employee-ranks') ?>">&larr; Back to employee ranks</a></p>
  </div>
</div>

<div class="form-card">
  <form method="post" action="<?= $isEdit ? site_url('employee-ranks/' . $employeeRank['id']) : site_url('employee-ranks') ?>">
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
        <input type="text" id="name" name="name" value="<?= $val('name') ?>" placeholder="e.g. Rank &amp; File, Managerial, Executive" required>
      </div>
      <div>
        <label for="sort_order">Sort order</label>
        <input type="number" id="sort_order" name="sort_order" value="<?= $val('sort_order') ?: '0' ?>">
        <p class="muted" style="margin-top:4px;">Lower numbers appear first in dropdowns.</p>
      </div>
      <div class="full">
        <label class="check">
          <input type="checkbox" name="is_exempt" value="1" <?= $isExempt ? 'checked' : '' ?>>
          Exempt from late/undertime payroll deductions
        </label>
        <p class="muted" style="margin-top:4px;">Typically used for Managerial/Executive ranks — checked by the payroll engine.</p>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add rank' ?></button>
      <a class="btn" href="<?= site_url('employee-ranks') ?>">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
