<style>
.sm-error-page { flex:1; }
.sm-error-hero { padding:72px 0; background:var(--sm-cream); border-bottom:1px solid var(--sm-line); }
.sm-error-layout { display:grid; grid-template-columns:minmax(0,1.15fr) minmax(0,1fr); align-items:center; gap:48px; }
.sm-error-copy h1 { margin:20px 0; color:var(--sm-ink); font-size:clamp(34px,4.5vw,60px); font-weight:800; line-height:1.12; letter-spacing:-.055em; }
.sm-error-page .sm-error-copy h1 { font-weight:800; }
.sm-error-copy h1 em { color:var(--sm-brand-primary); font-style:normal; background:linear-gradient(110deg,var(--sm-brand-primary),var(--sm-brand-secondary)); background-clip:text; -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
.sm-error-description { max-width:470px; color:var(--sm-body); font-size:16px; line-height:1.85; }
.sm-error-actions { display:flex; flex-wrap:wrap; gap:12px; margin-top:28px; }
.sm-error-actions a { display:inline-flex; align-items:center; justify-content:center; gap:10px; min-height:48px; padding:13px 20px; border-radius:10px; font-size:14px; font-weight:700; }
.sm-error-page .sm-error-primary { color:#fff; background:var(--sm-brand-primary); border:1px solid var(--sm-brand-primary); }
.sm-error-primary i { color:inherit; }
.sm-error-page .sm-error-secondary { color:var(--sm-ink); background:#fff; border:1px solid var(--sm-line); }
.sm-error-page .sm-error-primary:hover { color:#fff; background:color-mix(in srgb,var(--sm-brand-primary) 88%,black); }
.sm-error-page .sm-error-secondary:hover { color:var(--sm-brand-primary); background:var(--sm-brand-soft); border-color:var(--sm-brand-secondary); }
.sm-error-page a:focus-visible { outline:3px solid var(--sm-brand-primary); outline-offset:4px; }
.sm-error-help { margin-top:22px; color:var(--sm-muted); font-size:13px; line-height:1.8; }
.sm-error-page .sm-error-help a { color:var(--sm-brand-primary); font-weight:700; text-decoration:underline; text-underline-offset:4px; }
.sm-error-art { position:relative; display:grid; place-items:center; min-height:330px; padding:36px 18px; }
.sm-error-orbit { position:absolute; width:310px; max-width:100%; aspect-ratio:1; border:1px dashed #b9cfc2; border-radius:50%; }
.sm-error-window { position:relative; width:100%; max-width:350px; background:#fff; border:1px solid #bdd0c4; border-radius:18px; transform:rotate(-5deg); overflow:hidden; }
.sm-error-window-bar { display:flex; align-items:center; gap:6px; padding:14px 18px; background:#eef4ef; border-bottom:1px solid var(--sm-line); }
.sm-error-window-bar span { width:7px; height:7px; background:#9cb9a8; border-radius:50%; }
.sm-error-window-bar i { margin-left:auto; color:var(--sm-muted); font-size:18px; }
.sm-error-number { color:var(--sm-brand-primary); font-size:clamp(88px,10vw,128px); font-weight:800; letter-spacing:-.08em; line-height:1.4; text-align:center; padding-right:10px; }
.sm-error-window-label { display:flex; justify-content:center; align-items:center; gap:8px; margin:0 18px 26px; color:var(--sm-muted); font-size:13px; }
.sm-error-note { position:relative; margin-top:-10px; padding:12px 16px; background:var(--sm-peach); color:var(--sm-ink); border-radius:10px; font-size:12px; font-weight:700; transform:rotate(3deg); }
.sm-error-discover { padding-block:42px 56px; }
.sm-error-discover-heading { display:flex; justify-content:space-between; align-items:center; gap:20px; margin-bottom:24px; }
.sm-error-discover h2 { margin-top:8px; color:var(--sm-ink); font-size:26px; font-weight:750; letter-spacing:-.04em; }
.sm-error-page .sm-error-discover-heading>a { color:var(--sm-brand-primary); font-size:13px; font-weight:700; }
.sm-error-links { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
.sm-error-links>a { display:flex; align-items:center; gap:12px; padding:18px 14px; border:1px solid var(--sm-line); border-radius:12px; min-width:0; }
.sm-error-links>a:hover { background:var(--sm-brand-soft); border-color:var(--sm-brand-secondary); }
.sm-error-page .sm-error-links .sm-platform-icon { display:grid; place-items:center; width:40px; height:40px; flex-shrink:0; background:#fff; border:1px solid var(--sm-line); border-radius:10px; font-size:20px; }
.sm-error-links strong { display:block; color:var(--sm-ink); font-size:14px; }
.sm-error-links small { display:block; margin-top:4px; color:var(--sm-muted); font-size:11px; }
.sm-error-links>a>i { margin-left:auto; color:var(--sm-brand-primary); }
@media(max-width:1024px) { .sm-error-layout { gap:32px; } .sm-error-links { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:767px) { .sm-error-hero { padding:40px 0 32px; } .sm-error-layout { grid-template-columns:minmax(0,1fr); gap:24px; } .sm-error-copy { text-align:center; } .sm-error-description { margin-inline:auto; font-size:14px; } .sm-error-actions { justify-content:center; } .sm-error-art { width:100%; max-width:380px; margin-inline:auto; min-height:280px; } .sm-error-number { font-size:100px; } .sm-error-discover { padding-block:32px 40px; } .sm-error-discover-heading { align-items:flex-start; flex-direction:column; gap:12px; } }
@media(max-width:420px) { .sm-error-actions { flex-direction:column; } .sm-error-actions a { width:100%; } .sm-error-links { grid-template-columns:minmax(0,1fr); } .sm-error-note { font-size:11px; } }
</style>
