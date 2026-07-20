<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit   = $branch !== null;
$val      = static fn (string $key) => esc(old($key, $branch[$key] ?? ''));
$selected = (int) old('company_id', $branch['company_id'] ?? $preselect ?? 0);
$status   = old('status', $branch['status'] ?? 'active');
$isHq     = (bool) old('is_hq', $branch['is_hq'] ?? false);
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit branch' : 'Add branch' ?></h1>
    <p class="sub"><a href="<?= site_url('branches') ?>">&larr; Back to branches</a></p>
  </div>
</div>

<div class="form-card">
  <form method="post" action="<?= $isEdit ? site_url('branches/' . $branch['id']) : site_url('branches') ?>">
    <?= csrf_field() ?>
    <div class="form-grid">
      <div>
        <label for="company_id">Company *</label>
        <select id="company_id" name="company_id" required>
          <option value="">Choose a company…</option>
          <?php foreach ($companies as $c): ?>
            <option value="<?= esc($c['id']) ?>" <?= $selected === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="name">Branch name *</label>
        <input type="text" id="name" name="name" value="<?= $val('name') ?>" required>
      </div>
      <div>
        <label for="code">Branch code</label>
        <input type="text" id="code" name="code" value="<?= $val('code') ?>" placeholder="e.g. MKT-01">
      </div>
      <div>
        <label for="status">Status *</label>
        <select id="status" name="status" required>
          <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <div>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= $val('email') ?>">
      </div>
      <div>
        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="<?= $val('phone') ?>">
      </div>
      <div class="full">
        <label for="address_line">Address</label>
        <input type="text" id="address_line" name="address_line" value="<?= $val('address_line') ?>">
      </div>
      <div>
        <label for="city">City</label>
        <input type="text" id="city" name="city" value="<?= $val('city') ?>">
      </div>
      <div>
        <label for="province">Province / State</label>
        <input type="text" id="province" name="province" value="<?= $val('province') ?>">
      </div>
      <div>
        <label for="postal_code">Postal code</label>
        <input type="text" id="postal_code" name="postal_code" value="<?= $val('postal_code') ?>">
      </div>
      <div>
        <label for="country">Country</label>
        <input type="text" id="country" name="country" value="<?= esc(old('country', $branch['country'] ?? 'Philippines')) ?>">
      </div>
      <div class="full">
        <label class="check">
          <input type="checkbox" name="is_hq" value="1" <?= $isHq ? 'checked' : '' ?>>
          This branch is the company headquarters
        </label>
        <p class="muted" style="font-size:12px; color:var(--text-secondary); margin:4px 0 0;">Marking this as HQ removes the HQ flag from any other branch of the same company.</p>
      </div>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add branch' ?></button>
      <a class="btn" href="<?= site_url('branches') ?>">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
