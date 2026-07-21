<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Document templates</h1>
    <p class="sub">Token-based boilerplate used to generate employee documents — <code>{{token}}</code> placeholders get filled in with real employee data</p>
  </div>
  <a class="btn primary" href="<?= site_url('document-templates/new') ?>"><i class="ti ti-plus" aria-hidden="true"></i>Add template</a>
</div>

<?php if (empty($grouped)): ?>
  <div class="empty">
    No templates yet. <a href="<?= site_url('document-templates/new') ?>">Add one</a> to get started.
  </div>
<?php else: ?>
  <?php foreach ($grouped as $typeName => $rows): ?>
    <p class="section-label" style="margin-top:1.5rem;"><?= esc($typeName) ?></p>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Scope</th>
            <th>Status</th>
            <th style="width:1%;"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $t): ?>
            <tr>
              <td><strong><?= esc($t['name']) ?></strong></td>
              <td>
                <?php if ($t['company_id'] === null): ?>
                  <span class="badge active">System-wide</span>
                <?php else: ?>
                  <?= esc($t['company_name'] ?? 'Company') ?>
                <?php endif; ?>
              </td>
              <td><span class="badge <?= db_bool($t['is_active']) ? 'active' : 'inactive' ?>"><?= db_bool($t['is_active']) ? 'Active' : 'Inactive' ?></span></td>
              <td style="white-space:nowrap;">
                <?php if ($t['editable']): ?>
                  <a class="btn sm" href="<?= site_url('document-templates/' . $t['id'] . '/edit') ?>">Edit</a>
                  <form method="post" action="<?= site_url('document-templates/' . $t['id'] . '/delete') ?>" style="display:inline;"
                        onsubmit="return confirm('Delete the <?= esc($t['name'], 'js') ?> template?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn sm danger">Delete</button>
                  </form>
                <?php else: ?>
                  <span class="muted">Read-only</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?= $this->endSection() ?>
