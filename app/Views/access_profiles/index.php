<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Access profiles</h1>
    <p class="sub">Named roles you can assign to employees (e.g. "HR", "Employee")</p>
  </div>
  <a class="btn primary" href="<?= site_url('access-profiles/new') ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add profile</a>
</div>

<?php if (empty($profiles)): ?>
  <div class="empty">
    No access profiles yet. <a href="<?= site_url('access-profiles/new') ?>">Add your first one</a> to get started.
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Profile</th>
          <th>Modules</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($profiles as $p): ?>
          <tr>
            <td><strong><?= esc($p['name']) ?></strong></td>
            <td>
              <?php if (empty($p['modules'])): ?>
                <span class="muted">None</span>
              <?php else: ?>
                <?php foreach ($p['modules'] as $key): ?>
                  <span class="badge active" style="margin:2px 4px 2px 0;"><?= esc($modules[$key] ?? $key) ?></span>
                <?php endforeach; ?>
              <?php endif; ?>
            </td>
            <td style="white-space:nowrap;">
              <a class="btn sm" href="<?= site_url('access-profiles/' . $p['id'] . '/edit') ?>">Edit</a>
              <form method="post" action="<?= site_url('access-profiles/' . $p['id'] . '/delete') ?>" style="display:inline;"
                    onsubmit="return confirm('Delete the <?= esc($p['name'], 'js') ?> profile?');">
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
