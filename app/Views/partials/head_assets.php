<?php
/**
 * head_assets — shared design system for the app shell + auth screens.
 *
 * One source of truth: Tailwind (Play CDN) drives every style. The only raw
 * CSS here is the design-token layer (CSS custom properties) that Tailwind's
 * colours map onto — this is idiomatic Tailwind theming and also keeps the
 * many inline `var(--…)` references already living inside the views working.
 *
 * All component classes (.btn, .stat, .table-wrap, .form-card, .badge, …) are
 * rebuilt with @apply inside <style type="text/tailwindcss"> — no hand-written
 * component CSS. Included by layouts/main.php and auth/login.php.
 */
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400;1,9..144,500;1,9..144,600&family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700&display=swap" rel="stylesheet">

<script>
  // Apply saved theme before paint to avoid a flash.
  (function () {
    var m = document.cookie.match(/hris_theme=(\w+)/);
    if (m && m[1] === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
  })();
</script>

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
  tailwind.config = {
    darkMode: ['selector', '[data-theme="dark"]'],
    theme: {
      extend: {
        colors: {
          paper:        'var(--bg)',
          'paper-tint': 'var(--bg-tint)',
          surface:      'var(--surface-1)',
          'surface-2':  'var(--surface-2)',
          'surface-hi': 'var(--surface-hi)',
          line:         'var(--border)',
          'line-strong':'var(--border-strong)',
          ink:          'var(--text)',
          'ink-soft':   'var(--text-secondary)',
          'ink-mute':   'var(--text-muted)',
          sage:         'var(--accent)',
          'sage-deep':  'var(--accent-deep)',
          'sage-soft':  'var(--accent-soft)',
          'sage-ink':   'var(--text-accent)',
          clay:         'var(--clay)',
          'clay-soft':  'var(--clay-soft)',
          gold:         'var(--gold)',
          danger:       'var(--danger)',
          'danger-bg':  'var(--bg-danger)',
          'danger-ink': 'var(--text-danger)',
          ok:           'var(--success)',
          'ok-bg':      'var(--bg-success)',
        },
        fontFamily: {
          sans:    ['"Bricolage Grotesque"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
          serif:   ['Fraunces', 'Georgia', 'serif'],
          display: ['Fraunces', 'Georgia', 'serif'],
        },
        borderRadius: { ctl: '11px', card: '16px', pill: '999px' },
        boxShadow: {
          soft:  'var(--shadow-sm)',
          lift:  'var(--shadow-md)',
          float: 'var(--shadow-lift)',
        },
        letterSpacing: { eyebrow: '0.14em' },
        keyframes: {
          rise:    { '0%': { opacity: 0, transform: 'translateY(12px)' }, '100%': { opacity: 1, transform: 'none' } },
          pageIn:  { '0%': { opacity: 0, transform: 'translateY(8px)' },  '100%': { opacity: 1, transform: 'none' } },
          floaty:  { '0%,100%': { transform: 'translateY(0)' }, '50%': { transform: 'translateY(-7px)' } },
          drift:   { '0%,100%': { transform: 'translate(0,0) scale(1)' }, '50%': { transform: 'translate(40px,34px) scale(1.08)' } },
        },
        animation: {
          rise:   'rise .55s cubic-bezier(.65,.05,.15,1) forwards',
          pageIn: 'pageIn .5s cubic-bezier(.65,.05,.15,1)',
          floaty: 'floaty 6s ease-in-out infinite',
          drift:  'drift 22s ease-in-out infinite',
        },
      },
    },
  };
</script>

<style>
  /* ── Design tokens (light) — Tailwind colours resolve to these ─────────── */
  :root {
    --bg:#f4f1e8; --bg-tint:#ece8db; --surface-1:#fffdf8; --surface-2:#f0ede2; --surface-hi:#ffffff;
    --border:#e2e0d2; --border-strong:#d4d1bf;
    --text:#2b322a; --text-secondary:#697065; --text-muted:#9aa08f;
    --accent:#6f8763; --accent-deep:#556b4b; --accent-soft:#e7ecdd; --text-accent:#4f6446; --bg-accent:#e7ecdd;
    --clay:#b86a47; --clay-soft:#f4e6de; --gold:#c69326;
    --danger:#b0553a; --bg-danger:#f6e7e0; --text-danger:#8f3f28;
    --success:#557048; --bg-success:#e5ecdb;
    --radius:11px; --radius-lg:16px;
    --shadow-sm:0 1px 2px rgba(43,50,42,.04),0 2px 8px rgba(43,50,42,.05);
    --shadow-md:0 4px 14px rgba(43,50,42,.08),0 2px 5px rgba(43,50,42,.05);
    --shadow-lift:0 18px 46px -12px rgba(43,50,42,.24),0 6px 16px rgba(43,50,42,.08);
    --ease:cubic-bezier(.65,.05,.15,1);
    --font-serif:'Fraunces',Georgia,serif;
    --font-sans:'Bricolage Grotesque',ui-sans-serif,system-ui,sans-serif;
  }
  /* ── Design tokens (dark) ──────────────────────────────────────────────── */
  :root[data-theme="dark"] {
    --bg:#14180f; --bg-tint:#1a1f14; --surface-1:#1e241a; --surface-2:#262d20; --surface-hi:#2b3325;
    --border:#2e3626; --border-strong:#3c4531;
    --text:#ece9dc; --text-secondary:#9ba38d; --text-muted:#6d7563;
    --accent:#a9c497; --accent-deep:#bcd3ab; --accent-soft:#262d20; --text-accent:#a9c497; --bg-accent:#262d20;
    --clay:#d59167; --clay-soft:#33241c; --gold:#dcae4f;
    --danger:#d59167; --bg-danger:#33241c; --text-danger:#e0a582;
    --success:#a9c497; --bg-success:#262d20;
    --shadow-sm:0 1px 2px rgba(0,0,0,.3);
    --shadow-md:0 4px 16px rgba(0,0,0,.4);
    --shadow-lift:0 20px 50px -12px rgba(0,0,0,.6),0 6px 16px rgba(0,0,0,.4);
  }
  html { scroll-behavior: smooth; }
</style>

<style type="text/tailwindcss">
  @layer base {
    body {
      @apply bg-paper text-ink font-sans antialiased;
      font-size: 14px; line-height: 1.5;
    }
    /* atmospheric ground — soft sage/clay light in the corners */
    body::before {
      content:""; position: fixed; inset: 0; z-index: -1; pointer-events: none; opacity: .6;
      background:
        radial-gradient(720px circle at 10% -6%, var(--accent-soft) 0%, transparent 55%),
        radial-gradient(620px circle at 100% 0%, var(--clay-soft) 0%, transparent 52%);
    }
    a { @apply text-sage-ink no-underline; }
    ::selection { background: var(--accent); color: var(--surface-hi); }
    h1,h2,h3 { @apply font-serif; }
    /* form-plugin resets so our own control styling wins */
    [type="text"],[type="email"],[type="password"],[type="date"],[type="number"],[type="search"],[type="tel"],[type="time"],textarea,select {
      @apply w-full bg-surface-hi border border-line-strong rounded-ctl text-ink transition;
      padding:.68rem .82rem; font-size:13.5px;
    }
    [type="text"]:focus,[type="email"]:focus,[type="password"]:focus,[type="date"]:focus,[type="number"]:focus,[type="search"]:focus,textarea:focus,select:focus {
      @apply outline-none border-sage; box-shadow: 0 0 0 3px var(--accent-soft);
    }
    ::placeholder { color: var(--text-muted); }
    label { @apply block text-[12px] font-medium text-ink-soft mb-1.5; }
    input[type="file"] { @apply text-[13px] text-ink-soft; }
  }

  @layer components {
    /* ── layout primitives ─────────────────────────────────────────────── */
    .eyebrow { @apply inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-eyebrow text-ink-mute; }
    .muted { @apply text-ink-mute; }
    .sub   { @apply text-[13.5px] text-ink-soft; }
    .sep   { @apply inline-block align-middle w-1 h-1 rounded-full bg-sage mx-2; opacity:.7; }

    .page-head { @apply flex justify-between items-start gap-4 flex-wrap mb-7; }
    .page-head h1 { @apply font-serif text-[27px] font-medium tracking-[-.015em] m-0; }
    .page-head h1 em { @apply not-italic text-sage-deep; }
    .page-head .sub { @apply mt-1.5; }

    .section-label { @apply inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-eyebrow text-ink-mute mb-3.5 w-full; }
    .section-label::after { content:""; @apply flex-1 h-px bg-line ml-1; }

    .avatar {
      @apply inline-flex items-center justify-center w-11 h-11 rounded-full text-white font-serif text-[15px] font-medium shadow-soft;
      background: var(--clay);
    }

    /* ── stat cards ────────────────────────────────────────────────────── */
    .stat-grid { @apply grid gap-4 mb-8; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); }
    .stat {
      @apply relative overflow-hidden bg-surface border border-line rounded-card p-5 opacity-0;
      animation: rise .55s var(--ease) forwards;
      transition: transform .28s var(--ease), box-shadow .28s var(--ease), border-color .2s;
    }
    .stat:hover { @apply -translate-y-1 shadow-lift border-line-strong; }
    .stat:nth-child(1){animation-delay:.04s}.stat:nth-child(2){animation-delay:.12s}
    .stat:nth-child(3){animation-delay:.2s}.stat:nth-child(4){animation-delay:.28s}
    .stat .label { @apply relative text-[12.5px] text-ink-soft mb-2 m-0; }
    .stat .value { @apply relative font-serif text-[30px] font-medium leading-none tracking-[-.01em] m-0; }
    /* leaf-vein corner flourish — the recurring brand motif */
    .stat::after {
      content:""; position:absolute; right:-24px; bottom:-24px; width:78px; height:78px; border-radius:50%;
      background: radial-gradient(circle at 30% 30%, var(--accent-soft), transparent 70%); opacity:.7;
    }

    /* ── quick / link cards ────────────────────────────────────────────── */
    .card-grid { @apply grid gap-3.5; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }
    .card-link {
      @apply relative overflow-hidden flex gap-4 items-start p-5 bg-surface text-ink border border-line rounded-card no-underline;
      transition: transform .22s var(--ease), box-shadow .22s, border-color .2s;
    }
    .card-link:hover { @apply -translate-y-1 border-sage shadow-lift no-underline; }
    .card-link > i, .card-link .card-ic {
      @apply flex items-center justify-center w-12 h-12 rounded-[13px] shrink-0 bg-sage-soft text-sage-deep;
      transition: background .22s, color .22s, transform .22s var(--ease);
    }
    .card-link:hover > i, .card-link:hover .card-ic { @apply bg-sage text-surface-hi -translate-y-0.5; }
    :root[data-theme="dark"] .card-link:hover > i, :root[data-theme="dark"] .card-link:hover .card-ic { color:#14180f; }
    .card-link .t { @apply font-serif text-[16px] font-medium mt-0.5; }
    .card-link .d { @apply text-[12.5px] text-ink-soft mt-1.5 leading-relaxed; }
    .card-link::after {
      content:"\2192"; @apply absolute right-5 top-5 text-ink-mute text-lg opacity-0;
      transform: translateX(-4px); transition:.22s var(--ease);
    }
    .card-link:hover::after { @apply opacity-100; transform: translateX(0); }

    /* ── buttons ───────────────────────────────────────────────────────── */
    .btn {
      @apply inline-flex items-center gap-2 rounded-ctl border border-line-strong bg-surface text-ink text-[13px] font-medium no-underline cursor-pointer;
      padding:.56rem 1rem; font-family: inherit;
      transition: transform .12s var(--ease), background .18s, border-color .18s, box-shadow .18s;
    }
    .btn:hover { @apply bg-surface-2 border-ink-mute no-underline; }
    .btn:active { @apply scale-[.97]; }
    .btn .ic { @apply -ml-0.5; }
    .btn.primary { @apply bg-sage border-transparent text-surface-hi shadow-soft; }
    :root[data-theme="dark"] .btn.primary { color:#14180f; }
    .btn.primary:hover { @apply bg-sage-deep shadow-lift; }
    .btn.danger { @apply text-danger-ink border-line; }
    .btn.danger:hover { @apply bg-danger-bg border-danger; }
    .btn.sm { @apply text-[12px] rounded-[8px]; padding:.36rem .68rem; }
    .btn.ghost { @apply bg-transparent border-transparent text-ink-soft; }
    .btn.ghost:hover { @apply bg-surface-2 text-ink; }

    /* ── tables ────────────────────────────────────────────────────────── */
    .table-wrap { @apply border border-line rounded-card overflow-hidden bg-surface shadow-soft; }
    .table-wrap table { @apply w-full border-collapse text-[13px]; }
    .table-wrap th, .table-wrap td { @apply text-left; padding:.82rem 1.1rem; }
    .table-wrap thead th { @apply text-[11px] tracking-[.05em] uppercase text-ink-mute font-medium bg-surface-2 border-b border-line; }
    .table-wrap tbody tr { transition: background .15s; }
    .table-wrap tbody tr + tr td { @apply border-t border-line; }
    .table-wrap tbody tr:hover { @apply bg-surface-2; }
    .table-wrap td strong { @apply font-medium; }
    .table-wrap td .muted { @apply text-ink-mute text-[12px] mt-0.5; }

    /* ── badges ────────────────────────────────────────────────────────── */
    .badge { @apply inline-flex items-center rounded-pill text-[11px] font-medium; padding:.18rem .62rem; }
    .badge.hq { @apply bg-sage-soft text-sage-ink; }
    .badge.active { @apply bg-ok-bg text-ok; }
    .badge.inactive { @apply bg-surface-2 text-ink-mute; }

    /* ── forms ─────────────────────────────────────────────────────────── */
    .form-card { @apply bg-surface border border-line rounded-card p-6 max-w-[760px] shadow-soft; }
    .form-grid { @apply grid gap-x-5 gap-y-4; grid-template-columns: 1fr 1fr; }
    .form-grid .full { grid-column: 1 / -1; }
    .check { @apply flex items-center gap-2.5 text-[13px] text-ink; }
    .check input { @apply w-[17px] h-[17px]; accent-color: var(--accent); }
    .form-actions { @apply flex gap-3 mt-7; }

    /* ── tabs ──────────────────────────────────────────────────────────── */
    .tabs { @apply flex gap-1.5 border-b border-line mb-6; }
    .tabs a { @apply px-4 py-2.5 text-[13.5px] text-ink-soft border-b-2 border-transparent -mb-px; transition: color .18s, border-color .18s; }
    .tabs a:hover { @apply text-ink no-underline; }
    .tabs a.active { @apply text-sage-ink font-medium; border-bottom-color: var(--accent); }

    /* ── alerts ────────────────────────────────────────────────────────── */
    .alert { @apply rounded-xl text-[13px] mb-5 border border-transparent; padding:.82rem 1rem; animation: rise .4s var(--ease); }
    .alert.error { @apply bg-danger-bg text-danger-ink border-danger; }
    .alert.success { @apply bg-ok-bg text-ok; border-color: var(--accent); }
    .alert ul { @apply m-0 pl-4 list-disc; }

    /* ── empty state ───────────────────────────────────────────────────── */
    .empty { @apply rounded-card text-center text-ink-soft text-[13.5px] bg-surface; padding:3rem 1rem; border:1.5px dashed var(--border-strong); }

    /* ── sidebar nav link ──────────────────────────────────────────────── */
    .nav-link {
      @apply relative flex items-center gap-3 px-3.5 py-2.5 rounded-[10px] text-[13.5px] text-ink-soft no-underline;
      transition: background .18s, color .18s;
    }
    .nav-link .ic { @apply shrink-0 opacity-80; transition: transform .2s var(--ease), opacity .18s; }
    .nav-link:hover { @apply bg-surface-2 text-ink no-underline; }
    .nav-link:hover .ic { @apply opacity-100; transform: translateX(1px); }
    .nav-link.active { @apply bg-sage text-surface-hi font-medium shadow-soft; }
    .nav-link.active .ic { @apply opacity-100; }
    :root[data-theme="dark"] .nav-link.active { color:#14180f; }
    .nav-link.active::before {
      content:""; @apply absolute -left-[15px] top-1/2 w-1 h-5 rounded-r; background: var(--accent); transform: translateY(-50%);
    }

    /* ── profile menu ──────────────────────────────────────────────────── */
    .profile { @apply relative; }
    .profile-btn { @apply flex items-center gap-2.5 bg-transparent border-0 cursor-pointer p-0; font-family: inherit; }
    .profile-btn .avatar { transition: transform .18s var(--ease), box-shadow .18s; }
    .profile-btn:hover .avatar { @apply scale-105 shadow-lift; }
    .profile-btn .who { @apply text-right leading-tight; }
    .profile-btn .who .nm { @apply text-[13.5px] font-medium text-ink; }
    .profile-btn .who .rl { @apply text-[11.5px] text-ink-mute; }
    .profile-btn .chev { @apply text-ink-mute; transition: transform .2s var(--ease); }
    .profile.open .chev { transform: rotate(180deg); }
    .profile-menu {
      @apply absolute right-0 min-w-[220px] bg-surface-hi border border-line rounded-2xl shadow-float p-2 invisible opacity-0 z-50;
      top: calc(100% + 10px); transform: translateY(-6px) scale(.98); transform-origin: top right;
      transition: opacity .2s var(--ease), transform .2s var(--ease), visibility .2s;
    }
    .profile.open .profile-menu { @apply visible opacity-100; transform: translateY(0) scale(1); }
    .profile-menu .pm-head { @apply px-3 pt-2.5 pb-3 border-b border-line mb-1.5; }
    .profile-menu .pm-head .nm { @apply font-serif text-[15px] font-medium; }
    .profile-menu .pm-head .em { @apply text-[12px] text-ink-soft mt-0.5 break-all; }
    .profile-menu a, .profile-menu button {
      @apply flex items-center gap-3 w-full px-3 py-2.5 rounded-[9px] text-[13px] text-ink bg-transparent border-0 cursor-pointer text-left no-underline;
      font-family: inherit;
    }
    .profile-menu a:hover, .profile-menu button:hover { @apply bg-surface-2 no-underline; }
    .profile-menu a .ic, .profile-menu button .ic { @apply text-ink-soft; }
    .profile-menu .pm-danger, .profile-menu .pm-danger .ic { @apply text-danger-ink; }

    /* theme toggle */
    .theme-toggle { @apply flex items-center justify-center w-10 h-10 rounded-xl border border-line bg-surface-2 text-ink-soft cursor-pointer transition; }
    .theme-toggle:hover { @apply bg-sage-soft text-sage-ink; transform: rotate(-12deg); }
  }
</style>

<style>
  @media (prefers-reduced-motion: reduce) {
    *, *::before, *::after { animation: none !important; transition: none !important; scroll-behavior: auto !important; }
  }
</style>
