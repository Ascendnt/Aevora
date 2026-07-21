<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $selectedType = old('filing_type', 'leave'); ?>

<div class="page-head">
  <div>
    <h1>New filing</h1>
    <p class="sub"><a href="<?= site_url('filings') ?>">&larr; Back to my filings</a></p>
  </div>
</div>

<div class="form-card" style="max-width:900px;">
  <?php if ($supervisorName): ?>
    <p class="muted" style="margin-top:0;">Filings you submit are routed to <strong><?= esc($supervisorName) ?></strong> for approval.</p>
  <?php else: ?>
    <p class="muted" style="margin-top:0; color:var(--text-danger);">No supervisor is assigned to you yet — a filing you submit now will have no approver until one is set.</p>
  <?php endif; ?>

  <form method="post" action="<?= site_url('filings') ?>" id="filingForm">
    <?= csrf_field() ?>

    <p class="section-label">Filing type</p>
    <div class="filing-type-grid">
      <label>
        <input type="radio" name="filing_type" value="leave" <?= $selectedType === 'leave' ? 'checked' : '' ?>>
        Leave
        <small>Vacation, sick, or other leave against your balance</small>
      </label>
      <label>
        <input type="radio" name="filing_type" value="official_business" <?= $selectedType === 'official_business' ? 'checked' : '' ?>>
        Official business
        <small>Off-site work, errands, client visits</small>
      </label>
      <label>
        <input type="radio" name="filing_type" value="schedule_change" <?= $selectedType === 'schedule_change' ? 'checked' : '' ?>>
        Schedule change
        <small>Request a different work schedule for specific dates</small>
      </label>
      <label>
        <input type="radio" name="filing_type" value="time_adjustment" <?= $selectedType === 'time_adjustment' ? 'checked' : '' ?>>
        Time adjustment
        <small>Correct a missed or wrong time in/out</small>
      </label>
    </div>

    <div data-section="leave">
      <p class="section-label">Leave details</p>
      <div class="form-grid">
        <div>
          <label for="leave_type_id">Leave type *</label>
          <select id="leave_type_id" name="leave_type_id">
            <option value="">Choose…</option>
            <?php foreach ($leaveTypes as $lt): ?>
              <option value="<?= esc($lt['id']) ?>" <?= (int) old('leave_type_id') === (int) $lt['id'] ? 'selected' : '' ?>>
                <?= esc($lt['name']) ?> (<?= db_bool($lt['is_paid']) ? 'Paid' : 'Unpaid' ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (empty($leaveTypes)): ?>
            <p class="muted" style="margin-top:6px;">No leave types have been set up for your company yet — ask HR.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div data-section="schedule_change">
      <p class="section-label">Schedule change details</p>
      <div class="form-grid">
        <div>
          <label for="requested_work_schedule_id">Requested schedule *</label>
          <select id="requested_work_schedule_id" name="requested_work_schedule_id">
            <option value="">Choose…</option>
            <?php foreach ($workSchedules as $ws): ?>
              <option value="<?= esc($ws['id']) ?>" <?= (int) old('requested_work_schedule_id') === (int) $ws['id'] ? 'selected' : '' ?>>
                <?= esc($ws['name']) ?> (<?= esc($ws['time_in']) ?>–<?= esc($ws['time_out']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
          <?php if (empty($workSchedules)): ?>
            <p class="muted" style="margin-top:6px;">No work schedules have been set up for your company yet — ask HR.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div data-section="official_business,time_adjustment">
      <p class="section-label">Time</p>
      <div class="form-grid">
        <div>
          <label for="requested_time_in">Time in</label>
          <input type="time" id="requested_time_in" name="requested_time_in" value="<?= esc(old('requested_time_in')) ?>">
        </div>
        <div>
          <label for="requested_time_out">Time out</label>
          <input type="time" id="requested_time_out" name="requested_time_out" value="<?= esc(old('requested_time_out')) ?>">
        </div>
      </div>
    </div>

    <p class="section-label" style="margin-top:1.5rem;">Dates</p>
    <p class="muted" style="margin-top:-6px;">Tick every date this filing applies to — they don't need to be contiguous. <span id="dayCount"></span></p>
    <div class="cal-months">
      <?php $oldDates = (array) old('dates', []); ?>
      <?php foreach ($calendarMonths as $month): ?>
        <div class="cal-month">
          <h4><?= esc($month['label']) ?></h4>
          <div class="cal-grid">
            <?php foreach (['M', 'T', 'W', 'T', 'F', 'S', 'S'] as $dow): ?>
              <div class="dow"><?= $dow ?></div>
            <?php endforeach; ?>
            <?php foreach ($month['weeks'] as $week): ?>
              <?php foreach ($week as $day): ?>
                <?php if ($day === null): ?>
                  <div class="blank"></div>
                <?php else: ?>
                  <label>
                    <input type="checkbox" name="dates[]" value="<?= esc($day) ?>" <?= in_array($day, $oldDates, true) ? 'checked' : '' ?>>
                    <span><?= (int) date('j', strtotime($day)) ?></span>
                  </label>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <p class="section-label" style="margin-top:1.5rem;">Reason / notes</p>
    <div class="form-grid">
      <div class="full">
        <textarea name="reason" rows="3" placeholder="Optional context for your approver"><?= esc(old('reason')) ?></textarea>
      </div>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn primary">Submit filing</button>
      <a class="btn" href="<?= site_url('filings') ?>">Cancel</a>
    </div>
  </form>
</div>

<style>
.filing-type-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(170px,1fr)); gap:10px; margin-bottom:1.75rem; }
.filing-type-grid label { display:flex; flex-direction:column; gap:4px; margin:0; border:1.5px solid var(--border-strong); border-radius:var(--radius); padding:14px 16px; cursor:pointer; font-weight:500; font-size:13.5px; color:var(--text); background:var(--surface-hi); transition:border-color .15s, background .15s; }
.filing-type-grid input { position:absolute; opacity:0; pointer-events:none; }
.filing-type-grid label:hover { border-color:var(--accent); }
.filing-type-grid label.selected { border-color:var(--accent); background:var(--accent-soft); color:var(--text-accent); }
.filing-type-grid small { font-weight:400; color:var(--text-secondary); font-size:11.5px; }
.cal-months { display:flex; gap:20px; flex-wrap:wrap; margin-bottom:1.25rem; }
.cal-month { background:var(--surface-hi); border:1px solid var(--border); border-radius:var(--radius-lg); padding:14px 16px; min-width:260px; flex:1 1 260px; }
.cal-month h4 { margin:0 0 10px; font-size:13px; font-weight:600; text-align:center; color:var(--text); }
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
.cal-grid .dow { font-size:10px; color:var(--text-muted); font-weight:600; text-align:center; padding-bottom:4px; }
.cal-grid label { display:flex; align-items:center; justify-content:center; height:30px; margin:0; cursor:pointer; }
.cal-grid label span { display:flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:8px; font-size:12px; color:var(--text); }
.cal-grid label:hover span { background:var(--surface-2); }
.cal-grid label input:checked + span { background:var(--accent); color:var(--surface-1); font-weight:600; }
.cal-grid .blank { height:30px; }
textarea { width:100%; padding:11px 13px; background:var(--surface-hi); border:1px solid var(--border-strong); border-radius:10px; color:var(--text); font-size:13.5px; font-family:inherit; resize:vertical; }
textarea:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-soft); }
</style>

<script>
(function () {
  var typeInputs = Array.prototype.slice.call(document.querySelectorAll('input[name="filing_type"]'));
  var sections = Array.prototype.slice.call(document.querySelectorAll('[data-section]'));
  var leaveTypeSelect = document.getElementById('leave_type_id');
  var scheduleSelect = document.getElementById('requested_work_schedule_id');
  var form = document.getElementById('filingForm');

  function currentType() {
    var checked = document.querySelector('input[name="filing_type"]:checked');
    return checked ? checked.value : '';
  }

  function applyType() {
    var type = currentType();

    typeInputs.forEach(function (input) {
      input.closest('label').classList.toggle('selected', input.checked);
    });

    sections.forEach(function (section) {
      var types = (section.getAttribute('data-section') || '').split(',');
      section.style.display = types.indexOf(type) !== -1 ? '' : 'none';
    });

    if (leaveTypeSelect) leaveTypeSelect.required = (type === 'leave');
    if (scheduleSelect) scheduleSelect.required = (type === 'schedule_change');
  }

  typeInputs.forEach(function (input) {
    input.addEventListener('change', applyType);
  });
  applyType();

  var dayCount = document.getElementById('dayCount');
  function updateCount() {
    var n = document.querySelectorAll('input[name="dates[]"]:checked').length;
    if (dayCount) dayCount.textContent = n > 0 ? '(' + n + ' day' + (n === 1 ? '' : 's') + ' selected)' : '';
  }
  Array.prototype.slice.call(document.querySelectorAll('input[name="dates[]"]')).forEach(function (box) {
    box.addEventListener('change', updateCount);
  });
  updateCount();

  form.addEventListener('submit', function (e) {
    var type = currentType();
    if (!type) {
      e.preventDefault();
      alert('Please choose a filing type.');
      return;
    }

    var checkedDates = Array.prototype.slice.call(document.querySelectorAll('input[name="dates[]"]:checked')).map(function (b) {
      return b.value;
    });
    if (checkedDates.length === 0) {
      e.preventDefault();
      alert('Please select at least one date.');
      return;
    }

    if (type === 'official_business' || type === 'time_adjustment') {
      var timeInEl = document.getElementById('requested_time_in');
      var timeIn = (timeInEl && timeInEl.value) ? timeInEl.value : '00:00';
      var now = new Date();

      var soon = checkedDates.some(function (d) {
        var when = new Date(d + 'T' + timeIn + ':00');
        var diffMs = Math.abs(when.getTime() - now.getTime());
        return diffMs < 24 * 60 * 60 * 1000;
      });

      if (soon) {
        var ok = confirm('This is within 24 hours of the scheduled date/time — are you sure?');
        if (!ok) {
          e.preventDefault();
          return;
        }
      }
    }
  });
})();
</script>

<?= $this->endSection() ?>
