<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$name     = session()->get('user_name') ?? 'there';
$initials = strtoupper(implode('', array_map(static fn ($p) => $p[0] ?? '', array_slice(explode(' ', $name), 0, 2))));
$hour     = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$hasProfile = ! is_superadmin() && current_employee() !== null;

$in     = (int) $attendanceToday['clockedIn'];
$active = (int) $attendanceToday['active'];
$pct    = $active > 0 ? round($in / $active * 100) : 0;

$stats = [
  ['label' => 'Total employees', 'value' => esc($totalEmployees),            'icon' => 'users'],
  ['label' => 'Branches',        'value' => esc($branchCount),               'icon' => 'branch'],
  ['label' => 'Clocked in today','value' => esc($in) . '<span class="text-[.5em] font-medium text-ink-soft"> / ' . esc($active) . '</span>', 'icon' => 'clock'],
  ['label' => 'Turnover (YTD)',  'value' => esc($turnoverYtd['rate']) . '<span class="text-[.5em] font-medium text-ink-soft">%</span>', 'icon' => 'refresh'],
];
?>

<div class="page-head">
  <div>
    <p class="eyebrow mb-2"><?= esc(date('l, F j')) ?></p>
    <h1><?= esc($greeting) ?>, <em><?= esc(explode(' ', $name)[0]) ?></em></h1>
    <p class="sub"><?= esc($companyLabel) ?></p>
  </div>
  <?php if ($hasProfile): ?>
    <a href="<?= site_url('my-profile') ?>" class="profile-btn no-underline">
      <div class="who"><div class="nm"><?= esc($name) ?></div><div class="rl"><?= esc($roleLabel) ?></div></div>
      <div class="avatar"><?= esc($initials) ?></div>
    </a>
  <?php else: ?>
    <div class="profile-btn cursor-default">
      <div class="who"><div class="nm"><?= esc($name) ?></div><div class="rl"><?= esc($roleLabel) ?></div></div>
      <div class="avatar"><?= esc($initials) ?></div>
    </div>
  <?php endif; ?>
</div>

<div class="stat-grid">
  <?php foreach ($stats as $s): ?>
    <div class="stat">
      <div class="relative flex items-start justify-between">
        <div>
          <p class="label"><?= $s['label'] ?></p>
          <p class="value"><?= $s['value'] ?></p>
        </div>
        <span class="grid place-items-center w-9 h-9 rounded-[11px] bg-sage-soft text-sage-deep shrink-0"><?= icon($s['icon'], '', 18) ?></span>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="card-grid mt-6" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));">
  <!-- attendance -->
  <div class="form-card max-w-none">
    <p class="section-label !mt-0"><?= icon('clock', '', 15) ?>Attendance today</p>
    <?php if ($active === 0): ?>
      <p class="muted">No active employees yet.</p>
    <?php else: ?>
      <div class="flex items-end justify-between mb-4">
        <div>
          <p class="font-serif text-[34px] font-medium leading-none"><?= esc($in) ?><span class="text-[.45em] text-ink-soft"> / <?= esc($active) ?></span></p>
          <p class="muted text-[12.5px] mt-1"><?= esc($pct) ?>% clocked in</p>
        </div>
        <a href="<?= site_url('attendance') ?>" class="btn sm"><?= icon('arrow-right', '', 15) ?>View</a>
      </div>
      <div class="h-2 rounded-pill bg-surface-2 overflow-hidden">
        <div class="h-full rounded-pill bg-sage" style="width:<?= esc($pct) ?>%;transition:width .6s var(--ease);"></div>
      </div>
      <div class="grid grid-cols-3 gap-3 mt-5">
        <div class="rounded-ctl bg-surface-2 border border-line px-3 py-2.5">
          <p class="muted text-[11.5px]">Clocked in</p>
          <p class="font-serif text-[19px] font-medium"><?= esc($attendanceToday['clockedIn']) ?></p>
        </div>
        <div class="rounded-ctl bg-surface-2 border border-line px-3 py-2.5">
          <p class="muted text-[11.5px]">Not yet in</p>
          <p class="font-serif text-[19px] font-medium"><?= esc($attendanceToday['notYet']) ?></p>
        </div>
        <div class="rounded-ctl bg-surface-2 border border-line px-3 py-2.5">
          <p class="muted text-[11.5px]">Late</p>
          <p class="font-serif text-[19px] font-medium text-clay"><?= esc($attendanceToday['late']) ?></p>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <!-- birthdays -->
  <div class="form-card max-w-none">
    <p class="section-label !mt-0"><?= icon('calendar-star', '', 15) ?>Birthdays this month</p>
    <?php if (empty($birthdaysThisMonth)): ?>
      <div class="flex flex-col items-center justify-center text-center py-8">
        <span class="grid place-items-center w-12 h-12 rounded-full bg-clay-soft text-clay mb-3"><?= icon('calendar-star', '', 22) ?></span>
        <p class="muted">No birthdays on file for this month.</p>
      </div>
    <?php else: ?>
      <ul class="m-0 p-0 list-none">
        <?php foreach ($birthdaysThisMonth as $b): ?>
          <li class="flex items-center justify-between py-2.5 border-b border-dashed border-line last:border-0">
            <span class="flex items-center gap-3">
              <span class="grid place-items-center w-8 h-8 rounded-full bg-sage-soft text-sage-deep font-serif text-[13px]"><?= esc(strtoupper(substr($b['user_name'], 0, 1))) ?></span>
              <?= esc($b['user_name']) ?>
            </span>
            <span class="muted text-[12.5px]"><?= esc(date('M j', strtotime($b['date_of_birth']))) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
