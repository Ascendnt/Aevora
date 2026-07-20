<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Aevora — HR that moves as one</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,400;1,9..144,500;1,9..144,600&family=Instrument+Sans:wght@400;500;600&family=Space+Grotesque&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#f3efe4; --bg2:#ece7d8; --ink:#242a20; --ink-soft:#5c6353; --ink-mute:#8b917f;
  --sage:#6f8763; --sage-deep:#4f6446; --sage-glow:#a7c095; --cream:#fffdf7;
  --clay:#bb6a44; --clay-soft:#f0dccf; --gold:#c99a34; --line:#ddd8c7; --aurora:#8fb3a0;
  --serif:'Fraunces',Georgia,serif; --sans:'Instrument Sans',sans-serif; --mono:'Space Grotesque',monospace;
  --e:cubic-bezier(.6,.05,.15,1);
}
*{margin:0;padding:0;box-sizing:border-box;}
html{scroll-behavior:smooth;}
body{font-family:var(--sans);background:var(--bg);color:var(--ink);overflow-x:hidden;-webkit-font-smoothing:antialiased;}
a{color:inherit;text-decoration:none;}
::selection{background:var(--sage);color:var(--cream);}

/* animated aurora atmosphere */
.aurora-bg{position:fixed;inset:0;z-index:0;pointer-events:none;overflow:hidden;}
.aurora-bg span{position:absolute;border-radius:50%;filter:blur(70px);opacity:.32;mix-blend-mode:multiply;}
.aurora-bg .a1{width:640px;height:640px;background:var(--sage-glow);top:-220px;left:-140px;animation:drift1 20s ease-in-out infinite;}
.aurora-bg .a2{width:520px;height:520px;background:var(--clay-soft);top:-120px;right:-120px;animation:drift2 24s ease-in-out infinite;}
.aurora-bg .a3{width:440px;height:440px;background:var(--aurora);bottom:-180px;left:34%;animation:drift3 28s ease-in-out infinite;opacity:.2;}
@keyframes drift1{0%,100%{transform:translate(0,0) scale(1);}50%{transform:translate(60px,50px) scale(1.1);}}
@keyframes drift2{0%,100%{transform:translate(0,0) scale(1);}50%{transform:translate(-50px,40px) scale(1.08);}}
@keyframes drift3{0%,100%{transform:translate(0,0) scale(1);}50%{transform:translate(40px,-40px) scale(1.12);}}

.wrap{position:relative;z-index:1;max-width:1200px;margin:0 auto;padding:0 32px;}

/* ---------- NAV ---------- */
nav{position:sticky;top:0;z-index:60;backdrop-filter:blur(12px);background:color-mix(in srgb,var(--bg) 80%,transparent);border-bottom:1px solid transparent;transition:border-color .3s;}
nav.scrolled{border-color:var(--line);}
.nav-in{max-width:1200px;margin:0 auto;padding:16px 32px;display:flex;align-items:center;justify-content:space-between;}
.brand{display:flex;align-items:center;gap:11px;font-family:var(--serif);font-weight:600;font-size:23px;letter-spacing:-.015em;}
.brand .mark{width:38px;height:38px;}
.nav-links{display:flex;align-items:center;gap:34px;}
.nav-links a{font-size:14.5px;color:var(--ink-soft);position:relative;transition:color .2s;}
.nav-links a:not(.login-btn)::after{content:'';position:absolute;left:0;bottom:-4px;width:0;height:1.5px;background:var(--sage);transition:width .25s var(--e);}
.nav-links a:not(.login-btn):hover{color:var(--ink);}
.nav-links a:not(.login-btn):hover::after{width:100%;}
.login-btn{display:inline-flex;align-items:center;gap:8px;background:var(--ink);color:var(--cream)!important;padding:10px 20px;border-radius:100px;font-weight:500;transition:transform .18s var(--e),background .2s;}
.login-btn:hover{background:var(--sage-deep);transform:translateY(-2px);}
.login-btn svg{transition:transform .25s var(--e);}
.login-btn:hover svg{transform:translateX(3px);}

/* ---------- HERO ---------- */
.hero{padding:70px 0 40px;position:relative;}
.hero-grid{display:grid;grid-template-columns:1.05fr .95fr;gap:40px;align-items:center;}
h1.hero-h{font-family:var(--serif);font-weight:500;font-size:clamp(44px,6.2vw,78px);line-height:1.0;letter-spacing:-.028em;}
h1.hero-h .ln{display:block;overflow:hidden;padding-bottom:2px;}
h1.hero-h .ln i{display:inline-block;font-style:normal;transform:translateY(112%);animation:rise .95s var(--e) forwards;}
h1.hero-h .ln:nth-child(1) i{animation-delay:.12s;}
h1.hero-h .ln:nth-child(2) i{animation-delay:.24s;}
h1.hero-h .ln:nth-child(3) i{animation-delay:.36s;}
.hero-h em{font-style:italic;color:var(--sage-deep);}
.hero-h .accent{color:var(--clay);}
.hero-sub{font-size:18px;line-height:1.62;color:var(--ink-soft);max-width:440px;margin:28px 0 34px;opacity:0;animation:fade .8s .66s forwards;}
.hero-cta{display:flex;gap:14px;align-items:center;opacity:0;animation:fade .8s .8s forwards;flex-wrap:wrap;}
.btn-primary{display:inline-flex;align-items:center;gap:9px;background:var(--sage);color:var(--cream);padding:15px 28px;border-radius:100px;font-weight:600;font-size:15px;transition:transform .2s var(--e),box-shadow .25s;box-shadow:0 6px 20px rgba(79,100,70,.22);will-change:transform;}
.btn-primary:hover{box-shadow:0 12px 30px rgba(79,100,70,.34);}
.btn-primary svg{transition:transform .25s var(--e);}
.btn-primary:hover svg{transform:translateX(4px);}
.btn-ghost{display:inline-flex;align-items:center;gap:10px;padding:15px 24px;border-radius:100px;font-weight:500;font-size:15px;color:var(--ink);border:1.5px solid var(--line);transition:.2s;}
.btn-ghost:hover{border-color:var(--sage);color:var(--sage-deep);}
.btn-ghost .play{width:22px;height:22px;}

/* interactive constellation hero art */
.hero-art{position:relative;height:480px;opacity:0;animation:fade 1s .4s forwards;}
#constellation{width:100%;height:100%;overflow:visible;}
.c-core{transform-origin:center;}
.c-node{cursor:pointer;transition:transform .3s var(--e);transform-box:fill-box;transform-origin:center;}
.c-node:hover{transform:scale(1.18);}
.c-link{stroke:var(--sage);stroke-width:1;opacity:0;transition:opacity .4s;}
.c-ring{fill:none;stroke:var(--line);stroke-width:1;stroke-dasharray:3 6;}

@keyframes rise{to{transform:translateY(0);}}
@keyframes fade{to{opacity:1;}}
@keyframes pulse-core{0%,100%{transform:scale(1);}50%{transform:scale(1.04);}}

/* ---------- MARQUEE ---------- */
.strip{border-top:1px solid var(--line);border-bottom:1px solid var(--line);padding:20px 0;margin-top:36px;overflow:hidden;background:color-mix(in srgb,var(--cream) 40%,transparent);}
.marq{display:flex;gap:0;white-space:nowrap;animation:marq 28s linear infinite;font-family:var(--mono);font-size:13px;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-mute);}
.marq span{display:flex;align-items:center;padding-right:56px;gap:12px;}
.marq b{color:var(--sage-deep);font-weight:400;}
.marq .dot{width:5px;height:5px;border-radius:50%;background:var(--clay);opacity:.6;}
@keyframes marq{to{transform:translateX(-50%);}}

/* ---------- SECTION ---------- */
section{padding:104px 0;position:relative;}
h2{font-family:var(--serif);font-weight:500;font-size:clamp(32px,4.4vw,50px);line-height:1.06;letter-spacing:-.022em;max-width:660px;}
h2 em{font-style:italic;color:var(--sage-deep);}
.lead{font-size:17px;color:var(--ink-soft);line-height:1.65;max-width:560px;margin-top:20px;}
.reveal{opacity:0;transform:translateY(30px);transition:opacity .75s var(--e),transform .75s var(--e);}
.reveal.in{opacity:1;transform:none;}

/* section-number instead of eyebrow */
.h2-wrap{display:flex;align-items:flex-start;gap:22px;}
.h2-num{font-family:var(--serif);font-style:italic;font-size:20px;color:var(--clay);margin-top:10px;flex-shrink:0;position:relative;}
.h2-num::after{content:'';display:block;width:30px;height:1px;background:var(--clay);margin-top:12px;opacity:.5;}

/* pillars */
.pillars{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:56px;}
.pillar{background:var(--cream);border:1px solid var(--line);border-radius:22px;padding:32px;position:relative;overflow:hidden;transition:transform .35s var(--e),box-shadow .35s,border-color .35s;}
.pillar::before{content:'';position:absolute;inset:0;background:radial-gradient(400px circle at var(--mx,50%) var(--my,0%),var(--sage-glow),transparent 45%);opacity:0;transition:opacity .4s;pointer-events:none;}
.pillar:hover::before{opacity:.12;}
.pillar:hover{transform:translateY(-6px);box-shadow:0 20px 44px rgba(36,42,32,.12);border-color:var(--sage-glow);}
.pillar .p-ic{width:58px;height:58px;border-radius:17px;background:var(--bg2);display:flex;align-items:center;justify-content:center;margin-bottom:24px;transition:background .35s;position:relative;}
.pillar:hover .p-ic{background:var(--sage);}
.pillar .p-ic svg{width:30px;height:30px;transition:.35s;}
.pillar:hover .p-ic svg [stroke]{stroke:var(--cream);}
.pillar:hover .p-ic svg [fill]:not([fill="none"]){fill:var(--cream);}
.pillar h3{font-family:var(--serif);font-size:23px;font-weight:600;margin-bottom:11px;position:relative;}
.pillar p{font-size:14.5px;color:var(--ink-soft);line-height:1.62;position:relative;}
.pillar .p-num{position:absolute;top:28px;right:30px;font-family:var(--mono);font-size:13px;color:var(--ink-mute);}

/* flow */
.flow{margin-top:58px;display:grid;grid-template-columns:repeat(4,1fr);gap:16px;position:relative;}
.step{background:var(--cream);border:1px solid var(--line);border-radius:20px;padding:28px 24px;position:relative;transition:transform .3s var(--e),box-shadow .3s;}
.step:hover{transform:translateY(-5px);box-shadow:0 16px 34px rgba(36,42,32,.1);}
.step .s-n{font-family:var(--serif);font-style:italic;font-size:16px;color:var(--clay);margin-bottom:18px;}
.step .s-ic{width:46px;height:46px;margin-bottom:18px;}
.step h4{font-family:var(--serif);font-size:19px;font-weight:600;margin-bottom:9px;}
.step p{font-size:13.5px;color:var(--ink-soft);line-height:1.55;}
.step .connector{position:absolute;top:44px;right:-16px;width:16px;z-index:2;color:var(--sage-glow);}
.step:last-child .connector{display:none;}

/* stats band */
.band{background:var(--sage-deep);border-radius:36px;padding:70px 52px;position:relative;overflow:hidden;color:var(--cream);}
.band-orb{position:absolute;right:-60px;bottom:-90px;width:360px;height:360px;opacity:.5;}
.band .b-head{font-family:var(--serif);font-size:clamp(28px,3.6vw,42px);font-weight:500;line-height:1.12;max-width:660px;position:relative;}
.band .b-head em{font-style:italic;color:var(--sage-glow);}
.band-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:28px;margin-top:52px;position:relative;}
.b-stat .b-v{font-family:var(--serif);font-size:clamp(40px,5vw,58px);font-weight:600;line-height:1;}
.b-stat .b-v .u{color:var(--sage-glow);}
.b-stat .b-l{font-size:13.5px;color:color-mix(in srgb,var(--cream) 72%,transparent);margin-top:10px;}

/* final cta */
.cta-final{text-align:center;padding:120px 0 110px;}
.cta-final h2{margin:0 auto;}
.cta-final .lead{margin:20px auto 38px;}
.cta-final .btn-primary{margin:0 auto;}
.cta-star{width:34px;height:34px;margin:0 auto 26px;display:block;color:var(--clay);animation:twinkle 3s ease-in-out infinite;}
@keyframes twinkle{0%,100%{opacity:.5;transform:scale(.9) rotate(0);}50%{opacity:1;transform:scale(1.1) rotate(20deg);}}

/* footer */
footer{border-top:1px solid var(--line);padding:60px 0 40px;}
.foot-grid{display:flex;justify-content:space-between;align-items:flex-start;gap:40px;flex-wrap:wrap;}
.foot-brand{max-width:300px;}
.foot-brand .brand{margin-bottom:16px;}
.foot-brand p{font-size:14px;color:var(--ink-soft);line-height:1.6;}
.foot-cols{display:flex;gap:64px;flex-wrap:wrap;}
.foot-col h5{font-family:var(--mono);font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:var(--ink-mute);margin-bottom:15px;}
.foot-col a{display:block;font-size:14px;color:var(--ink-soft);margin-bottom:10px;transition:color .2s,transform .2s;}
.foot-col a:hover{color:var(--sage-deep);transform:translateX(3px);}
.foot-base{margin-top:48px;padding-top:26px;border-top:1px solid var(--line);display:flex;justify-content:space-between;font-size:13px;color:var(--ink-mute);flex-wrap:wrap;gap:10px;}
.foot-base em{font-style:italic;font-family:var(--serif);}

@media(max-width:900px){
  .hero-grid{grid-template-columns:1fr;}.hero-art{height:380px;order:-1;}
  .pillars,.flow,.band-stats{grid-template-columns:1fr 1fr;}
  .nav-links a:not(.login-btn){display:none;}
  .step .connector{display:none;}
}
@media(max-width:560px){.pillars,.flow,.band-stats{grid-template-columns:1fr;}.wrap{padding:0 20px;}.h2-wrap{flex-direction:column;gap:10px;}}
@media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important;}h1.hero-h .ln i{transform:none;}.reveal{opacity:1;transform:none;}.aurora-bg{display:none;}}
</style>
</head>
<body>

<div class="aurora-bg"><span class="a1"></span><span class="a2"></span><span class="a3"></span></div>

<!-- NAV -->
<nav id="nav">
  <div class="nav-in">
    <a href="/" class="brand">
      <span class="mark">
        <svg viewBox="0 0 40 40" fill="none">
          <circle cx="20" cy="20" r="17" stroke="var(--sage)" stroke-width="1.5" stroke-dasharray="2 5" opacity=".5">
            <animateTransform attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="40s" repeatCount="indefinite"/>
          </circle>
          <path d="M20 8C15 12 12.5 15.5 12.5 20a7.5 7.5 0 0 0 15 0C27.5 15.5 25 12 20 8Z" fill="var(--sage)">
            <animate attributeName="opacity" values=".82;1;.82" dur="4s" repeatCount="indefinite"/>
          </path>
          <path d="M20 27V17" stroke="var(--cream)" stroke-width="1.7" stroke-linecap="round"/>
          <circle cx="20" cy="4" r="1.6" fill="var(--clay)"/>
        </svg>
      </span>
      Aevora
    </a>
    <div class="nav-links">
      <a href="#platform">Platform</a>
      <a href="#how">How it works</a>
      <a href="#story">Why Aevora</a>
      <a href="<?= site_url('login') ?>" class="login-btn">
        Log in
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </a>
    </div>
  </div>
</nav>

<!-- HERO -->
<header class="hero">
  <div class="wrap hero-grid">
    <div>
      <h1 class="hero-h">
        <span class="ln"><i>HR that moves</i></span>
        <span class="ln"><i>as <em>one</em>,</i></span>
        <span class="ln"><i>not one <span class="accent">chore</span>.</i></span>
      </h1>
      <p class="hero-sub">Aevora brings your companies, branches, people, and payroll into one calm workspace built for teams who'd rather grow than chase paperwork.</p>
      <div class="hero-cta">
        <a href="<?= site_url('login') ?>" class="btn-primary" data-magnetic>
          Enter workspace
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <a href="#platform" class="btn-ghost">
          <span class="play"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/><path d="M10 8.5l5 3.5-5 3.5V8.5Z" fill="currentColor"/></svg></span>
          See the platform
        </a>
      </div>
    </div>

    <!-- INTERACTIVE CONSTELLATION -->
    <div class="hero-art" id="heroArt">
      <svg id="constellation" viewBox="0 0 480 480" fill="none">
        <circle class="c-ring" cx="240" cy="240" r="80"/>
        <circle class="c-ring" cx="240" cy="240" r="150"/>
        <circle class="c-ring" cx="240" cy="240" r="215"/>
        <g id="links"></g>
        <g class="c-core" id="core">
          <rect x="182" y="182" width="116" height="116" rx="32" fill="url(#coreGrad)"/>
          <path d="M240 208c-9 7-13 12-13 19a13 13 0 0 0 26 0c0-7-4-12-13-19Z" fill="var(--cream)" opacity=".96"/>
          <path d="M240 255v-22" stroke="var(--cream)" stroke-width="2.4" stroke-linecap="round"/>
          <circle cx="240" cy="176" r="2.4" fill="var(--gold)"/>
        </g>
        <defs>
          <linearGradient id="coreGrad" x1="182" y1="182" x2="298" y2="298" gradientUnits="userSpaceOnUse">
            <stop stop-color="#7d9670"/><stop offset="1" stop-color="#4f6446"/>
          </linearGradient>
        </defs>
        <g id="nodes"></g>
      </svg>
    </div>
  </div>
</header>

<!-- MARQUEE -->
<div class="strip">
  <div class="marq" id="marq"></div>
</div>

<!-- PLATFORM -->
<section id="platform">
  <div class="wrap">
    <div class="reveal h2-wrap">
      <span class="h2-num">01</span>
      <div>
        <h2>Everything your people need, <em>gathered in one place.</em></h2>
        <p class="lead">No more scattered spreadsheets and lost employee files. Aevora keeps the whole organisation every company, branch, and person in one considered system.</p>
      </div>
    </div>
    <div class="pillars">
      <div class="pillar reveal" data-glow>
        <span class="p-num">01</span>
        <div class="p-ic"><svg viewBox="0 0 30 30" fill="none"><path d="M5 26V10l10-6 10 6v16" stroke="var(--sage-deep)" stroke-width="1.8" stroke-linejoin="round"/><rect x="11" y="17" width="8" height="9" stroke="var(--clay)" stroke-width="1.8"/><path d="M10 11h3M17 11h3" stroke="var(--sage-deep)" stroke-width="1.8" stroke-linecap="round"/></svg></div>
        <h3>Company &amp; branches</h3>
        <p>Register every company, map headquarters and branches, and hold all your registration details in one profile.</p>
      </div>
      <div class="pillar reveal" data-glow>
        <span class="p-num">02</span>
        <div class="p-ic"><svg viewBox="0 0 30 30" fill="none"><circle cx="11" cy="10" r="4" stroke="var(--sage-deep)" stroke-width="1.8"/><path d="M4 25c0-4 3-7 7-7s7 3 7 7" stroke="var(--sage-deep)" stroke-width="1.8" stroke-linecap="round"/><path d="M20 5a4 4 0 0 1 0 8M23 25c0-3-1.4-5.5-3.6-6.6" stroke="var(--clay)" stroke-width="1.8" stroke-linecap="round"/></svg></div>
        <h3>People &amp; org structure</h3>
        <p>Departments, positions, and reporting lines that mirror how your teams truly sit ready for the org chart.</p>
      </div>
      <div class="pillar reveal" data-glow>
        <span class="p-num">03</span>
        <div class="p-ic"><svg viewBox="0 0 30 30" fill="none"><rect x="4" y="7" width="22" height="16" rx="3" stroke="var(--sage-deep)" stroke-width="1.8"/><path d="M4 12h22" stroke="var(--sage-deep)" stroke-width="1.8"/><circle cx="9" cy="17.5" r="1.6" fill="var(--clay)"/><path d="M14 18h8" stroke="var(--clay)" stroke-width="1.8" stroke-linecap="round"/></svg></div>
        <h3>Payroll &amp; time</h3>
        <p>Pay schedules, attendance, and leave that stay in step with each branch so payday is never a scramble.</p>
      </div>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section id="how" style="background:color-mix(in srgb,var(--cream) 45%,transparent);border-top:1px solid var(--line);border-bottom:1px solid var(--line);">
  <div class="wrap">
    <div class="reveal h2-wrap">
      <span class="h2-num">02</span>
      <h2>From empty to <em>fully organised</em> in four moves.</h2>
    </div>
    <div class="flow">
      <div class="step reveal">
        <svg class="connector" viewBox="0 0 16 24" fill="none"><path d="M2 12h12M9 7l5 5-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <div class="s-n">i.</div>
        <div class="s-ic"><svg viewBox="0 0 46 46" fill="none"><rect x="8" y="11" width="30" height="26" rx="4" stroke="var(--sage-deep)" stroke-width="2"/><path d="M23 18v12M17 24h12" stroke="var(--clay)" stroke-width="2" stroke-linecap="round"/></svg></div>
        <h4>Add your company</h4>
        <p>Legal details, tax IDs, and statutory numbers the full company profile.</p>
      </div>
      <div class="step reveal">
        <svg class="connector" viewBox="0 0 16 24" fill="none"><path d="M2 12h12M9 7l5 5-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <div class="s-n">ii.</div>
        <div class="s-ic"><svg viewBox="0 0 46 46" fill="none"><path d="M9 35V15l14-8 14 8v20" stroke="var(--sage-deep)" stroke-width="2" stroke-linejoin="round"/><rect x="18" y="24" width="10" height="11" stroke="var(--clay)" stroke-width="2"/></svg></div>
        <h4>Map the branches</h4>
        <p>Set HQ, add every location, and Aevora keeps a single headquarters honest.</p>
      </div>
      <div class="step reveal">
        <svg class="connector" viewBox="0 0 16 24" fill="none"><path d="M2 12h12M9 7l5 5-5 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <div class="s-n">iii.</div>
        <div class="s-ic"><svg viewBox="0 0 46 46" fill="none"><circle cx="23" cy="15" r="6" stroke="var(--sage-deep)" stroke-width="2"/><path d="M13 37c0-6 4.5-10 10-10s10 4 10 10" stroke="var(--clay)" stroke-width="2" stroke-linecap="round"/></svg></div>
        <h4>Build the structure</h4>
        <p>Departments and positions slot into place, ready for your people.</p>
      </div>
      <div class="step reveal">
        <div class="s-n">iv.</div>
        <div class="s-ic"><svg viewBox="0 0 46 46" fill="none"><circle cx="23" cy="23" r="15" stroke="var(--sage-deep)" stroke-width="2"/><path d="M16 23l5 5 10-10" stroke="var(--clay)" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
        <h4>Run the day-to-day</h4>
        <p>Attendance, leave, and payroll move together calm and on time.</p>
      </div>
    </div>
  </div>
</section>

<!-- STATS BAND -->
<section id="story">
  <div class="wrap">
    <div class="band reveal">
      <svg class="band-orb" viewBox="0 0 360 360" fill="none">
        <circle cx="180" cy="180" r="120" stroke="var(--sage-glow)" stroke-width="1" stroke-dasharray="2 6" opacity=".5"/>
        <circle cx="180" cy="180" r="160" stroke="var(--sage-glow)" stroke-width="1" stroke-dasharray="2 6" opacity=".3"/>
        <circle cx="300" cy="180" r="4" fill="var(--sage-glow)"/><circle cx="180" cy="60" r="3" fill="var(--gold)"/><circle cx="90" cy="240" r="3" fill="var(--sage-glow)"/>
      </svg>
      <p class="b-head">Built by people who've <em>felt the Monday-morning payroll panic</em> and decided it didn't have to be that way.</p>
      <div class="band-stats">
        <div class="b-stat"><div class="b-v"><span data-target="1">0</span></div><div class="b-l">Workspace for every company you run</div></div>
        <div class="b-stat"><div class="b-v"><span data-target="4">0</span></div><div class="b-l">Branches mapped in the current demo</div></div>
        <div class="b-stat"><div class="b-v"><span data-target="201">0</span><span class="u">+</span></div><div class="b-l">Employee files, kept in order</div></div>
        <div class="b-stat"><div class="b-v"><span data-target="1">0</span></div><div class="b-l">Calm, considered place for it all</div></div>
      </div>
    </div>
  </div>
</section>

<!-- FINAL CTA -->
<section class="cta-final">
  <div class="wrap reveal">
    <svg class="cta-star" viewBox="0 0 34 34" fill="none"><path d="M17 2l3 11 11 3-11 3-3 11-3-11-11-3 11-3 3-11Z" fill="currentColor"/></svg>
    <h2 style="text-align:center;">Gather your team.<br><em>Rise with Aevora.</em></h2>
    <p class="lead" style="text-align:center;">Sign in to the workspace and watch your whole organisation come together.</p>
    <a href="<?= site_url('login') ?>" class="btn-primary" data-magnetic>
      Enter workspace
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </a>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="wrap">
    <div class="foot-grid">
      <div class="foot-brand">
        <a href="/" class="brand">
          <span class="mark"><svg viewBox="0 0 40 40" fill="none"><path d="M20 8C15 12 12.5 15.5 12.5 20a7.5 7.5 0 0 0 15 0C27.5 15.5 25 12 20 8Z" fill="var(--sage)"/><path d="M20 27V17" stroke="var(--sage-deep)" stroke-width="1.7" stroke-linecap="round"/><circle cx="20" cy="6" r="1.6" fill="var(--clay)"/></svg></span>
          Aevora
        </a>
        <p>People-first HR, built for teams who'd rather grow than chase paperwork.</p>
      </div>
      <div class="foot-cols">
        <div class="foot-col"><h5>Platform</h5><a href="#platform">Company settings</a><a href="#platform">People &amp; org</a><a href="#platform">Payroll &amp; time</a><a href="#how">How it works</a></div>
        <div class="foot-col"><h5>Workspace</h5><a href="<?= site_url('login') ?>">Log in</a><a href="#story">Why Aevora</a></div>
      </div>
    </div>
    <div class="foot-base">
      <span>© <?= date('Y') ?> Aevora · HR that moves as one</span>
      <span style="font-family:var(--mono);">aevora · <em>rise, together</em></span>
    </div>
  </div>
</footer>

<script>
var nav=document.getElementById('nav');
addEventListener('scroll',function(){nav.classList.toggle('scrolled',scrollY>12);});

// scroll reveal
var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){e.target.classList.add('in');io.unobserve(e.target);}});},{threshold:.14});
document.querySelectorAll('.reveal').forEach(function(el,i){el.style.transitionDelay=(i%4*0.06)+'s';io.observe(el);});

// marquee build (duplicated for seamless loop)
(function(){
  var items=['One workspace','All your companies','Branches & HQ mapped','Employee files in order','Departments structured','Payroll on schedule','Leave tracked','Org chart ready'];
  var html='';for(var k=0;k<2;k++){items.forEach(function(t){html+='<span><b>'+t+'</b><span class="dot"></span></span>';});}
  document.getElementById('marq').innerHTML=html;
})();

// count-up
var band=document.querySelector('.band'),counted=false;
if(band){new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting&&!counted){counted=true;
  document.querySelectorAll('.b-v span[data-target]').forEach(function(s){var t=+s.dataset.target,d=1500,st=performance.now();
    (function tick(now){var p=Math.min((now-st)/d,1),ev=1-Math.pow(1-p,3);s.textContent=Math.round(t*ev);if(p<1)requestAnimationFrame(tick);})(performance.now());});
}});},{threshold:.4}).observe(band);}

// pillar cursor glow
document.querySelectorAll('[data-glow]').forEach(function(p){
  p.addEventListener('mousemove',function(e){var r=p.getBoundingClientRect();p.style.setProperty('--mx',(e.clientX-r.left)+'px');p.style.setProperty('--my',(e.clientY-r.top)+'px');});
});

// magnetic buttons
document.querySelectorAll('[data-magnetic]').forEach(function(b){
  b.addEventListener('mousemove',function(e){var r=b.getBoundingClientRect();var x=e.clientX-r.left-r.width/2,y=e.clientY-r.top-r.height/2;b.style.transform='translate('+x*0.25+'px,'+y*0.35+'px)';});
  b.addEventListener('mouseleave',function(){b.style.transform='';});
});

// INTERACTIVE CONSTELLATION
(function(){
  var svg=document.getElementById('constellation'),art=document.getElementById('heroArt');
  var nodesG=document.getElementById('nodes'),linksG=document.getElementById('links'),core=document.getElementById('core');
  var C=240;
  var icons=[
    {r:150,a:-90,ic:'<circle cx="0" cy="-4" r="4" stroke="var(--sage-deep)" stroke-width="1.7"/><path d="M-6 8c0-3.3 2.7-6 6-6s6 2.7 6 6" stroke="var(--sage-deep)" stroke-width="1.7" stroke-linecap="round"/><path d="M7 -8a3 3 0 0 1 0 6" stroke="var(--clay)" stroke-width="1.7" stroke-linecap="round"/>'},
    {r:150,a:20,ic:'<path d="M-8 8V-3l8-5 8 5V8" stroke="var(--sage-deep)" stroke-width="1.7" stroke-linejoin="round"/><rect x="-3" y="1" width="6" height="7" stroke="var(--clay)" stroke-width="1.7"/>'},
    {r:150,a:160,ic:'<rect x="-9" y="-6" width="18" height="12" rx="2" stroke="var(--sage-deep)" stroke-width="1.7"/><path d="M-9 -1h18" stroke="var(--sage-deep)" stroke-width="1.7"/><circle cx="-5" cy="3" r="1.3" fill="var(--clay)"/>'},
    {r:215,a:65,ic:'<circle cx="0" cy="0" r="9" stroke="var(--sage-deep)" stroke-width="1.7"/><path d="M0 -5v5l3.5 2" stroke="var(--clay)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>'},
    {r:215,a:115,ic:'<path d="M-4 -9v3M4 -9v3" stroke="var(--sage-deep)" stroke-width="1.7" stroke-linecap="round"/><rect x="-8.5" y="-6.5" width="17" height="15" rx="2.5" stroke="var(--sage-deep)" stroke-width="1.7"/><path d="M-3 2l2 2 4-4" stroke="var(--clay)" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>'}
  ];
  var pts=[];
  icons.forEach(function(o,i){
    var rad=o.a*Math.PI/180,x=C+o.r*Math.cos(rad),y=C+o.r*Math.sin(rad);
    pts.push({x:x,y:y});
    var ln=document.createElementNS('http://www.w3.org/2000/svg','line');
    ln.setAttribute('x1',C);ln.setAttribute('y1',C);ln.setAttribute('x2',x);ln.setAttribute('y2',y);ln.setAttribute('class','c-link');ln.dataset.i=i;linksG.appendChild(ln);
    var g=document.createElementNS('http://www.w3.org/2000/svg','g');
    g.setAttribute('class','c-node');g.setAttribute('transform','translate('+x+','+y+')');g.dataset.i=i;
    g.innerHTML='<rect x="-22" y="-22" width="44" height="44" rx="14" fill="var(--cream)" stroke="var(--line)"/><g>'+o.ic+'</g>';
    g.addEventListener('mouseenter',function(){linksG.querySelector('[data-i="'+i+'"]').style.opacity='.6';});
    g.addEventListener('mouseleave',function(){linksG.querySelector('[data-i="'+i+'"]').style.opacity='0';});
    nodesG.appendChild(g);
  });
  // gentle float per node + parallax
  var t=0;
  function loop(){t+=0.008;
    nodesG.querySelectorAll('.c-node').forEach(function(g,i){
      var p=pts[i],off=Math.sin(t+i*1.4)*4;
      g.setAttribute('transform','translate('+p.x+','+(p.y+off)+')');
      var ln=linksG.querySelector('[data-i="'+i+'"]');ln.setAttribute('y2',p.y+off);
    });
    core.style.transform='scale('+(1+Math.sin(t*1.3)*0.02)+')';core.style.transformOrigin='240px 240px';
    requestAnimationFrame(loop);
  }
  loop();
  // mouse parallax
  addEventListener('mousemove',function(e){
    if(innerWidth<900)return;var x=(e.clientX/innerWidth-.5),y=(e.clientY/innerHeight-.5);
    art.style.transform='translate('+x*18+'px,'+y*18+'px)';
  });
})();
</script>
</body>
</html>