<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Employees</h1>
    <p class="sub">Directory of everyone in <?= is_superadmin() ? 'the system' : 'your company' ?></p>
  </div>
  <?php if (can_access(\App\Constants\Modules::EMPLOYEE_MANAGEMENT)): ?>
    <a class="btn primary" href="<?= site_url('employee-management/new') ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add employee</a>
  <?php endif; ?>
</div>

<?php if (empty($employees)): ?>
  <div class="empty">No employees yet.</div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Employee</th>
          <th>Company</th>
          <th>Branch</th>
          <th>Department</th>
          <th>Position</th>
          <th>Access</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employees as $e): ?>
          <tr>
            <td><strong><?= esc($e['user_name']) ?></strong><div class="muted"><?= esc($e['user_email']) ?></div></td>
            <td><?= esc($e['company_name']) ?></td>
            <td><?= esc($e['branch_name'] ?? '—') ?></td>
            <td><?= esc($e['department_name'] ?? '—') ?></td>
            <td><?= esc($e['position_title'] ?? '—') ?></td>
            <td><?= esc($e['access_profile_name'] ?? '—') ?></td>
            <td><span class="badge <?= $e['status'] === 'active' ? 'active' : 'inactive' ?>"><?= esc(ucfirst($e['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
