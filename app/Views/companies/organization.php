<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1><?= esc($company['name']) ?></h1>
    <p class="sub"><a href="<?= site_url('companies') ?>">&larr; Back to company settings</a></p>
  </div>
</div>

<div class="tabs">
  <a href="<?= site_url('companies/' . $company['id'] . '/edit') ?>">Profile</a>
  <a class="active" href="<?= site_url('companies/' . $company['id'] . '/organization') ?>">Organizational structure</a>
</div>

<div class="page-head">
  <p class="section-label" style="margin:0;">Branches / Locations</p>
  <a class="btn sm primary" href="<?= site_url('branches/new?company=' . $company['id']) ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add branch</a>
</div>
<?php if (empty($branches)): ?>
  <div class="empty">No branches yet for this company.</div>
<?php else: ?>
  <div class="table-wrap" style="margin-bottom:2rem;">
    <table>
      <thead><tr><th>Branch</th><th>Code</th><th>Location</th><th>Status</th><th style="width:1%;"></th></tr></thead>
      <tbody>
        <?php foreach ($branches as $b): ?>
          <tr>
            <td><?= esc($b['name']) ?><?php if (db_bool($b['is_hq'])): ?> <span class="badge hq">HQ</span><?php endif; ?></td>
            <td><?= esc($b['code'] ?? '—') ?></td>
            <td><?= esc(trim(($b['city'] ?? '') . ', ' . ($b['province'] ?? ''), ', ') ?: '—') ?></td>
            <td><span class="badge <?= esc($b['status']) ?>"><?= esc(ucfirst($b['status'])) ?></span></td>
            <td style="white-space:nowrap;"><a class="btn sm" href="<?= site_url('branches/' . $b['id'] . '/edit') ?>">Edit</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<p class="section-label">Departments / Divisions</p>
<form method="post" action="<?= site_url('companies/' . $company['id'] . '/departments') ?>" style="display:flex; gap:8px; margin-bottom:10px; max-width:420px;">
  <?= csrf_field() ?>
  <input type="text" name="name" placeholder="e.g. Human Resources" required>
  <button class="btn primary" type="submit">Add</button>
</form>
<?php if (empty($departments)): ?>
  <div class="empty" style="margin-bottom:2rem;">No departments yet.</div>
<?php else: ?>
  <div class="table-wrap" style="margin-bottom:2rem; max-width:560px;">
    <table>
      <tbody>
        <?php foreach ($departments as $d): ?>
          <tr>
            <td><?= esc($d['name']) ?></td>
            <td style="width:1%;">
              <form method="post" action="<?= site_url('departments/' . $d['id'] . '/delete') ?>"
                    onsubmit="return confirm('Remove <?= esc($d['name'], 'js') ?>?');">
                <?= csrf_field() ?>
                <button class="btn sm danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<p class="section-label">Job positions / Titles</p>
<form method="post" action="<?= site_url('companies/' . $company['id'] . '/positions') ?>" style="display:flex; gap:8px; margin-bottom:10px; max-width:560px;">
  <?= csrf_field() ?>
  <input type="text" name="title" placeholder="e.g. HR Officer" required>
  <select name="department_id" style="max-width:200px;">
    <option value="">No department</option>
    <?php foreach ($departments as $d): ?>
      <option value="<?= esc($d['id']) ?>"><?= esc($d['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn primary" type="submit">Add</button>
</form>
<?php
$deptNames = array_column($departments, 'name', 'id');
?>
<?php if (empty($positions)): ?>
  <div class="empty">No positions yet.</div>
<?php else: ?>
  <div class="table-wrap" style="max-width:560px;">
    <table>
      <tbody>
        <?php foreach ($positions as $p): ?>
          <tr>
            <td><?= esc($p['title']) ?><?php if ($p['department_id']): ?><div class="muted"><?= esc($deptNames[$p['department_id']] ?? '') ?></div><?php endif; ?></td>
            <td style="width:1%;">
              <form method="post" action="<?= site_url('positions/' . $p['id'] . '/delete') ?>"
                    onsubmit="return confirm('Remove <?= esc($p['title'], 'js') ?>?');">
                <?= csrf_field() ?>
                <button class="btn sm danger" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>