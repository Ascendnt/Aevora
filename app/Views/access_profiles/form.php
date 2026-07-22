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
        <p class="muted" style="margin:2px 0 8px;">Check a whole module for full access, or just its sub-areas below for narrower access.</p>
        <?php foreach ($modules as $key => $label): ?>
          <div style="margin-bottom:10px;">
            <label style="display:inline-flex; align-items:center; gap:6px; margin:0 16px 4px 0; font-weight:500;">
              <input type="checkbox" name="modules[]" value="<?= esc($key) ?>" style="width:auto;"
                     <?= in_array($key, $checked, true) ? 'checked' : '' ?>>
              <?= esc($label) ?>
            </label>
            <?php if (! empty($subModules[$key])): ?>
              <div style="margin-left:26px; display:flex; flex-wrap:wrap; gap:2px 16px;">
                <?php foreach ($subModules[$key] as $subKey => $subLabel): ?>
                  <label style="display:inline-flex; align-items:center; gap:6px; font-weight:400; color:var(--text-secondary);">
                    <input type="checkbox" name="modules[]" value="<?= esc($subKey) ?>" style="width:auto;"
                           <?= in_array($subKey, $checked, true) ? 'checked' : '' ?>>
                    <?= esc($subLabel) ?>
                  </label>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add profile' ?></button>
      <a class="btn" href="<?= site_url('access-profiles') ?>">Cancel</a>
    </div>
  </form>
</div>

<?php if ($isEdit): ?>
  <div class="form-card" style="margin-top:20px;">
    <p class="section-label">Employees using this profile</p>
    <p class="muted" style="margin:2px 0 12px;">
      To assign this profile to someone (or move them to a different one), edit them from Employee Management.
    </p>
    <?php if (empty($assignedTo)): ?>
      <p class="muted">Nobody is assigned this profile yet.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Employee</th><th>Company</th><th style="width:1%;"></th></tr></thead>
          <tbody>
            <?php foreach ($assignedTo as $a): ?>
              <tr>
                <td><?= esc($a['user_name']) ?></td>
                <td><?= esc($a['company_name']) ?></td>
                <td><a class="btn sm" href="<?= site_url('employee-management/' . $a['id'] . '/edit') ?>">Edit</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
