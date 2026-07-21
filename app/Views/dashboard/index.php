<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$name     = session()->get('user_name') ?? 'there';
$initials = strtoupper(implode('', array_map(static fn ($p) => $p[0] ?? '', array_slice(explode(' ', $name), 0, 2))));
$hour     = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
?>

<div class="page-head">
  <div>
    <h1><?= esc($greeting) ?>, <?= esc(explode(' ', $name)[0]) ?></h1>
    <p class="sub"><?= esc($companyLabel) ?></p>
  </div>
  <div class="profile" id="profileMenu">
    <button type="button" class="profile-btn" id="profileBtn" aria-haspopup="true" aria-expanded="false">
      <div class="who">
        <div class="nm"><?= esc($name) ?></div>
        <div class="rl"><?= esc($roleLabel) ?></div>
      </div>
      <div class="avatar"><?= esc($initials) ?></div>
      <i class="ti ti-chevron-down chev" aria-hidden="true"></i>
    </button>
    <div class="profile-menu" role="menu">
      <div class="pm-head">
        <div class="nm"><?= esc($name) ?></div>
        <div class="em"><?= esc(session()->get('user_email') ?? 'admin@hris.test') ?></div>
      </div>
      <?php if (can_access(\App\Constants\Modules::COMPANY_SETTINGS)): ?>
        <a href="<?= site_url('companies') ?>" role="menuitem"><i class="ti ti-building" aria-hidden="true"></i> Company settings</a>
      <?php endif; ?>
      <a href="<?= site_url('dashboard') ?>" role="menuitem"><i class="ti ti-user-cog" aria-hidden="true"></i> My profile</a>
      <a href="<?= site_url('logout') ?>" class="pm-danger" role="menuitem"><i class="ti ti-logout" aria-hidden="true"></i> Sign out</a>
    </div>
  </div>
</div>

<div class="stat-grid">
  <div class="stat">
    <p class="label">Total employees</p>
    <p class="value"><?= esc($totalEmployees) ?></p>
  </div>
  <div class="stat">
    <p class="label">Branches</p>
    <p class="value"><?= esc($branchCount) ?></p>
  </div>
  <div class="stat">
    <p class="label">On leave today</p>
    <p class="value"><?= esc($onLeaveToday) ?></p>
  </div>
  <div class="stat">
    <p class="label">Payroll run</p>
    <p class="value"><?= esc($payrollRun) ?></p>
  </div>
</div>

<?php
use App\Constants\Modules;

$showCompanySettings   = can_access(Modules::COMPANY_SETTINGS);
$showEmployeeMgmt      = can_access(Modules::EMPLOYEE_MANAGEMENT);
$showEmployeesDirectory = ! $showEmployeeMgmt && can_access(Modules::EMPLOYEES);
?>
<?php if ($showCompanySettings || $showEmployeeMgmt || $showEmployeesDirectory): ?>
  <p class="section-label">Quick settings</p>
  <div class="card-grid">
    <?php if ($showCompanySettings): ?>
      <a class="card-link" href="<?= site_url('companies') ?>">
        <i class="ti ti-sitemap" aria-hidden="true"></i>
        <div>
          <p class="t">Company settings</p>
          <p class="d">Companies, branches, org structure, pay schedules<span class="sep"></span><?= esc($companyCount) ?> compan<?= $companyCount === 1 ? 'y' : 'ies' ?></p>
        </div>
      </a>
    <?php endif; ?>
    <?php if ($showEmployeeMgmt): ?>
      <a class="card-link" href="<?= site_url('employee-management') ?>">
        <i class="ti ti-id-badge-2" aria-hidden="true"></i>
        <div>
          <p class="t">Employee management</p>
          <p class="d">Add employees, assign access, reset passwords<span class="sep"></span><?= esc($totalEmployees) ?> employee<?= $totalEmployees === 1 ? '' : 's' ?></p>
        </div>
      </a>
    <?php elseif ($showEmployeesDirectory): ?>
      <a class="card-link" href="<?= site_url('employees') ?>">
        <i class="ti ti-id-badge-2" aria-hidden="true"></i>
        <div>
          <p class="t">Employees</p>
          <p class="d">Directory<span class="sep"></span><?= esc($totalEmployees) ?> employee<?= $totalEmployees === 1 ? '' : 's' ?></p>
        </div>
      </a>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>