<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Time &amp; attendance</h1>
    <p class="sub">Sample data — this module is not wired to the database yet</p>
  </div>
</div>

<div class="table-wrap">
  <table>
    <thead><tr><th>Employee</th><th>Date</th><th>Time in</th><th>Time out</th><th>Hours</th></tr></thead>
    <tbody>
      <tr><td>Juan Dela Cruz</td><td>Jul 17, 2026</td><td>8:02 AM</td><td>5:05 PM</td><td>8.0</td></tr>
      <tr><td>Ana Santos</td><td>Jul 17, 2026</td><td>8:45 AM</td><td>5:47 PM</td><td>8.0</td></tr>
      <tr><td>Mark Villanueva</td><td>Jul 17, 2026</td><td>7:58 AM</td><td>—</td><td>—</td></tr>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
