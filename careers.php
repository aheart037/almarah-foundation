<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">

<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
  --pink:         #A92D63;
  --pink-dark:    #8A2050;
  --pink-mid:     #C4386F;
  --yellow:       #F2C200;
  --yellow-light: #FFD740;
  --white:        #FFFFFF;
  --off-white:    #FDF5F8;
  --text-dark:    #1A0810;
  --text-mid:     #5A2040;
  --text-light:   #9A6080;
}

html { scroll-behavior: smooth; }

.cr-page {
  font-family: 'Manrope', sans-serif;
  background: var(--off-white);
  color: var(--text-dark);
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
}

/* ═══════════════════
   ANIMATIONS
═══════════════════ */
@keyframes cr-fadeUp {
  from { opacity: 0; transform: translateY(40px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes cr-fadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}
@keyframes cr-rotateSlow {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
@keyframes cr-float {
  0%, 100% { transform: translateY(0px); }
  50%       { transform: translateY(-10px); }
}
@keyframes cr-pulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50%       { transform: scale(0.92); opacity: .7; }
}
@keyframes cr-shimmer-line {
  0%   { background-position: -400px 0; }
  100% { background-position: 400px 0; }
}

.cr-animate {
  opacity: 0;
  transform: translateY(36px);
  transition: opacity .7s ease, transform .7s ease;
}
.cr-animate.cr-visible { opacity: 1; transform: translateY(0); }

.cr-animate-left {
  opacity: 0;
  transform: translateX(-50px);
  transition: opacity .75s ease, transform .75s ease;
}
.cr-animate-left.cr-visible { opacity: 1; transform: translateX(0); }

.cr-animate-right {
  opacity: 0;
  transform: translateX(50px);
  transition: opacity .75s ease, transform .75s ease;
}
.cr-animate-right.cr-visible { opacity: 1; transform: translateX(0); }

.cr-animate-scale {
  opacity: 0;
  transform: scale(0.88);
  transition: opacity .6s ease, transform .6s cubic-bezier(.22,.9,.36,1);
}
.cr-animate-scale.cr-visible { opacity: 1; transform: scale(1); }

.cr-delay-1 { transition-delay: .1s !important; }
.cr-delay-2 { transition-delay: .2s !important; }
.cr-delay-3 { transition-delay: .3s !important; }
.cr-delay-4 { transition-delay: .4s !important; }
.cr-delay-5 { transition-delay: .5s !important; }

/* ═══════════════════
   HERO
═══════════════════ */
.cr-hero {
  background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 30%, var(--pink) 65%, var(--pink-mid) 100%);
  padding: 120px 24px 110px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
@media (min-width: 768px) { .cr-hero { padding: 140px 40px 120px; } }

.cr-hero::before {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 70% 60% at 10% 60%, rgba(242,194,0,.18) 0%, transparent 60%),
    radial-gradient(ellipse 50% 50% at 90% 15%, rgba(255,255,255,.09) 0%, transparent 55%),
    radial-gradient(ellipse 40% 40% at 50% 100%, rgba(0,0,0,.14) 0%, transparent 60%);
  pointer-events: none;
}

.cr-hero-ring {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 900px; height: 900px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,.04);
  animation: cr-rotateSlow 45s linear infinite;
  pointer-events: none;
}
.cr-hero-ring::before {
  content: '';
  position: absolute;
  top: 8%; left: 0;
  width: 10px; height: 10px;
  border-radius: 50%;
  background: rgba(242,194,0,.6);
  box-shadow: 0 0 18px rgba(242,194,0,.5);
}

.cr-hero-circle-tl {
  position: absolute;
  top: -120px; left: -120px;
  width: 420px; height: 420px;
  border-radius: 50%;
  border: 60px solid rgba(255,255,255,.04);
  pointer-events: none;
  animation: cr-fadeIn 1.5s ease both;
}
.cr-hero-circle-br {
  position: absolute;
  bottom: -100px; right: -180px;
  width: 560px; height: 560px;
  border-radius: 50%;
  border: 75px solid rgba(242,194,0,.05);
  pointer-events: none;
  animation: cr-fadeIn 1.5s ease .3s both;
}

.cr-hero-inner {
  position: relative;
  z-index: 2;
  max-width: 780px;
  margin: 0 auto;
}

/* Icon */
.cr-hero-icon-wrap {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 30px;
  animation: cr-fadeUp .8s ease .1s both;
}
.cr-hero-icon-bg {
  width: 88px; height: 88px;
  border-radius: 28px;
  background: rgba(255,255,255,.12);
  border: 1.5px solid rgba(255,255,255,.20);
  backdrop-filter: blur(12px);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: cr-float 4s ease-in-out infinite;
  box-shadow: 0 20px 60px rgba(0,0,0,.20);
}
.cr-hero-icon-bg svg {
  width: 40px; height: 40px;
  stroke: var(--yellow);
  fill: none;
  stroke-width: 1.5;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.cr-hero-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: .62rem;
  font-weight: 700;
  letter-spacing: 5px;
  text-transform: uppercase;
  color: var(--yellow-light);
  margin-bottom: 18px;
  animation: cr-fadeUp .8s ease .2s both;
}
.cr-hero-eyebrow::before,
.cr-hero-eyebrow::after {
  content: '';
  display: block;
  width: 28px; height: 1.5px;
  background: var(--yellow);
  opacity: .6;
}

.cr-hero h1 {
  font-weight: 800;
  font-size: clamp(2.2rem, 7vw, 4.2rem);
  color: var(--white);
  letter-spacing: -2px;
  line-height: 1.05;
  text-shadow: 0 4px 40px rgba(0,0,0,.22);
  margin-bottom: 20px;
  animation: cr-fadeUp .8s ease .3s both;
}
.cr-hero h1 em {
  font-style: normal;
  color: var(--yellow);
  filter: drop-shadow(0 0 24px rgba(242,194,0,.30));
}

.cr-hero-sub {
  font-size: .93rem;
  font-family: 'Open Sans', sans-serif;
  color: rgba(255,255,255,.65);
  line-height: 1.85;
  max-width: 620px;
  margin: 0 auto 42px;
  animation: cr-fadeUp .8s ease .4s both;
}

.cr-hero-badges {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 12px;
  animation: cr-fadeUp .8s ease .5s both;
}
.cr-hero-badge {
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
.cr-hero-badge:hover {
  background: rgba(255,255,255,.18);
  transform: translateY(-2px);
}
.cr-hero-badge svg {
  width: 13px; height: 13px;
  stroke: currentColor; fill: none;
  stroke-width: 2;
  stroke-linecap: round; stroke-linejoin: round;
}
.cr-hero-badge.gold {
  background: rgba(242,194,0,.15);
  border-color: rgba(242,194,0,.35);
  color: var(--yellow-light);
}

/* ═══════════════════
   WHY JOIN US
═══════════════════ */
.cr-why {
  padding: 90px 24px 0;
  max-width: 1160px;
  margin: 0 auto;
}

.cr-section-header {
  text-align: center;
  margin-bottom: 64px;
}
.cr-section-tag {
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
.cr-section-tag::before,
.cr-section-tag::after {
  content: '';
  display: block;
  width: 18px; height: 1px;
  background: var(--pink);
  opacity: .45;
}
.cr-section-title {
  font-weight: 800;
  font-size: clamp(1.9rem, 4.5vw, 2.8rem);
  color: var(--text-dark);
  letter-spacing: -1.5px;
  line-height: 1.1;
  margin-bottom: 14px;
}
.cr-section-title .accent { color: var(--pink); }
.cr-section-desc {
  font-size: .92rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-light);
  line-height: 1.85;
  max-width: 580px;
  margin: 0 auto;
}

/* ── WHY CARDS ── */
.cr-why-cards {
  display: flex;
  flex-direction: column;
  gap: 0;
}

/* Each reason = full-width alternating row */
.cr-why-row {
  display: grid;
  grid-template-columns: 44% 56%;
  min-height: 480px;
  border-top: 1px solid rgba(169,45,99,.07);
}
.cr-why-row.reverse {
  grid-template-columns: 56% 44%;
}
.cr-why-row.reverse .cr-why-img     { order: 2; }
.cr-why-row.reverse .cr-why-content { order: 1; }

/* Image */
.cr-why-img {
  position: relative;
  overflow: hidden;
  background: #1a0810;
  min-height: 420px;
}
.cr-why-img img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center center;
  transition: transform .8s cubic-bezier(.22,.9,.36,1);
}
.cr-why-row:hover .cr-why-img img { transform: scale(1.04); }
.cr-why-img::after {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(to bottom, rgba(26,8,16,.0) 55%, rgba(26,8,16,.30) 100%);
  pointer-events: none;
  z-index: 1;
}

/* Number watermark on image */
.cr-why-num {
  position: absolute;
  bottom: 20px; right: 24px;
  z-index: 2;
  font-family: 'Manrope', sans-serif;
  font-size: 5rem;
  font-weight: 800;
  color: rgba(255,255,255,.12);
  line-height: 1;
  letter-spacing: -4px;
  user-select: none;
}

/* Content */
.cr-why-content {
  padding: 64px 60px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  background: var(--white);
  position: relative;
}
.cr-why-row.reverse .cr-why-content { background: var(--off-white); }

.cr-why-content::before {
  content: '';
  position: absolute;
  top: 50%; left: 0;
  transform: translateY(-50%);
  width: 3px; height: 64px;
  background: linear-gradient(180deg, var(--yellow), var(--pink));
  border-radius: 0 3px 3px 0;
  transition: height .4s ease;
}
.cr-why-row:hover .cr-why-content::before { height: 100px; }
.cr-why-row.reverse .cr-why-content::before {
  left: auto; right: 0;
  border-radius: 3px 0 0 3px;
}

.cr-why-eyebrow {
  font-size: .57rem;
  font-weight: 700;
  letter-spacing: 3.5px;
  text-transform: uppercase;
  color: var(--pink);
  margin-bottom: 10px;
  font-family: 'Open Sans', sans-serif;
}

.cr-why-number-bg {
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
.cr-why-row:hover .cr-why-number-bg { color: rgba(169,45,99,.13); }

.cr-why-title {
  font-size: clamp(1.5rem, 2.5vw, 2rem);
  font-weight: 800;
  color: var(--text-dark);
  line-height: 1.2;
  letter-spacing: -.5px;
  margin-bottom: 8px;
  font-family: 'Manrope', sans-serif;
  transition: color .3s;
}
.cr-why-row:hover .cr-why-title { color: var(--pink-dark); }

.cr-why-divider {
  width: 48px;
  height: 3px;
  background: linear-gradient(90deg, var(--yellow), var(--pink));
  border-radius: 2px;
  margin: 16px 0;
  transition: width .4s ease;
}
.cr-why-row:hover .cr-why-divider { width: 80px; }

.cr-why-desc {
  font-size: .9rem;
  font-family: 'Open Sans', sans-serif;
  color: #665060;
  line-height: 1.95;
}

/* ═══════════════════
   STATS STRIP
═══════════════════ */
.cr-stats-strip {
  background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 35%, var(--pink) 70%, var(--pink-mid) 100%);
  padding: 64px 24px;
  position: relative;
  overflow: hidden;
  margin-top: 80px;
}
.cr-stats-strip::before {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 60% 80% at 0% 50%, rgba(242,194,0,.12) 0%, transparent 55%),
    radial-gradient(ellipse 40% 60% at 100% 50%, rgba(255,255,255,.06) 0%, transparent 55%);
  pointer-events: none;
}
.cr-stats-inner {
  position: relative;
  z-index: 1;
  max-width: 1000px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0;
}
.cr-stat {
  text-align: center;
  padding: 24px 20px;
  border-right: 1px solid rgba(255,255,255,.10);
  cursor: default;
  transition: background .3s;
}
.cr-stat:last-child { border-right: none; }
.cr-stat:hover { background: rgba(255,255,255,.05); }
.cr-stat-num {
  font-family: 'Manrope', sans-serif;
  font-size: 2.8rem;
  font-weight: 800;
  color: var(--white);
  line-height: 1;
  letter-spacing: -2px;
  transition: transform .3s;
}
.cr-stat:hover .cr-stat-num { transform: scale(1.08); }
.cr-stat-num .sup {
  color: var(--yellow);
  font-size: 1.6rem;
  vertical-align: super;
  line-height: 0;
}
.cr-stat-label {
  font-size: .6rem;
  font-weight: 600;
  color: rgba(255,255,255,.55);
  letter-spacing: 1.5px;
  text-transform: uppercase;
  margin-top: 8px;
  font-family: 'Open Sans', sans-serif;
}

/* ═══════════════════
   APPLY SECTION
═══════════════════ */
.cr-apply {
  padding: 90px 24px 100px;
  max-width: 1160px;
  margin: 0 auto;
}

.cr-apply-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  align-items: center;
}

/* Left image */
.cr-apply-img-wrap {
  position: relative;
  border-radius: 24px;
  overflow: hidden;
  box-shadow: 0 24px 60px rgba(169,45,99,.15);
}
.cr-apply-img-wrap img {
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  transition: transform .8s ease;
  min-height: 420px;
}
.cr-apply-img-wrap:hover img { transform: scale(1.04); }

/* Pink overlay tag on image */
.cr-apply-img-tag {
  position: absolute;
  bottom: 24px; left: 24px;
  background: linear-gradient(135deg, var(--pink-dark), var(--pink));
  color: white;
  font-family: 'Manrope', sans-serif;
  font-size: .7rem;
  font-weight: 700;
  letter-spacing: 1px;
  padding: 10px 20px;
  border-radius: 100px;
  box-shadow: 0 6px 20px rgba(169,45,99,.35);
  display: flex;
  align-items: center;
  gap: 8px;
}
.cr-apply-img-tag svg {
  width: 14px; height: 14px;
  stroke: currentColor; fill: none;
  stroke-width: 2;
  stroke-linecap: round; stroke-linejoin: round;
}

/* Right content */
.cr-apply-content {}

.cr-apply-title {
  font-weight: 800;
  font-size: clamp(1.8rem, 3.5vw, 2.6rem);
  color: var(--text-dark);
  letter-spacing: -1.5px;
  line-height: 1.1;
  margin-bottom: 8px;
}
.cr-apply-title .accent { color: var(--pink); }

.cr-apply-subtitle {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--pink);
  margin-bottom: 18px;
  font-family: 'Open Sans', sans-serif;
  font-style: italic;
}

.cr-apply-divider {
  width: 48px;
  height: 3px;
  background: linear-gradient(90deg, var(--yellow), var(--pink));
  border-radius: 2px;
  margin-bottom: 22px;
}

.cr-apply-desc {
  font-size: .92rem;
  font-family: 'Open Sans', sans-serif;
  color: #665060;
  line-height: 1.95;
  margin-bottom: 32px;
}

/* Roles list */
.cr-roles-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 36px;
}
.cr-role-chip {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: rgba(169,45,99,.07);
  border: 1px solid rgba(169,45,99,.14);
  color: var(--pink);
  font-family: 'Manrope', sans-serif;
  font-size: .72rem;
  font-weight: 700;
  padding: 8px 16px;
  border-radius: 100px;
  transition: all .3s;
}
.cr-role-chip:hover {
  background: var(--pink);
  border-color: var(--pink);
  color: white;
  transform: translateY(-2px);
}
.cr-role-chip svg {
  width: 12px; height: 12px;
  stroke: currentColor; fill: none;
  stroke-width: 2;
  stroke-linecap: round; stroke-linejoin: round;
}

/* Email CTA button */
.cr-apply-btn {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  background: linear-gradient(135deg, var(--pink-dark), var(--pink));
  color: white;
  font-family: 'Manrope', sans-serif;
  font-size: .95rem;
  font-weight: 800;
  padding: 18px 40px;
  border-radius: 100px;
  text-decoration: none;
  letter-spacing: .3px;
  transition: all .35s ease;
  box-shadow: 0 8px 28px rgba(169,45,99,.30);
  position: relative;
  overflow: hidden;
  margin-bottom: 16px;
}
.cr-apply-btn::before {
  content: '';
  position: absolute;
  top: 0; left: -100%;
  width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.20), transparent);
  transition: left .5s ease;
}
.cr-apply-btn:hover::before { left: 100%; }
.cr-apply-btn svg {
  width: 18px; height: 18px;
  stroke: currentColor; fill: none;
  stroke-width: 2;
  stroke-linecap: round; stroke-linejoin: round;
  transition: transform .3s;
}
.cr-apply-btn:hover {
  background: linear-gradient(135deg, #6B0F35, var(--pink-dark));
  transform: translateY(-3px);
  box-shadow: 0 16px 42px rgba(169,45,99,.38);
  text-decoration: none;
  color: white;
}
.cr-apply-btn:hover svg { transform: translateX(4px); }

.cr-apply-email-note {
  font-size: .78rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-light);
  display: flex;
  align-items: center;
  gap: 7px;
}
.cr-apply-email-note svg {
  width: 14px; height: 14px;
  stroke: var(--pink); fill: none;
  stroke-width: 2;
  stroke-linecap: round; stroke-linejoin: round;
  flex-shrink: 0;
}
.cr-apply-email-note a {
  color: var(--pink);
  font-weight: 700;
  text-decoration: none;
}
.cr-apply-email-note a:hover { text-decoration: underline; }

/* ═══════════════════
   VALUES SECTION
═══════════════════ */
.cr-values {
  background: var(--white);
  padding: 80px 24px;
  border-top: 1px solid rgba(169,45,99,.07);
  border-bottom: 1px solid rgba(169,45,99,.07);
}
.cr-values-inner {
  max-width: 1160px;
  margin: 0 auto;
}
.cr-values-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
  margin-top: 56px;
}
.cr-value-card {
  background: var(--off-white);
  border-radius: 20px;
  padding: 32px 26px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  position: relative;
  overflow: hidden;
  transition: transform .35s cubic-bezier(.22,.9,.36,1), box-shadow .35s;
  border: 1px solid rgba(169,45,99,.07);
}
.cr-value-card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--yellow), var(--pink));
  border-radius: 20px 20px 0 0;
  transform: scaleX(0);
  transform-origin: left;
  transition: transform .4s ease;
}
.cr-value-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 48px rgba(169,45,99,.12);
}
.cr-value-card:hover::before { transform: scaleX(1); }
.cr-value-icon {
  width: 50px; height: 50px;
  border-radius: 14px;
  background: rgba(169,45,99,.08);
  border: 1.5px solid rgba(169,45,99,.12);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .3s;
}
.cr-value-icon svg {
  width: 22px; height: 22px;
  stroke: var(--pink); fill: none;
  stroke-width: 2;
  stroke-linecap: round; stroke-linejoin: round;
  transition: stroke .3s;
}
.cr-value-card:hover .cr-value-icon {
  background: linear-gradient(135deg, var(--pink-dark), var(--pink));
  border-color: transparent;
}
.cr-value-card:hover .cr-value-icon svg { stroke: white; }
.cr-value-title {
  font-size: .98rem;
  font-weight: 800;
  color: var(--text-dark);
  letter-spacing: -.3px;
}
.cr-value-desc {
  font-size: .82rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-light);
  line-height: 1.8;
}

/* ═══════════════════
   PROCESS SECTION
═══════════════════ */
.cr-process {
  padding: 90px 24px;
  max-width: 1000px;
  margin: 0 auto;
}
.cr-process-steps {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 32px;
  margin-top: 56px;
  position: relative;
}
/* Connector line */
.cr-process-steps::before {
  content: '';
  position: absolute;
  top: 36px; left: calc(16.6% + 16px); right: calc(16.6% + 16px);
  height: 2px;
  background: linear-gradient(90deg, var(--yellow), var(--pink), var(--yellow));
  background-size: 200% 100%;
  z-index: 0;
}
.cr-process-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 16px;
  position: relative;
  z-index: 1;
}
.cr-process-step-num {
  width: 72px; height: 72px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--pink-dark), var(--pink));
  color: white;
  font-family: 'Manrope', sans-serif;
  font-size: 1.4rem;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 8px 24px rgba(169,45,99,.30);
  transition: transform .3s ease, box-shadow .3s;
  flex-shrink: 0;
}
.cr-process-step:hover .cr-process-step-num {
  transform: scale(1.1);
  box-shadow: 0 12px 32px rgba(169,45,99,.40);
}
.cr-process-step-title {
  font-size: 1rem;
  font-weight: 800;
  color: var(--text-dark);
  letter-spacing: -.3px;
}
.cr-process-step-desc {
  font-size: .82rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-light);
  line-height: 1.8;
}

/* ═══════════════════
   CTA BOTTOM
═══════════════════ */
.cr-cta {
  background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 35%, var(--pink) 70%, var(--pink-mid) 100%);
  padding: 100px 24px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.cr-cta::before {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 60% 70% at 10% 50%, rgba(242,194,0,.14) 0%, transparent 60%),
    radial-gradient(ellipse 50% 50% at 90% 20%, rgba(255,255,255,.07) 0%, transparent 55%);
  pointer-events: none;
}
.cr-cta-c1 {
  position: absolute;
  top: -80px; right: -80px;
  width: 300px; height: 300px;
  border-radius: 50%;
  border: 50px solid rgba(242,194,0,.06);
  pointer-events: none;
  animation: cr-rotateSlow 30s linear infinite;
}
.cr-cta-c2 {
  position: absolute;
  bottom: -60px; left: -60px;
  width: 240px; height: 240px;
  border-radius: 50%;
  border: 40px solid rgba(255,255,255,.04);
  pointer-events: none;
  animation: cr-rotateSlow 25s linear infinite reverse;
}
.cr-cta-inner {
  position: relative;
  z-index: 2;
  max-width: 640px;
  margin: 0 auto;
}
.cr-cta-tag {
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
.cr-cta-tag::before,
.cr-cta-tag::after {
  content: '';
  display: block;
  width: 18px; height: 1px;
  background: var(--yellow);
  opacity: .6;
}
.cr-cta-title {
  font-weight: 800;
  font-size: clamp(2rem, 5vw, 3.2rem);
  color: var(--white);
  letter-spacing: -2px;
  line-height: 1.1;
  margin-bottom: 16px;
}
.cr-cta-title em {
  font-style: normal;
  color: var(--yellow);
}
.cr-cta-desc {
  font-size: .92rem;
  font-family: 'Open Sans', sans-serif;
  color: rgba(255,255,255,.70);
  line-height: 1.85;
  margin-bottom: 40px;
}
.cr-cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: var(--yellow);
  color: #1a0810;
  font-family: 'Manrope', sans-serif;
  font-size: .95rem;
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
.cr-cta-btn::before {
  content: '';
  position: absolute;
  top: 0; left: -100%;
  width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.25), transparent);
  transition: left .5s ease;
}
.cr-cta-btn:hover::before { left: 100%; }
.cr-cta-btn svg {
  width: 16px; height: 16px;
  stroke: currentColor; fill: none;
  stroke-width: 2.5;
  stroke-linecap: round; stroke-linejoin: round;
  transition: transform .3s;
}
.cr-cta-btn:hover {
  background: var(--white);
  color: var(--pink);
  transform: translateY(-3px);
  box-shadow: 0 18px 48px rgba(0,0,0,.32);
  text-decoration: none;
}
.cr-cta-btn:hover svg { transform: translateX(5px); }

/* ═══════════════════
   RESPONSIVE
═══════════════════ */
@media (max-width: 1024px) {
  .cr-values-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 900px) {
  .cr-why-row,
  .cr-why-row.reverse {
    grid-template-columns: 1fr !important;
  }
  .cr-why-row.reverse .cr-why-img     { order: 0; }
  .cr-why-row.reverse .cr-why-content { order: 1; }
  .cr-why-img { min-height: 300px !important; height: 300px; }
  .cr-why-content { padding: 44px 28px; }
  .cr-why-content::before,
  .cr-why-row.reverse .cr-why-content::before {
    top: 0; left: 50%; right: auto;
    transform: translateX(-50%);
    width: 60px; height: 3px;
    border-radius: 0 0 3px 3px;
  }
  .cr-apply-grid { grid-template-columns: 1fr; }
  .cr-apply-img-wrap { min-height: 320px; }
  .cr-stats-inner { grid-template-columns: repeat(2, 1fr); }
  .cr-stat { border-right: none; border-bottom: 1px solid rgba(255,255,255,.10); }
  .cr-stat:nth-child(odd) { border-right: 1px solid rgba(255,255,255,.10); }
  .cr-stat:last-child, .cr-stat:nth-last-child(2):nth-child(odd) { border-bottom: none; }
  .cr-process-steps { grid-template-columns: 1fr; }
  .cr-process-steps::before { display: none; }
}
@media (max-width: 640px) {
  .cr-hero { padding: 90px 20px 70px; }
  .cr-why-content { padding: 36px 20px; }
  .cr-values-grid { grid-template-columns: 1fr; }
  .cr-apply { padding: 60px 20px 80px; }
  .cr-cta { padding: 80px 20px; }
  .cr-cta-btn { padding: 16px 32px; font-size: .85rem; }
}
</style>

<div class="cr-page">

<!-- ═══════════ HERO ═══════════ -->
<section class="cr-hero">
  <div class="cr-hero-ring"></div>
  <div class="cr-hero-circle-tl"></div>
  <div class="cr-hero-circle-br"></div>
  <div class="cr-hero-inner">

    <div class="cr-hero-icon-wrap">
      <div class="cr-hero-icon-bg">
        <svg viewBox="0 0 24 24">
          <rect x="2" y="7" width="20" height="14" rx="2"/>
          <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
          <line x1="12" y1="12" x2="12" y2="16"/>
          <line x1="10" y1="14" x2="14" y2="14"/>
        </svg>
      </div>
    </div>

    <div class="cr-hero-eyebrow">Join Our Team</div>
    <h1>Build a Career with <em>Purpose</em></h1>
    <p class="cr-hero-sub">
      At Almarah Foundation, every role is more than a job — it's a chance to change lives. Join our passionate team of educators, therapists, and professionals committed to empowering children and communities across Pakistan.
    </p>
    <div class="cr-hero-badges">
      <div class="cr-hero-badge gold">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Be a Changemaker
      </div>
      <div class="cr-hero-badge">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Collaborative Team
      </div>
      <div class="cr-hero-badge">
        <svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        Real Impact Daily
      </div>
    </div>

  </div>
</section>

<!-- ═══════════ WHY JOIN US ═══════════ -->
<div class="cr-why">

  <div class="cr-section-header cr-animate">
    <div class="cr-section-tag">Why Almarah</div>
    <h2 class="cr-section-title">Why <span class="accent">Join Us?</span></h2>
    <p class="cr-section-desc">Discover what makes working at Almarah Foundation a truly rewarding and fulfilling experience for every member of our team.</p>
  </div>

  <div class="cr-why-cards">

    <!-- Reason 01 -->
    <div class="cr-why-row">
      <div class="cr-why-img cr-animate-left">
        <img src="https://almarah.org/wp-content/uploads/2026/04/514345247_738404035846951_4585600570098139203_n-e1775071405969-600x540.jpg"
             alt="Meaningful Impact at Almarah Foundation" loading="lazy">
        <div class="cr-why-num">01</div>
      </div>
      <div class="cr-why-content cr-animate-right">
        <div class="cr-why-number-bg">01</div>
        <div class="cr-why-eyebrow">Why Work With Us</div>
        <h3 class="cr-why-title">Meaningful Impact</h3>
        <div class="cr-why-divider"></div>
        <p class="cr-why-desc">At Almarah Foundation, your work creates real change. Every role contributes to improving lives and strengthening communities, giving your work true purpose. You will not just be doing a job — you will be part of a mission that transforms the lives of orphans, homeless children, and vulnerable families across Pakistan. Here, your contributions are visible, valued, and lasting.</p>
      </div>
    </div>

    <!-- Reason 02 -->
    <div class="cr-why-row reverse">
      <div class="cr-why-img cr-animate-right">
        <img src="https://almarah.org/wp-content/uploads/2026/04/558493899_756282497392438_8291981798387155468_n-600x540.jpg"
             alt="Growth Opportunities at Almarah Foundation" loading="lazy">
        <div class="cr-why-num">02</div>
      </div>
      <div class="cr-why-content cr-animate-left">
        <div class="cr-why-number-bg">02</div>
        <div class="cr-why-eyebrow">Career Development</div>
        <h3 class="cr-why-title">Growth Opportunities</h3>
        <div class="cr-why-divider"></div>
        <p class="cr-why-desc">We support your growth through learning, mentorship, and new challenges. You'll have opportunities to build skills and advance your career as we grow together. Almarah Foundation is committed to investing in its people — offering professional development, exposure to diverse fields, and the chance to take on increasing responsibilities that match your ambitions and strengths.</p>
      </div>
    </div>

    <!-- Reason 03 -->
    <div class="cr-why-row">
      <div class="cr-why-img cr-animate-left">
        <img src="https://almarah.org/wp-content/uploads/2026/04/558992303_758919967128691_9045861793811987060_n-e1775071659838.jpg"
             alt="Inspirational Environment at Almarah Foundation" loading="lazy">
        <div class="cr-why-num">03</div>
      </div>
      <div class="cr-why-content cr-animate-right">
        <div class="cr-why-number-bg">03</div>
        <div class="cr-why-eyebrow">Team Culture</div>
        <h3 class="cr-why-title">Inspirational Environment</h3>
        <div class="cr-why-divider"></div>
        <p class="cr-why-desc">Join a supportive and passionate team that values collaboration and respect. Here, your ideas matter and your work is appreciated. Our workplace fosters a culture of inclusivity, creativity, and shared purpose — where every team member feels empowered to contribute, innovate, and grow both personally and professionally in an environment built on trust and compassion.</p>
      </div>
    </div>

  </div>
</div>

<!-- ═══════════ APPLY ═══════════ -->
<div class="cr-apply">

  <div class="cr-section-header cr-animate">
    <div class="cr-section-tag">Open Positions</div>
    <h2 class="cr-section-title">Ready to <span class="accent">Join Us?</span></h2>
    <p class="cr-section-desc">We are always looking for passionate, skilled, and dedicated individuals who share our vision of empowering vulnerable communities.</p>
  </div>

  <div class="cr-apply-grid">

    <!-- Image -->
    <div class="cr-apply-img-wrap cr-animate-left">
      <img src="https://almarah.org/wp-content/uploads/2026/04/474486786_559756057045084_820850258245305115_n.jpg"
           alt="Join Almarah Foundation Team" loading="lazy">
      <div class="cr-apply-img-tag">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Be a Changemaker
      </div>
    </div>

    <!-- Content -->
    <div class="cr-apply-content cr-animate-right">

      <div class="cr-section-tag" style="margin-bottom:14px;">Apply Now</div>
      <h2 class="cr-apply-title">Build a <span class="accent">Rewarding Career</span> with Almarah Foundation!</h2>
      <p class="cr-apply-subtitle">Looking for a fulfilling career where you can make a real difference?</p>
      <div class="cr-apply-divider"></div>
      <p class="cr-apply-desc">
        Join our team of passionate educators, therapists, and professionals dedicated to empowering children with special needs. At Almarah Foundation, every position contributes to a larger mission — creating a brighter future for every child. We welcome talented individuals from all backgrounds who are committed to social impact and community service.
      </p>

      <div class="cr-roles-list">
        <div class="cr-role-chip">
          <svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
          Educators
        </div>
        <div class="cr-role-chip">
          <svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
          Therapists
        </div>
        <div class="cr-role-chip">
          <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
          Admin Staff
        </div>
        <div class="cr-role-chip">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          Social Workers
        </div>
        <div class="cr-role-chip">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
          Outreach
        </div>
        <div class="cr-role-chip">
          <svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>
          Volunteers
        </div>
      </div>

      <a href="mailto:careers@almarah.org?subject=Career Application - Almarah Foundation" class="cr-apply-btn">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Send Your CV Now
      </a>

      <div class="cr-apply-email-note">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Email your CV directly to:
        <a href="mailto:careers@almarah.org">careers@almarah.org</a>
      </div>

    </div>
  </div>
</div>

<!-- ═══════════ VALUES ═══════════ -->
<div class="cr-values">
  <div class="cr-values-inner">
    <div class="cr-section-header cr-animate">
      <div class="cr-section-tag">Our Culture</div>
      <h2 class="cr-section-title">What We <span class="accent">Stand For</span></h2>
      <p class="cr-section-desc">Our values guide everything we do — from how we work together to how we serve the communities that trust us.</p>
    </div>
    <div class="cr-values-grid">
      <div class="cr-value-card cr-animate cr-delay-1">
        <div class="cr-value-icon">
          <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <div class="cr-value-title">Integrity</div>
        <p class="cr-value-desc">We operate with transparency, honesty, and accountability in everything we do — building trust with every child, family, and partner.</p>
      </div>
      <div class="cr-value-card cr-animate cr-delay-2">
        <div class="cr-value-icon">
          <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
        <div class="cr-value-title">Compassion</div>
        <p class="cr-value-desc">We lead with empathy and kindness, treating every individual with dignity and care — because humanity is at the heart of our mission.</p>
      </div>
      <div class="cr-value-card cr-animate cr-delay-3">
        <div class="cr-value-icon">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="cr-value-title">Collaboration</div>
        <p class="cr-value-desc">Together we achieve more. We work as a united team, valuing every voice and building partnerships that amplify our collective impact.</p>
      </div>
      <div class="cr-value-card cr-animate cr-delay-4">
        <div class="cr-value-icon">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="cr-value-title">Excellence</div>
        <p class="cr-value-desc">We hold ourselves to the highest standards, continuously improving our programmes, processes, and outcomes for those who depend on us.</p>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════ PROCESS ═══════════ -->
<div class="cr-process">
  <div class="cr-section-header cr-animate">
    <div class="cr-section-tag">How to Apply</div>
    <h2 class="cr-section-title">Application <span class="accent">Process</span></h2>
    <p class="cr-section-desc">Joining Almarah Foundation is simple. Follow these three steps to start your journey with us.</p>
  </div>
  <div class="cr-process-steps">

    <div class="cr-process-step cr-animate cr-delay-1">
      <div class="cr-process-step-num">01</div>
      <div class="cr-process-step-title">Send Your CV</div>
      <p class="cr-process-step-desc">Email your updated CV and a brief cover letter to <strong>careers@almarah.org</strong> mentioning the role you are interested in.</p>
    </div>

    <div class="cr-process-step cr-animate cr-delay-2">
      <div class="cr-process-step-num">02</div>
      <div class="cr-process-step-title">Initial Review</div>
      <p class="cr-process-step-desc">Our HR team will review your application and reach out within 7–10 working days if your profile matches our current requirements.</p>
    </div>

    <div class="cr-process-step cr-animate cr-delay-3">
      <div class="cr-process-step-num">03</div>
      <div class="cr-process-step-title">Interview &amp; Join</div>
      <p class="cr-process-step-desc">Shortlisted candidates will be invited for an interview. Successful applicants will receive an offer and join our growing team of changemakers.</p>
    </div>

  </div>
</div>

<!-- ═══════════ CTA ═══════════ -->
<section class="cr-cta">
  <div class="cr-cta-c1"></div>
  <div class="cr-cta-c2"></div>
  <div class="cr-cta-inner cr-animate">
    <div class="cr-cta-tag">Take the First Step</div>
    <h2 class="cr-cta-title">Ready to Make a <em>Difference?</em></h2>
    <p class="cr-cta-desc">Send your CV today and become part of our mission to create a brighter future for every child. Your journey as a changemaker starts here.</p>
    <a href="mailto:careers@almarah.org?subject=Career Application - Almarah Foundation" class="cr-cta-btn">
      Apply Now — careers@almarah.org
      <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
    </a>
  </div>
</section>

</div><!-- end cr-page -->

<script>
(function(){
  const els = document.querySelectorAll(
    '.cr-animate, .cr-animate-left, .cr-animate-right, .cr-animate-scale'
  );
  if('IntersectionObserver' in window){
    const obs = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{
        if(e.isIntersecting){
          e.target.classList.add('cr-visible');
          obs.unobserve(e.target);
        }
      });
    },{ threshold: 0.10, rootMargin:'0px 0px -50px 0px' });
    els.forEach(el => obs.observe(el));
  } else {
    els.forEach(el => el.classList.add('cr-visible'));
  }
})();
</script>