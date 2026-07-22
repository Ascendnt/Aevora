<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Employee management</h1>
    <p class="sub">Add employees, assign access, reset passwords</p>
  </div>
  <div style="display:flex; gap:10px;">
    <?php if (can_access_sub('employee_management.job_levels', \App\Constants\Modules::EMPLOYEE_MANAGEMENT)): ?>
      <a class="btn" href="<?= site_url('job-levels') ?>">Job levels</a>
    <?php endif; ?>
    <?php if (can_access_sub('employee_management.ranks', \App\Constants\Modules::EMPLOYEE_MANAGEMENT)): ?>
      <a class="btn" href="<?= site_url('employee-ranks') ?>">Employee ranks</a>
    <?php endif; ?>
    <a class="btn" href="<?= site_url('employee-management/import') ?>">Bulk import</a>
    <a class="btn primary" href="<?= site_url('employee-management/new') ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add employee</a>
  </div>
</div>

<?php if ($pendingProfileCount > 0): ?>
  <div class="alert" style="background:var(--bg-accent); color:var(--text-accent); margin-bottom:16px;">
    <a href="<?= site_url('employee-management/profile-requests') ?>" style="font-weight:600;">
      <?= esc($pendingProfileCount) ?> employee profile change request<?= $pendingProfileCount === 1 ? '' : 's' ?> waiting for review &rarr;
    </a>
  </div>
<?php endif; ?>

<?php if (empty($employees)): ?>
  <div class="empty">
    No employees yet. <a href="<?= site_url('employee-management/new') ?>">Add your first employee</a> to get started.
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employee</th>
          <th>Company</th>
          <th>Access profile</th>
          <th>Status</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employees as $e): ?>
          <tr>
            <td>
              <a href="<?= site_url('employee-management/' . $e['id'] . '/edit') ?>"><strong><?= esc($e['user_name']) ?></strong></a>
              <div class="muted"><?= esc($e['user_email']) ?></div>
            </td>
            <td><?= esc($e['company_name']) ?></td>
            <td><?= esc($e['access_profile_name'] ?? '—') ?></td>
            <td><span class="badge <?= $e['status'] === 'active' ? 'active' : 'inactive' ?>"><?= esc(ucfirst($e['status'])) ?></span></td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('employee-management/' . $e['id'] . '/edit') ?>">Edit</a>
              <form method="post" action="<?= site_url('employee-management/' . $e['id'] . '/toggle-status') ?>" style="display:inline;"
                    onsubmit="return confirm('<?= $e['status'] === 'active' ? 'Deactivate' : 'Reactivate' ?> <?= esc($e['user_name'], 'js') ?>?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn sm <?= $e['status'] === 'active' ? 'danger' : '' ?>">
                  <?= $e['status'] === 'active' ? 'Deactivate' : 'Reactivate' ?>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
