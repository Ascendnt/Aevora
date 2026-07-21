<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $isEdit = $profile !== null; ?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit access profile' : 'Add access profile' ?></h1>
    <p class="sub"><a href="<?= site_url('access-profiles') ?>">&larr; Back to access profiles</a></p>
  </div>
</div>

<div class="form-card">
  <form method="post"
        action="<?= $isEdit ? site_url('access-profiles/' . $profile['id']) : site_url('access-profiles') ?>">
    <?= csrf_field() ?>

    <div class="form-grid">
      <div class="full">
        <label for="name">Profile name *</label>
        <input type="text" id="name" name="name" value="<?= esc(old('name', $profile['name'] ?? '')) ?>" required>
      </div>
      <div class="full">
        <label>Modules included</label>
        <?php foreach ($modules as $key => $label): ?>
          <label style="display:inline-flex; align-items:center; gap:6px; margin:6px 16px 6px 0; font-weight:400;">
            <input type="checkbox" name="modules[]" value="<?= esc($key) ?>" style="width:auto;"
                   <?= in_array($key, $checked, true) ? 'checked' : '' ?>>
            <?= esc($label) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add profile' ?></button>
      <a class="btn" href="<?= site_url('access-profiles') ?>">Cancel</a>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
