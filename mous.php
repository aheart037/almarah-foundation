<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">

<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
  --pink:         #A92D63;
  --pink-dark:    #8A2050;
  --pink-mid:     #C4386F;
  --pink-soft:    rgba(169,45,99,.08);
  --yellow:       #F2C200;
  --yellow-light: #FFD740;
  --white:        #FFFFFF;
  --off-white:    #FDF5F8;
  --text-dark:    #1A0810;
  --text-mid:     #5A2040;
  --text-light:   #9A6080;
}

html { scroll-behavior: smooth; }

.mou-page {
  font-family: 'Manrope', sans-serif;
  background: var(--off-white);
  color: var(--text-dark);
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
}

/* ═══════════════════
   ANIMATIONS
═══════════════════ */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(40px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes fadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}
@keyframes slideLeft {
  from { opacity: 0; transform: translateX(-60px); }
  to   { opacity: 1; transform: translateX(0); }
}
@keyframes slideRight {
  from { opacity: 0; transform: translateX(60px); }
  to   { opacity: 1; transform: translateX(0); }
}
@keyframes countUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes lineGrow {
  from { width: 0; }
  to   { width: 48px; }
}
@keyframes badgePop {
  0%   { opacity: 0; transform: scale(0.7) translateY(10px); }
  70%  { transform: scale(1.06) translateY(-2px); }
  100% { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes heroFloat {
  0%,100% { transform: translateY(0px); }
  50%      { transform: translateY(-10px); }
}
@keyframes shimmer {
  0%   { background-position: -400px 0; }
  100% { background-position: 400px 0; }
}
@keyframes pulse-ring {
  0%   { transform: scale(1);   opacity: .6; }
  100% { transform: scale(1.5); opacity: 0; }
}
@keyframes rotateSlow {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}

/* Intersection observer classes */
.mou-animate {
  opacity: 0;
  transform: translateY(40px);
  transition: opacity .7s ease, transform .7s ease;
}
.mou-animate.mou-visible {
  opacity: 1;
  transform: translateY(0);
}
.mou-animate-left {
  opacity: 0;
  transform: translateX(-50px);
  transition: opacity .8s ease, transform .8s ease;
}
.mou-animate-left.mou-visible {
  opacity: 1;
  transform: translateX(0);
}
.mou-animate-right {
  opacity: 0;
  transform: translateX(50px);
  transition: opacity .8s ease, transform .8s ease;
}
.mou-animate-right.mou-visible {
  opacity: 1;
  transform: translateX(0);
}
.mou-animate-scale {
  opacity: 0;
  transform: scale(0.85);
  transition: opacity .6s ease, transform .6s cubic-bezier(.22,.9,.36,1);
}
.mou-animate-scale.mou-visible {
  opacity: 1;
  transform: scale(1);
}

/* Delay utilities */
.mou-delay-1 { transition-delay: .1s !important; }
.mou-delay-2 { transition-delay: .2s !important; }
.mou-delay-3 { transition-delay: .3s !important; }
.mou-delay-4 { transition-delay: .4s !important; }
.mou-delay-5 { transition-delay: .5s !important; }
.mou-delay-6 { transition-delay: .6s !important; }

/* ═══════════════════
   HERO
═══════════════════ */
.mou-hero {
  background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 30%, var(--pink) 65%, var(--pink-mid) 100%);
  padding: 120px 24px 100px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
@media (min-width: 768px) { .mou-hero { padding: 140px 40px 110px; } }

.mou-hero::before {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 70% 60% at 10% 60%, rgba(242,194,0,.18) 0%, transparent 60%),
    radial-gradient(ellipse 50% 50% at 90% 15%, rgba(255,255,255,.09) 0%, transparent 55%),
    radial-gradient(ellipse 40% 40% at 50% 100%, rgba(0,0,0,.14) 0%, transparent 60%);
  pointer-events: none;
}

/* Animated rotating ring */
.mou-hero-ring {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 900px; height: 900px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,.04);
  animation: rotateSlow 40s linear infinite;
  pointer-events: none;
}
.mou-hero-ring::before {
  content: '';
  position: absolute;
  top: 10%; left: 0;
  width: 12px; height: 12px;
  border-radius: 50%;
  background: rgba(242,194,0,.5);
  box-shadow: 0 0 20px rgba(242,194,0,.4);
}

.mou-hero-circle-tl {
  position: absolute;
  top: -120px; left: -120px;
  width: 420px; height: 420px;
  border-radius: 50%;
  border: 60px solid rgba(255,255,255,.04);
  pointer-events: none;
  animation: fadeIn 1.5s ease both;
}
.mou-hero-circle-br {
  position: absolute;
  bottom: -100px; right: -180px;
  width: 560px; height: 560px;
  border-radius: 50%;
  border: 75px solid rgba(242,194,0,.05);
  pointer-events: none;
  animation: fadeIn 1.5s ease .3s both;
}

.mou-hero-inner {
  position: relative;
  z-index: 2;
  max-width: 760px;
  margin: 0 auto;
}

.mou-hero-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: .62rem;
  font-weight: 700;
  letter-spacing: 5px;
  text-transform: uppercase;
  color: var(--yellow-light);
  margin-bottom: 20px;
  animation: fadeUp .8s ease .1s both;
}
.mou-hero-eyebrow::before,
.mou-hero-eyebrow::after {
  content: '';
  display: block;
  width: 28px; height: 1.5px;
  background: var(--yellow);
  opacity: .6;
}

.mou-hero h1 {
  font-weight: 800;
  font-size: clamp(2.2rem, 7vw, 4.2rem);
  color: var(--white);
  letter-spacing: -2px;
  line-height: 1.05;
  text-shadow: 0 4px 40px rgba(0,0,0,.22);
  margin-bottom: 20px;
  animation: fadeUp .8s ease .25s both;
}
.mou-hero h1 em {
  font-style: normal;
  color: var(--yellow);
  filter: drop-shadow(0 0 24px rgba(242,194,0,.30));
}

.mou-hero-sub {
  font-size: .93rem;
  font-weight: 400;
  font-family: 'Open Sans', sans-serif;
  color: rgba(255,255,255,.65);
  line-height: 1.85;
  max-width: 640px;
  margin: 0 auto 42px;
  animation: fadeUp .8s ease .4s both;
}

.mou-hero-badges {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 12px;
  margin-bottom: 56px;
  animation: fadeUp .8s ease .55s both;
}

.mou-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border-radius: 100px;
  background: rgba(255,255,255,.10);
  border: 1.5px solid rgba(255,255,255,.16);
  backdrop-filter: blur(8px);
  font-size: .67rem;
  font-weight: 700;
  color: rgba(255,255,255,.85);
  letter-spacing: .5px;
  transition: all .3s ease;
}
.mou-hero-badge:hover {
  background: rgba(255,255,255,.18);
  transform: translateY(-2px);
}
.mou-hero-badge svg {
  width: 14px; height: 14px;
  flex-shrink: 0;
  stroke: currentColor;
  fill: none;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
}
.mou-hero-badge.gold {
  background: rgba(242,194,0,.15);
  border-color: rgba(242,194,0,.35);
  color: var(--yellow-light);
}

/* STATS GLASSMORPHISM BAR */
.mou-hero-stats {
  display: flex;
  justify-content: center;
  gap: 0;
  flex-wrap: wrap;
  background: rgba(255,255,255,.08);
  border: 1.5px solid rgba(255,255,255,.14);
  border-radius: 22px;
  backdrop-filter: blur(16px);
  padding: 8px 0;
  max-width: 700px;
  margin: 0 auto;
  animation: fadeUp .8s ease .7s both;
  overflow: hidden;
}

.mou-hero-stat {
  flex: 1;
  min-width: 130px;
  padding: 22px 20px;
  text-align: center;
  border-right: 1px solid rgba(255,255,255,.10);
  position: relative;
  cursor: default;
  transition: background .3s ease;
}
.mou-hero-stat:last-child { border-right: none; }
.mou-hero-stat:hover {
  background: rgba(255,255,255,.06);
}

/* Animated counter numbers */
.mou-hero-stat-num {
  font-size: 2.6rem;
  font-weight: 800;
  color: var(--white);
  line-height: 1;
  letter-spacing: -1.5px;
  font-family: 'Manrope', sans-serif;
  transition: transform .3s ease;
}
.mou-hero-stat:hover .mou-hero-stat-num {
  transform: scale(1.08);
}
.mou-hero-stat-num .sup {
  color: var(--yellow);
  font-size: 1.5rem;
  vertical-align: super;
  line-height: 0;
}

.mou-hero-stat-label {
  font-size: .57rem;
  font-weight: 600;
  color: rgba(255,255,255,.50);
  letter-spacing: 1.5px;
  text-transform: uppercase;
  margin-top: 8px;
  font-family: 'Open Sans', sans-serif;
}

/* Animated progress bar under each stat */
.mou-hero-stat::after {
  content: '';
  position: absolute;
  bottom: 0; left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 2px;
  background: var(--yellow);
  border-radius: 2px;
  transition: width .4s ease;
}
.mou-hero-stat:hover::after { width: 60%; }

/* ═══════════════════
   SECTION INTRO
═══════════════════ */
.mou-section-intro {
  text-align: center;
  padding: 90px 24px 64px;
  max-width: 740px;
  margin: 0 auto;
}
.mou-section-tag {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: .58rem;
  font-weight: 700;
  letter-spacing: 5px;
  text-transform: uppercase;
  color: var(--pink);
  margin-bottom: 14px;
}
.mou-section-tag::before,
.mou-section-tag::after {
  content: '';
  display: block;
  width: 18px; height: 1px;
  background: var(--pink);
  opacity: .45;
}
.mou-section-title {
  font-weight: 800;
  font-size: clamp(1.9rem, 4.5vw, 2.8rem);
  color: var(--text-dark);
  letter-spacing: -1.5px;
  line-height: 1.1;
  margin-bottom: 16px;
}
.mou-section-title .accent { color: var(--pink); }
.mou-section-desc {
  font-size: .92rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-light);
  line-height: 1.85;
}

/* ═══════════════════
   PARTNER ENTRIES  — KEY FIX
═══════════════════ */
.mou-entries { background: var(--off-white); }

.mou-entry {
  display: grid;
  /* image col = 44%, content col = 56% */
  grid-template-columns: 44% 56%;
  border-top: 1px solid rgba(169,45,99,.07);
}
.mou-entry.reverse {
  grid-template-columns: 56% 44%;
}
.mou-entry.reverse .mou-entry-img  { order: 2; }
.mou-entry.reverse .mou-entry-content { order: 1; }

/* ── IMAGE PANEL (FIXED) ── */
.mou-entry-img {
  position: relative;
  overflow: hidden;
  background: #1a0810;
  min-height: 480px;
  
  /* NEW: center the image inside without cropping */
  display: flex;
  align-items: center;
  justify-content: center;
}

.mou-entry-img img {
  /* Show full image — no cutting */
  display: block;
  width: 100%;
  height: 100%;
  object-fit: contain;          /* changed from cover */
  object-position: center;      /* changed from center top */
  transition: transform .8s cubic-bezier(.22,.9,.36,1);
}
.mou-entry:hover .mou-entry-img img {
  transform: scale(1.03);       /* slightly smaller zoom for contain */
}

/* Subtle gradient overlay (kept) */
.mou-entry-img::after {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(
    to bottom,
    rgba(26,8,16,.05) 0%,
    rgba(26,8,16,.25) 100%
  );
  pointer-events: none;
  z-index: 1;
}

/* Badge (kept for possible future use, but not displayed) */
.mou-img-badge {
  position: absolute;
  top: 22px; left: 22px;
  z-index: 2;
  background: var(--pink);
  color: #fff;
  font-family: 'Manrope', sans-serif;
  font-size: .57rem;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  padding: 7px 15px;
  border-radius: 100px;
  box-shadow: 0 4px 20px rgba(169,45,99,.40);
  transition: background .3s, transform .3s;
}
.mou-entry:hover .mou-img-badge {
  background: var(--yellow);
  color: var(--text-dark);
  transform: translateY(-2px);
}

/* Placeholder when no image */
.mou-img-placeholder {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 18px;
  background: linear-gradient(135deg, #1a0810 0%, #2d0f20 55%, #3d1230 100%);
}
.mou-img-placeholder-icon {
  width: 80px; height: 80px;
  border-radius: 50%;
  background: rgba(242,194,0,.10);
  border: 1.5px solid rgba(242,194,0,.22);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}
.mou-img-placeholder-icon::before {
  content: '';
  position: absolute;
  inset: -8px;
  border-radius: 50%;
  border: 1px solid rgba(242,194,0,.15);
  animation: pulse-ring 2s ease infinite;
}
.mou-img-placeholder-icon svg {
  width: 32px; height: 32px;
  stroke: var(--yellow);
  fill: none;
  stroke-width: 1.5;
  stroke-linecap: round;
  stroke-linejoin: round;
  opacity: .75;
}
.mou-img-placeholder-text {
  font-family: 'Manrope', sans-serif;
  font-size: .6rem;
  font-weight: 700;
  color: rgba(255,255,255,.28);
  letter-spacing: 3px;
  text-transform: uppercase;
}

/* ── CONTENT PANEL ── */
.mou-entry-content {
  padding: 64px 60px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  background: var(--white);
  position: relative;
}
.mou-entry.reverse .mou-entry-content {
  background: var(--off-white);
}

/* Accent left bar */
.mou-entry-content::before {
  content: '';
  position: absolute;
  top: 50%; left: 0;
  transform: translateY(-50%);
  width: 3px; height: 64px;
  background: linear-gradient(180deg, var(--yellow), var(--pink));
  border-radius: 0 3px 3px 0;
  transition: height .4s ease;
}
.mou-entry:hover .mou-entry-content::before { height: 100px; }
.mou-entry.reverse .mou-entry-content::before {
  left: auto; right: 0;
  border-radius: 3px 0 0 3px;
}

.mou-entry-number {
  font-size: 5.5rem;
  font-weight: 800;
  color: rgba(169,45,99,.07);
  line-height: .85;
  letter-spacing: -4px;
  margin-bottom: -4px;
  font-family: 'Manrope', sans-serif;
  user-select: none;
  transition: color .4s;
}
.mou-entry:hover .mou-entry-number {
  color: rgba(169,45,99,.13);
}

.mou-entry-category {
  font-size: .57rem;
  font-weight: 700;
  letter-spacing: 3.5px;
  text-transform: uppercase;
  color: var(--pink);
  margin-bottom: 10px;
  font-family: 'Open Sans', sans-serif;
}

.mou-entry-name {
  font-size: clamp(1.4rem, 2.2vw, 1.85rem);
  font-weight: 800;
  color: var(--text-dark);
  line-height: 1.2;
  letter-spacing: -.5px;
  margin-bottom: 8px;
  font-family: 'Manrope', sans-serif;
  transition: color .3s;
}
.mou-entry:hover .mou-entry-name { color: var(--pink-dark); }

.mou-entry-subtitle {
  font-size: .88rem;
  font-style: italic;
  color: var(--pink);
  font-weight: 600;
  margin-bottom: 20px;
  line-height: 1.55;
  font-family: 'Open Sans', sans-serif;
}

/* Animated divider */
.mou-divider {
  width: 48px;
  height: 3px;
  background: linear-gradient(90deg, var(--yellow), var(--pink));
  border-radius: 2px;
  margin-bottom: 20px;
  transition: width .4s ease;
}
.mou-entry:hover .mou-divider { width: 80px; }

.mou-entry-body {
  font-size: .875rem;
  font-family: 'Open Sans', sans-serif;
  color: #665060;
  line-height: 1.95;
  margin-bottom: 22px;
}
.mou-entry-body strong { color: var(--text-dark); font-weight: 700; }

/* Vision box */
.mou-vision-box {
  background: var(--off-white);
  border-left: 3px solid var(--yellow);
  border-radius: 0 12px 12px 0;
  padding: 16px 20px;
  margin-bottom: 24px;
  transition: border-color .3s, background .3s;
}
.mou-entry:hover .mou-vision-box {
  border-left-color: var(--pink);
  background: rgba(169,45,99,.04);
}
.mou-entry.reverse .mou-vision-box { background: var(--white); }
.mou-entry.reverse:hover .mou-vision-box { background: rgba(169,45,99,.04); }

.mou-vision-label {
  font-size: .56rem;
  font-weight: 700;
  letter-spacing: 2.5px;
  text-transform: uppercase;
  color: var(--pink);
  margin-bottom: 7px;
  font-family: 'Manrope', sans-serif;
}
.mou-vision-text {
  font-size: .84rem;
  font-family: 'Open Sans', sans-serif;
  color: #665060;
  line-height: 1.9;
}

/* Date chip */
.mou-date-chip {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: .7rem;
  font-weight: 700;
  color: var(--text-light);
  background: rgba(169,45,99,.05);
  border: 1px solid rgba(169,45,99,.10);
  padding: 7px 16px;
  border-radius: 100px;
  width: fit-content;
  font-family: 'Manrope', sans-serif;
  transition: all .3s;
}
.mou-date-chip:hover,
.mou-entry:hover .mou-date-chip {
  background: var(--pink);
  border-color: var(--pink);
  color: var(--white);
}
.mou-date-chip svg {
  width: 13px; height: 13px;
  stroke: currentColor;
  fill: none;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
  flex-shrink: 0;
}

/* ═══════════════════
   CTA
═══════════════════ */
.mou-cta {
  background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 35%, var(--pink) 70%, var(--pink-mid) 100%);
  padding: 110px 24px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.mou-cta::before {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 60% 70% at 10% 50%, rgba(242,194,0,.14) 0%, transparent 60%),
    radial-gradient(ellipse 50% 50% at 90% 20%, rgba(255,255,255,.07) 0%, transparent 55%);
  pointer-events: none;
}
.mou-cta-circle1 {
  position: absolute;
  top: -90px; right: -90px;
  width: 340px; height: 340px;
  border-radius: 50%;
  border: 55px solid rgba(242,194,0,.06);
  pointer-events: none;
  animation: rotateSlow 30s linear infinite;
}
.mou-cta-circle2 {
  position: absolute;
  bottom: -70px; left: -70px;
  width: 260px; height: 260px;
  border-radius: 50%;
  border: 45px solid rgba(255,255,255,.04);
  pointer-events: none;
  animation: rotateSlow 25s linear infinite reverse;
}
.mou-cta-inner {
  position: relative;
  z-index: 2;
  max-width: 660px;
  margin: 0 auto;
}
.mou-cta-tag {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: .58rem;
  font-weight: 700;
  letter-spacing: 5px;
  text-transform: uppercase;
  color: var(--yellow-light);
  margin-bottom: 18px;
}
.mou-cta-tag::before,
.mou-cta-tag::after {
  content: '';
  display: block;
  width: 18px; height: 1px;
  background: var(--yellow);
  opacity: .6;
}
.mou-cta-title {
  font-weight: 800;
  font-size: clamp(2.2rem, 5vw, 3.4rem);
  color: var(--white);
  letter-spacing: -2px;
  line-height: 1.08;
  margin-bottom: 18px;
}
.mou-cta-title em {
  font-style: normal;
  color: var(--yellow);
}
.mou-cta-desc {
  font-size: .93rem;
  font-family: 'Open Sans', sans-serif;
  color: rgba(255,255,255,.70);
  line-height: 1.85;
  margin-bottom: 44px;
}
.mou-cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: var(--yellow);
  color: #1a0810;
  font-family: 'Manrope', sans-serif;
  font-size: .92rem;
  font-weight: 800;
  padding: 18px 52px;
  border-radius: 100px;
  text-decoration: none;
  letter-spacing: .3px;
  transition: all .35s ease;
  box-shadow: 0 8px 32px rgba(0,0,0,.28);
  position: relative;
  overflow: hidden;
}
.mou-cta-btn::before {
  content: '';
  position: absolute;
  top: 0; left: -100%;
  width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.25), transparent);
  transition: left .5s ease;
}
.mou-cta-btn:hover::before { left: 100%; }
.mou-cta-btn svg {
  width: 16px; height: 16px;
  stroke: currentColor;
  fill: none;
  stroke-width: 2.5;
  stroke-linecap: round;
  stroke-linejoin: round;
  transition: transform .3s;
}
.mou-cta-btn:hover {
  background: var(--white);
  color: var(--pink);
  transform: translateY(-3px);
  box-shadow: 0 18px 48px rgba(0,0,0,.32);
  text-decoration: none;
}
.mou-cta-btn:hover svg { transform: translateX(5px); }

/* ═══════════════════
   RESPONSIVE
═══════════════════ */
@media (max-width: 900px) {
  .mou-entry,
  .mou-entry.reverse {
    grid-template-columns: 1fr !important;
  }
  .mou-entry.reverse .mou-entry-img  { order: 0; }
  .mou-entry.reverse .mou-entry-content { order: 1; }

  .mou-entry-img {
    min-height: 340px !important;
    height: 340px;
    /* Keep flex centering on mobile */
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .mou-entry-content {
    padding: 44px 28px;
  }
  .mou-entry-content::before,
  .mou-entry.reverse .mou-entry-content::before {
    top: 0; left: 50%; right: auto; bottom: auto;
    transform: translateX(-50%);
    width: 60px; height: 3px;
    border-radius: 0 0 3px 3px;
  }
  .mou-entry-number { font-size: 3.8rem; }
  .mou-entry-name   { font-size: 1.4rem; }
  .mou-img-badge,
  .mou-entry.reverse .mou-img-badge { left: 16px; right: auto; }
  .mou-hero-stats { max-width: 100%; }
  .mou-hero-stat  { min-width: 110px; padding: 16px 12px; }
  .mou-hero-stat-num { font-size: 2rem; }
}

@media (max-width: 540px) {
  .mou-hero { padding: 90px 20px 70px; }
  .mou-entry-img { height: 260px; min-height: 260px !important; }
  .mou-entry-content { padding: 36px 20px; }
  .mou-entry-number { font-size: 3rem; }
  .mou-cta { padding: 80px 20px; }
  .mou-cta-btn { padding: 16px 32px; font-size: .85rem; }
}
</style>

<div class="mou-page">

<!-- ═══════════ HERO ═══════════ -->
<section class="mou-hero">
  <div class="mou-hero-ring"></div>
  <div class="mou-hero-circle-tl"></div>
  <div class="mou-hero-circle-br"></div>
  <div class="mou-hero-inner">
    <div class="mou-hero-eyebrow">Almarah Foundation</div>
    <h1>Memorandum of <em>Understanding</em></h1>
    <p class="mou-hero-sub">
      Almarah Foundation actively collaborates with leading organisations through Memorandums of Understanding (MoUs) to enhance community development, education, healthcare, and social welfare — each partnership a shared commitment to lasting, meaningful change across Pakistan.
    </p>
    <div class="mou-hero-badges">
      <div class="mou-hero-badge gold">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        18 Active Partners
      </div>
      <div class="mou-hero-badge">
        <svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        Healthcare &amp; Education
      </div>
      <div class="mou-hero-badge">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        Community Impact
      </div>
    </div>
</section>

<!-- SECTION INTRO -->
<div class="mou-section-intro mou-animate">
  <div class="mou-section-tag">Our Partners</div>
  <h2 class="mou-section-title">Building a Network of <span class="accent">Shared Purpose</span></h2>
  <p class="mou-section-desc">Each MoU represents a commitment between Almarah Foundation and a partner organisation to work together for the welfare of orphans, underprivileged children, and vulnerable communities across Pakistan.</p>
</div>

<!-- ═══════════ ENTRIES ═══════════ -->
<div class="mou-entries">

  <!-- 01 FCCU -->
  <div class="mou-entry">
    <div class="mou-entry-img mou-animate-left">
      <img src="https://almarah.org/wp-content/uploads/2026/04/Forman-Christian-College.jpeg" alt="Forman Christian College FCCU" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-right">
      <div class="mou-entry-number">01</div>
      <div class="mou-entry-category">Clinical Research Partnership</div>
      <h3 class="mou-entry-name">Forman Christian College (FCCU)</h3>
      <p class="mou-entry-subtitle">Almarah Foundation and FCCU Partner for Clinical Excellence and Research</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — In June 2025, Almarah Foundation signed a Memorandum of Understanding (MoU) with Forman Christian College (FCCU). This partnership provides specialized internship opportunities for undergraduate students, supervised clinical placements, collaborative research initiatives, and service-based projects aimed at data collection and professional training.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Bridges academic theory with clinical practice, cultivating a generation of socially conscious professionals equipped to serve marginalised communities with evidence-based insights and academic rigour.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        June 2025
      </div>
    </div>
  </div>

  <!-- 02 UNIVERSITY OF LAHORE -->
  <div class="mou-entry reverse">
    <div class="mou-entry-img mou-animate-right">
      <img src="https://almarah.org/wp-content/uploads/2026/04/University-of-Lahore.jpeg" alt="University of Lahore" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-left">
      <div class="mou-entry-number">02</div>
      <div class="mou-entry-category">Academic Partnership</div>
      <h3 class="mou-entry-name">University of Lahore</h3>
      <p class="mou-entry-subtitle">Almarah Foundation and University of Lahore to Launch Social Campaigns</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — In November 2025, Almarah Foundation and the University of Lahore signed an MoU to establish an <strong>"Almarah Society."</strong> The collaboration will focus on monthly seminars, humanitarian student-led initiatives, and joint social media campaigns to promote community outreach.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Investing in the next generation of social leaders by turning the campus into a hub for activism — applying academic knowledge to real-world humanitarian challenges and amplifying the foundation's message through digital advocacy.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        November 2025
      </div>
    </div>
  </div>

  <!-- 03 CHILDREN HOSPITAL -->
  <div class="mou-entry">
    <div class="mou-entry-img mou-animate-left">
      <img src="https://almarah.org/wp-content/uploads/2026/04/Children_s-Hospital.jpeg" alt="Children Hospital Lahore" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-right">
      <div class="mou-entry-number">03</div>
      <div class="mou-entry-category">Pediatric Healthcare Partnership</div>
      <h3 class="mou-entry-name">University of Children Health Sciences, Children Hospital</h3>
      <p class="mou-entry-subtitle">Almarah Foundation Partners with Children Hospital Lahore</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — In March 2025, Almarah Foundation signed a significant MoU with the University of Children Health Sciences and Children Hospital. This collaboration ensures the provision of healthcare services for children, including free-of-cost or discounted treatments, and joint participation in medical camps and awareness sessions.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Ensures that even the most complex medical cases receive expert attention regardless of the family's financial background, through specialist pediatric care linked directly to the foundation's outreach programmes.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        March 2025
      </div>
    </div>
  </div>

  <!-- 04 CHEEZIOUS -->
  <div class="mou-entry reverse">
    <div class="mou-entry-img mou-animate-right">
      <img src="https://almarah.org/wp-content/uploads/2026/04/Cheezious.jpeg" alt="Cheezious Pakistan" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-left">
      <div class="mou-entry-number">04</div>
      <div class="mou-entry-category">Corporate Social Responsibility</div>
      <h3 class="mou-entry-name">Cheezious Pakistan</h3>
      <p class="mou-entry-subtitle">Almarah Foundation and Cheezious Launch "Almarah Go's Together" Campaign</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — In November 2025, Almarah Foundation entered into a partnership with Cheezious. The collaboration, titled <strong>"Cheezious and Almarah Go's Together,"</strong> focuses on mutual branding, promotional exposure, and corporate social responsibility to raise awareness for the foundation's causes.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Utilises the wide reach of a popular brand to bring the foundation's mission to the mainstream public, highlighting how corporate success and social responsibility can work hand-in-hand to support the community.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        November 2025
      </div>
    </div>
  </div>

  <!-- 05 PUNJAB POLICE -->
  <div class="mou-entry">
    <div class="mou-entry-img mou-animate-left">
      <img src="https://almarah.org/wp-content/uploads/2026/04/Punjab-Police.jpeg" alt="Punjab Police" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-right">
      <div class="mou-entry-number">05</div>
      <div class="mou-entry-category">Security &amp; Welfare Partnership</div>
      <h3 class="mou-entry-name">Punjab Police</h3>
      <p class="mou-entry-subtitle">Punjab Police and Almarah Foundation Collaborate for the "Compassionate Haven"</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — In December 2025, Almarah Foundation signed an MoU with the Punjab Police. This partnership ensures the provision of security arrangements and administrative aid by the Punjab Police, along with discounts for treatments at the "Compassionate Haven."</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Underscores the importance of public-private partnerships in maintaining social safety nets — creating a secure sanctuary where residents can focus entirely on their recovery and growth.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        December 2025
      </div>
    </div>
  </div>

  <!-- 06 MSN & CO (photo added) -->
  <div class="mou-entry reverse">
    <div class="mou-entry-img mou-animate-right">
      <img src="https://almarah.org/wp-content/uploads/2026/05/mns-co-mou.jpg" alt="MSN &amp; Co" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-left">
      <div class="mou-entry-number">06</div>
      <div class="mou-entry-category">Legal Support Partnership</div>
      <h3 class="mou-entry-name">MSN &amp; Co.</h3>
      <p class="mou-entry-subtitle">Almarah Foundation Partners with MSN &amp; Co for Comprehensive Legal Support</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — On August 1, 2024, Almarah Foundation officially signed a Memorandum of Understanding (MoU) with MSN &amp; Co, a prominent law firm based in Lahore. Under this agreement, MSN &amp; Co will provide essential legal services including legal representation, professional consultation, and formal documentation to settle various legal matters.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Ensures that the foundation's mission to serve orphans and homeless children is supported by a strong legal framework, allowing leadership to focus entirely on its core humanitarian goals and beneficiary welfare.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        August 2024
      </div>
    </div>
  </div>

  <!-- 07 GALLERIA DESIGNS -->
  <div class="mou-entry">
    <div class="mou-entry-img mou-animate-left">
      <img src="https://almarah.org/wp-content/uploads/2026/04/GD-Architectural-Firm.jpeg" alt="Galleria Designs" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-right">
      <div class="mou-entry-number">07</div>
      <div class="mou-entry-category">Infrastructure Partnership</div>
      <h3 class="mou-entry-name">Galleria Designs</h3>
      <p class="mou-entry-subtitle">Galleria Designs to Provide Architectural Excellence to Almarah Foundation</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — Almarah Foundation signed an MoU with Galleria Designs in October 2025. Galleria Designs has committed to preparing free-of-cost architectural designs, drawings, layouts, interior designs, and comprehensive project plans for the foundation's infrastructure projects.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Brings professional aesthetic and functional expertise to the foundation's physical spaces — ensuring facilities are safe, efficient, and dignified environments for all beneficiaries.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        October 2025
      </div>
    </div>
  </div>

  <!-- 08 UMT (photo updated, fallback kept) -->
  <div class="mou-entry reverse">
    <div class="mou-entry-img mou-animate-right">
      <img src="https://almarah.org/wp-content/uploads/2026/05/umt.jpeg" alt="University of Management and Technology UMT" loading="lazy"
        onerror="this.style.display='none';this.parentElement.querySelector('.mou-img-placeholder').style.display='flex';">
      <div class="mou-img-placeholder" style="display:none;">
        <div class="mou-img-placeholder-icon">
          <svg viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
        </div>
        <span class="mou-img-placeholder-text">UMT</span>
      </div>
    </div>
    <div class="mou-entry-content mou-animate-left">
      <div class="mou-entry-number">08</div>
      <div class="mou-entry-category">Academic Partnership</div>
      <h3 class="mou-entry-name">University of Management and Technology (UMT)</h3>
      <p class="mou-entry-subtitle">Almarah Foundation and UMT Partner for Sustainable Social Impact</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — In January 2026, Almarah Foundation signed an MoU with the University of Management and Technology (UMT). This partnership aims to enhance practical learning for students through social welfare programs and community development projects, utilizing institutional resources for sustainable impact.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">UMT students gain invaluable hands-on experience in social management while the foundation benefits from the university's research capabilities, ensuring social projects are data-driven and built for long-term sustainability.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        January 2026
      </div>
    </div>
  </div>

  <!-- 09 AL-MUSTAFA EYE TRUST -->
  <div class="mou-entry">
    <div class="mou-entry-img mou-animate-left">
      <img src="https://almarah.org/wp-content/uploads/2026/05/Al-Mustafa-Eye-Trust.jpg" alt="Al-Mustafa Eye Hospital" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-right">
      <div class="mou-entry-number">09</div>
      <div class="mou-entry-category">Healthcare Partnership</div>
      <h3 class="mou-entry-name">Al-Mustafa Eye Hospital</h3>
      <p class="mou-entry-subtitle">Almarah Foundation Partners with Al-Mustafa Eye Hospital for Vision Care</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — In February 2024, Almarah Foundation officially signed a Memorandum of Understanding (MoU) with Al-Mustafa Eye Hospital. This collaboration focuses on providing free-of-cost eye checkups and treatments specifically for the children and employees of the Almarah Foundation, ensuring essential vision care is accessible to all.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Addresses a critical gap in preventative healthcare for underprivileged youth — integrating regular screenings to eliminate preventable blindness and ensure every child has the visual clarity needed to succeed.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        February 2024
      </div>
    </div>
  </div>

  <!-- 10 PROVIDENTIA BOOKS -->
  <div class="mou-entry reverse">
    <div class="mou-entry-img mou-animate-right">
      <img src="https://almarah.org/wp-content/uploads/2026/04/Providentia-Book-Foundation.jpeg" alt="Providentia Books Foundation" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-left">
      <div class="mou-entry-number">10</div>
      <div class="mou-entry-category">Literacy &amp; Education Partnership</div>
      <h3 class="mou-entry-name">Providentia Books Foundation</h3>
      <p class="mou-entry-subtitle">Almarah Foundation and Providentia Books Foundation Promote Literacy and Education</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — In May 2025, Almarah Foundation signed an MoU with Providentia Books Foundation. This initiative focuses on the promotion of education through the provision of free books to students in need and joint participation in various educational initiatives and literacy drives.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Built on the belief that literacy is the strongest tool for social mobility — empowering young learners to take ownership of their education and fostering a lifelong love for reading and critical thinking.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        May 2025
      </div>
    </div>
  </div>

  <!-- 11 ESSA LABORATORY (photo added) -->
  <div class="mou-entry">
    <div class="mou-entry-img mou-animate-left">
      <img src="https://almarah.org/wp-content/uploads/2026/05/essa-lab.jpeg" alt="Essa Laboratory" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-right">
      <div class="mou-entry-number">11</div>
      <div class="mou-entry-category">Diagnostic Healthcare Partnership</div>
      <h3 class="mou-entry-name">Essa Laboratory</h3>
      <p class="mou-entry-subtitle">Almarah Foundation Partners with Essa Laboratory for Diagnostic Services</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — Almarah Foundation signed a Memorandum of Understanding (MoU) with Essa Laboratory, one of Pakistan's leading diagnostic services providers. This partnership is dedicated to ensuring that the children and beneficiaries of Almarah Foundation have access to high-quality diagnostic and laboratory services.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">By integrating professional diagnostic support into the foundation's welfare framework, this partnership ensures that early detection and timely medical intervention remain accessible to every child regardless of financial means.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Partnership Active
      </div>
    </div>
  </div>

  <!-- 12 BACHAA PARTY -->
  <div class="mou-entry reverse">
    <div class="mou-entry-img mou-animate-right">
      <img src="https://almarah.org/wp-content/uploads/2026/05/Bachaa-Party-scaled.jpg" alt="Bachaa Party" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-left">
      <div class="mou-entry-number">12</div>
      <div class="mou-entry-category">Child Welfare Partnership</div>
      <h3 class="mou-entry-name">Bachaa Party</h3>
      <p class="mou-entry-subtitle">Almarah Foundation and Bachaa Party Join Forces for Child Welfare</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — Almarah Foundation signed an MoU with Bachaa Party in July 2024. Under this partnership, Bachaa Party committed to providing clothing, toys, and educational materials. The agreement also facilitates awareness sessions, seasonal donation drives, and encourages Bachaa Party employees to participate in volunteering efforts.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Beyond material support, this collaboration focuses on the emotional well-being of children — bringing joy and dignity through new apparel and play materials while bridging the gap between corporate professionals and social causes.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        July 2024
      </div>
    </div>
  </div>

  <!-- 13 BORDER & BRIDGES -->
  <div class="mou-entry">
    <div class="mou-entry-img mou-animate-left">
      <img src="https://almarah.org/wp-content/uploads/2026/05/Border-Bridges-scaled.jpg" alt="Border and Bridges" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-right">
      <div class="mou-entry-number">13</div>
      <div class="mou-entry-category">Global Education Partnership</div>
      <h3 class="mou-entry-name">Border &amp; Bridges</h3>
      <p class="mou-entry-subtitle">Almarah Foundation Announces Global Education Partnership with Border &amp; Bridges</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — In October 2024, Almarah Foundation entered into a formal agreement with Border &amp; Bridges. This partnership is designed to support educational expos and events, provide international guest speakers, and facilitate participation in global events through liaison with international universities.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Provides students and beneficiaries with a global perspective, breaking down geographical barriers to high-quality education and equipping the next generation with cross-cultural competencies for a modern workforce.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        October 2024
      </div>
    </div>
  </div>

  <!-- 14 FITWELL HUB -->
  <div class="mou-entry reverse">
    <div class="mou-entry-img mou-animate-right">
      <img src="https://almarah.org/wp-content/uploads/2026/04/Fitwell-Hub.jpeg" alt="Fitwell Hub" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-left">
      <div class="mou-entry-number">14</div>
      <div class="mou-entry-category">Wellness Partnership</div>
      <h3 class="mou-entry-name">Fitwell Hub</h3>
      <p class="mou-entry-subtitle">Fitwell Hub and Almarah Foundation Partner for Comprehensive Health Screenings</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — Almarah Foundation signed an MoU with Fitwell Hub in October 2024. The collaboration aims to improve health outcomes through free-of-cost initial health screenings, training and capacity building for staff, maintenance of digital health records, and expert nutritional guidance.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Recognising that health is the foundation of all progress, this partnership focuses on proactive and preventative wellness — ensuring medical issues are identified early and managed professionally throughout the community.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        October 2024
      </div>
    </div>
  </div>

  <!-- 15 THE PUNJAB SCHOOL -->
  <div class="mou-entry">
    <div class="mou-entry-img mou-animate-left">
      <img src="https://almarah.org/wp-content/uploads/2026/04/Punjab-School.jpeg" alt="The Punjab School" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-right">
      <div class="mou-entry-number">15</div>
      <div class="mou-entry-category">Civic Engagement Partnership</div>
      <h3 class="mou-entry-name">The Punjab School</h3>
      <p class="mou-entry-subtitle">Almarah Foundation and The Punjab School Promote Civic Engagement and Shared Learning</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — Almarah Foundation signed an MoU with The Punjab School in November 2025. The agreement is designed to facilitate awareness sessions, teacher-student workshops, and civic engagement initiatives through a mutual exchange of knowledge, expertise, and educational resources.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Focuses on institutional synergy to enhance the educational landscape of Lahore, instilling civic duty and social responsibility in students from a young age through joint workshops that equip the next generation with modern tools and empathy.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        November 2025
      </div>
    </div>
  </div>

  <!-- 16 AL-BARKAT FOUNDATION -->
  <div class="mou-entry reverse">
    <div class="mou-entry-img mou-animate-right">
      <img src="https://almarah.org/wp-content/uploads/2026/05/Al-Barkat-Foundation-scaled.jpg" alt="Al-Barkat Foundation" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-left">
      <div class="mou-entry-number">16</div>
      <div class="mou-entry-category">Community Healthcare Partnership</div>
      <h3 class="mou-entry-name">Al-Barkat Foundation</h3>
      <p class="mou-entry-subtitle">Almarah Foundation and Al-Barkat Foundation Join Forces for Community Healthcare</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — In May 2025, Almarah Foundation formalized a partnership with Al-Barkat Foundation. The two organizations have committed to working together to organize free medical camps and collaborate on various healthcare-related activities to serve marginalized populations in Lahore.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Leverages the shared expertise of two mission-driven organizations to reach the most underserved areas — bringing life-saving healthcare directly to those who cannot afford traditional hospital visits.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        May 2025
      </div>
    </div>
  </div>

  <!-- 17 PAFP (photo added) -->
  <div class="mou-entry">
    <div class="mou-entry-img mou-animate-left">
      <img src="https://almarah.org/wp-content/uploads/2026/05/FamilyCon.jpeg" alt="Pakistan Academy of Family Physicians" loading="lazy">
    </div>
    <div class="mou-entry-content mou-animate-right">
      <div class="mou-entry-number">17</div>
      <div class="mou-entry-category">Medical Association Partnership</div>
      <h3 class="mou-entry-name">Pakistan Academy of Family Physicians (PAFP)</h3>
      <p class="mou-entry-subtitle">Almarah Foundation and Pakistan Academy of Family Physicians Join Hands for Orphan Care</p>
      <div class="mou-divider"></div>
      <p class="mou-entry-body"><strong>LAHORE</strong> — Almarah Foundation is pleased to announce a strategic collaboration with the Pakistan Academy of Family Physicians (PAFP). This partnership, inaugurated by PAFP President <strong>Dr. Tariq Mahmood Mian</strong> and Almarah Foundation Chairperson <strong>Mrs. Sophiya Warraich</strong>, is dedicated to supporting orphan children through a holistic approach to care and community-driven initiatives.</p>
      <div class="mou-vision-box">
        <div class="mou-vision-label">Vision &amp; Impact</div>
        <p class="mou-vision-text">Combines PAFP's medical expertise with the foundation's established welfare programs to provide orphan children with comprehensive care, vital educational assistance, and the compassion and opportunities necessary to thrive.</p>
      </div>
      <div class="mou-date-chip">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        Partnership Active
      </div>
    </div>
  </div>
  
  <!-- 18 Khawaja Junaid Hockey Academy (Sports Initiative with Dutch Ambassador) - Reverse Layout -->
<div class="mou-entry reverse">
  <div class="mou-entry-img mou-animate-right">
    <img src="https://almarah.org/wp-content/uploads/2026/06/khawaja-junaid-hockey-academy.JPG-scaled.jpeg" alt="Khawaja Junaid Hockey Academy partnership signing with Dutch Ambassador" loading="lazy">
  </div>
  <div class="mou-entry-content mou-animate-left">
    <div class="mou-entry-number">18</div>
    <div class="mou-entry-category">Sports Partnership &amp; Youth Empowerment</div>
    <h3 class="mou-entry-name">Khawaja Junaid Hockey Academy (KJHA)</h3>
    <p class="mou-entry-subtitle">Almarah Foundation and Khawaja Junaid Hockey Academy Launch Sports Initiative with Dutch Ambassador</p>
    <div class="mou-divider"></div>
    <p class="mou-entry-body"><strong>LAHORE — On June 29, 2023</strong>, Almarah Foundation officially signed a Memorandum of Understanding (MoU) with the Khawaja Junaid Hockey Academy (KJHA). The ceremony was notably attended by <strong>Ambassador Henny de Vries</strong>, who joined to celebrate this monumental partnership aimed at providing professional hockey training and skill development to the children residing at the Almarah Foundation.</p>
    <p class="mou-entry-body">The primary purpose of this partnership is to open new athletic pathways for youth, with the academy providing trained hockey coaches to deliver specialized coaching services. The initiative proudly emphasizes inclusivity, making high-standard training accessible to all children, including young girls.</p>
    <div class="mou-vision-box">
      <div class="mou-vision-label">Vision &amp; Impact</div>
      <p class="mou-vision-text">Highlighting the deep-rooted hockey legacy shared by Pakistan and the Netherlands—both nations boasting remarkable international success with World Cup titles and Olympic gold medals—Ambassador Henny de Vries emphasized the immense value of sports diplomacy. By using sport as a tool for connection, this collaboration fosters cultural exchange, builds strong community relationships, and uses the game of hockey to unite people while empowering vulnerable youth with discipline, health, and athletic ambition.</p>
    </div>
    <div class="mou-date-chip">
      <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      June 29, 2023
    </div>
  </div>
</div>

</div><!-- end mou-entries -->

<!-- ═══════════ CTA ═══════════ -->
<section class="mou-cta">
  <div class="mou-cta-circle1"></div>
  <div class="mou-cta-circle2"></div>
  <div class="mou-cta-inner mou-animate">
    <div class="mou-cta-tag">Collaborate With Us</div>
    <h2 class="mou-cta-title">Partner With <em>Almarah Foundation</em></h2>
    <p class="mou-cta-desc">Join hands with us to create meaningful, lasting impact. Whether you are a hospital, university, business, or NGO — there is a place for your organisation in our growing family of partners.</p>
    <a href="https://almarah.org/contact/" class="mou-cta-btn">
      Contact Us to Discuss a Partnership
      <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  </div>
</section>

</div><!-- end mou-page -->

<script>
(function(){
  /* ── Intersection Observer for scroll animations ── */
  const animEls = document.querySelectorAll(
    '.mou-animate, .mou-animate-left, .mou-animate-right, .mou-animate-scale'
  );
  if('IntersectionObserver' in window){
    const obs = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{
        if(e.isIntersecting){
          e.target.classList.add('mou-visible');
          obs.unobserve(e.target);
        }
      });
    },{ threshold: 0.12, rootMargin:'0px 0px -60px 0px' });
    animEls.forEach(el => obs.observe(el));
  } else {
    animEls.forEach(el => el.classList.add('mou-visible'));
  }

  /* ── Animated number counter for hero stats ── */
  function animateCounter(el, target, duration){
    const suffix = el.querySelector('.sup');
    const suffixText = suffix ? suffix.outerHTML : '';
    let start = 0;
    const step = Math.ceil(target / (duration / 16));
    const timer = setInterval(()=>{
      start += step;
      if(start >= target){
        start = target;
        clearInterval(timer);
      }
      el.innerHTML = start + suffixText;
    }, 16);
  }

  const statsBar = document.querySelector('.mou-hero-stats');
  if(statsBar && 'IntersectionObserver' in window){
    let counted = false;
    const statsObs = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{
        if(e.isIntersecting && !counted){
          counted = true;
          document.querySelectorAll('.mou-hero-stat-num[data-target]').forEach((el,i)=>{
            const target = parseInt(el.getAttribute('data-target'));
            setTimeout(()=> animateCounter(el, target, 1200), i * 150);
          });
        }
      });
    },{ threshold: 0.5 });
    statsObs.observe(statsBar);
  } else {
    /* Fallback: just show numbers */
    document.querySelectorAll('.mou-hero-stat-num[data-target]').forEach(el=>{
      const t = el.getAttribute('data-target');
      const sup = el.querySelector('.sup').outerHTML;
      el.innerHTML = t + sup;
    });
  }
})();
</script>