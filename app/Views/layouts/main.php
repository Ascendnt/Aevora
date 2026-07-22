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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/3.31.0/tabler-icons.min.css">
  <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Bricolage+Grotesque:wght@400;500;600&display=swap" rel="stylesheet">
  <script>var t=document.cookie.match(/hris_theme=(\w+)/);if(t&&t[1]==='dark')document.documentElement.setAttribute('data-theme','dark');</script>
</head>
<body>
<div class="shell">
  <aside class="sidebar">
    <div class="brand">
      <span class="brand-mark" aria-hidden="true">
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
      <?= esc($brand) ?>
    </div>
    <nav class="nav" aria-label="Main navigation">
      <a href="<?= site_url('dashboard') ?>" class="<?= $active === 'dashboard' ? 'active' : '' ?>"><i class="ti ti-layout-dashboard" aria-hidden="true"></i>Dashboard</a>
      <a href="<?= site_url('notifications') ?>" class="<?= $active === 'notifications' ? 'active' : '' ?>"><i class="ti ti-bell" aria-hidden="true"></i>Notifications</a>
      <?php if (! is_superadmin() && current_employee() !== null): ?>
        <a href="<?= site_url('my-profile') ?>" class="<?= $active === 'my-profile' ? 'active' : '' ?>"><i class="ti ti-user-circle" aria-hidden="true"></i>My profile</a>
      <?php endif; ?>
      <?php if (can_access(Modules::EMPLOYEES)): ?>
        <a href="<?= site_url('employees') ?>" class="<?= $active === 'employees' ? 'active' : '' ?>"><i class="ti ti-users" aria-hidden="true"></i>Employees</a>
      <?php endif; ?>
      <?php if (can_access(Modules::DOCUMENTS)): ?>
        <a href="<?= site_url('document-templates') ?>" class="<?= $active === 'document-templates' ? 'active' : '' ?>"><i class="ti ti-files" aria-hidden="true"></i>Documents</a>
      <?php endif; ?>
      <?php if (can_access(Modules::TIME_ATTENDANCE)): ?>
        <a href="<?= site_url('attendance') ?>" class="<?= $active === 'attendance' ? 'active' : '' ?>"><i class="ti ti-clock" aria-hidden="true"></i>Time &amp; attendance</a>
      <?php endif; ?>
      <?php if (can_access(Modules::FILINGS)): ?>
        <a href="<?= site_url('filings') ?>" class="<?= $active === 'filings' ? 'active' : '' ?>"><i class="ti ti-calendar-off" aria-hidden="true"></i>Filings</a>
      <?php endif; ?>
      <?php if (can_access(Modules::PAYROLL)): ?>
        <a href="<?= site_url('payroll') ?>" class="<?= $active === 'payroll' ? 'active' : '' ?>"><i class="ti ti-receipt" aria-hidden="true"></i>Payroll</a>
      <?php endif; ?>
      <?php if (can_access(Modules::COMPANY_SETTINGS) || can_access(Modules::EMPLOYEE_MANAGEMENT) || is_superadmin()): ?>
        <div class="divider" role="separator"></div>
      <?php endif; ?>
      <?php if (can_access(Modules::COMPANY_SETTINGS)): ?>
        <a href="<?= site_url('companies') ?>" class="<?= $active === 'companies' ? 'active' : '' ?>"><i class="ti ti-sitemap" aria-hidden="true"></i>Company settings</a>
      <?php endif; ?>
      <?php if (can_access(Modules::EMPLOYEE_MANAGEMENT)): ?>
        <a href="<?= site_url('employee-management') ?>" class="<?= $active === 'employee-mgmt' ? 'active' : '' ?>"><i class="ti ti-id-badge-2" aria-hidden="true"></i>Employee management</a>
      <?php endif; ?>
      <?php if (is_superadmin()): ?>
        <a href="<?= site_url('access-profiles') ?>" class="<?= $active === 'access-profiles' ? 'active' : '' ?>"><i class="ti ti-shield-lock" aria-hidden="true"></i>Access profiles</a>
      <?php endif; ?>
    </nav>
    <div class="foot nav">
      <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode" style="margin:0 auto 10px;">
        <i class="ti ti-moon" aria-hidden="true"></i>
      </button>
      <div class="divider" role="separator"></div>
      <a href="<?= site_url('logout') ?>"><i class="ti ti-logout" aria-hidden="true"></i>Sign out</a>
    </div>
    <!-- <div class="foot nav">
      <div class="divider" role="separator"></div>
      <a href="<?= site_url('logout') ?>"><i class="ti ti-logout" aria-hidden="true"></i>Sign out</a>
    </div> -->
  </aside>

  <main class="main">
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
      var saved = document.cookie.match(/hris_theme=(\w+)/);
      if (saved && saved[1] === 'dark') root.setAttribute('data-theme', 'dark');
      var btn = document.getElementById('themeToggle');
      function sync() {
        var dark = root.getAttribute('data-theme') === 'dark';
        btn.querySelector('i').className = dark ? 'ti ti-sun' : 'ti ti-moon';
      }
      sync();
      btn.addEventListener('click', function () {
        var dark = root.getAttribute('data-theme') === 'dark';
        if (dark) { root.removeAttribute('data-theme'); document.cookie = 'hris_theme=light;path=/;max-age=31536000'; }
        else { root.setAttribute('data-theme', 'dark'); document.cookie = 'hris_theme=dark;path=/;max-age=31536000'; }
        sync();
      });
    })();
    </script>
    <?= $this->include('partials/assistant') ?>
</body>
</html>