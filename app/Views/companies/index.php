<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Company settings</h1>
    <p class="sub">Manage companies and their branches</p>
  </div>
  <div style="display:flex; gap:8px;">
    <a class="btn" href="<?= site_url('branches') ?>"><i class="ti ti-map-pin" aria-hidden="true"></i>All branches</a>
    <?php if (is_superadmin()): ?>
      <a class="btn primary" href="<?= site_url('companies/new') ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add company</a>
    <?php endif; ?>
  </div>
</div>

<?php if (empty($companies)): ?>
  <div class="empty">
    No companies yet. <a href="<?= site_url('companies/new') ?>">Add your first company</a> to get started.
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Company</th>
          <th>Industry</th>
          <th>HQ</th>
          <th>Branches</th>
          <th>Contact</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($companies as $c): ?>
          <tr>
            <td>
              <a href="<?= site_url('companies/' . $c['id'] . '/edit') ?>"><strong><?= esc($c['name']) ?></strong></a>
              <?php if (! empty($c['legal_name'])): ?><div class="muted"><?= esc($c['legal_name']) ?></div><?php endif; ?>
            </td>
            <td><?= esc($c['industry'] ?? '—') ?></td>
            <td><?= esc($c['hq_city'] ?? '—') ?></td>
            <td><a href="<?= site_url('branches?company=' . $c['id']) ?>"><?= esc($c['branch_count']) ?></a></td>
            <td>
              <?= esc($c['email'] ?? '') ?>
              <?php if (! empty($c['phone'])): ?><div class="muted"><?= esc($c['phone']) ?></div><?php endif; ?>
            </td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('companies/' . $c['id'] . '/edit') ?>">Edit</a>
              <?php if (is_superadmin()): ?>
                <form method="post" action="<?= site_url('companies/' . $c['id'] . '/delete') ?>" style="display:inline;"
                      onsubmit="return confirm('Delete <?= esc($c['name'], 'js') ?> and all its branches?');">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn sm danger">Delete</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
