<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit    = $holiday !== null;
$val       = static fn (string $key, $default = '') => esc(old($key, $holiday[$key] ?? $default));
$selected  = (int) old('company_id', $holiday['company_id'] ?? $preselect ?? 0);
$type      = old('holiday_type', $holiday['holiday_type'] ?? 'legal');
$scopeType = old('scope_type', $holiday['scope_type'] ?? 'national');
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit holiday' : 'Add holiday' ?></h1>
    <p class="sub"><a href="<?= site_url('holidays') ?>">&larr; Back to holidays</a></p>
  </div>
</div>

<div class="form-card">
  <form method="post" action="<?= $isEdit ? site_url('holidays/' . $holiday['id']) : site_url('holidays') ?>">
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
              <option value="<?= esc($c['id']) ?>" <?= $selected === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>
      <div>
        <label for="name">Holiday name *</label>
        <input type="text" id="name" name="name" value="<?= $val('name') ?>" required>
      </div>
      <div>
        <label for="date">Date *</label>
        <input type="date" id="date" name="date" value="<?= $val('date') ?>" required>
      </div>
      <div>
        <label for="holiday_type">Holiday type *</label>
        <select id="holiday_type" name="holiday_type" required>
          <option value="legal" <?= $type === 'legal' ? 'selected' : '' ?>>Legal</option>
          <option value="special" <?= $type === 'special' ? 'selected' : '' ?>>Special</option>
        </select>
      </div>
      <div>
        <label for="scope_type">Scope *</label>
        <select id="scope_type" name="scope_type" required onchange="document.getElementById('scopeValueRow').style.display = this.value === 'national' ? 'none' : 'block';">
          <option value="national" <?= $scopeType === 'national' ? 'selected' : '' ?>>National</option>
          <option value="regional" <?= $scopeType === 'regional' ? 'selected' : '' ?>>Regional</option>
          <option value="local" <?= $scopeType === 'local' ? 'selected' : '' ?>>Local</option>
        </select>
      </div>
      <div id="scopeValueRow" class="full" style="<?= $scopeType === 'national' ? 'display:none;' : '' ?>">
        <label for="scope_value">Region / locality</label>
        <input type="text" id="scope_value" name="scope_value" value="<?= $val('scope_value') ?>" placeholder="e.g. Metro Manila">
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add holiday' ?></button>
      <a class="btn" href="<?= site_url('holidays') ?>">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
