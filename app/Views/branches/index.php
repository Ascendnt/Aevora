<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Branches</h1>
    <p class="sub"><a href="<?= site_url('companies') ?>">&larr; Back to company settings</a></p>
  </div>
  <a class="btn primary" href="<?= site_url('branches/new' . ($filter ? '?company=' . $filter : '')) ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add branch</a>
</div>

<form method="get" action="<?= site_url('branches') ?>" style="margin-bottom:1rem; max-width:320px;">
  <label for="company">Filter by company</label>
  <select id="company" name="company" onchange="this.form.submit()">
    <option value="">All companies</option>
    <?php foreach ($companies as $c): ?>
      <option value="<?= esc($c['id']) ?>" <?= (int) $filter === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<?php if (empty($branches)): ?>
  <div class="empty">
    No branches found. <a href="<?= site_url('branches/new') ?>">Add a branch</a>.
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Branch</th>
          <th>Company</th>
          <th>Code</th>
          <th>Location</th>
          <th>Status</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($branches as $b): ?>
          <tr>
            <td>
              <a href="<?= site_url('branches/' . $b['id'] . '/edit') ?>"><strong><?= esc($b['name']) ?></strong></a>
              <?php if ($b['is_hq']): ?> <span class="badge hq">HQ</span><?php endif; ?>
            </td>
            <td><?= esc($b['company_name']) ?></td>
            <td><?= esc($b['code'] ?? '—') ?></td>
            <td><?= esc(trim(($b['city'] ?? '') . ', ' . ($b['province'] ?? ''), ', ') ?: '—') ?></td>
            <td><span class="badge <?= esc($b['status']) ?>"><?= esc(ucfirst($b['status'])) ?></span></td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('branches/' . $b['id'] . '/edit') ?>">Edit</a>
              <form method="post" action="<?= site_url('branches/' . $b['id'] . '/delete') ?>" style="display:inline;"
                    onsubmit="return confirm('Delete branch <?= esc($b['name'], 'js') ?>?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn sm danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
