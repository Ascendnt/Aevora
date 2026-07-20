<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-head">
  <div>
    <h1>Leave</h1>
    <p class="sub">Sample data — this module is not wired to the database yet</p>
  </div>
</div>

<div class="table-wrap">
  <table>
    <thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Status</th></tr></thead>
    <tbody>
      <tr><td>Liza Fernandez</td><td>Vacation leave</td><td>Jul 15 – Jul 18</td><td><span class="badge active">Approved</span></td></tr>
      <tr><td>Ana Santos</td><td>Sick leave</td><td>Jul 21</td><td><span class="badge inactive">Pending</span></td></tr>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
