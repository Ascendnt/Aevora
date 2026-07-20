<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Employees</h1>
    <p class="sub">Sample data — this module is not wired to the database yet</p>
  </div>
</div>

<div class="table-wrap">
  <table>
    <thead><tr><th>Employee</th><th>Position</th><th>Branch</th><th>Status</th></tr></thead>
    <tbody>
      <tr><td><strong>Juan Dela Cruz</strong><div class="muted">EMP-0001</div></td><td>HR Officer</td><td>Main Branch</td><td><span class="badge active">Active</span></td></tr>
      <tr><td><strong>Ana Santos</strong><div class="muted">EMP-0002</div></td><td>Accountant</td><td>Main Branch</td><td><span class="badge active">Active</span></td></tr>
      <tr><td><strong>Mark Villanueva</strong><div class="muted">EMP-0003</div></td><td>Branch Supervisor</td><td>Cebu Branch</td><td><span class="badge active">Active</span></td></tr>
      <tr><td><strong>Liza Fernandez</strong><div class="muted">EMP-0004</div></td><td>Sales Associate</td><td>Davao Branch</td><td><span class="badge inactive">On leave</span></td></tr>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
