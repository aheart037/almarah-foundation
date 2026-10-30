<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
  --pink:         #A92D63;
  --pink-dark:    #8A2050;
  --pink-mid:     #C4386F;
  --pink-soft:    rgba(169,45,99,.08);
  --yellow:       #F2C200;
  --yellow-dark:  #C9A000;
  --yellow-light: #FFD740;
  --yellow-soft:  rgba(242,194,0,.12);
  --white:        #FFFFFF;
  --off-white:    #FDF5F8;
  --cream:        #FAF0F4;
  --text-dark:    #1A0810;
  --text-mid:     #5A2040;
  --text-light:   #9A6080;
}

html { scroll-behavior: smooth; }

body {
  font-family: 'Manrope', sans-serif;
  background: var(--off-white);
  color: var(--text-dark);
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
}

img { max-width: 100%; display: block; }

.ab-wrap {
  width: 100%;
  padding: 0 40px;
}
@media (min-width: 768px)  { .ab-wrap { padding: 0 56px; } }
@media (min-width: 1200px) { .ab-wrap { padding: 0 80px; } }
@media (min-width: 1600px) { .ab-wrap { padding: 0 120px; } }

.ab-reveal { opacity: 0; transform: translateY(36px); transition: opacity 0.75s ease, transform 0.75s ease; }
.ab-reveal.ab-in { opacity: 1; transform: none; }
.ab-d1 { transition-delay: 0.10s; }
.ab-d2 { transition-delay: 0.20s; }
.ab-d3 { transition-delay: 0.30s; }
.ab-d4 { transition-delay: 0.40s; }
.ab-d5 { transition-delay: 0.50s; }

.ab-tag { display: inline-flex; align-items: center; gap: 10px; font-size: .54rem; font-weight: 700; letter-spacing: 5px; text-transform: uppercase; color: var(--pink); margin-bottom: 14px; }
.ab-tag::before, .ab-tag::after { content: ''; display: block; width: 18px; height: 1px; background: var(--pink); opacity: .45; }
.ab-title { font-weight: 800; font-size: clamp(1.8rem, 4.5vw, 3.2rem); color: var(--text-dark); letter-spacing: -1.5px; line-height: 1.1; margin-bottom: 16px; }
.ab-title .accent { color: var(--pink); }
.ab-body { font-size: .94rem; line-height: 1.88; color: var(--text-mid); margin-bottom: 18px; }

.ab-btn-primary { display: inline-flex; align-items: center; gap: 10px; padding: 14px 28px; border-radius: 100px; background: linear-gradient(135deg, var(--pink-dark), var(--pink)); color: var(--white); font-size: .72rem; font-weight: 800; letter-spacing: 1px; text-decoration: none; transition: transform .3s, box-shadow .3s; box-shadow: 0 8px 24px rgba(169,45,99,.30); width: fit-content; }
.ab-btn-primary svg { width: 16px; height: 16px; stroke: white; stroke-width: 2.5; fill: none; stroke-linecap: round; stroke-linejoin: round; transition: transform .2s; }
.ab-btn-primary:hover { transform: translateY(-3px); box-shadow: 0 16px 36px rgba(169,45,99,.40); }
.ab-btn-primary:hover svg { transform: translateX(3px); }

/* ══════════════════════════════════════ HERO ══════════════════════════════════════ */
.ab-hero { background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 30%, var(--pink) 65%, var(--pink-mid) 100%); padding: 130px 40px 100px; text-align: center; position: relative; overflow: hidden; }
@media (min-width: 768px) { .ab-hero { padding: 160px 80px 120px; } }
.ab-hero::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse 70% 60% at 10% 60%, rgba(242,194,0,.18) 0%, transparent 60%), radial-gradient(ellipse 50% 50% at 90% 15%, rgba(255,255,255,.09) 0%, transparent 55%), radial-gradient(ellipse 40% 40% at 50% 100%, rgba(0,0,0,.14) 0%, transparent 60%); pointer-events: none; }
.ab-hero-circle-tl { position: absolute; top: -150px; left: -150px; width: 700px; height: 700px; border-radius: 50%; border: 65px solid rgba(255,255,255,.04); pointer-events: none; }
.ab-hero-circle-br { position: absolute; bottom: -120px; right: -220px; width: 900px; height: 900px; border-radius: 50%; border: 80px solid rgba(242,194,0,.05); pointer-events: none; }
.ab-hero-inner { position: relative; z-index: 2; max-width: 860px; margin: 0 auto; }
.ab-hero h1 { font-weight: 800; font-size: clamp(2.4rem, 7vw, 5.2rem); color: var(--white); letter-spacing: -2px; line-height: 1.05; text-shadow: 0 4px 40px rgba(0,0,0,.22); margin-bottom: 16px; }
.ab-hero h1 em { font-style: normal; color: var(--yellow); filter: drop-shadow(0 0 24px rgba(242,194,0,.30)); }
.ab-hero-sub { font-size: 1rem; font-weight: 400; color: rgba(255,255,255,.58); line-height: 1.78; max-width: 680px; margin: 0 auto 36px; }
.ab-hero-badges { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 12px; }
.ab-hero-badge { display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 100px; background: rgba(255,255,255,.10); border: 1.5px solid rgba(255,255,255,.16); backdrop-filter: blur(8px); font-size: .66rem; font-weight: 700; color: rgba(255,255,255,.85); letter-spacing: .5px; }
.ab-hero-badge.gold { background: rgba(242,194,0,.15); border-color: rgba(242,194,0,.30); color: var(--yellow-light); }

/* ══════════════════════════════════════ WHO WE ARE ══════════════════════════════════════ */
.ab-who { background: var(--white); padding: 100px 0; position: relative; overflow: hidden; }
.ab-who::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light), var(--pink-mid)); }
.ab-who-layout { display: grid; gap: 72px; align-items: center; }
@media (min-width: 900px) { .ab-who-layout { grid-template-columns: 1fr 1fr; } }
.ab-who-img { border-radius: 28px; overflow: hidden; box-shadow: 0 0 0 1px rgba(169,45,99,.07), 0 24px 70px rgba(169,45,99,.13); position: relative; }
.ab-who-img::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light)); z-index: 2; }
.ab-who-img img { width: 100%; aspect-ratio: 4/3; object-fit: cover; display: block; }

/* ══════════════════════════════════════ STATS ══════════════════════════════════════ */
.ab-stats { background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 35%, var(--pink) 70%, var(--pink-mid) 100%); padding: 70px 0; position: relative; overflow: hidden; }
.ab-stats::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse 70% 60% at 10% 60%, rgba(242,194,0,.14) 0%, transparent 60%); pointer-events: none; }
.ab-stats-grid { display: grid; grid-template-columns: repeat(2,1fr); }
@media (min-width: 768px) { .ab-stats-grid { grid-template-columns: repeat(4,1fr); } }
.ab-stat-item { padding: 56px 32px; text-align: center; border-right: 1px solid rgba(255,255,255,.08); border-bottom: 1px solid rgba(255,255,255,.08); transition: background .3s; }
.ab-stat-item:hover { background: rgba(255,255,255,.04); }
@media (min-width: 768px) { .ab-stat-item:last-child { border-right: none; } }
@media (max-width: 767px) { .ab-stat-item:nth-child(2n) { border-right: none; } }
.ab-stat-val { font-weight: 800; font-size: 3rem; color: var(--yellow); letter-spacing: -2px; line-height: 1; margin-bottom: 8px; }
.ab-stat-label { font-size: .60rem; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: rgba(255,255,255,.55); line-height: 1.5; }

/* ══════════════════════════════════════ MISSION VISION VALUES ══════════════════════════════════════ */
.ab-mvv { background: var(--off-white); padding: 100px 0; position: relative; overflow: hidden; }
.ab-mvv-outer { background: var(--white); border-radius: 28px; overflow: hidden; box-shadow: 0 0 0 1px rgba(169,45,99,.06), 0 16px 60px rgba(169,45,99,.08); }
.ab-mvv-grid { display: grid; }
@media (min-width: 768px) { .ab-mvv-grid { grid-template-columns: repeat(3,1fr); } }
.ab-mvv-card { padding: 60px 52px; position: relative; transition: background .3s; }
.ab-mvv-card:hover { background: rgba(169,45,99,.02); }
.ab-mvv-card + .ab-mvv-card { border-left: 1px solid rgba(169,45,99,.08); }
@media (max-width: 767px) { .ab-mvv-card + .ab-mvv-card { border-left: none; border-top: 1px solid rgba(169,45,99,.08); } }
.ab-mvv-card::before { content: ''; position: absolute; top: 0; left: 52px; width: 44px; height: 3px; background: linear-gradient(90deg, var(--pink-dark), var(--pink)); border-radius: 2px; }
.ab-mvv-num { font-size: 5rem; font-weight: 800; color: rgba(169,45,99,.05); letter-spacing: -5px; line-height: 1; margin-bottom: -12px; }
.ab-mvv-card h3 { font-weight: 800; font-size: 1.4rem; color: var(--text-dark); letter-spacing: -.4px; margin-bottom: 16px; }
.ab-mvv-card p { font-size: .92rem; color: var(--text-mid); line-height: 1.88; }

/* ══════════════════════════════════════ PROGRAMS ══════════════════════════════════════ */
.ab-programs { background: var(--cream); padding: 100px 0; position: relative; overflow: hidden; }
.ab-programs-header { text-align: center; margin-bottom: 64px; }
.ab-programs-header p { font-size: .92rem; color: var(--text-light); max-width: 520px; margin: 0 auto; line-height: 1.75; }

.ab-prog-split { display: grid; border-radius: 28px; overflow: hidden; box-shadow: 0 0 0 1px rgba(169,45,99,.07), 0 20px 60px rgba(169,45,99,.11); margin-bottom: 24px; background: var(--white); }
@media (min-width: 800px) {
  .ab-prog-split { grid-template-columns: 54% 1fr; }
  .ab-prog-split.ab-flip { grid-template-columns: 1fr 54%; }
  .ab-prog-split.ab-flip .ab-prog-split-img { order: 2; }
  .ab-prog-split.ab-flip .ab-prog-split-body { order: 1; }
}
.ab-prog-split-img { position: relative; overflow: hidden; min-height: 400px; }
.ab-prog-split-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .7s cubic-bezier(.22,.9,.36,1); }
.ab-prog-split:hover .ab-prog-split-img img { transform: scale(1.05); }
.ab-prog-split-ov { position: absolute; inset: 0; background: linear-gradient(135deg, rgba(58,0,24,.40) 0%, transparent 65%); }
.ab-prog-split-tag { position: absolute; top: 24px; left: 24px; background: rgba(0,0,0,.38); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,.12); border-radius: 100px; padding: 7px 16px; font-size: .56rem; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; color: rgba(255,255,255,.85); }
.ab-prog-split-body { padding: 64px 56px; display: flex; flex-direction: column; justify-content: center; }
@media (max-width: 799px) { .ab-prog-split-body { padding: 36px 28px; } }
.ab-prog-label { font-size: .50rem; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; color: var(--pink); opacity: .7; margin-bottom: 10px; }
.ab-prog-split-body h3 { font-weight: 800; font-size: clamp(1.6rem, 3vw, 2.2rem); letter-spacing: -.6px; line-height: 1.1; margin-bottom: 14px; color: var(--text-dark); }
.ab-prog-split-body > p { font-size: .90rem; color: var(--text-mid); line-height: 1.88; margin-bottom: 20px; }
.ab-prog-bullets { list-style: none; margin-bottom: 24px; }
.ab-prog-bullets li { display: flex; align-items: flex-start; gap: 10px; font-size: .86rem; color: var(--text-mid); line-height: 1.55; margin-bottom: 10px; }
.ab-prog-bullets li::before { content: ''; width: 6px; height: 6px; min-width: 6px; background: var(--pink); border-radius: 50%; margin-top: 7px; flex-shrink: 0; }

.ab-prog-grid { display: grid; gap: 22px; margin-top: 24px; }
@media (min-width: 640px) { .ab-prog-grid { grid-template-columns: repeat(2,1fr); } }
@media (min-width: 960px) { .ab-prog-grid { grid-template-columns: repeat(3,1fr); } }
.ab-prog-card { border-radius: 22px; overflow: hidden; background: var(--white); box-shadow: 0 0 0 1px rgba(169,45,99,.06), 0 8px 24px rgba(169,45,99,.07); transition: transform .35s cubic-bezier(.22,.9,.36,1), box-shadow .35s; }
.ab-prog-card:hover { transform: translateY(-10px); box-shadow: 0 0 0 1px rgba(169,45,99,.10), 0 28px 60px rgba(169,45,99,.14); }
.ab-prog-card-img { overflow: hidden; aspect-ratio: 16/10; position: relative; }
.ab-prog-card-img::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light)); z-index: 2; }
.ab-prog-card-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .55s cubic-bezier(.22,.9,.36,1); }
.ab-prog-card:hover .ab-prog-card-img img { transform: scale(1.07); }
.ab-prog-card-body { padding: 32px 28px; }
.ab-prog-card-body h3 { font-weight: 800; font-size: 1.1rem; color: var(--text-dark); letter-spacing: -.3px; margin-bottom: 10px; }
.ab-prog-card-body p { font-size: .85rem; color: var(--text-mid); line-height: 1.78; margin-bottom: 18px; }
.ab-link-arrow { display: inline-flex; align-items: center; gap: 8px; font-size: .64rem; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; color: var(--pink); text-decoration: none; border-bottom: 1.5px solid rgba(169,45,99,.22); padding-bottom: 2px; transition: gap .2s, border-color .2s; }
.ab-link-arrow svg { width: 14px; height: 14px; stroke: currentColor; stroke-width: 2.5; fill: none; stroke-linecap: round; stroke-linejoin: round; transition: transform .2s; }
.ab-link-arrow:hover { gap: 13px; border-bottom-color: var(--pink); }
.ab-link-arrow:hover svg { transform: translateX(3px); }

/* ══════════════════════════════════════ APNA GHAR ══════════════════════════════════════ */
.ab-apnaghar { background: var(--white); padding: 100px 0; position: relative; overflow: hidden; }
.ab-apnaghar-layout { display: grid; gap: 72px; align-items: center; }
@media (min-width: 960px) { .ab-apnaghar-layout { grid-template-columns: 1fr 1fr; } }
.ab-apnaghar-img { border-radius: 28px; overflow: hidden; box-shadow: 0 0 0 1px rgba(169,45,99,.07), 0 24px 70px rgba(169,45,99,.13); position: relative; }
.ab-apnaghar-img::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light)); z-index: 2; }
.ab-apnaghar-img img { width: 100%; aspect-ratio: 4/3; object-fit: cover; display: block; }
.ab-apnaghar-list { list-style: none; margin-top: 28px; display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
.ab-apnaghar-list li { display: flex; align-items: center; gap: 10px; font-size: .87rem; font-weight: 600; color: var(--text-dark); padding: 11px 0; border-bottom: 1px solid rgba(169,45,99,.08); }
.ab-apnaghar-list li::before { content: ''; width: 6px; height: 6px; min-width: 6px; background: linear-gradient(135deg, var(--pink-dark), var(--pink)); border-radius: 50%; flex-shrink: 0; }

/* ══════════════════════════════════════ CARE HOMES ══════════════════════════════════════ */
.ab-homes { background: var(--cream); padding: 100px 0; position: relative; overflow: hidden; }
.ab-homes-header { text-align: center; margin-bottom: 16px; }
.ab-homes-intro { max-width: 720px; margin: 0 auto 40px; text-align: center; font-size: .94rem; color: var(--text-mid); line-height: 1.80; }
.ab-homes-counter { display: flex; justify-content: center; margin-bottom: 56px; }
.ab-homes-counter-inner { display: inline-flex; align-items: center; gap: 20px; background: linear-gradient(135deg, #6B0F35, var(--pink-dark), var(--pink)); border-radius: 22px; padding: 22px 52px; box-shadow: 0 20px 50px rgba(169,45,99,.28); }
.ab-homes-num { font-size: 3.4rem; font-weight: 800; color: var(--yellow); letter-spacing: -3px; line-height: 1; }
.ab-homes-label { font-size: .64rem; font-weight: 800; letter-spacing: 2.5px; text-transform: uppercase; color: rgba(255,255,255,.60); line-height: 1.6; }

.ab-homes-mosaic { display: grid; grid-template-columns: repeat(2,1fr); gap: 14px; }
@media (min-width: 640px) { .ab-homes-mosaic { grid-template-columns: repeat(3,1fr); } }
@media (min-width: 1000px) { .ab-homes-mosaic { grid-template-columns: repeat(4,1fr); } }
@media (min-width: 1400px) { .ab-homes-mosaic { grid-template-columns: repeat(5,1fr); } }
.ab-homes-mosaic .ab-tile:nth-child(1), .ab-homes-mosaic .ab-tile:nth-child(7) { grid-row: span 2; }
.ab-tile { border-radius: 20px; overflow: hidden; position: relative; box-shadow: 0 4px 16px rgba(0,0,0,.08); }
.ab-tile img { width: 100%; height: 100%; object-fit: cover; display: block; min-height: 180px; transition: transform .7s cubic-bezier(.22,.9,.36,1); }
.ab-tile:hover img { transform: scale(1.06); }
.ab-tile-ov { position: absolute; inset: 0; background: linear-gradient(0deg, rgba(10,0,6,.70) 0%, rgba(10,0,6,.10) 50%, transparent 100%); opacity: 0; transition: opacity .35s; }
.ab-tile:hover .ab-tile-ov { opacity: 1; }
.ab-tile-label { position: absolute; bottom: 0; left: 0; right: 0; padding: 18px 14px 12px; background: linear-gradient(0deg, rgba(10,0,6,.72) 0%, transparent 100%); font-size: .60rem; font-weight: 800; letter-spacing: 1.5px; text-transform: uppercase; color: rgba(255,255,255,.85); transform: translateY(3px); transition: transform .3s; }
.ab-tile:hover .ab-tile-label { transform: none; }

/* ══════════════════════════════════════ HAVEN ══════════════════════════════════════ */
.ab-haven { background: var(--white); padding: 100px 0; position: relative; overflow: hidden; }
.ab-haven-layout { display: grid; gap: 72px; align-items: center; }
@media (min-width: 960px) { .ab-haven-layout { grid-template-columns: 1fr 1fr; } }
.ab-haven-img { border-radius: 28px; overflow: hidden; box-shadow: 0 0 0 1px rgba(169,45,99,.07), 0 24px 70px rgba(169,45,99,.13); position: relative; }
.ab-haven-img::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light)); z-index: 2; }
.ab-haven-img img { width: 100%; aspect-ratio: 4/3; object-fit: cover; display: block; }

/* ══════════════════════════════════════ CHAIRPERSON ══════════════════════════════════════ */
.ab-chair { background: var(--off-white); padding: 100px 0; position: relative; overflow: hidden; }
.ab-chair-layout { display: grid; gap: 72px; align-items: center; }
@media (min-width: 960px) { .ab-chair-layout { grid-template-columns: 420px 1fr; gap: 80px; } }
.ab-chair-photo-wrap { position: relative; }
.ab-chair-photo { border-radius: 28px; overflow: hidden; box-shadow: 0 0 0 1px rgba(169,45,99,.08), 0 24px 70px rgba(169,45,99,.15); position: relative; }
.ab-chair-photo::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light)); z-index: 2; }
.ab-chair-photo img { width: 100%; aspect-ratio: 3/4; object-fit: cover; object-position: center top; display: block; }
.ab-chair-badge { position: absolute; bottom: -20px; left: 24px; right: 24px; background: var(--white); border-radius: 18px; padding: 16px 22px; box-shadow: 0 12px 40px rgba(0,0,0,.12); border: 1px solid rgba(169,45,99,.08); z-index: 3; }
.ab-chair-name { font-weight: 800; font-size: 1.1rem; color: var(--text-dark); letter-spacing: -.3px; margin-bottom: 3px; }
.ab-chair-role { font-size: .55rem; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: var(--pink); }
.ab-chair-text { padding-top: 20px; }
.ab-chair-text p { font-size: .94rem; color: var(--text-mid); line-height: 1.90; margin-bottom: 20px; }
.ab-blockquote { border-left: 3px solid var(--pink); background: var(--white); border-radius: 0 16px 16px 0; padding: 24px 32px; margin: 28px 0; box-shadow: 0 4px 18px rgba(169,45,99,.07); }
.ab-blockquote p { font-size: 1.02rem; font-weight: 600; font-style: italic; color: var(--text-dark); line-height: 1.75; margin-bottom: 10px; }
.ab-blockquote cite { font-size: .56rem; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--pink); font-style: normal; }

/* ══════════════════════════════════════ AMBASSADOR ══════════════════════════════════════ */
.ab-ambassador { background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 30%, var(--pink) 65%, var(--pink-mid) 100%); padding: 100px 0; position: relative; overflow: hidden; }
.ab-ambassador::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse 65% 60% at 5% 40%, rgba(242,194,0,.14) 0%, transparent 60%), radial-gradient(ellipse 50% 50% at 95% 60%, rgba(0,0,0,.20) 0%, transparent 55%); pointer-events: none; }
.ab-ambassador-circle-tl { position: absolute; top: -150px; left: -150px; width: 600px; height: 600px; border-radius: 50%; border: 65px solid rgba(242,194,0,.05); pointer-events: none; }
.ab-ambassador-circle-br { position: absolute; bottom: -200px; right: -200px; width: 750px; height: 750px; border-radius: 50%; border: 80px solid rgba(255,255,255,.04); pointer-events: none; }
.ab-ambassador-layout { display: grid; gap: 72px; align-items: center; position: relative; z-index: 2; }
@media (min-width: 960px) { .ab-ambassador-layout { grid-template-columns: 400px 1fr; gap: 80px; } }
.ab-ambassador-photo { border-radius: 28px; overflow: hidden; box-shadow: 0 0 0 1px rgba(255,255,255,.10), 0 24px 70px rgba(0,0,0,.30); position: relative; }
.ab-ambassador-photo::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--yellow-dark), var(--yellow), var(--yellow-light)); z-index: 2; }
.ab-ambassador-photo img { width: 100%; aspect-ratio: 3/4; object-fit: cover; object-position: center top; display: block; }
.ab-ambassador-badge { position: absolute; bottom: -20px; left: 24px; right: 24px; background: rgba(255,255,255,.10); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,.15); border-radius: 18px; padding: 16px 22px; z-index: 3; }
.ab-ambassador-name { font-weight: 800; font-size: 1.05rem; color: var(--white); letter-spacing: -.3px; margin-bottom: 3px; }
.ab-ambassador-role { font-size: .55rem; font-weight: 700; letter-spacing: 2.5px; text-transform: uppercase; color: var(--yellow-light); }
.ab-ambassador-tag { display: inline-flex; align-items: center; gap: 10px; font-size: .54rem; font-weight: 700; letter-spacing: 5px; text-transform: uppercase; color: var(--yellow); margin-bottom: 14px; }
.ab-ambassador-tag::before, .ab-ambassador-tag::after { content: ''; width: 18px; height: 1px; background: var(--yellow); opacity: .5; display: block; }
.ab-ambassador-text { padding-top: 20px; }
.ab-ambassador-text p { font-size: .94rem; color: rgba(255,255,255,.70); line-height: 1.90; margin-bottom: 18px; }
.ab-ambassador-title { font-weight: 800; font-size: clamp(1.8rem, 4vw, 2.6rem); color: var(--white); letter-spacing: -1px; line-height: 1.1; margin-bottom: 4px; }
.ab-ambassador-title em { font-style: normal; color: var(--yellow); filter: drop-shadow(0 0 20px rgba(242,194,0,.30)); }
.ab-quote-glyph { font-family: Georgia, serif; font-size: 7rem; line-height: .55; color: var(--yellow); opacity: .14; display: block; margin-bottom: -16px; user-select: none; }

/* ══════════════════════════════════════ RECOGNITION ══════════════════════════════════════ */
.ab-recognition { background: var(--white); padding: 100px 0; position: relative; overflow: hidden; }
.ab-recognition-header { text-align: center; margin-bottom: 56px; }
.ab-rec-grid { display: grid; gap: 20px; }
@media (min-width: 600px) { .ab-rec-grid { grid-template-columns: repeat(2,1fr); } }
@media (min-width: 960px) { .ab-rec-grid { grid-template-columns: repeat(3,1fr); } }
@media (min-width: 1400px) { .ab-rec-grid { grid-template-columns: repeat(6,1fr); } }
.ab-rec-card { background: var(--off-white); border-radius: 22px; overflow: hidden; position: relative; box-shadow: 0 0 0 1px rgba(169,45,99,.07), 0 6px 20px rgba(169,45,99,.06); transition: transform .35s cubic-bezier(.22,.9,.36,1), box-shadow .35s; }
.ab-rec-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--yellow-dark), var(--yellow-light)); }
.ab-rec-card:hover { transform: translateY(-8px); box-shadow: 0 0 0 1px rgba(169,45,99,.10), 0 24px 52px rgba(169,45,99,.12); }
.ab-rec-img-wrap { overflow: hidden; height: 185px; background: var(--white); }
.ab-rec-img-wrap img { width: 100%; height: 100%; object-fit: contain; padding: 22px; transition: transform .6s cubic-bezier(.22,.9,.36,1); }
.ab-rec-card:hover .ab-rec-img-wrap img { transform: scale(1.04); }
.ab-rec-body { padding: 24px 26px 28px; }
.ab-rec-title { font-weight: 800; font-size: .94rem; color: var(--text-dark); margin-bottom: 7px; line-height: 1.35; }
.ab-rec-sub { font-size: .79rem; color: var(--text-light); line-height: 1.62; }

/* ══════════════════════════════════════ SINDH ══════════════════════════════════════ */
.ab-sindh { background: var(--cream); padding: 100px 0; position: relative; overflow: hidden; }
.ab-sindh-layout { display: grid; gap: 72px; align-items: center; }
@media (min-width: 960px) { .ab-sindh-layout { grid-template-columns: 1fr 0.75fr; } }
.ab-sindh-text p { font-size: .94rem; color: var(--text-mid); line-height: 1.90; margin-bottom: 18px; }
.ab-danish-card { background: var(--white); border-radius: 28px; overflow: hidden; box-shadow: 0 0 0 1px rgba(169,45,99,.07), 0 20px 60px rgba(169,45,99,.10); position: relative; }
.ab-danish-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light)); z-index: 2; }
.ab-danish-img-wrap { overflow: hidden; }
.ab-danish-img-wrap img { width: 100%; aspect-ratio: 4/3; object-fit: cover; object-position: center top; display: block; transition: transform .6s cubic-bezier(.22,.9,.36,1); }
.ab-danish-card:hover .ab-danish-img-wrap img { transform: scale(1.04); }
.ab-danish-body { padding: 28px 32px 32px; background: linear-gradient(145deg, #6B0F35, var(--pink-dark), var(--pink)); position: relative; overflow: hidden; }
.ab-danish-body::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse 70% 60% at 0% 30%, rgba(242,194,0,.14) 0%, transparent 60%); pointer-events: none; }
.ab-danish-chapter { font-size: .50rem; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; color: var(--yellow); margin-bottom: 8px; position: relative; z-index: 1; }
.ab-danish-name { font-weight: 800; font-size: 1.4rem; color: var(--white); letter-spacing: -.4px; margin-bottom: 2px; position: relative; z-index: 1; }
.ab-danish-title { font-size: .72rem; font-weight: 600; color: rgba(255,255,255,.58); margin-bottom: 4px; position: relative; z-index: 1; }
.ab-danish-role { font-size: .60rem; font-weight: 700; color: rgba(255,255,255,.48); letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 16px; position: relative; z-index: 1; }
.ab-danish-divider { width: 40px; height: 2px; background: rgba(242,194,0,.40); border-radius: 2px; margin-bottom: 14px; position: relative; z-index: 1; }
.ab-danish-desc { font-size: .84rem; color: rgba(255,255,255,.70); line-height: 1.70; position: relative; z-index: 1; }

/* ══════════════════════════════════════ LOGO CAROUSEL ══════════════════════════════════════ */
.lc-carousel-section {
  background: var(--white);
  padding: 70px 0 80px;
  position: relative;
  overflow: hidden;
}
.lc-carousel-section::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light), var(--pink));
}
.lc-carousel-section::after {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 50% 60% at 0% 50%, rgba(169,45,99,.03) 0%, transparent 60%),
    radial-gradient(ellipse 50% 60% at 100% 50%, rgba(242,194,0,.03) 0%, transparent 60%);
  pointer-events: none;
}

.lc-carousel-header {
  text-align: center;
  margin-bottom: 52px;
  position: relative;
  z-index: 1;
}
.lc-carousel-tag {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: .54rem;
  font-weight: 700;
  letter-spacing: 5px;
  text-transform: uppercase;
  color: var(--pink);
  margin-bottom: 12px;
  font-family: 'Manrope', sans-serif;
}
.lc-carousel-tag::before, .lc-carousel-tag::after {
  content: '';
  display: block;
  width: 18px; height: 1px;
  background: var(--pink);
  opacity: .45;
}
.lc-carousel-title {
  font-weight: 800;
  font-size: clamp(1.5rem, 3.5vw, 2.2rem);
  color: var(--text-dark);
  letter-spacing: -1.2px;
  line-height: 1.1;
  font-family: 'Manrope', sans-serif;
}
.lc-carousel-title .accent { color: var(--pink); }

.lc-carousel-outer {
  position: relative;
  overflow: hidden;
  padding: 16px 0;
}
.lc-carousel-outer::before,
.lc-carousel-outer::after {
  content: '';
  position: absolute;
  top: 0; bottom: 0;
  width: 160px;
  z-index: 3;
  pointer-events: none;
}
.lc-carousel-outer::before {
  left: 0;
  background: linear-gradient(90deg, var(--white) 0%, transparent 100%);
}
.lc-carousel-outer::after {
  right: 0;
  background: linear-gradient(90deg, transparent 0%, var(--white) 100%);
}

.lc-track {
  display: flex;
  align-items: center;
  width: max-content;
  animation: lcGlide 55s linear infinite;
  will-change: transform;
}
.lc-carousel-outer:hover .lc-track {
  animation-play-state: paused;
}
@keyframes lcGlide {
  0%   { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}

.lc-logo-card {
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 18px;
  flex-shrink: 0;
}
.lc-logo-box {
  width: 200px;
  height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--white);
  border-radius: 18px;
  border: 1.5px solid rgba(169,45,99,.09);
  padding: 18px 22px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(169,45,99,.05), 0 6px 24px rgba(169,45,99,.06);
  transition: transform .5s cubic-bezier(.22,.9,.36,1), box-shadow .5s cubic-bezier(.22,.9,.36,1), border-color .4s;
}
.lc-logo-box::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light));
  border-radius: 18px 18px 0 0;
  opacity: 0;
  transition: opacity .4s;
}
.lc-logo-box:hover {
  transform: translateY(-6px) scale(1.04);
  box-shadow: 0 10px 36px rgba(169,45,99,.14), 0 2px 8px rgba(169,45,99,.07);
  border-color: rgba(169,45,99,.22);
}
.lc-logo-box:hover::before { opacity: 1; }
.lc-logo-box img {
  max-width: 100%;
  max-height: 140px;
  width: auto;
  height: auto;
  object-fit: contain;
  display: block;
  filter: grayscale(50%) opacity(.70);
  transition: filter .45s ease;
}
.lc-logo-box:hover img { filter: grayscale(0%) opacity(1); }

.lc-dot-sep {
  width: 5px; height: 5px;
  min-width: 5px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--pink), var(--yellow));
  opacity: .22;
  margin: 0 6px;
  flex-shrink: 0;
}

/* ══════════════════════════════════════ CTA ══════════════════════════════════════ */
.ab-cta { background: linear-gradient(145deg, #5A0A2A 0%, var(--pink-dark) 35%, var(--pink) 70%, var(--pink-mid) 100%); padding: 110px 40px 120px; text-align: center; position: relative; overflow: hidden; }
.ab-cta::before { content: ''; position: absolute; inset: 0; background: radial-gradient(ellipse 65% 60% at 5% 40%, rgba(242,194,0,.14) 0%, transparent 60%), radial-gradient(ellipse 50% 50% at 95% 60%, rgba(0,0,0,.20) 0%, transparent 55%); pointer-events: none; }
.ab-cta-d1 { position: absolute; top: -150px; left: -150px; width: 600px; height: 600px; border-radius: 50%; border: 65px solid rgba(242,194,0,.05); pointer-events: none; }
.ab-cta-d2 { position: absolute; bottom: -200px; right: -200px; width: 750px; height: 750px; border-radius: 50%; border: 80px solid rgba(255,255,255,.04); pointer-events: none; }
.ab-cta-inner { position: relative; z-index: 2; max-width: 720px; margin: 0 auto; }
.ab-cta-ornament { display: flex; align-items: center; justify-content: center; gap: 14px; margin-bottom: 28px; }
.ab-cta-ornament .bar { width: 60px; height: 2px; background: linear-gradient(90deg, transparent, var(--yellow)); border-radius: 2px; }
.ab-cta-ornament .bar.r { background: linear-gradient(90deg, var(--yellow), transparent); }
.ab-cta-ornament .diamond { width: 10px; height: 10px; background: var(--yellow); transform: rotate(45deg); border-radius: 2px; box-shadow: 0 0 16px rgba(242,194,0,.60); }
.ab-cta h2 { font-weight: 800; font-size: clamp(2rem, 5vw, 3.2rem); color: var(--white); letter-spacing: -1.5px; line-height: 1.08; margin-bottom: 14px; }
.ab-cta h2 .accent { color: var(--yellow); }
.ab-cta p { font-size: .96rem; color: rgba(255,255,255,.55); line-height: 1.75; margin-bottom: 36px; }
.ab-cta-btns { display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }
.ab-cta-btn-primary { display: inline-flex; align-items: center; gap: 10px; padding: 16px 32px; border-radius: 100px; background: linear-gradient(135deg, var(--yellow-dark), var(--yellow), var(--yellow-light)); color: var(--text-dark); font-size: .80rem; font-weight: 800; letter-spacing: 1px; text-decoration: none; transition: transform .3s, box-shadow .3s; box-shadow: 0 8px 28px rgba(242,194,0,.40); position: relative; overflow: hidden; }
.ab-cta-btn-primary::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg, rgba(255,255,255,.20) 0%, transparent 60%); border-radius: inherit; pointer-events: none; }
.ab-cta-btn-primary svg { width: 17px; height: 17px; stroke: currentColor; fill: none; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
.ab-cta-btn-primary:hover { transform: translateY(-3px); box-shadow: 0 16px 44px rgba(242,194,0,.50); }
.ab-cta-btn-ghost { display: inline-flex; align-items: center; gap: 10px; padding: 15px 30px; border-radius: 100px; background: rgba(255,255,255,.10); border: 1.5px solid rgba(255,255,255,.20); color: rgba(255,255,255,.85); font-size: .80rem; font-weight: 700; letter-spacing: 1px; text-decoration: none; transition: all .3s; backdrop-filter: blur(8px); }
.ab-cta-btn-ghost svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
.ab-cta-btn-ghost:hover { background: rgba(255,255,255,.18); border-color: rgba(255,255,255,.35); transform: translateY(-3px); }
</style>

<!-- ══════════ HERO ══════════ -->
<section class="ab-hero">
  <div class="ab-hero-circle-tl"></div>
  <div class="ab-hero-circle-br"></div>
  <div class="ab-hero-inner ab-reveal">
    <h1>About <em>Almarah Foundation</em></h1>
    <p class="ab-hero-sub">A leading non-profit committed to the relief and empowerment of the marginalized across Pakistan — building paths toward dignity, education, and long-term well-being.</p>
    <div class="ab-hero-badges">
      <div class="ab-hero-badge gold">Est. 2021 · Legacy 1995</div>
      <div class="ab-hero-badge">2M+ Beneficiaries</div>
      <div class="ab-hero-badge">13 Care Homes</div>
      <div class="ab-hero-badge gold">UN Excellence Award 2023</div>
    </div>
  </div>
</section>

<!-- ══════════ WHO WE ARE ══════════ -->
<section class="ab-who">
  <div class="ab-wrap">
    <div class="ab-who-layout">
      <div class="ab-reveal">
        <div class="ab-tag">Who We Are</div>
        <h2 class="ab-title">One Mission.<br><span class="accent">Every Life Matters.</span></h2>
        <p class="ab-body">Almarah Foundation Pakistan is a leading non-profit committed to the relief and empowerment of the marginalized. We believe in service without discrimination, ensuring that orphans and vulnerable communities have access to quality healthcare, modern education, and secure shelter.</p>
        <p class="ab-body">Through tireless volunteer work and strategic resource mobilization, we don't just provide temporary relief — we build paths toward long-term livelihood and well-being. By uniting with public and private partners, we are transforming the humanitarian landscape of Pakistan, one life at a time.</p>
      </div>
      <div class="ab-reveal ab-d2">
        <div class="ab-who-img">
          <img src="https://almarah.org/wp-content/uploads/2026/03/475257968_563576563329700_712829272225032489_n.jpg" alt="Almarah Foundation Team">
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ STATS ══════════ -->
<div class="ab-stats">
  <div class="ab-wrap">
    <div class="ab-stats-grid">
      <div class="ab-stat-item ab-reveal">
        <div class="ab-stat-val"><span class="ab-count" data-target="275">0</span>+</div>
        <div class="ab-stat-label">Children in Our Homes</div>
      </div>
      <div class="ab-stat-item ab-reveal ab-d1">
        <div class="ab-stat-val"><span class="ab-count" data-target="1500">0</span>+</div>
        <div class="ab-stat-label">Daily Meals Served</div>
      </div>
      <div class="ab-stat-item ab-reveal ab-d2">
        <div class="ab-stat-val"><span class="ab-count" data-target="13">0</span></div>
        <div class="ab-stat-label">Care Homes in Lahore</div>
      </div>
      <div class="ab-stat-item ab-reveal ab-d3">
        <div class="ab-stat-val"><span class="ab-count" data-target="2">0</span>M+</div>
        <div class="ab-stat-label">Lives Impacted</div>
      </div>
    </div>
  </div>
</div>

<!-- ══════════ MISSION VISION VALUES ══════════ -->
<section class="ab-mvv">
  <div class="ab-wrap">
    <div class="ab-mvv-outer ab-reveal">
      <div class="ab-mvv-grid">
        <div class="ab-mvv-card">
          <div class="ab-mvv-num">01</div>
          <h3>Our Mission</h3>
          <p>Committed to alleviating hunger, nurturing orphaned children in home-like environments, delivering accessible education and healthcare, and strengthening families through empowerment programs — because every life is valuable and should be supported to reach its fullest potential.</p>
        </div>
        <div class="ab-mvv-card">
          <div class="ab-mvv-num">02</div>
          <h3>Our Vision</h3>
          <p>A compassionate and inclusive society where every vulnerable child and family thrives with dignity and the opportunity to fulfill their potential. A Pakistan where no child goes to sleep hungry, uneducated, or without love.</p>
        </div>
        <div class="ab-mvv-card">
          <div class="ab-mvv-num">03</div>
          <h3>Our Values</h3>
          <p>Every life is inherently valuable. Whether we are serving a meal or providing a home, we ensure our services are delivered with the utmost respect, preserving the honor and self-esteem of every child and family.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ PROGRAMS ══════════ -->
<section class="ab-programs">
  <div class="ab-wrap">
    <div class="ab-programs-header ab-reveal">
      <div class="ab-tag">Our Programs</div>
      <h2 class="ab-title">Programs <span class="accent">&amp; Initiatives</span></h2>
      <p>Alleviating the plight of vulnerable children by offering them a nurturing environment for growth and development.</p>
    </div>
    <div class="ab-prog-split ab-reveal">
      <div class="ab-prog-split-img">
        <img src="https://almarah.org/wp-content/uploads/2026/03/Parent-the-Orphan.png" alt="Parent the Orphan">
        <div class="ab-prog-split-ov"></div>
        <div class="ab-prog-split-tag">Flagship Program</div>
      </div>
      <div class="ab-prog-split-body">
        <div class="ab-prog-label">Program 01</div>
        <h3>Parent the Orphan</h3>
        <p>Almarah Foundation came into existence with a mission to work for the welfare of orphans, unattended and homeless children. The foundation gives special attention to health, hygiene, mental health, and education with sports and entertainment provisions.</p>
        <ul class="ab-prog-bullets">
          <li>Started in 2021 — Orphanage Adoption Center in Cavalry Ground, Lahore</li>
          <li>In-house facilities for orphaned kids aged 3 to 13 years</li>
          <li>Currently caring for 275+ children in 13 homes — boys and girls</li>
          <li>Dedicated home for the Christian community</li>
        </ul>
        <a href="https://almarah.org/parent-the-orphan/" class="ab-btn-primary">Learn More <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>
    </div>
    <div class="ab-prog-split ab-flip ab-reveal">
      <div class="ab-prog-split-img">
        <img src="https://almarah.org/wp-content/uploads/2026/03/Feed-the-Hungry-1.png" alt="Feed the Hungry">
        <div class="ab-prog-split-ov"></div>
        <div class="ab-prog-split-tag">Core Initiative</div>
      </div>
      <div class="ab-prog-split-body">
        <div class="ab-prog-label">Program 02</div>
        <h3>Feed the Hungry</h3>
        <p>We provide daily meals and monthly food rations to individuals and families from lower-income sectors, addressing the most fundamental human need while offering financial assistance to those who need it most.</p>
        <ul class="ab-prog-bullets">
          <li>Daily meals to 1,500+ individuals from lower-income sectors</li>
          <li>Supports orphaned children aged 18 or below to stay with families</li>
          <li>Monthly food rations to 70+ families</li>
          <li>Financial assistance to 60+ individuals</li>
        </ul>
        <a href="https://almarah.org/feed-the-hungry-2/" class="ab-btn-primary">Learn More <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>
    </div>
    <div class="ab-prog-grid">
      <div class="ab-prog-card ab-reveal">
        <div class="ab-prog-card-img"><img src="https://almarah.org/wp-content/uploads/2026/03/Educate-Pakistan.png" alt="Educate Pakistan"></div>
        <div class="ab-prog-card-body">
          <h3>Educate Pakistan</h3>
          <p>Almarah Grammar School offers Playgroup through High School education at 132/B Rajpoot Town, Canal Road, Lahore — empowering underprivileged children to break the cycle of poverty.</p>
          <a href="https://almarah.org/educate-pakistan/" class="ab-link-arrow">Explore Program <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
        </div>
      </div>
      <div class="ab-prog-card ab-reveal ab-d1">
        <div class="ab-prog-card-img"><img src="https://almarah.org/wp-content/uploads/2026/05/DSC07430-scaled.jpg" alt="Izzat ki Roti"></div>
        <div class="ab-prog-card-body">
          <h3>Izzat ki Roti</h3>
          <p>A dignified food initiative that allows individuals to receive a meal without the stigma of charity. Community participation and compassion preserve every person's self-respect.</p>
          <a href="https://almarah.org/izat-ki-roti/" class="ab-link-arrow">Explore Program <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
        </div>
      </div>
      <div class="ab-prog-card ab-reveal ab-d2">
        <div class="ab-prog-card-img"><img src="https://almarah.org/wp-content/uploads/2026/05/comp.jpeg" alt="Compassionate Haven"></div>
        <div class="ab-prog-card-body">
          <h3>Compassionate Haven</h3>
          <p>A dedicated home for children with special needs offering sensory-friendly spaces, therapy rooms, and specialized learning tools to help every child reach their fullest potential.</p>
          <a href="https://almarahhaven.org/" class="ab-link-arrow">Explore Program <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ APNA GHAR ══════════ -->
<section class="ab-apnaghar">
  <div class="ab-wrap">
    <div class="ab-apnaghar-layout">
      <div class="ab-apnaghar-img ab-reveal">
        <img src="https://almarah.org/wp-content/uploads/2026/05/apna-ghr.jpg" alt="Apna Ghar Children's Complex">
      </div>
      <div class="ab-reveal ab-d1">
        <div class="ab-tag">Future Initiative</div>
        <h2 class="ab-title">Apna Ghar <span class="accent">Children's Complex</span></h2>
        <p class="ab-body">A key initiative designed to address the pressing needs of orphaned children, ensuring they receive comprehensive care and support at a single, world-class facility. Located at Central Park, Lahore, the Apna Ghar Children's Complex will bring together care, education, health, and recreation under one roof.</p>
        <p class="ab-body">This comprehensive complex will serve as a beacon of hope — a place where every child, regardless of background, can thrive physically, emotionally, and intellectually.</p>
        <ul class="ab-apnaghar-list">
          <li>Academic &amp; Administrative Blocks</li>
          <li>Soft Skills Centre</li>
          <li>Library &amp; R&amp;D Center</li>
          <li>Science Labs</li>
          <li>Basic Health Unit</li>
          <li>Sports Complex &amp; Grounds</li>
          <li>Art &amp; Design Studios</li>
          <li>Conference Room &amp; Auditorium</li>
          <li>Guest Spaces &amp; Staff Amenities</li>
          <li>Cultural Integration Spaces</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ CARE HOMES ══════════ -->
<section class="ab-homes">
  <div class="ab-wrap">
    <div class="ab-homes-header ab-reveal">
      <div class="ab-tag">275 Smiles</div>
      <h2 class="ab-title">Our <span class="accent">Care Homes</span> in Lahore</h2>
    </div>
    <p class="ab-homes-intro ab-reveal">We currently care for 275+ children across 13 homes in DHA, Cavalry Ground, Engineer Town, and KB Colony. Every home is a real family — built on love, structure, and opportunity.</p>
    <div class="ab-homes-counter ab-reveal">
      <div class="ab-homes-counter-inner">
        <div class="ab-homes-num">13</div>
        <div class="ab-homes-label">Active Care Homes<br>across Lahore</div>
      </div>
    </div>
    <div class="ab-homes-mosaic ab-reveal">
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/House-01.JPG-scaled.jpeg" alt="Home 1"><div class="ab-tile-ov"></div><div class="ab-tile-label">Home 01 · DHA</div></div>
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/House-13.jpg" alt="Home 13"><div class="ab-tile-ov"></div><div class="ab-tile-label">Home 13 · Cavalry</div></div>
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/house-5-scaled.png" alt="Home 5"><div class="ab-tile-ov"></div><div class="ab-tile-label">Home 05 · DHA Ph5</div></div>
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/House-04.jpg.jpeg" alt="Home 4"><div class="ab-tile-ov"></div><div class="ab-tile-label">Home 04 · DHA Ph1</div></div>
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/ACH.JPG-scaled.jpeg" alt="Compassionate Haven"><div class="ab-tile-ov"></div><div class="ab-tile-label">Compassionate Haven</div></div>
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/house-11-scaled.png" alt="Home 11"><div class="ab-tile-ov"></div><div class="ab-tile-label">Home 11 · DHA</div></div>
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/House-06.JPG-scaled.jpeg" alt="Home 6"><div class="ab-tile-ov"></div><div class="ab-tile-label">Home 06 · DHA Ph1</div></div>
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/House-08.JPG-scaled.jpeg" alt="Home 8"><div class="ab-tile-ov"></div><div class="ab-tile-label">Home 08 · Cavalry</div></div>
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/House-03-scaled.jpg" alt="Home 3"><div class="ab-tile-ov"></div><div class="ab-tile-label">Home 03 · KB Colony</div></div>
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/House-09.JPG-scaled.jpeg" alt="Home 9"><div class="ab-tile-ov"></div><div class="ab-tile-label">Home 09 · Engineer Town</div></div>
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/House-10.JPG-scaled.jpeg" alt="Home 10"><div class="ab-tile-ov"></div><div class="ab-tile-label">Home 10 · Cavalry</div></div>
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/House-07.JPG-scaled.jpeg" alt="Home 7"><div class="ab-tile-ov"></div><div class="ab-tile-label">Home 07 · DHA Ph1</div></div>
      <div class="ab-tile"><img src="https://almarah.org/wp-content/uploads/2026/05/house-12-scaled.png" alt="Home 12"><div class="ab-tile-ov"></div><div class="ab-tile-label">Home 12 · DHA Ph1</div></div>
    </div>
  </div>
</section>

<!-- ══════════ COMPASSIONATE HAVEN ══════════ -->
<section class="ab-haven">
  <div class="ab-wrap">
    <div class="ab-haven-layout">
      <div class="ab-haven-img ab-reveal">
        <img src="https://almarah.org/wp-content/uploads/2026/05/comp.jpeg" alt="Compassionate Haven">
      </div>
      <div class="ab-reveal ab-d1">
        <div class="ab-tag">Special Needs</div>
        <h2 class="ab-title">Compassionate <span class="accent">Haven</span></h2>
        <p class="ab-body" style="font-size:.72rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--pink);margin-bottom:12px;">Institute for the Development of Special Children</p>
        <p class="ab-body">Almarah Foundation has proudly expanded its mission to include a dedicated home for children with special needs. This initiative offers a nurturing environment where children with physical, cognitive, and developmental challenges receive personalized care and support.</p>
        <p class="ab-body">Our facility is equipped with sensory-friendly spaces, therapy rooms, and specialized learning tools, all designed to help each child thrive. With trained educators, therapists, and caregivers, we focus on each child's unique strengths — providing tailored educational and developmental programs.</p>
        <p class="ab-body">At Almarah, we are committed to ensuring that every child, regardless of their abilities, feels valued, empowered, and supported in reaching their fullest potential.</p>
        <a href="https://almarahhaven.org/" class="ab-btn-primary">Visit Almarahhaven.org <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ CHAIRPERSON ══════════ -->
<section class="ab-chair">
  <div class="ab-wrap">
    <div class="ab-chair-layout">
      <div class="ab-reveal">
        <div class="ab-chair-photo-wrap">
          <div class="ab-chair-photo">
            <img src="https://almarah.org/wp-content/uploads/2026/03/522075580_602997796196541_465002084556126553_n.jpg" alt="Sophiya Warraich">
          </div>
          <div class="ab-chair-badge">
            <div class="ab-chair-name">Sophiya Warraich</div>
            <div class="ab-chair-role">Chairperson, Almarah Foundation</div>
          </div>
        </div>
      </div>
      <div class="ab-chair-text ab-reveal ab-d1">
        <div class="ab-tag">Leading from the Front</div>
        <h2 class="ab-title">Our <span class="accent">Chairperson</span></h2>
        <p>Ms. Sophiya Rasheed Warraich is a distinguished Pakistani philanthropist, psychologist, and entrepreneur whose multi-dimensional approach to social change is as strategic as it is compassionate.</p>
        <p>Following the passing of her father, Major (R) Rasheed Warraich, in 2009, she established the Almarah Foundation to transform personal loss into a lifelong commitment to the marginalized. Formally registered in 2021, the Foundation is the continuation of the legacy her father began in 1995.</p>
        <p>As a Clinical Psychologist and successful business leader in the clothing and real estate sectors, Ms. Sophiya does not merely provide charity; she constructs systems designed to restore dignity and foster long-term self-reliance.</p>
        <div class="ab-blockquote">
          <p>"Once we accept our own ability to make a difference, recognize our own passions and convictions, we then are able to empathize with those in need in our communities."</p>
          <cite>— Sophiya Warraich, Chairperson, Almarah Foundation</cite>
        </div>
        <p>Her work has earned national and international recognition, including the United Nations Excellence Award in 2023, inspiring a generation of changemakers across Pakistan and beyond.</p>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ HAMZA ALI ABBASI ══════════ -->
<section class="ab-ambassador">
  <div class="ab-ambassador-circle-tl"></div>
  <div class="ab-ambassador-circle-br"></div>
  <div class="ab-wrap">
    <div class="ab-ambassador-layout">
      <div class="ab-reveal">
        <div style="position:relative;">
          <div class="ab-ambassador-photo">
            <img src="https://almarah.org/wp-content/uploads/2026/05/Hamza-Ali-Abbasi.jpg" alt="Hamza Ali Abbasi">
          </div>
          <div class="ab-ambassador-badge">
            <div class="ab-ambassador-name">Hamza Ali Abbasi</div>
            <div class="ab-ambassador-role">Brand Ambassador</div>
          </div>
        </div>
      </div>
      <div class="ab-reveal ab-d1">
        <div class="ab-ambassador-tag">Brand Ambassador</div>
        <div class="ab-ambassador-title">Hamza Ali <em>Abbasi</em></div>
        <div class="ab-ambassador-text">
          <span class="ab-quote-glyph">"</span>
          <p>I am honored to be associated with Almarah Foundation because what I have witnessed here is something truly rare. This is not just an organization providing food, education, and safety — it is a place where children find the love of a mother and father. That emotional connection, that sense of belonging, is what sets Almarah apart.</p>
          <p>When I visited, I saw children who had nothing now living with dignity, receiving quality education, proper nutrition, and above all, unconditional love. Recently, the Foundation also opened a dedicated home for children with special needs, and that moved me deeply.</p>
          <p>There are very few NGOs in Pakistan that are working at this level, with such sincerity and compassion. I believe it is our moral obligation to support organizations like Almarah Foundation, because they are not just changing lives — they are giving these children a future.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ RECOGNITION ══════════ -->
<section class="ab-recognition">
  <div class="ab-wrap">
    <div class="ab-recognition-header ab-reveal">
      <div class="ab-tag">Awards &amp; Recognition</div>
      <h2 class="ab-title">Recognized <span class="accent">Across the Globe</span></h2>
    </div>
    <div class="ab-rec-grid">
      <div class="ab-rec-card ab-reveal"><div class="ab-rec-img-wrap"><img src="https://almarah.org/wp-content/uploads/2026/05/United-nations.jpg" alt="UN Award"></div><div class="ab-rec-body"><div class="ab-rec-title">UN Excellence Award 2023</div><div class="ab-rec-sub">Recognized by the United Nations for outstanding humanitarian service and impact across Pakistan.</div></div></div>
      <div class="ab-rec-card ab-reveal ab-d1"><div class="ab-rec-img-wrap"><img src="https://almarah.org/wp-content/uploads/2026/05/FPCCI.jpg" alt="FPCCI"></div><div class="ab-rec-body"><div class="ab-rec-title">FPCCI Recognition</div><div class="ab-rec-sub">Acknowledged by the Federation of Pakistan Chambers of Commerce and Industry for philanthropic excellence.</div></div></div>
      <div class="ab-rec-card ab-reveal ab-d2"><div class="ab-rec-img-wrap"><img src="https://almarah.org/wp-content/uploads/2026/05/sitara-hilal.jpg" alt="Sitara-e-Hilal"></div><div class="ab-rec-body"><div class="ab-rec-title">Sitara-e-Hilal</div><div class="ab-rec-sub">One of Pakistan's highest civil honours, awarded for distinguished service to the nation and its people.</div></div></div>
      <div class="ab-rec-card ab-reveal"><div class="ab-rec-img-wrap"><img src="https://almarah.org/wp-content/uploads/2026/05/TEDx.jpg" alt="TEDx"></div><div class="ab-rec-body"><div class="ab-rec-title">TEDx Speaker</div><div class="ab-rec-sub">Featured as a TEDx speaker, sharing the Almarah story of compassion and systemic change with global audiences.</div></div></div>
      <div class="ab-rec-card ab-reveal ab-d1"><div class="ab-rec-img-wrap"><img src="https://almarah.org/wp-content/uploads/2026/05/house-of-lords.jpg" alt="House of Lords"></div><div class="ab-rec-body"><div class="ab-rec-title">House of Lords, UK</div><div class="ab-rec-sub">Invited to speak at the UK House of Lords, bringing Pakistan's humanitarian work to the international stage.</div></div></div>
      <div class="ab-rec-card ab-reveal ab-d2"><div class="ab-rec-img-wrap"><img src="https://almarah.org/wp-content/uploads/2026/05/house-of-common.jpg" alt="House of Commons"></div><div class="ab-rec-body"><div class="ab-rec-title">House of Commons, UK</div><div class="ab-rec-sub">Honored at the UK Parliament for exemplary cross-border humanitarian leadership and vision.</div></div></div>
    </div>
  </div>
</section>

<!-- ══════════ SINDH CHAPTER ══════════ -->
<section class="ab-sindh">
  <div class="ab-wrap">
    <div class="ab-sindh-layout">
      <div class="ab-reveal">
        <div class="ab-tag">Expanding Our Reach</div>
        <h2 class="ab-title">Almarah <span class="accent">Sindh Chapter</span></h2>
        <div class="ab-sindh-text">
          <p>The Sindh Chapter of the Almarah Foundation marks a significant milestone in our mission to serve the most vulnerable members of society across Pakistan. Building on decades of humanitarian service, formalized in 2021, this new chapter brings our commitment to feed the hungry, support orphans, and uplift underserved communities closer to the people of Sindh.</p>
          <p>Since our inception, the Almarah Foundation has been driven by compassion and a deep belief in human dignity. We provide daily food assistance, safe shelter, education, and holistic care to children who have lost parental support, as well as practical support to families in crisis.</p>
          <p>The Sindh Chapter will expand these efforts across the province, tailoring services to local needs and working hand-in-hand with communities, volunteers, and partners. Our vision is a Sindh where every child has access to nutritious meals, safe shelter, quality education, and the opportunity to thrive.</p>
        </div>
      </div>
      <div class="ab-danish-card ab-reveal ab-d2">
        <div class="ab-danish-img-wrap"><img src="https://almarah.org/wp-content/uploads/2026/05/Danish-Aman.jpg" alt="Danish Aman"></div>
        <div class="ab-danish-body">
          <div class="ab-danish-chapter">Sindh Chapter</div>
          <div class="ab-danish-name">Danish Aman</div>
          <div class="ab-danish-title">Owner, Hafiz Tannery</div>
          <div class="ab-danish-role">Chapter Head — Sindh</div>
          <div class="ab-danish-divider"></div>
          <div class="ab-danish-desc">Spearheading Almarah Foundation's expansion into Sindh, bringing the same dignity-first model of care to a new region — feeding the hungry, sheltering orphans, and uplifting underserved communities across the province.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════ LOGO CAROUSEL ══════════ -->
<section class="lc-carousel-section">
  <div class="lc-carousel-header">
    <div class="lc-carousel-tag">Recognised By</div>
    <h2 class="lc-carousel-title">Our <span class="accent">Partners</span> and Authorities</h2>
  </div>
  <div class="lc-carousel-outer">
    <div class="lc-track">
      <!-- Set 1 -->
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/adv2.png" alt="ADV Partner"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/adv.jpg" alt="ADV"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/pcp-icon-01.png" alt="PCP"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/companies-House-uk-icon-01.png" alt="Companies House UK"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/punjab-chaity-comission-1.png" alt="Punjab Charity Commission"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/eman-ethad-nazr-1.png" alt="Eman Ittehad"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/child-protection.png" alt="Child Protection"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/fbr-pak.png" alt="FBR Pakistan"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/government-punjab.png" alt="Government of Punjab"></div></div>
      <div class="lc-dot-sep"></div>
      <!-- Set 2 (duplicate for seamless loop) -->
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/adv2.png" alt="ADV Partner"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/adv.jpg" alt="ADV"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/pcp-icon-01.png" alt="PCP"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/companies-House-uk-icon-01.png" alt="Companies House UK"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/punjab-chaity-comission-1.png" alt="Punjab Charity Commission"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/eman-ethad-nazr-1.png" alt="Eman Ittehad"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/child-protection.png" alt="Child Protection"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/fbr-pak.png" alt="FBR Pakistan"></div></div>
      <div class="lc-dot-sep"></div>
      <div class="lc-logo-card"><div class="lc-logo-box"><img src="https://almarah.org/wp-content/uploads/2026/03/government-punjab.png" alt="Government of Punjab"></div></div>
      <div class="lc-dot-sep"></div>
    </div>
  </div>
</section>

<!-- ══════════ CTA ══════════ -->
<section class="ab-cta">
  <div class="ab-cta-d1"></div>
  <div class="ab-cta-d2"></div>
  <div class="ab-cta-inner ab-reveal">
    <div class="ab-cta-ornament">
      <div class="bar"></div>
      <div class="diamond"></div>
      <div class="bar r"></div>
    </div>
    <h2>Be Part of the <span class="accent">Change</span></h2>
    <p>Join thousands of supporters who trust Almarah Foundation to make a real and lasting difference across Pakistan — one child, one family, one life at a time.</p>
    <div class="ab-cta-btns">
      <a href="https://almarah.org/donate/" class="ab-cta-btn-primary">
        <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        Donate Now
      </a>
      <a href="https://almarah.org/contact/" class="ab-cta-btn-ghost">
        <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Get in Touch
      </a>
    </div>
  </div>
</section>

<script>
(function () {
  var els = document.querySelectorAll('.ab-reveal');
  var obs = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (e.isIntersecting) { e.target.classList.add('ab-in'); obs.unobserve(e.target); }
    });
  }, { threshold: 0.10, rootMargin: '0px 0px -32px 0px' });
  els.forEach(function (el) { obs.observe(el); });

  var done = false;
  var nums = document.querySelectorAll('.ab-count');
  function runCounters() {
    nums.forEach(function (el) {
      var target = parseInt(el.getAttribute('data-target'), 10);
      var start = performance.now();
      var dur = 1500;
      (function tick(now) {
        var p = Math.min((now - start) / dur, 1);
        var e = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.floor(e * target);
        if (p < 1) requestAnimationFrame(tick);
        else el.textContent = target;
      })(start);
    });
  }
  var sObs = new IntersectionObserver(function (entries) {
    if (entries[0].isIntersecting && !done) { done = true; runCounters(); sObs.disconnect(); }
  }, { threshold: 0.4 });
  var s = document.querySelector('.ab-stats');
  if (s) sObs.observe(s);
})();
</script>