<?php
/**
 * Migrated page body: projects (source: almarah-foundation/projects.php).
 * Markup preserved from the original site; styles live in assets/css/pages/projects.css.
 */
?>
<div class="prj-page">

<!-- ══════════════════════
     HERO
══════════════════════ -->
<section class="prj-hero">
  <div class="prj-hero-circle-tl"></div>
  <div class="prj-hero-circle-br"></div>
  <div class="prj-hero-inner">
    <span class="hx-eyebrow">What We Do</span>
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
          <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/homepage/Parent-the-Orphan.jpg')) ?>"
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
          <a href="<?= e(base_url('parent-the-orphan')) ?>" target="_blank" rel="noopener noreferrer" class="prj-card-btn">
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
          <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/homepage/Educate-Pakistan.jpg')) ?>"
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
          <a href="<?= e(base_url('educate-pakistan')) ?>" target="_blank" rel="noopener noreferrer" class="prj-card-btn">
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
          <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/homepage/Feed-the-Hungry-1.jpg')) ?>"
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
          <a href="<?= e(base_url('feed-the-hungry-2')) ?>" target="_blank" rel="noopener noreferrer" class="prj-card-btn">
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
          <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/projects/WhatsApp-Image-2026-05-13-at-2.22.22-PM-2-copy-scaled.jpg')) ?>"
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
          <a href="<?= e(base_url('izat-ki-roti')) ?>" target="_blank" rel="noopener noreferrer" class="prj-card-btn">
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
          <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/homepage/comp.jpeg')) ?>"
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
