<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$isEdit = $employee !== null;
$val    = static fn (string $key, $default = '') => esc(old($key, $employee[$key] ?? $default));
$sel    = static fn ($optionId, $current) => ((int) $optionId === (int) $current) ? 'selected' : '';
?>

<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit employee' : 'Add employee' ?></h1>
    <p class="sub"><a href="<?= site_url('employee-management') ?>">&larr; Back to employee management</a></p>
  </div>
</div>

<div class="form-card">
  <form method="post"
        action="<?= $isEdit ? site_url('employee-management/' . $employee['id']) : site_url('employee-management') ?>">
    <?= csrf_field() ?>

    <p class="section-label">Account</p>
    <div class="form-grid">
      <div>
        <label for="name">Full name *</label>
        <input type="text" id="name" name="name" value="<?= $val('name', $employee['user_name'] ?? '') ?>" required>
      </div>
      <div>
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" value="<?= $val('email', $employee['user_email'] ?? '') ?>" required>
      </div>
      <?php if (! $isEdit): ?>
        <div>
          <label for="password">Initial password *</label>
          <input type="text" id="password" name="password" minlength="8" required>
          <p class="muted" style="margin-top:4px;">Share this with the employee directly — there's no email step.</p>
        </div>
      <?php endif; ?>
    </div>

    <p class="section-label" style="margin-top:1.5rem;">Assignment</p>
    <div class="form-grid">
      <div>
        <label for="company_id">Company *</label>
        <?php if (count($companies) === 1): ?>
          <input type="hidden" name="company_id" value="<?= esc($companies[0]['id']) ?>">
          <input type="text" value="<?= esc($companies[0]['name']) ?>" disabled>
        <?php else: ?>
          <select id="company_id" name="company_id" required>
            <?php foreach ($companies as $c): ?>
              <option value="<?= esc($c['id']) ?>" <?= $sel($c['id'], $employee['company_id'] ?? 0) ?>><?= esc($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        <?php endif; ?>
      </div>
      <div>
        <label for="branch_id">Branch</label>
        <select id="branch_id" name="branch_id">
          <option value="">— None —</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= esc($b['id']) ?>" <?= $sel($b['id'], $employee['branch_id'] ?? 0) ?>><?= esc($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="department_id">Department</label>
        <select id="department_id" name="department_id">
          <option value="">— None —</option>
          <?php foreach ($departments as $d): ?>
            <option value="<?= esc($d['id']) ?>" <?= $sel($d['id'], $employee['department_id'] ?? 0) ?>><?= esc($d['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="position_id">Position</label>
        <select id="position_id" name="position_id">
          <option value="">— None —</option>
          <?php foreach ($positions as $p): ?>
            <option value="<?= esc($p['id']) ?>" <?= $sel($p['id'], $employee['position_id'] ?? 0) ?>><?= esc($p['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="employee_number">Employee number</label>
        <input type="text" id="employee_number" name="employee_number" value="<?= $val('employee_number') ?>">
      </div>
      <div>
        <label for="hire_date">Hire date</label>
        <input type="date" id="hire_date" name="hire_date" value="<?= $val('hire_date') ?>">
      </div>
      <div>
        <label for="status">Status</label>
        <select id="status" name="status">
          <option value="active" <?= ($employee['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= ($employee['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
    </div>

    <p class="section-label" style="margin-top:1.5rem;">Access</p>
    <div class="form-grid">
      <div>
        <label for="access_profile_id">Access profile</label>
        <select id="access_profile_id" name="access_profile_id">
          <option value="">— None —</option>
          <?php foreach ($accessProfiles as $ap): ?>
            <option value="<?= esc($ap['id']) ?>" <?= $sel($ap['id'], $employee['access_profile_id'] ?? 0) ?>><?= esc($ap['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="full">
        <label>Additional individual access</label>
        <p class="muted" style="margin:2px 0 8px;">Grants beyond whatever the access profile above already includes.</p>
        <?php foreach ($modules as $key => $label): ?>
          <label style="display:inline-flex; align-items:center; gap:6px; margin:0 16px 8px 0; font-weight:400;">
            <input type="checkbox" name="modules[]" value="<?= esc($key) ?>" style="width:auto;"
                   <?= in_array($key, $grants, true) ? 'checked' : '' ?>>
            <?= esc($label) ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn primary"><?= $isEdit ? 'Save changes' : 'Add employee' ?></button>
      <a class="btn" href="<?= site_url('employee-management') ?>">Cancel</a>
    </div>
  </form>
</div>

<?php if ($isEdit): ?>
  <div class="form-card" style="margin-top:20px;">
    <p class="section-label">Reset password</p>
    <p class="muted" style="margin:2px 0 12px;">Sets a new password immediately — share it with the employee directly.</p>
    <form method="post" action="<?= site_url('employee-management/' . $employee['id'] . '/reset-password') ?>" style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
      <?= csrf_field() ?>
      <div>
        <label for="reset_password">New password</label>
        <input type="text" id="reset_password" name="password" minlength="8" required>
      </div>
      <button type="submit" class="btn">Reset password</button>
    </form>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
