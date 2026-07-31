<?php

use App\Constants\Modules;

$active = $active ?? '';
$brand  = hq_company_name();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? $brand) ?> · <?= esc($brand) ?></title>
  <?= $this->include('partials/head_assets') ?>
</head>
<body>
<div class="shell flex min-h-screen">
  <aside class="sidebar sticky top-0 h-screen w-[244px] shrink-0 flex flex-col bg-surface border-r border-line px-4 py-6">
    <!-- brand -->
    <a href="<?= site_url('dashboard') ?>" class="brand flex items-center gap-3 px-2 pb-7 no-underline text-ink">
      <span class="grid place-items-center w-9 h-9 rounded-[11px] bg-sage text-surface-hi shrink-0 shadow-soft dark:text-[#14180f]" aria-hidden="true">
        <svg viewBox="0 0 40 40" width="21" height="21" fill="none">
          <circle cx="20" cy="20" r="17" stroke="currentColor" stroke-width="1.4" stroke-dasharray="2 5" opacity=".5">
            <animateTransform attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="40s" repeatCount="indefinite"/>
          </circle>
          <path d="M20 8C15 12 12.5 15.5 12.5 20a7.5 7.5 0 0 0 15 0C27.5 15.5 25 12 20 8Z" fill="currentColor" opacity=".95">
            <animate attributeName="opacity" values=".78;1;.78" dur="4s" repeatCount="indefinite"/>
          </path>
          <path d="M20 27V17" stroke="var(--surface-1)" stroke-width="1.7" stroke-linecap="round"/>
          <circle cx="20" cy="4" r="1.5" fill="var(--clay)"/>
        </svg>
      </span>
      <span class="font-serif font-semibold text-[19px] tracking-[-.01em]"><?= esc($brand) ?></span>
    </a>

    <nav class="nav flex flex-col gap-0.5" aria-label="Main navigation">
      <a href="<?= site_url('dashboard') ?>" class="nav-link <?= $active === 'dashboard' ? 'active' : '' ?>"><?= icon('dashboard') ?>Dashboard</a>
      <a href="<?= site_url('notifications') ?>" class="nav-link <?= $active === 'notifications' ? 'active' : '' ?>"><?= icon('bell') ?>Notifications</a>
      <?php if (! is_superadmin() && current_employee() !== null): ?>
        <a href="<?= site_url('my-profile') ?>" class="nav-link <?= $active === 'my-profile' ? 'active' : '' ?>"><?= icon('user-circle') ?>My profile</a>
      <?php endif; ?>
      <?php if (can_access(Modules::EMPLOYEES)): ?>
        <a href="<?= site_url('employees') ?>" class="nav-link <?= $active === 'employees' ? 'active' : '' ?>"><?= icon('users') ?>Employees</a>
      <?php endif; ?>
      <?php if (can_access(Modules::DOCUMENTS)): ?>
        <a href="<?= site_url('document-templates') ?>" class="nav-link <?= $active === 'document-templates' ? 'active' : '' ?>"><?= icon('files') ?>Documents</a>
      <?php endif; ?>
      <?php if (can_access(Modules::TIME_ATTENDANCE)): ?>
        <a href="<?= site_url('attendance') ?>" class="nav-link <?= $active === 'attendance' ? 'active' : '' ?>"><?= icon('clock') ?>Time &amp; attendance</a>
      <?php endif; ?>
      <?php if (can_access(Modules::FILINGS)): ?>
        <a href="<?= site_url('filings') ?>" class="nav-link <?= $active === 'filings' ? 'active' : '' ?>"><?= icon('calendar-off') ?>Filings</a>
      <?php endif; ?>
      <?php if (can_access(Modules::PAYROLL)): ?>
        <a href="<?= site_url('payroll') ?>" class="nav-link <?= $active === 'payroll' ? 'active' : '' ?>"><?= icon('receipt') ?>Payroll</a>
      <?php endif; ?>

      <?php if (can_access(Modules::COMPANY_SETTINGS) || can_access(Modules::EMPLOYEE_MANAGEMENT) || is_superadmin()): ?>
        <p class="eyebrow px-3.5 pt-6 pb-2">Workspace</p>
      <?php endif; ?>
      <?php if (can_access(Modules::COMPANY_SETTINGS)): ?>
        <a href="<?= site_url('companies') ?>" class="nav-link <?= $active === 'companies' ? 'active' : '' ?>"><?= icon('sitemap') ?>Company settings</a>
      <?php endif; ?>
      <?php if (can_access(Modules::EMPLOYEE_MANAGEMENT)): ?>
        <a href="<?= site_url('employee-management') ?>" class="nav-link <?= $active === 'employee-mgmt' ? 'active' : '' ?>"><?= icon('id-badge-2') ?>Employee management</a>
      <?php endif; ?>
      <?php if (is_superadmin()): ?>
        <a href="<?= site_url('access-profiles') ?>" class="nav-link <?= $active === 'access-profiles' ? 'active' : '' ?>"><?= icon('shield-lock') ?>Access profiles</a>
      <?php endif; ?>
    </nav>

    <div class="mt-auto pt-4">
      <div class="h-px bg-line mx-1.5 mb-3"></div>
      <div class="flex items-center gap-2">
        <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
          <?= icon('moon') ?>
        </button>
        <a href="<?= site_url('logout') ?>" class="nav-link flex-1"><?= icon('logout') ?>Sign out</a>
      </div>
    </div>
  </aside>

  <main class="main flex-1 w-full max-w-[1240px] mx-auto px-6 py-8 md:px-12" style="animation: pageIn .5s var(--ease);">
    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
      <div class="alert error"><ul>
        <?php foreach ((array) session()->getFlashdata('errors') as $e): ?>
          <li><?= esc($e) ?></li>
        <?php endforeach; ?>
      </ul></div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
  </main>
</div>

<script>
  (function () {
    var root = document.documentElement;
    var btn = document.getElementById('themeToggle');
    var moon = <?= json_encode(icon('moon')) ?>;
    var sun  = <?= json_encode(icon('sun')) ?>;
    function sync() { btn.innerHTML = root.getAttribute('data-theme') === 'dark' ? sun : moon; }
    sync();
    btn.addEventListener('click', function () {
      var dark = root.getAttribute('data-theme') === 'dark';
      if (dark) { root.removeAttribute('data-theme'); document.cookie = 'hris_theme=light;path=/;max-age=31536000'; }
      else { root.setAttribute('data-theme', 'dark'); document.cookie = 'hris_theme=dark;path=/;max-age=31536000'; }
      sync();
    });
  })();
</script>

<style>
  /* shell responsive — collapse sidebar to a top bar on small screens */
  @media (max-width: 760px) {
    .shell { flex-direction: column; }
    .sidebar { position: static; width: 100%; height: auto; flex-direction: row; flex-wrap: wrap; align-items: center; }
    .sidebar .nav { flex-direction: row; flex-wrap: wrap; }
    .sidebar .brand { padding-bottom: 0; }
    .nav-link.active::before { display: none; }
    .main { padding: 20px; }
  }
  @media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }
</style>

<?= $this->include('partials/assistant') ?>
</body>
</html>
