<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════
   ROOT & RESET
═══════════════════════════════════════ */
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

/* ═══════════════════════════════════════
   PAGE WRAPPER
═══════════════════════════════════════ */
.prj-page {
  font-family: 'Manrope', sans-serif;
  background: var(--off-white);
  color: var(--text-dark);
  -webkit-font-smoothing: antialiased;
  overflow-x: hidden;
}

/* ═══════════════════════════════════════
   HERO
═══════════════════════════════════════ */
.prj-hero {
  background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 35%, var(--pink) 70%, var(--pink-mid) 100%);
  padding: 110px 24px 90px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.prj-hero::before {
  content: '';
  position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 70% 60% at 10% 60%, rgba(242,194,0,.18) 0%, transparent 60%),
    radial-gradient(ellipse 50% 50% at 90% 15%, rgba(255,255,255,.09) 0%, transparent 55%),
    radial-gradient(ellipse 40% 40% at 50% 100%, rgba(0,0,0,.14) 0%, transparent 60%);
  pointer-events: none;
}
.prj-hero::after {
  content: '';
  position: absolute; inset: 0;
  background-image: radial-gradient(rgba(255,255,255,.06) 1px, transparent 1px);
  background-size: 24px 24px;
  pointer-events: none;
}
.prj-hero-circle-tl {
  position: absolute; top: -120px; left: -120px;
  width: 420px; height: 420px;
  border-radius: 50%;
  border: 60px solid rgba(255,255,255,.04);
  pointer-events: none;
}
.prj-hero-circle-br {
  position: absolute; bottom: -100px; right: -160px;
  width: 560px; height: 560px;
  border-radius: 50%;
  border: 70px solid rgba(242,194,0,.05);
  pointer-events: none;
}
.prj-hero-inner {
  position: relative; z-index: 2;
  max-width: 700px;
  margin: 0 auto;
}
.prj-hero h1 {
  font-weight: 800;
  font-size: clamp(2.4rem, 6vw, 4rem);
  color: var(--white);
  letter-spacing: -2px;
  line-height: 1.05;
  margin-bottom: 20px;
  text-shadow: 0 4px 40px rgba(0,0,0,.22);
}
.prj-hero h1 em {
  font-style: normal;
  color: var(--yellow);
  filter: drop-shadow(0 0 24px rgba(242,194,0,.30));
}
.prj-hero-desc {
  font-size: .95rem;
  font-family: 'Open Sans', sans-serif;
  color: rgba(255,255,255,.70);
  line-height: 1.9;
  max-width: 560px;
  margin: 0 auto;
}

/* ═══════════════════════════════════════
   PROJECTS SECTION
═══════════════════════════════════════ */
.prj-section {
  padding: 90px 24px 100px;
  max-width: 1180px;
  margin: 0 auto;
}

/* Section Header */
.prj-section-header {
  text-align: center;
  margin-bottom: 16px;
}
.prj-section-tag {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: .58rem;
  font-weight: 700;
  letter-spacing: 5px;
  text-transform: uppercase;
  color: var(--pink);
  margin-bottom: 12px;
  justify-content: center;
}
.prj-section-tag::before,
.prj-section-tag::after {
  content: '';
  display: block;
  width: 18px; height: 1px;
  background: var(--pink); opacity: .4;
}
.prj-section-title {
  font-weight: 800;
  font-size: clamp(2rem, 4.5vw, 2.8rem);
  color: var(--text-dark);
  letter-spacing: -1.5px;
  line-height: 1.1;
  margin-bottom: 14px;
}
.prj-section-title .accent { color: var(--pink); }
.prj-section-desc {
  font-size: .92rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-light);
  line-height: 1.85;
  max-width: 560px;
  margin: 0 auto;
}

/* ── TAB NAV ── */
.prj-tabs-wrap {
  display: flex;
  justify-content: center;
  margin-bottom: 56px;
  margin-top: 48px;
}
.prj-tabs {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
  background: var(--white);
  border: 1px solid rgba(169,45,99,.10);
  border-radius: 20px;
  padding: 10px;
  box-shadow: 0 4px 20px rgba(169,45,99,.08);
  max-width: 100%;
}
.prj-tab {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 22px;
  border-radius: 12px;
  font-family: 'Manrope', sans-serif;
  font-size: .78rem;
  font-weight: 700;
  color: var(--text-light);
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all .3s ease;
  letter-spacing: .3px;
  white-space: nowrap;
}
.prj-tab:hover {
  color: var(--pink);
  background: rgba(169,45,99,.06);
}
.prj-tab.active {
  background: linear-gradient(135deg, var(--pink-dark), var(--pink));
  color: var(--white);
  box-shadow: 0 6px 20px rgba(169,45,99,.30);
}
.prj-tab-icon { font-size: 1rem; line-height: 1; }

/* ── TAB PANELS ── */
.prj-panels { position: relative; }
.prj-panel { display: none; animation: prjFadeUp .5s ease both; }
.prj-panel.active { display: block; }

@keyframes prjFadeUp {
  from { opacity: 0; transform: translateY(24px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ── PROJECT CARD ── */
.prj-card {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
  background: var(--white);
  border-radius: 28px;
  overflow: hidden;
  box-shadow:
    0 0 0 1px rgba(169,45,99,.07),
    0 24px 60px rgba(169,45,99,.12);
}

/* Image Side */
.prj-card-img {
  position: relative;
  overflow: hidden;
  min-height: 520px;
}
.prj-card-img img {
  display: block;
  width: 100%; height: 100%;
  object-fit: cover;
  object-position: center;
  transition: transform .8s cubic-bezier(.22,.9,.36,1);
}
.prj-card:hover .prj-card-img img { transform: scale(1.05); }
.prj-card-img::after {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(26,8,16,.45) 0%, transparent 55%);
  pointer-events: none;
}
.prj-card-cat {
  position: absolute;
  top: 24px; left: 24px;
  z-index: 2;
  background: linear-gradient(135deg, var(--pink-dark), var(--pink));
  color: white;
  font-family: 'Manrope', sans-serif;
  font-size: .6rem;
  font-weight: 700;
  letter-spacing: 2px;
  text-transform: uppercase;
  padding: 8px 18px;
  border-radius: 100px;
  box-shadow: 0 4px 14px rgba(169,45,99,.35);
}
.prj-card-num {
  position: absolute;
  bottom: 20px; right: 24px;
  z-index: 2;
  font-family: 'Manrope', sans-serif;
  font-size: 5rem;
  font-weight: 800;
  color: rgba(255,255,255,.15);
  line-height: 1;
  letter-spacing: -4px;
  pointer-events: none;
  user-select: none;
}

/* Content Side */
.prj-card-body {
  padding: 52px 50px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative;
  overflow: hidden;
}
.prj-card-body::before {
  content: '';
  position: absolute;
  bottom: -80px; right: -80px;
  width: 280px; height: 280px;
  border-radius: 50%;
  background: radial-gradient(ellipse at center, rgba(169,45,99,.04) 0%, transparent 70%);
  pointer-events: none;
}

.prj-card-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: .55rem;
  font-weight: 700;
  letter-spacing: 4px;
  text-transform: uppercase;
  color: var(--pink);
  margin-bottom: 16px;
}
.prj-card-tag::before {
  content: '';
  display: block;
  width: 16px; height: 1.5px;
  background: var(--pink); opacity: .5;
}

.prj-card-title {
  font-weight: 800;
  font-size: clamp(1.8rem, 3.5vw, 2.5rem);
  color: var(--text-dark);
  letter-spacing: -1.5px;
  line-height: 1.08;
  margin-bottom: 18px;
}
.prj-card-title .accent { color: var(--pink); }

.prj-card-divider {
  width: 44px; height: 3px;
  background: linear-gradient(90deg, var(--yellow), var(--pink));
  border-radius: 2px;
  margin-bottom: 22px;
}

.prj-card-desc {
  font-size: .91rem;
  font-family: 'Open Sans', sans-serif;
  color: #665060;
  line-height: 1.9;
  margin-bottom: 28px;
}

/* Highlights */
.prj-card-highlights {
  display: flex;
  flex-direction: column;
  gap: 12px;
  margin-bottom: 36px;
}
.prj-card-highlight {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  font-size: .84rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-dark);
  line-height: 1.55;
}
.prj-card-highlight-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--yellow);
  flex-shrink: 0;
  margin-top: 5px;
  box-shadow: 0 0 8px rgba(242,194,0,.5);
}

/* CTA Button */
.prj-card-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: linear-gradient(135deg, var(--pink-dark), var(--pink));
  color: white;
  font-family: 'Manrope', sans-serif;
  font-size: .84rem;
  font-weight: 800;
  padding: 16px 36px;
  border-radius: 100px;
  text-decoration: none;
  transition: all .35s ease;
  box-shadow: 0 8px 24px rgba(169,45,99,.28);
  width: fit-content;
  position: relative;
  overflow: hidden;
  letter-spacing: .3px;
}
.prj-card-btn::before {
  content: '';
  position: absolute; top: 0; left: -100%;
  width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.20), transparent);
  transition: left .5s ease;
}
.prj-card-btn:hover::before { left: 100%; }
.prj-card-btn svg {
  width: 15px; height: 15px;
  stroke: currentColor; fill: none;
  stroke-width: 2.5;
  stroke-linecap: round; stroke-linejoin: round;
  transition: transform .3s;
}
.prj-card-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 16px 40px rgba(169,45,99,.38);
  text-decoration: none;
  color: white;
  background: linear-gradient(135deg, #6B0F35, var(--pink-dark));
}
.prj-card-btn:hover svg { transform: translateX(4px); }

/* External link note */
.prj-card-ext {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: .70rem;
  font-family: 'Open Sans', sans-serif;
  color: var(--text-light);
  margin-top: 14px;
  text-decoration: none;
}
.prj-card-ext svg {
  width: 12px; height: 12px;
  stroke: currentColor; fill: none;
  stroke-width: 2;
  stroke-linecap: round; stroke-linejoin: round;
}

/* ═══════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════ */
@media (max-width: 960px) {
  .prj-card { grid-template-columns: 1fr; }
  .prj-card-img { min-height: 340px; }
  .prj-card-body { padding: 40px 32px; }
}
@media (max-width: 600px) {
  .prj-hero { padding: 90px 20px 70px; }
  .prj-hero h1 { font-size: 2.2rem; }
  .prj-section { padding: 56px 16px 70px; }
  .prj-card-body { padding: 28px 20px; }
  .prj-card-img { min-height: 260px; }
  .prj-tabs { gap: 6px; padding: 8px; }
  .prj-tab { padding: 10px 12px; font-size: .70rem; }
  .prj-tab-icon { display: none; }
  .prj-card-title { font-size: 1.8rem; }
}
@media (max-width: 420px) {
  .prj-tab { padding: 8px 10px; font-size: .65rem; }
  .prj-tabs-wrap { overflow-x: auto; justify-content: flex-start; padding-bottom: 4px; }
  .prj-tabs { flex-wrap: nowrap; border-radius: 14px; }
}
</style>

<div class="prj-page">

<!-- ══════════════════════
     HERO
══════════════════════ -->
<section class="prj-hero">
  <div class="prj-hero-circle-tl"></div>
  <div class="prj-hero-circle-br"></div>
  <div class="prj-hero-inner">
    <h1>Our <em>Projects</em></h1>
    <p class="prj-hero-desc">
      Empowering underprivileged children through quality education, holistic care, and essential food support — transforming lives across Pakistan, one child at a time.
    </p>
  </div>
</section>

<!-- ══════════════════════
     PROJECTS
══════════════════════ -->
<div class="prj-section">

  <!-- Section Header -->
  <div class="prj-section-header">
    <div class="prj-section-tag">Our Programmes</div>
    <h2 class="prj-section-title">What We <span class="accent">Do</span></h2>
    <p class="prj-section-desc">
      Each initiative is a unique commitment to uplifting lives. Select a project below to learn more about our work and the communities we serve every day.
    </p>
  </div>

  <!-- Tab Navigation -->
  <div class="prj-tabs-wrap">
    <div class="prj-tabs" role="tablist">
      <button class="prj-tab active" role="tab" onclick="prjSwitch(event,'orphan')" data-panel="orphan">
        Parent the Orphan
      </button>
      <button class="prj-tab" role="tab" onclick="prjSwitch(event,'educate')" data-panel="educate">
        Educate Pakistan
      </button>
      <button class="prj-tab" role="tab" onclick="prjSwitch(event,'feed')" data-panel="feed">
       Feed the Hungry
      </button>
      <button class="prj-tab" role="tab" onclick="prjSwitch(event,'roti')" data-panel="roti">
        Izzat Ki Roti
      </button>
      <button class="prj-tab" role="tab" onclick="prjSwitch(event,'haven')" data-panel="haven">
     Compassionate Haven
      </button>
    </div>
  </div>

  <!-- ══ TAB PANELS ══ -->
  <div class="prj-panels">

    <!-- 1. PARENT THE ORPHAN -->
    <div class="prj-panel active" id="prj-orphan" role="tabpanel">
      <div class="prj-card">
        <div class="prj-card-img">
          <img src="https://almarah.org/wp-content/uploads/2026/03/Parent-the-Orphan.png"
               alt="Parent the Orphan — Almarah Foundation" loading="lazy">
          <div class="prj-card-cat">Flagship Project</div>
          <div class="prj-card-num">01</div>
        </div>
        <div class="prj-card-body">
          <div class="prj-card-tag">Child Welfare</div>
          <h3 class="prj-card-title">Parent the <span class="accent">Orphan</span></h3>
          <div class="prj-card-divider"></div>
          <p class="prj-card-desc">
            Almarah Foundation came into existence with a mission to work for the welfare of orphans, unattended, and homeless children. Through the <strong>Apna Ghar</strong> initiative, we provide internationally recognised standards of care — giving special attention to health, hygiene, mental health, education, sports, and entertainment. With 4.6 million orphaned children in Pakistan, our work addresses a critical national need.
          </p>
          <div class="prj-card-highlights">
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              275+ children currently cared for across 13 homes — both boys and girls
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              State-of-the-art homes in DHA and Cavalry Ground, Lahore — started in 2021
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Provides in-house facilities to orphaned children aged 3 to 13 years
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              One dedicated home for the Christian community
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Apna Ghar Children's Complex under construction at Central Park, Lahore
            </div>
          </div>
          <a href="https://almarah.org/parent-the-orphan/" target="_blank" rel="noopener noreferrer" class="prj-card-btn">
            Explore This Project
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <div class="prj-card-ext">
            <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Opens on almarah.org
          </div>
        </div>
      </div>
    </div>

    <!-- 2. EDUCATE PAKISTAN -->
    <div class="prj-panel" id="prj-educate" role="tabpanel">
      <div class="prj-card">
        <div class="prj-card-img">
          <img src="https://almarah.org/wp-content/uploads/2026/03/Educate-Pakistan.png"
               alt="Educate Pakistan — Almarah Foundation" loading="lazy">
          <div class="prj-card-cat">Active Project</div>
          <div class="prj-card-num">02</div>
        </div>
        <div class="prj-card-body">
          <div class="prj-card-tag">Education</div>
          <h3 class="prj-card-title">Educate <span class="accent">Pakistan</span></h3>
          <div class="prj-card-divider"></div>
          <p class="prj-card-desc">
            With 26 million children in Pakistan lacking access to education, the Almarah Foundation establishes affordable yet high-quality educational institutes for orphans and children from needy families. By offering high-standard education, we empower children from underprivileged backgrounds to break the cycle of poverty and become successful, contributing citizens.
          </p>
          <div class="prj-card-highlights">
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Almarah Grammar School — Playgroup through High School
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Located at 132/B Rajpoot Town, Canal Road, Lahore
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Education through public and private partnerships
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Gender-based inclusion — dedicated girls' homes and schools
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Skills training to foster growth into responsible members of society
            </div>
          </div>
          <a href="https://almarah.org/educate-pakistan/" target="_blank" rel="noopener noreferrer" class="prj-card-btn">
            Explore This Project
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <div class="prj-card-ext">
            <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Opens on almarah.org
          </div>
        </div>
      </div>
    </div>

    <!-- 3. FEED THE HUNGRY -->
    <div class="prj-panel" id="prj-feed" role="tabpanel">
      <div class="prj-card">
        <div class="prj-card-img">
          <img src="https://almarah.org/wp-content/uploads/2026/03/Feed-the-Hungry-1.png"
               alt="Feed the Hungry — Almarah Foundation" loading="lazy">
          <div class="prj-card-cat">Active Project</div>
          <div class="prj-card-num">03</div>
        </div>
        <div class="prj-card-body">
          <div class="prj-card-tag">Food Security</div>
          <h3 class="prj-card-title">Feed the <span class="accent">Hungry</span></h3>
          <div class="prj-card-divider"></div>
          <p class="prj-card-desc">
            In Pakistan, nearly 7.5 million people are experiencing high levels of acute food insecurity. The Almarah Foundation provides daily meals to over 1,500+ individuals from lower-income sectors. By addressing the basic need for food, the foundation alleviates hunger and supports the overall well-being of these individuals and their families — a legacy that began in 1995 with our chairperson's father.
          </p>
          <div class="prj-card-highlights">
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Daily meals provided to 1,500+ individuals from lower-income sectors
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Monthly food rations currently provided to 70+ families
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Financial assistance provided to 60+ individuals
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Orphan Family Support — vital aid so children can stay with their families
            </div>
          </div>
          <a href="https://almarah.org/feed-the-hungry-2/" target="_blank" rel="noopener noreferrer" class="prj-card-btn">
            Explore This Project
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <div class="prj-card-ext">
            <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Opens on almarah.org
          </div>
        </div>
      </div>
    </div>

    <!-- 4. IZZAT KI ROTI -->
    <div class="prj-panel" id="prj-roti" role="tabpanel">
      <div class="prj-card">
        <div class="prj-card-img">
          <img src="https://almarah.org/wp-content/uploads/2026/05/WhatsApp-Image-2026-05-13-at-2.22.22-PM-2-copy-scaled.jpg"
               alt="Izzat Ki Roti — Almarah Foundation" loading="lazy">
          <div class="prj-card-cat">Active Project</div>
          <div class="prj-card-num">04</div>
        </div>
        <div class="prj-card-body">
          <div class="prj-card-tag">Dignity &amp; Food</div>
          <h3 class="prj-card-title">Izzat Ki <span class="accent">Roti</span></h3>
          <div class="prj-card-divider"></div>
          <p class="prj-card-desc">
            <em>Izzat Ki Roti</em> — "Bread with Dignity" — is a unique and deeply meaningful initiative designed to feed the hungry while preserving their self-respect and honour. We believe receiving food should never come with shame. This programme provides nutritious meals in a dignified, respectful environment for those who cannot afford them, upholding the dignity that every human being deserves.
          </p>
          <div class="prj-card-highlights">
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Meals served with full dignity, respect, and compassion
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Discreet support that preserves family honour and self-respect
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Community-based food distribution across underserved areas
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Rooted in the foundation's 1995 legacy of food distribution
            </div>
          </div>
          <a href="https://almarah.org/izat-ki-roti/" target="_blank" rel="noopener noreferrer" class="prj-card-btn">
            Explore This Project
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <div class="prj-card-ext">
            <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Opens on almarah.org
          </div>
        </div>
      </div>
    </div>

    <!-- 5. COMPASSIONATE HAVEN -->
    <div class="prj-panel" id="prj-haven" role="tabpanel">
      <div class="prj-card">
        <div class="prj-card-img">
          <img src="https://almarah.org/wp-content/uploads/2026/05/comp.jpeg"
               alt="Compassionate Haven — Almarah Foundation" loading="lazy">
          <div class="prj-card-cat">Sister Initiative</div>
          <div class="prj-card-num">05</div>
        </div>
        <div class="prj-card-body">
          <div class="prj-card-tag">Special Needs &amp; Care</div>
          <h3 class="prj-card-title">Compassionate <span class="accent">Haven</span></h3>
          <div class="prj-card-divider"></div>
          <p class="prj-card-desc">
            Almarah Foundation has proudly expanded its mission to include a dedicated home for children with special needs — the <strong>Institute for the Development of Special Children</strong>. This initiative offers a nurturing environment where children with physical, cognitive, and developmental challenges receive personalized care. Our facility features sensory-friendly spaces, therapy rooms, and specialized learning tools designed to help each child thrive.
          </p>
          <div class="prj-card-highlights">
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Dedicated home for children with physical, cognitive &amp; developmental challenges
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Sensory-friendly spaces, therapy rooms &amp; specialized learning tools
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Trained educators, therapists, and caregivers focused on each child's strengths
            </div>
            <div class="prj-card-highlight">
              <div class="prj-card-highlight-dot"></div>
              Tailored educational and developmental programmes for every child
            </div>
          </div>
          <a href="https://www.almarahhaven.org/" target="_blank" rel="noopener noreferrer" class="prj-card-btn">
            Explore This Project
            <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <div class="prj-card-ext">
            <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Opens on almarahhaven.org
          </div>
        </div>
      </div>
    </div>

  </div><!-- /.prj-panels -->
</div><!-- /.prj-section -->
</div><!-- /.prj-page -->

<script>
function prjSwitch(e, panel) {
  if (e) e.preventDefault();

  /* Deactivate all */
  document.querySelectorAll('.prj-tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.prj-panel').forEach(p => p.classList.remove('active'));

  /* Activate selected */
  document.getElementById('prj-' + panel).classList.add('active');

  /* Activate correct tab button */
  document.querySelectorAll('.prj-tab').forEach(t => {
    if (t.dataset.panel === panel) t.classList.add('active');
  });

  /* Smooth scroll to panel on mobile */
  if (window.innerWidth < 768) {
    setTimeout(() => {
      document.getElementById('prj-' + panel).scrollIntoView({
        behavior: 'smooth', block: 'start'
      });
    }, 100);
  }
}
</script>