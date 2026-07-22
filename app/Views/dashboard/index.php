<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$name     = session()->get('user_name') ?? 'there';
$initials = strtoupper(implode('', array_map(static fn ($p) => $p[0] ?? '', array_slice(explode(' ', $name), 0, 2))));
$hour     = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$hasProfile = ! is_superadmin() && current_employee() !== null;
?>

<div class="page-head">
  <div>
    <h1><?= esc($greeting) ?>, <?= esc(explode(' ', $name)[0]) ?></h1>
    <p class="sub"><?= esc($companyLabel) ?></p>
  </div>
  <?php if ($hasProfile): ?>
    <a href="<?= site_url('my-profile') ?>" class="profile-btn" style="text-decoration:none;">
      <div class="who">
        <div class="nm"><?= esc($name) ?></div>
        <div class="rl"><?= esc($roleLabel) ?></div>
      </div>
      <div class="avatar"><?= esc($initials) ?></div>
    </a>
  <?php else: ?>
    <div class="profile-btn" style="cursor:default;">
      <div class="who">
        <div class="nm"><?= esc($name) ?></div>
        <div class="rl"><?= esc($roleLabel) ?></div>
      </div>
      <div class="avatar"><?= esc($initials) ?></div>
    </div>
  <?php endif; ?>
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
    <p class="label">Clocked in today</p>
    <p class="value"><?= esc($attendanceToday['clockedIn']) ?> <span style="font-size:0.5em; font-weight:500; color:var(--text-secondary);">/ <?= esc($attendanceToday['active']) ?></span></p>
  </div>
  <div class="stat">
    <p class="label">Turnover (YTD)</p>
    <p class="value"><?= esc($turnoverYtd['rate']) ?>%</p>
  </div>
</div>

<div class="card-grid" style="margin-top:24px;">
  <div class="form-card" style="max-width:none;">
    <p class="section-label" style="margin-top:0;">Attendance today</p>
    <?php if ($attendanceToday['active'] === 0): ?>
      <p class="muted">No active employees yet.</p>
    <?php else: ?>
      <div style="display:flex; gap:20px; flex-wrap:wrap;">
        <div>
          <p class="muted" style="margin:0 0 2px;">Clocked in</p>
          <p style="margin:0; font-weight:600; font-size:1.1em;"><?= esc($attendanceToday['clockedIn']) ?></p>
        </div>
        <div>
          <p class="muted" style="margin:0 0 2px;">Not yet in</p>
          <p style="margin:0; font-weight:600; font-size:1.1em;"><?= esc($attendanceToday['notYet']) ?></p>
        </div>
        <div>
          <p class="muted" style="margin:0 0 2px;">Late (after 9am)</p>
          <p style="margin:0; font-weight:600; font-size:1.1em;"><?= esc($attendanceToday['late']) ?></p>
        </div>
      </div>
      <p class="muted" style="margin-top:10px;"><a href="<?= site_url('attendance') ?>">View time &amp; attendance &rarr;</a></p>
    <?php endif; ?>
  </div>

  <div class="form-card" style="max-width:none;">
    <p class="section-label" style="margin-top:0;">Birthdays this month</p>
    <?php if (empty($birthdaysThisMonth)): ?>
      <p class="muted">No birthdays on file for this month.</p>
    <?php else: ?>
      <ul style="margin:0; padding:0; list-style:none;">
        <?php foreach ($birthdaysThisMonth as $b): ?>
          <li style="display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px dashed var(--border);">
            <span><?= esc($b['user_name']) ?></span>
            <span class="muted"><?= esc(date('M j', strtotime($b['date_of_birth']))) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
