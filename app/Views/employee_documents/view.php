<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<style>
  @media print {
    .sidebar, .no-print { display: none !important; }
    .main { padding: 0 !important; }
    .doc-paper { box-shadow: none !important; border: none !important; }
  }
</style>

<div class="page-head no-print">
  <div>
    <h1><?= esc($document['title']) ?></h1>
    <p class="sub"><a href="<?= site_url('employee-management/' . $employee['id'] . '/documents') ?>">&larr; Back to <?= esc($employee['user_name']) ?>'s documents</a></p>
  </div>
  <div style="display:flex; gap:10px;">
    <?php if (empty($document['file_path'])): ?>
      <a class="btn" href="<?= site_url('documents/' . $document['id'] . '/pdf') ?>"><i class="ti ti-file-type-pdf" aria-hidden="true"></i>Download PDF</a>
    <?php endif; ?>
    <button type="button" class="btn primary" onclick="window.print()"><i class="ti ti-printer" aria-hidden="true"></i>Print / Save as PDF</button>
  </div>
</div>

<div class="form-card doc-paper" style="max-width:760px; margin:0 auto; font-family: Georgia, 'Times New Roman', serif; line-height:1.7;">
  <p class="muted" style="margin-bottom:1.25rem;">
    <?= esc($document['type_name'] ?? '') ?><?= ! empty($document['template_name']) ? ' · ' . esc($document['template_name']) : '' ?>
    <?php if (! empty($document['issued_date'])): ?> · Issued <?= esc($document['issued_date']) ?><?php endif; ?>
  </p>
  <div style="white-space: pre-wrap;"><?= esc($document['content'] ?? '') ?></div>
</div>

<?= $this->endSection() ?>
