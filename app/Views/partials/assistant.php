<?php
/**
 * {Brand} Assistant — floating placeholder chat widget. Brand name is
 * read dynamically via hq_company_name() (same source main.php's sidebar
 * uses), never hardcoded, so it always matches whatever the HQ company is
 * actually named.
 *
 * NOT wired to any real LLM. There are no AI provider API keys configured
 * for this deployment yet — this is a UI placeholder only. See
 * App\Controllers\Assistant::ask() for the (also placeholder) backing
 * endpoint and a comment marking exactly where a real integration would go.
 *
 * Fully self-contained: its own scoped inline <style> and <script>, no
 * dependency on any class from the app's main stylesheet. Safe to drop
 * into any layout with a single include call, e.g. right before the
 * closing </body> tag:
 *
 *   <?= $this->include('partials/assistant') ?>
 */
$assistantBrand = hq_company_name();
?>
<div id="aevora-assistant-root">

  <button type="button"
          id="aevora-assistant-fab"
          class="aevora-assistant-fab"
          aria-haspopup="dialog"
          aria-expanded="false"
          aria-controls="aevora-assistant-panel"
          title="<?= esc($assistantBrand) ?> Assistant">
    <svg viewBox="0 0 24 24" width="26" height="26" fill="none" aria-hidden="true">
      <path d="M4 5.5C4 4.67 4.67 4 5.5 4h13c.83 0 1.5.67 1.5 1.5v10c0 .83-.67 1.5-1.5 1.5H9.8L6 20.5V17H5.5C4.67 17 4 16.33 4 15.5v-10Z"
            fill="currentColor" opacity=".95"/>
      <circle cx="8.5" cy="10.3" r="1.15" fill="#fff"/>
      <circle cx="12" cy="10.3" r="1.15" fill="#fff"/>
      <circle cx="15.5" cy="10.3" r="1.15" fill="#fff"/>
    </svg>
  </button>

  <div class="aevora-assistant-overlay" id="aevora-assistant-overlay" hidden></div>

  <div id="aevora-assistant-panel"
       class="aevora-assistant-panel"
       role="dialog"
       aria-modal="false"
       aria-labelledby="aevora-assistant-title"
       aria-hidden="true">

    <div class="aevora-assistant-header">
      <div class="aevora-assistant-header-title">
        <span id="aevora-assistant-title"><?= esc($assistantBrand) ?> Assistant</span>
        <span class="aevora-assistant-badge">Coming soon</span>
      </div>
      <button type="button" id="aevora-assistant-close" class="aevora-assistant-close" aria-label="Close assistant panel">&times;</button>
    </div>

    <div class="aevora-assistant-body">
      <p>
        This panel is a placeholder for what will become the <strong><?= esc($assistantBrand) ?> Assistant</strong> —
        an AI helper built right into the app. Once it's connected to an AI provider
        (an LLM API key, or an automation tool like <strong>n8n</strong> or <strong>Zapier</strong>),
        it'll be able to help you navigate <?= esc($assistantBrand) ?>, answer questions about your employees,
        payroll, and leave data, and even take actions on your behalf.
      </p>

      <p class="aevora-assistant-label">Previews of what it will do (not working yet):</p>
      <ul class="aevora-assistant-list">
        <li>Ask about an employee's leave balance</li>
        <li>Get help filing a request</li>
        <li>Summarize this pay period's payroll</li>
        <li>Draft a Notice to Explain from a template</li>
      </ul>

      <p class="aevora-assistant-note">
        Nothing above is functional today — there's no AI provider connected yet.
        This is a UI preview only.
      </p>
    </div>

    <div class="aevora-assistant-footer">
      <div id="aevora-assistant-inline-msg" class="aevora-assistant-inline-msg" role="status" hidden></div>
      <form id="aevora-assistant-form" class="aevora-assistant-form" autocomplete="off">
        <input type="text"
               id="aevora-assistant-input"
               class="aevora-assistant-input"
               placeholder="Ask the assistant… (not connected yet)"
               readonly
               aria-label="Message the <?= esc($assistantBrand) ?> Assistant (not connected yet)">
        <button type="submit" id="aevora-assistant-send" class="aevora-assistant-send" aria-label="Send message">
          <svg viewBox="0 0 24 24" width="17" height="17" fill="none" aria-hidden="true">
            <path d="M3.4 20.6 21 12 3.4 3.4 3 10l12 2-12 2 .4 6.6Z" fill="currentColor"/>
          </svg>
        </button>
      </form>
    </div>
  </div>
</div>

<style>
  #aevora-assistant-root, #aevora-assistant-root * {
    box-sizing: border-box;
  }

  .aevora-assistant-fab {
    position: fixed;
    right: 24px;
    bottom: 24px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    border: none;
    background: #b86a47;
    color: #fff9f4;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(40, 25, 15, 0.28);
    z-index: 2147483000;
    transition: transform .15s ease, box-shadow .15s ease;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
  }
  .aevora-assistant-fab:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(40, 25, 15, 0.34);
  }
  .aevora-assistant-fab:focus-visible {
    outline: 2px solid #b86a47;
    outline-offset: 3px;
  }

  .aevora-assistant-overlay {
    position: fixed;
    inset: 0;
    background: rgba(20, 16, 10, 0.28);
    z-index: 2147482998;
  }
  .aevora-assistant-overlay[hidden] { display: none; }

  .aevora-assistant-panel {
    position: fixed;
    top: 0;
    right: 0;
    bottom: 0;
    width: 380px;
    max-width: 92vw;
    background: #fffdf8;
    color: #2b322a;
    border-left: 1px solid #e2e0d2;
    box-shadow: -12px 0 32px rgba(30, 20, 12, 0.18);
    z-index: 2147482999;
    display: flex;
    flex-direction: column;
    transform: translateX(100%);
    transition: transform .22s ease;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    font-size: 14px;
    line-height: 1.5;
  }
  .aevora-assistant-panel.aevora-assistant-open {
    transform: translateX(0);
  }

  .aevora-assistant-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 16px 18px;
    border-bottom: 1px solid #e2e0d2;
    background: #f0ede2;
    flex-shrink: 0;
  }
  .aevora-assistant-header-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 15px;
    color: #2b322a;
  }
  .aevora-assistant-badge {
    display: inline-block;
    padding: 2px 9px;
    border-radius: 999px;
    background: #f4e6de;
    color: #8a4a2f;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
  }
  .aevora-assistant-close {
    border: none;
    background: transparent;
    color: #697065;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 6px;
  }
  .aevora-assistant-close:hover { background: rgba(0,0,0,0.06); }

  .aevora-assistant-body {
    flex: 1;
    overflow-y: auto;
    padding: 18px;
  }
  .aevora-assistant-body p { margin: 0 0 14px; color: #2b322a; }
  .aevora-assistant-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #9aa08f;
    margin: 0 0 8px;
  }
  .aevora-assistant-list {
    margin: 0 0 16px;
    padding: 0;
    list-style: none;
  }
  .aevora-assistant-list li {
    position: relative;
    padding: 7px 0 7px 22px;
    border-bottom: 1px dashed #e2e0d2;
    color: #2b322a;
  }
  .aevora-assistant-list li:last-child { border-bottom: none; }
  .aevora-assistant-list li::before {
    content: "✦";
    position: absolute;
    left: 0;
    top: 7px;
    color: #b86a47;
    font-size: 12px;
  }
  .aevora-assistant-note {
    font-size: 12.5px;
    color: #9aa08f;
    font-style: italic;
  }

  .aevora-assistant-footer {
    border-top: 1px solid #e2e0d2;
    background: #f0ede2;
    padding: 12px 14px;
    flex-shrink: 0;
  }
  .aevora-assistant-inline-msg {
    font-size: 12.5px;
    color: #8f3f28;
    background: #f4e6de;
    border: 1px solid #e8cdbe;
    border-radius: 8px;
    padding: 7px 10px;
    margin-bottom: 8px;
  }
  .aevora-assistant-inline-msg[hidden] { display: none; }
  .aevora-assistant-form {
    display: flex;
    gap: 8px;
  }
  .aevora-assistant-input {
    flex: 1;
    min-width: 0;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #d4d1bf;
    background: #ece9dc;
    color: #9aa08f;
    cursor: not-allowed;
    font-size: 13.5px;
  }
  .aevora-assistant-input::placeholder { color: #9aa08f; }
  .aevora-assistant-input:focus { outline: none; }
  .aevora-assistant-send {
    flex-shrink: 0;
    width: 38px;
    border-radius: 10px;
    border: 1px solid #d4d1bf;
    background: #ece9dc;
    color: #9aa08f;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .aevora-assistant-send:hover { background: #e2e0d2; }

  /* Dark mode: respect either an explicit data-theme="dark" on <html>
     (this app's own toggle) or the OS-level preference, whichever applies. */
  @media (prefers-color-scheme: dark) {
    .aevora-assistant-panel { background: #1e241a; color: #ece9dc; border-left-color: #2e3626; box-shadow: -12px 0 32px rgba(0,0,0,0.45); }
    .aevora-assistant-header { background: #262d20; border-bottom-color: #2e3626; }
    .aevora-assistant-header-title { color: #ece9dc; }
    .aevora-assistant-badge { background: #33241c; color: #d59167; }
    .aevora-assistant-close { color: #9ba38d; }
    .aevora-assistant-close:hover { background: rgba(255,255,255,0.08); }
    .aevora-assistant-body p { color: #ece9dc; }
    .aevora-assistant-label { color: #6d7563; }
    .aevora-assistant-list li { color: #ece9dc; border-bottom-color: #2e3626; }
    .aevora-assistant-note { color: #6d7563; }
    .aevora-assistant-footer { background: #262d20; border-top-color: #2e3626; }
    .aevora-assistant-inline-msg { background: #33241c; color: #e0a582; border-color: #3c4531; }
    .aevora-assistant-input, .aevora-assistant-send { background: #2b3325; border-color: #3c4531; color: #6d7563; }
  }
  html[data-theme="dark"] .aevora-assistant-panel { background: #1e241a; color: #ece9dc; border-left-color: #2e3626; box-shadow: -12px 0 32px rgba(0,0,0,0.45); }
  html[data-theme="dark"] .aevora-assistant-header { background: #262d20; border-bottom-color: #2e3626; }
  html[data-theme="dark"] .aevora-assistant-header-title { color: #ece9dc; }
  html[data-theme="dark"] .aevora-assistant-badge { background: #33241c; color: #d59167; }
  html[data-theme="dark"] .aevora-assistant-close { color: #9ba38d; }
  html[data-theme="dark"] .aevora-assistant-close:hover { background: rgba(255,255,255,0.08); }
  html[data-theme="dark"] .aevora-assistant-body p { color: #ece9dc; }
  html[data-theme="dark"] .aevora-assistant-label { color: #6d7563; }
  html[data-theme="dark"] .aevora-assistant-list li { color: #ece9dc; border-bottom-color: #2e3626; }
  html[data-theme="dark"] .aevora-assistant-note { color: #6d7563; }
  html[data-theme="dark"] .aevora-assistant-footer { background: #262d20; border-top-color: #2e3626; }
  html[data-theme="dark"] .aevora-assistant-inline-msg { background: #33241c; color: #e0a582; border-color: #3c4531; }
  html[data-theme="dark"] .aevora-assistant-input, html[data-theme="dark"] .aevora-assistant-send { background: #2b3325; border-color: #3c4531; color: #6d7563; }
  html[data-theme="light"] .aevora-assistant-panel { background: #fffdf8; color: #2b322a; }

  @media (max-width: 480px) {
    .aevora-assistant-panel { width: 100vw; max-width: 100vw; }
  }
</style>

<script>
(function () {
  var fab   = document.getElementById('aevora-assistant-fab');
  var panel = document.getElementById('aevora-assistant-panel');
  var overlay = document.getElementById('aevora-assistant-overlay');
  var closeBtn = document.getElementById('aevora-assistant-close');
  var form  = document.getElementById('aevora-assistant-form');
  var input = document.getElementById('aevora-assistant-input');
  var msg   = document.getElementById('aevora-assistant-inline-msg');

  if (!fab || !panel) { return; }

  // Guard against this partial accidentally being included more than once.
  if (fab.getAttribute('data-aevora-bound') === '1') { return; }
  fab.setAttribute('data-aevora-bound', '1');

  function openPanel() {
    panel.classList.add('aevora-assistant-open');
    panel.setAttribute('aria-hidden', 'false');
    fab.setAttribute('aria-expanded', 'true');
    if (overlay) { overlay.hidden = false; }
  }

  function closePanel() {
    panel.classList.remove('aevora-assistant-open');
    panel.setAttribute('aria-hidden', 'true');
    fab.setAttribute('aria-expanded', 'false');
    if (overlay) { overlay.hidden = true; }
  }

  function togglePanel() {
    if (panel.classList.contains('aevora-assistant-open')) {
      closePanel();
    } else {
      openPanel();
    }
  }

  fab.addEventListener('click', togglePanel);
  if (closeBtn) { closeBtn.addEventListener('click', closePanel); }
  if (overlay) { overlay.addEventListener('click', closePanel); }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closePanel(); }
  });

  // There is nothing to call yet — this is a UI placeholder only. No
  // fetch/AJAX request is made; we just surface a friendly inline note.
  function showNotConnected() {
    if (!msg) { return; }
    msg.textContent = 'Not connected yet — the <?= esc($assistantBrand, 'js') ?> Assistant isn\'t wired up to an AI provider yet.';
    msg.hidden = false;
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      showNotConnected();
    });
  }
  if (input) {
    input.addEventListener('click', showNotConnected);
    input.addEventListener('focus', showNotConnected);
  }
})();
</script>
