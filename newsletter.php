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
  --text-light:   #9A6080;
}

html { scroll-behavior: smooth; }

.nl-page {
  font-family: 'Manrope', sans-serif;
  background: var(--off-white);
  color: var(--text-dark);
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
}

/* ═══════════════════
   ANIMATIONS
═══════════════════ */
@keyframes nl-fadeUp {
  from { opacity: 0; transform: translateY(40px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes nl-fadeIn {
  from { opacity: 0; }
  to   { opacity: 1; }
}
@keyframes nl-rotateSlow {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
@keyframes nl-float {
  0%, 100% { transform: translateY(0px); }
  50%       { transform: translateY(-12px); }
}
@keyframes nl-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%       { opacity: .7; transform: scale(0.95); }
}
@keyframes nl-shimmer {
  0%   { background-position: -400px 0; }
  100% { background-position: 400px 0; }
}
@keyframes nl-spin-dot {
  0%   { transform: rotate(0deg) translateX(60px) rotate(0deg); }
  100% { transform: rotate(360deg) translateX(60px) rotate(-360deg); }
}

.nl-animate {
  opacity: 0;
  transform: translateY(36px);
  transition: opacity .7s ease, transform .7s ease;
}
.nl-animate.nl-visible {
  opacity: 1;
  transform: translateY(0);
}
.nl-animate-left {
  opacity: 0;
  transform: translateX(-40px);
  transition: opacity .7s ease, transform .7s ease;
}
.nl-animate-left.nl-visible {
  opacity: 1;
  transform: translateX(0);
}
.nl-animate-right {
  opacity: 0;
  transform: translateX(40px);
  transition: opacity .7s ease, transform .7s ease;
}
.nl-animate-right.nl-visible {
  opacity: 1;
  transform: translateX(0);
}
.nl-delay-1 { transition-delay: .1s !important; }
.nl-delay-2 { transition-delay: .2s !important; }
.nl-delay-3 { transition-delay: .3s !important; }
.nl-delay-4 { transition-delay: .4s !important; }
.nl-delay-5 { transition-delay: .5s !important; }

/* ═══════════════════
   HERO
═══════════════════ */
.nl-hero {
  background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 30%, var(--pink) 65%, var(--pink-mid) 100%);
  padding: 120px 24px 100px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
@media (min-width: 768px) { .nl-hero { padding: 140px 40px 110px; } }

.nl-hero::before {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 70% 60% at 10% 60%, rgba(242,194,0,.18) 0%, transparent 60%),
    radial-gradient(ellipse 50% 50% at 90% 15%, rgba(255,255,255,.09) 0%, transparent 55%),
    radial-gradient(ellipse 40% 40% at 50% 100%, rgba(0,0,0,.14) 0%, transparent 60%);
  pointer-events: none;
}

/* Rotating ring */
.nl-hero-ring {
  position: absolute;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  width: 800px; height: 800px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,.05);
  animation: nl-rotateSlow 40s linear infinite;
  pointer-events: none;
}
.nl-hero-ring::before {
  content: '';
  position: absolute;
  top: 8%; left: 0;
  width: 10px; height: 10px;
  border-radius: 50%;
  background: rgba(242,194,0,.6);
  box-shadow: 0 0 18px rgba(242,194,0,.5);
}

.nl-hero-circle-tl {
  position: absolute;
  top: -120px; left: -120px;
  width: 420px; height: 420px;
  border-radius: 50%;
  border: 60px solid rgba(255,255,255,.04);
  pointer-events: none;
  animation: nl-fadeIn 1.5s ease both;
}
.nl-hero-circle-br {
  position: absolute;
  bottom: -100px; right: -180px;
  width: 560px; height: 560px;
  border-radius: 50%;
  border: 75px solid rgba(242,194,0,.05);
  pointer-events: none;
  animation: nl-fadeIn 1.5s ease .3s both;
}

/* Floating envelope illustration */
.nl-hero-icon-wrap {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 32px;
  animation: nl-fadeUp .8s ease .1s both;
}
.nl-hero-icon-bg {
  width: 88px; height: 88px;
  border-radius: 28px;
  background: rgba(255,255,255,.12);
  border: 1.5px solid rgba(255,255,255,.20);
  backdrop-filter: blur(12px);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: nl-float 4s ease-in-out infinite;
  box-shadow: 0 20px 60px rgba(0,0,0,.20);
}
.nl-hero-icon-bg svg {
  width: 40px; height: 40px;
  stroke: var(--yellow);
  fill: none;
  stroke-width: 1.5;
  stroke-linecap: round;
  stroke-linejoin: round;
}

/* Ping dot */
.nl-hero-ping {
  position: absolute;
  top: 4px; right: 4px;
  width: 14px; height: 14px;
}
.nl-hero-ping::before {
  content: '';
  position: absolute; inset: 0;
  border-radius: 50%;
  background: var(--yellow);
  animation: nl-pulse 2s ease infinite;
}
.nl-hero-ping::after {
  content: '';
  position: absolute;
  inset: 3px;
  border-radius: 50%;
  background: var(--yellow-light);
}

.nl-hero-inner {
  position: relative;
  z-index: 2;
  max-width: 760px;
  margin: 0 auto;
}

.nl-hero-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: .62rem;
  font-weight: 700;
  letter-spacing: 5px;
  text-transform: uppercase;
  color: var(--yellow-light);
  margin-bottom: 18px;
  animation: nl-fadeUp .8s ease .2s both;
}
.nl-hero-eyebrow::before,
.nl-hero-eyebrow::after {
  content: '';
  display: block;
  width: 28px; height: 1.5px;
  background: var(--yellow);
  opacity: .6;
}

.nl-hero h1 {
  font-weight: 800;
  font-size: clamp(2.2rem, 7vw, 4.2rem);
  color: var(--white);
  letter-spacing: -2px;
  line-height: 1.05;
  text-shadow: 0 4px 40px rgba(0,0,0,.22);
  margin-bottom: 20px;
  animation: nl-fadeUp .8s ease .3s both;
}
.nl-hero h1 em {
  font-style: normal;
  color: var(--yellow);
  filter: drop-shadow(0 0 24px rgba(242,194,0,.30));
}

.nl-hero-sub {
  font-size: .93rem;
  font-family: 'Open Sans', sans-serif;
  color: rgba(255,255,255,.65);
  line-height: 1.85;
  max-width: 580px;
  margin: 0 auto 40px;
  animation: nl-fadeUp .8s ease .4s both;
}

/* Hero badges */
.nl-hero-badges {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 12px;
  animation: nl-fadeUp .8s ease .5s both;
}
.nl-hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 18px;
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
.nl-hero-badge:hover {
  background: rgba(255,255,255,.18);
  transform: translateY(-2px);
}
.nl-hero-badge svg {
  width: 13px; height: 13px;
  stroke: currentColor;
  fill: none;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
  flex-shrink: 0;
}
.nl-hero-badge.gold {
  background: rgba(242,194,0,.15);
  border-color: rgba(242,194,0,.35);
  color: var(--yellow-light);
}

/* ═══════════════════
   MAIN CONTENT
═══════════════════ */
.nl-main {
  padding: 80px 24px 100px;
  max-width: 1140px;
  margin: 0 auto;
}

/* ── BENEFITS STRIP ── */
.nl-benefits {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 72px;
}

.nl-benefit-card {
  background: var(--white);
  border-radius: 20px;
  padding: 32px 28px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 14px;
  box-shadow:
    0 0 0 1px rgba(169,45,99,.06),
    0 8px 28px rgba(169,45,99,.07);
  position: relative;
  overflow: hidden;
  transition: transform .35s cubic-bezier(.22,.9,.36,1), box-shadow .35s;
}
.nl-benefit-card::before {
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
.nl-benefit-card:hover {
  transform: translateY(-6px);
  box-shadow:
    0 0 0 1px rgba(169,45,99,.10),
    0 20px 50px rgba(169,45,99,.12);
}
.nl-benefit-card:hover::before { transform: scaleX(1); }

.nl-benefit-icon {
  width: 52px; height: 52px;
  border-radius: 15px;
  background: rgba(169,45,99,.07);
  border: 1.5px solid rgba(169,45,99,.10);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .3s;
  flex-shrink: 0;
}
.nl-benefit-icon svg {
  width: 22px; height: 22px;
  stroke: var(--pink);
  fill: none;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
  transition: stroke .3s;
}
.nl-benefit-card:hover .nl-benefit-icon {
  background: linear-gradient(135deg, var(--pink-dark), var(--pink));
  border-color: transparent;
}
.nl-benefit-card:hover .nl-benefit-icon svg { stroke: white; }

.nl-benefit-title {
  font-size: 1rem;
  font-weight: 800;
  color: var(--text-dark);
  letter-spacing: -.3px;
  line-height: 1.3;
}
.nl-benefit-desc {
  font-size: .84rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-light);
  line-height: 1.8;
}

/* ── FORM SECTION ── */
.nl-form-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 48px;
  align-items: start;
}

/* LEFT — info panel */
.nl-info-panel {
  position: sticky;
  top: 40px;
}

.nl-section-tag {
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
.nl-section-tag::before,
.nl-section-tag::after {
  content: '';
  display: block;
  width: 18px; height: 1px;
  background: var(--pink);
  opacity: .45;
}

.nl-info-title {
  font-weight: 800;
  font-size: clamp(1.8rem, 3.5vw, 2.6rem);
  color: var(--text-dark);
  letter-spacing: -1.5px;
  line-height: 1.1;
  margin-bottom: 16px;
}
.nl-info-title .accent { color: var(--pink); }

.nl-info-desc {
  font-size: .92rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-light);
  line-height: 1.9;
  margin-bottom: 36px;
}

/* What you get list */
.nl-what-list {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 40px;
}
.nl-what-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  font-size: .88rem;
  font-family: 'Open Sans', sans-serif;
  color: #5A2040;
  line-height: 1.6;
}
.nl-what-check {
  width: 24px; height: 24px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--pink-dark), var(--pink));
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  margin-top: 1px;
  box-shadow: 0 4px 12px rgba(169,45,99,.25);
}
.nl-what-check svg {
  width: 12px; height: 12px;
  stroke: white;
  fill: none;
  stroke-width: 2.5;
  stroke-linecap: round;
  stroke-linejoin: round;
}

/* Subscriber count pill */
.nl-subscriber-pill {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: var(--white);
  border: 1px solid rgba(169,45,99,.12);
  border-radius: 100px;
  padding: 10px 20px 10px 10px;
  box-shadow: 0 4px 20px rgba(169,45,99,.08);
}
.nl-subscriber-avatars {
  display: flex;
}
.nl-subscriber-avatar {
  width: 30px; height: 30px;
  border-radius: 50%;
  border: 2px solid white;
  background: linear-gradient(135deg, var(--pink-dark), var(--pink-mid));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: .55rem;
  font-weight: 700;
  color: white;
  margin-right: -8px;
  flex-shrink: 0;
}
.nl-subscriber-avatar:last-child { margin-right: 0; }
.nl-subscriber-avatar:nth-child(2) { background: linear-gradient(135deg, #8A2050, #A92D63); }
.nl-subscriber-avatar:nth-child(3) { background: linear-gradient(135deg, #F2C200, #C9A000); color: #1a0810; }
.nl-subscriber-text {
  font-size: .72rem;
  font-weight: 700;
  color: var(--text-dark);
  line-height: 1.3;
}
.nl-subscriber-text span {
  display: block;
  font-size: .64rem;
  font-weight: 500;
  color: var(--text-light);
}

/* RIGHT — form card */
.nl-form-card {
  background: var(--white);
  border-radius: 24px;
  padding: 48px 44px;
  box-shadow:
    0 0 0 1px rgba(169,45,99,.07),
    0 24px 60px rgba(169,45,99,.10);
  position: relative;
  overflow: hidden;
}

/* Gold corner accent */
.nl-form-card::before {
  content: '';
  position: absolute;
  top: 0; right: 0;
  width: 120px; height: 120px;
  background: radial-gradient(circle at top right, rgba(242,194,0,.10), transparent 70%);
  pointer-events: none;
}
.nl-form-card::after {
  content: '';
  position: absolute;
  bottom: 0; left: 0;
  width: 100px; height: 100px;
  background: radial-gradient(circle at bottom left, rgba(169,45,99,.06), transparent 70%);
  pointer-events: none;
}

.nl-form-card-header {
  margin-bottom: 32px;
  position: relative;
  z-index: 1;
}
.nl-form-card-title {
  font-size: 1.4rem;
  font-weight: 800;
  color: var(--text-dark);
  letter-spacing: -.5px;
  margin-bottom: 8px;
}
.nl-form-card-sub {
  font-size: .84rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-light);
  line-height: 1.7;
}

/* Divider */
.nl-form-divider {
  width: 48px;
  height: 3px;
  background: linear-gradient(90deg, var(--yellow), var(--pink));
  border-radius: 2px;
  margin-bottom: 28px;
}

/* Forminator shortcode wrapper */
.nl-forminator-wrap {
  position: relative;
  z-index: 1;
}

/* Style overrides for Forminator */
.nl-forminator-wrap .forminator-custom-form .forminator-field input[type="text"],
.nl-forminator-wrap .forminator-custom-form .forminator-field input[type="email"],
.nl-forminator-wrap .forminator-custom-form .forminator-field input[type="tel"],
.nl-forminator-wrap .forminator-custom-form .forminator-field textarea,
.nl-forminator-wrap .forminator-custom-form .forminator-field select {
  border: 1.5px solid rgba(169,45,99,.15) !important;
  border-radius: 12px !important;
  padding: 14px 18px !important;
  font-family: 'Open Sans', sans-serif !important;
  font-size: .88rem !important;
  color: var(--text-dark) !important;
  background: var(--off-white) !important;
  transition: border-color .3s, box-shadow .3s !important;
  width: 100% !important;
}
.nl-forminator-wrap .forminator-custom-form .forminator-field input:focus,
.nl-forminator-wrap .forminator-custom-form .forminator-field textarea:focus,
.nl-forminator-wrap .forminator-custom-form .forminator-field select:focus {
  border-color: var(--pink) !important;
  box-shadow: 0 0 0 3px rgba(169,45,99,.10) !important;
  outline: none !important;
  background: white !important;
}
.nl-forminator-wrap .forminator-custom-form .forminator-field label {
  font-family: 'Manrope', sans-serif !important;
  font-size: .8rem !important;
  font-weight: 700 !important;
  color: var(--text-dark) !important;
  letter-spacing: .3px !important;
  margin-bottom: 6px !important;
}
.nl-forminator-wrap .forminator-custom-form button[type="submit"],
.nl-forminator-wrap .forminator-custom-form input[type="submit"] {
  background: linear-gradient(135deg, var(--pink-dark), var(--pink)) !important;
  color: white !important;
  font-family: 'Manrope', sans-serif !important;
  font-size: .92rem !important;
  font-weight: 700 !important;
  padding: 15px 40px !important;
  border-radius: 100px !important;
  border: none !important;
  cursor: pointer !important;
  transition: all .3s ease !important;
  box-shadow: 0 8px 24px rgba(169,45,99,.30) !important;
  letter-spacing: .3px !important;
  width: 100% !important;
}
.nl-forminator-wrap .forminator-custom-form button[type="submit"]:hover,
.nl-forminator-wrap .forminator-custom-form input[type="submit"]:hover {
  transform: translateY(-2px) !important;
  box-shadow: 0 14px 36px rgba(169,45,99,.38) !important;
  background: linear-gradient(135deg, #6B0F35, var(--pink-dark)) !important;
}

/* Privacy note */
.nl-privacy-note {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 20px;
  font-size: .75rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-light);
  position: relative;
  z-index: 1;
}
.nl-privacy-note svg {
  width: 14px; height: 14px;
  stroke: var(--pink);
  fill: none;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
  flex-shrink: 0;
}

/* ── TOPICS SECTION ── */
.nl-topics-section {
  margin-top: 80px;
}
.nl-topics-header {
  text-align: center;
  margin-bottom: 40px;
}
.nl-topics-title {
  font-weight: 800;
  font-size: clamp(1.6rem, 3vw, 2.2rem);
  color: var(--text-dark);
  letter-spacing: -1px;
  margin-bottom: 10px;
}
.nl-topics-title .accent { color: var(--pink); }
.nl-topics-desc {
  font-size: .88rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-light);
  line-height: 1.8;
  max-width: 520px;
  margin: 0 auto;
}

.nl-topics-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.nl-topic-chip {
  background: var(--white);
  border: 1.5px solid rgba(169,45,99,.10);
  border-radius: 14px;
  padding: 20px 18px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  text-align: center;
  transition: all .3s ease;
  cursor: default;
}
.nl-topic-chip:hover {
  border-color: var(--pink);
  background: rgba(169,45,99,.03);
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(169,45,99,.10);
}
.nl-topic-chip-icon {
  width: 42px; height: 42px;
  border-radius: 12px;
  background: rgba(169,45,99,.07);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .3s;
}
.nl-topic-chip-icon svg {
  width: 20px; height: 20px;
  stroke: var(--pink);
  fill: none;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
}
.nl-topic-chip:hover .nl-topic-chip-icon {
  background: var(--pink);
}
.nl-topic-chip:hover .nl-topic-chip-icon svg { stroke: white; }
.nl-topic-chip-label {
  font-size: .78rem;
  font-weight: 700;
  color: var(--text-dark);
  line-height: 1.3;
}

/* ── CTA BOTTOM ── */
.nl-cta {
  background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 35%, var(--pink) 70%, var(--pink-mid) 100%);
  padding: 100px 24px;
  text-align: center;
  position: relative;
  overflow: hidden;
  margin-top: 80px;
}
.nl-cta::before {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 60% 70% at 10% 50%, rgba(242,194,0,.14) 0%, transparent 60%),
    radial-gradient(ellipse 50% 50% at 90% 20%, rgba(255,255,255,.07) 0%, transparent 55%);
  pointer-events: none;
}
.nl-cta-circle1 {
  position: absolute;
  top: -80px; right: -80px;
  width: 300px; height: 300px;
  border-radius: 50%;
  border: 50px solid rgba(242,194,0,.06);
  pointer-events: none;
  animation: nl-rotateSlow 30s linear infinite;
}
.nl-cta-circle2 {
  position: absolute;
  bottom: -60px; left: -60px;
  width: 240px; height: 240px;
  border-radius: 50%;
  border: 40px solid rgba(255,255,255,.04);
  pointer-events: none;
  animation: nl-rotateSlow 25s linear infinite reverse;
}
.nl-cta-inner {
  position: relative;
  z-index: 2;
  max-width: 600px;
  margin: 0 auto;
}
.nl-cta-tag {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: .58rem;
  font-weight: 700;
  letter-spacing: 5px;
  text-transform: uppercase;
  color: var(--yellow-light);
  margin-bottom: 16px;
}
.nl-cta-tag::before,
.nl-cta-tag::after {
  content: '';
  display: block;
  width: 18px; height: 1px;
  background: var(--yellow);
  opacity: .6;
}
.nl-cta-title {
  font-weight: 800;
  font-size: clamp(2rem, 5vw, 3.2rem);
  color: var(--white);
  letter-spacing: -2px;
  line-height: 1.1;
  margin-bottom: 16px;
}
.nl-cta-title em {
  font-style: normal;
  color: var(--yellow);
}
.nl-cta-desc {
  font-size: .92rem;
  font-family: 'Open Sans', sans-serif;
  color: rgba(255,255,255,.70);
  line-height: 1.85;
  margin-bottom: 36px;
}
.nl-cta-share {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  flex-wrap: wrap;
}
.nl-cta-share-label {
  font-size: .78rem;
  font-weight: 600;
  color: rgba(255,255,255,.60);
}
.nl-cta-share-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255,255,255,.12);
  border: 1.5px solid rgba(255,255,255,.18);
  color: white;
  font-family: 'Manrope', sans-serif;
  font-size: .78rem;
  font-weight: 700;
  padding: 10px 20px;
  border-radius: 100px;
  text-decoration: none;
  backdrop-filter: blur(8px);
  transition: all .3s ease;
}
.nl-cta-share-btn svg {
  width: 14px; height: 14px;
  stroke: currentColor;
  fill: none;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
}
.nl-cta-share-btn:hover {
  background: rgba(255,255,255,.22);
  transform: translateY(-2px);
  text-decoration: none;
  color: white;
}

/* ═══════════════════
   RESPONSIVE
═══════════════════ */
@media (max-width: 960px) {
  .nl-form-section { grid-template-columns: 1fr; gap: 40px; }
  .nl-info-panel   { position: static; }
  .nl-benefits     { grid-template-columns: 1fr; gap: 16px; }
  .nl-topics-grid  { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
  .nl-hero  { padding: 90px 20px 70px; }
  .nl-main  { padding: 60px 20px 80px; }
  .nl-form-card { padding: 36px 24px; }
  .nl-topics-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
  .nl-cta { padding: 80px 20px; }
  .nl-benefits { grid-template-columns: 1fr; }
}
</style>

<div class="nl-page">

<!-- ═══════════ HERO ═══════════ -->
<section class="nl-hero">
  <div class="nl-hero-ring"></div>
  <div class="nl-hero-circle-tl"></div>
  <div class="nl-hero-circle-br"></div>
  <div class="nl-hero-inner">

    <div class="nl-hero-icon-wrap">
      <div class="nl-hero-icon-bg">
        <svg viewBox="0 0 24 24">
          <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
          <polyline points="22,6 12,13 2,6"/>
        </svg>
      </div>
      <div class="nl-hero-ping"></div>
    </div>

    <div class="nl-hero-eyebrow">Stay Connected</div>
    <h1>Subscribe to Our <em>Newsletter</em></h1>
    <p class="nl-hero-sub">
      Get the latest updates on Almarah Foundation's programmes, impact stories, partnership news, and upcoming events — delivered straight to your inbox.
    </p>
    <div class="nl-hero-badges">
      <div class="nl-hero-badge gold">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        No Spam, Ever
      </div>
      <div class="nl-hero-badge">
        <svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        Monthly Updates
      </div>
      <div class="nl-hero-badge">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Free to Join
      </div>
    </div>

  </div>
</section>

<!-- ═══════════ MAIN ═══════════ -->
<div class="nl-main">

  <!-- BENEFITS -->
  <div class="nl-benefits">

    <div class="nl-benefit-card nl-animate nl-delay-1">
      <div class="nl-benefit-icon">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      </div>
      <div class="nl-benefit-title">Impact Stories</div>
      <p class="nl-benefit-desc">Read real stories of children and families whose lives have been transformed through Almarah Foundation's programmes and partnerships.</p>
    </div>

    <div class="nl-benefit-card nl-animate nl-delay-2">
      <div class="nl-benefit-icon">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div class="nl-benefit-title">Partnership News</div>
      <p class="nl-benefit-desc">Stay informed about our latest MoUs, collaborations, and strategic alliances across healthcare, education, and social welfare sectors.</p>
    </div>

    <div class="nl-benefit-card nl-animate nl-delay-3">
      <div class="nl-benefit-icon">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
      </div>
      <div class="nl-benefit-title">Upcoming Events</div>
      <p class="nl-benefit-desc">Be the first to know about medical camps, fundraisers, awareness sessions, and community events organised by Almarah Foundation.</p>
    </div>

  </div>

  <!-- FORM SECTION -->
  <div class="nl-form-section">

    <!-- LEFT INFO -->
    <div class="nl-info-panel nl-animate-left">

      <div class="nl-section-tag">Newsletter</div>
      <h2 class="nl-info-title">Join Our <span class="accent">Community</span> of Changemakers</h2>
      <p class="nl-info-desc">
        By subscribing to the Almarah Foundation newsletter, you become part of a growing community dedicated to uplifting orphans, underprivileged children, and vulnerable families across Pakistan. Your inbox becomes a window into real impact.
      </p>

      <ul class="nl-what-list">
        <li class="nl-what-item">
          <div class="nl-what-check">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          Monthly impact reports and programme updates
        </li>
        <li class="nl-what-item">
          <div class="nl-what-check">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          Exclusive stories from children in our care
        </li>
        <li class="nl-what-item">
          <div class="nl-what-check">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          New MoU and partnership announcements
        </li>
        <li class="nl-what-item">
          <div class="nl-what-check">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          Upcoming events, camps &amp; donation drives
        </li>
        <li class="nl-what-item">
          <div class="nl-what-check">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          Volunteer and collaboration opportunities
        </li>
        <li class="nl-what-item">
          <div class="nl-what-check">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          Zero spam — unsubscribe anytime, instantly
        </li>
      </ul>

      <div class="nl-subscriber-pill">
        <div class="nl-subscriber-avatars">
          <div class="nl-subscriber-avatar">AK</div>
          <div class="nl-subscriber-avatar">SR</div>
          <div class="nl-subscriber-avatar">MN</div>
        </div>
        <div class="nl-subscriber-text">
          Join our subscribers
          <span>Growing community of supporters</span>
        </div>
      </div>

    </div>

    <!-- RIGHT FORM -->
    <div class="nl-form-card nl-animate-right">

      <div class="nl-form-card-header">
        <h3 class="nl-form-card-title">Subscribe Now — It's Free</h3>
        <p class="nl-form-card-sub">Fill in your details below and start receiving meaningful updates from Almarah Foundation directly in your inbox.</p>
      </div>

      <div class="nl-form-divider"></div>

      <div class="nl-forminator-wrap">
        [forminator_form id="1597"]
      </div>

      <div class="nl-privacy-note">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Your information is safe. We never share or sell your data to third parties.
      </div>

    </div>

  </div>

  <!-- TOPICS -->
  <div class="nl-topics-section nl-animate">

    <div class="nl-topics-header">
      <div class="nl-section-tag">What We Cover</div>
      <h2 class="nl-topics-title">Newsletter <span class="accent">Topics</span></h2>
      <p class="nl-topics-desc">Our newsletter covers a wide range of topics related to our mission, work, and the communities we serve.</p>
    </div>

    <div class="nl-topics-grid">

      <div class="nl-topic-chip nl-animate nl-delay-1">
        <div class="nl-topic-chip-icon">
          <svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        </div>
        <span class="nl-topic-chip-label">Healthcare Updates</span>
      </div>

      <div class="nl-topic-chip nl-animate nl-delay-2">
        <div class="nl-topic-chip-icon">
          <svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        </div>
        <span class="nl-topic-chip-label">Education &amp; Literacy</span>
      </div>

      <div class="nl-topic-chip nl-animate nl-delay-3">
        <div class="nl-topic-chip-icon">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <span class="nl-topic-chip-label">Child Welfare</span>
      </div>

      <div class="nl-topic-chip nl-animate nl-delay-4">
        <div class="nl-topic-chip-icon">
          <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <span class="nl-topic-chip-label">MoU &amp; Partnerships</span>
      </div>

      <div class="nl-topic-chip nl-animate nl-delay-1">
        <div class="nl-topic-chip-icon">
          <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
        </div>
        <span class="nl-topic-chip-label">Events &amp; Camps</span>
      </div>

      <div class="nl-topic-chip nl-animate nl-delay-2">
        <div class="nl-topic-chip-icon">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        </div>
        <span class="nl-topic-chip-label">Global Outreach</span>
      </div>

      <div class="nl-topic-chip nl-animate nl-delay-3">
        <div class="nl-topic-chip-icon">
          <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <span class="nl-topic-chip-label">Donation Drives</span>
      </div>

      <div class="nl-topic-chip nl-animate nl-delay-4">
        <div class="nl-topic-chip-icon">
          <svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <span class="nl-topic-chip-label">Impact Reports</span>
      </div>

    </div>
  </div>

</div><!-- end nl-main -->

<!-- ═══════════ CTA ═══════════ -->
<section class="nl-cta">
  <div class="nl-cta-circle1"></div>
  <div class="nl-cta-circle2"></div>
  <div class="nl-cta-inner nl-animate">
    <div class="nl-cta-tag">Spread the Word</div>
    <h2 class="nl-cta-title">Help Us <em>Reach More</em> People</h2>
    <p class="nl-cta-desc">Know someone who cares about orphan welfare and community development? Share this newsletter with your friends, family, and colleagues.</p>
    <div class="nl-cta-share">
      <span class="nl-cta-share-label">Share via:</span>
      <a href="https://www.facebook.com/sharer/sharer.php?u=https://almarah.org/newsletter/" target="_blank" rel="noopener" class="nl-cta-share-btn">
        <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
        Facebook
      </a>
      <a href="https://wa.me/?text=Subscribe+to+Almarah+Foundation+Newsletter:+https://almarah.org/newsletter/" target="_blank" rel="noopener" class="nl-cta-share-btn">
        <svg viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        WhatsApp
      </a>
      <a href="mailto:?subject=Subscribe to Almarah Foundation Newsletter&body=Stay updated on Almarah Foundation's work: https://almarah.org/newsletter/" class="nl-cta-share-btn">
        <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Email
      </a>
    </div>
  </div>
</section>

</div><!-- end nl-page -->

<script>
(function(){
  const els = document.querySelectorAll('.nl-animate, .nl-animate-left, .nl-animate-right');
  if('IntersectionObserver' in window){
    const obs = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{
        if(e.isIntersecting){
          e.target.classList.add('nl-visible');
          obs.unobserve(e.target);
        }
      });
    },{ threshold: 0.12, rootMargin: '0px 0px -50px 0px' });
    els.forEach(el => obs.observe(el));
  } else {
    els.forEach(el => el.classList.add('nl-visible'));
  }
})();
</script>