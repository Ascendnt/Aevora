<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Notifications</h1>
    <p class="sub">Reminders and updates for your account</p>
  </div>
</div>

<?php if (empty($notifications)): ?>
  <div class="empty">
    You're all caught up — no notifications yet.
  </div>
<?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Status</th>
          <th>Message</th>
          <th>Received</th>
          <th style="width:1%;"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($notifications as $n): ?>
          <?php $isUnread = ! db_bool($n['is_read']); ?>
          <tr>
            <td><span class="badge <?= $isUnread ? 'active' : 'inactive' ?>"><?= $isUnread ? 'Unread' : 'Read' ?></span></td>
            <td>
              <?php if (! empty($n['link'])): ?>
                <a href="<?= esc($n['link']) ?>"><?= esc($n['message']) ?></a>
              <?php else: ?>
                <?= esc($n['message']) ?>
              <?php endif; ?>
            </td>
            <td class="muted"><?= esc($n['created_at']) ?></td>
            <td style="white-space:nowrap;">
              <?php if ($isUnread): ?>
                <form method="post" action="<?= site_url('notifications/' . $n['id'] . '/mark-read') ?>" style="display:inline;">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn sm">Mark read</button>
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
