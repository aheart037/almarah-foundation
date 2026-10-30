
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
*, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

:root {
  --pink:      #A92D63;
  --pink-dark: #8A2050;
  --yellow:    #F2C200;
  --white:     #FFFFFF;
  --off-white: #FDF5F8;
  --cream:     #FAF0F4;
  --text-dark: #1A0810;
  --text-mid:  #5A2040;
  --text-light:#9A6080;
}

body {
  font-family: 'Manrope', sans-serif;
  background: var(--off-white);
  color: var(--text-dark);
  -webkit-font-smoothing: antialiased;
}

.vl-wrap {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 24px;
}

.vl-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: .55rem;
  font-weight: 700;
  letter-spacing: 4px;
  text-transform: uppercase;
  color: var(--pink);
  margin-bottom: 12px;
}
.vl-tag::before, .vl-tag::after {
  content: '';
  width: 16px; height: 1px;
  background: var(--pink); opacity: .45;
}
.vl-section-title {
  font-size: clamp(1.6rem, 3.5vw, 2.6rem);
  font-weight: 800;
  letter-spacing: -1px;
  line-height: 1.15;
  margin-bottom: 14px;
  color: var(--text-dark);
}
.vl-section-title span { color: var(--pink); }
.vl-section-desc {
  font-size: .94rem;
  color: var(--text-mid);
  line-height: 1.85;
}

/* HERO */
.vl-hero {
  background: linear-gradient(135deg, #6B0F35, var(--pink-dark), var(--pink));
  padding: 130px 24px 110px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.vl-hero::before {
  content: '';
  position: absolute; inset: 0;
  background: radial-gradient(ellipse 60% 60% at 10% 60%, rgba(242,194,0,.15) 0%, transparent 60%);
  pointer-events: none;
}
.vl-hero-inner {
  position: relative; z-index: 1;
  max-width: 760px; margin: 0 auto;
}
.vl-hero h1 {
  font-size: clamp(2.2rem, 6vw, 4.2rem);
  font-weight: 800;
  color: #fff;
  letter-spacing: -2px;
  line-height: 1.08;
  margin-bottom: 20px;
}
.vl-hero h1 span { color: var(--yellow); }
.vl-hero p {
  font-size: 1rem;
  color: rgba(255,255,255,.70);
  line-height: 1.80;
  max-width: 580px;
  margin: 0 auto 36px;
}
.vl-hero-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 15px 32px;
  border-radius: 100px;
  background: var(--yellow);
  color: var(--text-dark);
  font-weight: 800;
  font-size: .82rem;
  letter-spacing: .5px;
  text-decoration: none;
  transition: transform .25s, box-shadow .25s;
  box-shadow: 0 8px 24px rgba(242,194,0,.35);
}
.vl-hero-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 32px rgba(242,194,0,.45);
}

/* INTRO */
.vl-intro {
  padding: 90px 0;
  background: var(--white);
}
.vl-intro-inner {
  display: grid;
  gap: 60px;
  align-items: center;
}
@media (min-width: 860px) {
  .vl-intro-inner { grid-template-columns: 1fr 1fr; }
}
.vl-intro-img {
  border-radius: 20px;
  overflow: hidden;
  position: relative;
  box-shadow: 0 16px 48px rgba(169,45,99,.13);
}
.vl-intro-img::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow));
  z-index: 2;
}
.vl-intro-img img {
  width: 100%;
  aspect-ratio: 4/3;
  object-fit: cover;
  display: block;
}
.vl-intro-text .vl-section-desc { margin-bottom: 16px; }
.vl-intro-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-top: 10px;
  padding: 13px 28px;
  border-radius: 100px;
  background: linear-gradient(135deg, var(--pink-dark), var(--pink));
  color: #fff;
  font-size: .78rem;
  font-weight: 800;
  letter-spacing: .5px;
  text-decoration: none;
  box-shadow: 0 8px 22px rgba(169,45,99,.28);
  transition: transform .25s, box-shadow .25s;
}
.vl-intro-btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 32px rgba(169,45,99,.38);
}

/* WHY VOLUNTEER */
.vl-why {
  padding: 90px 0;
  background: var(--off-white);
}
.vl-why-header {
  text-align: center;
  margin-bottom: 52px;
}
.vl-why-header .vl-section-desc {
  max-width: 520px;
  margin: 0 auto;
}
.vl-why-grid {
  display: grid;
  gap: 20px;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
}
.vl-why-card {
  background: var(--white);
  border-radius: 18px;
  padding: 32px 26px;
  border: 1.5px solid rgba(169,45,99,.08);
  transition: transform .3s, box-shadow .3s;
  position: relative;
  overflow: hidden;
}
.vl-why-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow));
}
.vl-why-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 20px 48px rgba(169,45,99,.11);
}
.vl-why-num {
  font-size: 2.8rem;
  font-weight: 800;
  color: rgba(169,45,99,.08);
  letter-spacing: -2px;
  line-height: 1;
  margin-bottom: 8px;
}
.vl-why-card h3 {
  font-size: .98rem;
  font-weight: 800;
  color: var(--text-dark);
  margin-bottom: 10px;
}
.vl-why-card p {
  font-size: .87rem;
  color: var(--text-mid);
  line-height: 1.78;
}

/* WAYS TO HELP */
.vl-ways {
  padding: 90px 0;
  background: var(--cream);
}
.vl-ways-header {
  text-align: center;
  margin-bottom: 52px;
}
.vl-ways-grid {
  display: grid;
  gap: 18px;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
}
.vl-ways-item {
  background: var(--white);
  border-radius: 16px;
  padding: 28px 26px;
  display: flex;
  gap: 18px;
  align-items: flex-start;
  border: 1.5px solid rgba(169,45,99,.07);
  transition: transform .3s, box-shadow .3s;
}
.vl-ways-item:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 40px rgba(169,45,99,.10);
}
.vl-ways-icon {
  width: 46px; height: 46px;
  min-width: 46px;
  background: linear-gradient(135deg, var(--pink-dark), var(--pink));
  border-radius: 13px;
  display: flex; align-items: center; justify-content: center;
}
.vl-ways-icon svg {
  width: 20px; height: 20px;
  stroke: #fff; fill: none;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
}
.vl-ways-content h3 {
  font-size: .96rem;
  font-weight: 800;
  color: var(--text-dark);
  margin-bottom: 7px;
}
.vl-ways-content p {
  font-size: .86rem;
  color: var(--text-mid);
  line-height: 1.75;
}

/* WHO CAN VOLUNTEER */
.vl-who {
  padding: 90px 0;
  background: var(--white);
}
.vl-who-inner {
  display: grid;
  gap: 60px;
  align-items: center;
}
@media (min-width: 860px) {
  .vl-who-inner { grid-template-columns: 1fr 1fr; }
}
.vl-who-img {
  border-radius: 20px;
  overflow: hidden;
  position: relative;
  box-shadow: 0 16px 48px rgba(169,45,99,.13);
}
.vl-who-img::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow));
  z-index: 2;
}
.vl-who-img img {
  width: 100%;
  aspect-ratio: 4/3;
  object-fit: cover;
  display: block;
}
.vl-who-text .vl-section-desc { margin-bottom: 24px; }
.vl-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}
.vl-chip {
  padding: 8px 18px;
  border-radius: 100px;
  background: rgba(169,45,99,.07);
  border: 1.5px solid rgba(169,45,99,.13);
  font-size: .78rem;
  font-weight: 700;
  color: var(--pink);
}

/* STEPS */
.vl-steps {
  padding: 90px 0;
  background: var(--off-white);
}
.vl-steps-header {
  text-align: center;
  margin-bottom: 52px;
}
.vl-steps-grid {
  display: grid;
  gap: 22px;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
}
.vl-step {
  text-align: center;
  padding: 40px 24px;
  background: var(--white);
  border-radius: 18px;
  border: 1.5px solid rgba(169,45,99,.08);
  transition: transform .3s, box-shadow .3s;
}
.vl-step:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 44px rgba(169,45,99,.11);
}
.vl-step-num {
  width: 62px; height: 62px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--pink-dark), var(--pink));
  color: #fff;
  font-size: 1.5rem;
  font-weight: 800;
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 22px;
  box-shadow: 0 8px 22px rgba(169,45,99,.28);
}
.vl-step h3 {
  font-size: 1rem;
  font-weight: 800;
  color: var(--text-dark);
  margin-bottom: 10px;
}
.vl-step p {
  font-size: .87rem;
  color: var(--text-mid);
  line-height: 1.75;
}

/* FORM */
.vl-form-section {
  padding: 90px 0;
  background: var(--cream);
}
.vl-form-inner {
  display: grid;
  gap: 56px;
  align-items: start;
}
@media (min-width: 900px) {
  .vl-form-inner { grid-template-columns: 1fr 1.5fr; }
}
.vl-form-left .vl-section-desc {
  margin-top: 12px;
  margin-bottom: 28px;
}
.vl-form-perks {
  list-style: none;
  display: flex;
  flex-direction: column;
  gap: 13px;
}
.vl-form-perks li {
  display: flex;
  align-items: flex-start;
  gap: 11px;
  font-size: .88rem;
  color: var(--text-mid);
  line-height: 1.65;
}
.vl-form-perks li::before {
  content: '';
  width: 7px; height: 7px; min-width: 7px;
  background: var(--pink);
  border-radius: 50%;
  margin-top: 6px;
  flex-shrink: 0;
}
.vl-form-box {
  background: var(--white);
  border-radius: 22px;
  padding: 48px 44px;
  box-shadow: 0 4px 32px rgba(169,45,99,.09);
  border: 1.5px solid rgba(169,45,99,.08);
  position: relative;
  overflow: hidden;
}
.vl-form-box::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow));
}
@media (max-width: 600px) {
  .vl-form-box { padding: 32px 20px; }
}
.vl-form-box h3 {
  font-size: 1.4rem;
  font-weight: 800;
  color: var(--text-dark);
  letter-spacing: -.4px;
  margin-bottom: 6px;
}
.vl-form-box > p {
  font-size: .88rem;
  color: var(--text-light);
  margin-bottom: 28px;
  line-height: 1.70;
}

/* CTA BANNER */
.vl-cta {
  background: linear-gradient(135deg, #6B0F35, var(--pink-dark), var(--pink));
  padding: 90px 24px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.vl-cta::before {
  content: '';
  position: absolute; inset: 0;
  background: radial-gradient(ellipse 60% 60% at 90% 40%, rgba(242,194,0,.12) 0%, transparent 60%);
  pointer-events: none;
}
.vl-cta-inner {
  position: relative; z-index: 1;
  max-width: 640px; margin: 0 auto;
}
.vl-cta h2 {
  font-size: clamp(1.8rem, 4vw, 3rem);
  font-weight: 800;
  color: #fff;
  letter-spacing: -1px;
  line-height: 1.1;
  margin-bottom: 14px;
}
.vl-cta h2 span { color: var(--yellow); }
.vl-cta p {
  font-size: .96rem;
  color: rgba(255,255,255,.65);
  line-height: 1.78;
  margin-bottom: 32px;
}
.vl-cta-btns {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  justify-content: center;
}
.vl-cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 30px;
  border-radius: 100px;
  font-size: .80rem;
  font-weight: 800;
  letter-spacing: .5px;
  text-decoration: none;
  transition: transform .25s, box-shadow .25s;
}
.vl-cta-btn.yellow {
  background: var(--yellow);
  color: var(--text-dark);
  box-shadow: 0 8px 24px rgba(242,194,0,.35);
}
.vl-cta-btn.yellow:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 32px rgba(242,194,0,.45);
}
.vl-cta-btn.ghost {
  background: rgba(255,255,255,.10);
  color: rgba(255,255,255,.88);
  border: 1.5px solid rgba(255,255,255,.20);
  backdrop-filter: blur(8px);
}
.vl-cta-btn.ghost:hover {
  background: rgba(255,255,255,.18);
  transform: translateY(-3px);
}
</style>

<!-- HERO -->
<section class="vl-hero">
  <div class="vl-hero-inner">
    <h1>Give Your Time,<br><span>Change a Life</span></h1>
    <p>At Almarah Foundation, our volunteers are the backbone of everything we do. From feeding hundreds of families to mentoring children at our care homes, your time and effort create real, lasting change for those who need it most.</p>
    <a href="#volunteer-form" class="vl-hero-btn">Join as a Volunteer</a>
  </div>
</section>

<!-- INTRO -->
<section class="vl-intro">
  <div class="vl-wrap">
    <div class="vl-intro-inner">
      <div class="vl-intro-img">
        <img src="https://almarah.org/wp-content/uploads/2026/04/514345247_738404035846951_4585600570098139203_n-e1775071405969.jpg" alt="Volunteers at Almarah Foundation">
      </div>
      <div class="vl-intro-text">
        <div class="vl-tag">Who We Need</div>
        <h2 class="vl-section-title">You Can Make a <span>Difference</span></h2>
        <p class="vl-section-desc">Every day, our volunteers show up with a simple goal in mind, to make someone's life a little better. Whether you spend a few hours each week teaching children, helping distribute meals, or supporting our events, the impact you create is far greater than you think.</p>
        <p class="vl-section-desc">Almarah Foundation has been serving vulnerable communities across Pakistan since 1995. Today, we care for over 275 children across 13 homes in Lahore and serve more than 1,500 meals every single day. None of this would be possible without our incredible team of volunteers.</p>
        <a href="#volunteer-form" class="vl-intro-btn">Get Started Today</a>
      </div>
    </div>
  </div>
</section>

<!-- WHY VOLUNTEER -->
<section class="vl-why">
  <div class="vl-wrap">
    <div class="vl-why-header">
      <div class="vl-tag">Why Volunteer With Us</div>
      <h2 class="vl-section-title">What You <span>Gain</span></h2>
      <p class="vl-section-desc">Volunteering with Almarah Foundation is as rewarding for you as it is for the children and families we serve together.</p>
    </div>
    <div class="vl-why-grid">
      <div class="vl-why-card">
        <div class="vl-why-num">01</div>
        <h3>See Real Change Happen</h3>
        <p>You will witness the direct impact of your efforts. From a child learning to read for the first time to a family receiving a warm meal, the results of your work are visible and deeply meaningful.</p>
      </div>
      <div class="vl-why-card">
        <div class="vl-why-num">02</div>
        <h3>Grow as a Person</h3>
        <p>Volunteering builds empathy, patience, leadership, and communication skills. Working alongside our teams will help you grow in ways no classroom or office ever could.</p>
      </div>
      <div class="vl-why-card">
        <div class="vl-why-num">03</div>
        <h3>Be Part of Something Bigger</h3>
        <p>When you volunteer with us, you join a family of passionate individuals from all backgrounds who share one common goal: making Pakistan a better place for its most vulnerable citizens.</p>
      </div>
      <div class="vl-why-card">
        <div class="vl-why-num">04</div>
        <h3>Flexible and Fulfilling</h3>
        <p>We understand you have a life outside of volunteering. That is why we offer flexible time commitments so you can contribute at a pace that works for you, without any pressure.</p>
      </div>
    </div>
  </div>
</section>

<!-- WAYS TO HELP -->
<section class="vl-ways">
  <div class="vl-wrap">
    <div class="vl-ways-header">
      <div class="vl-tag">Volunteer Roles</div>
      <h2 class="vl-section-title">Ways You Can <span>Help</span></h2>
    </div>
    <div class="vl-ways-grid">
      <div class="vl-ways-item">
        <div class="vl-ways-icon">
          <svg viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        </div>
        <div class="vl-ways-content">
          <h3>Teaching and Mentorship</h3>
          <p>Support children at Almarah Grammar School with their studies. Share your knowledge and help guide young minds toward a future full of opportunity and confidence.</p>
        </div>
      </div>
      <div class="vl-ways-item">
        <div class="vl-ways-icon">
          <svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
        </div>
        <div class="vl-ways-content">
          <h3>Health and Well-being</h3>
          <p>Help organise medical camps, health screenings, and mental health awareness sessions for children and families at our care homes and community outreach programs.</p>
        </div>
      </div>
      <div class="vl-ways-item">
        <div class="vl-ways-icon">
          <svg viewBox="0 0 24 24"><path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
        </div>
        <div class="vl-ways-content">
          <h3>Food Distribution</h3>
          <p>Join our daily meal distribution teams and help us serve over 1,500 meals every day to individuals and families who depend on us for their most basic need.</p>
        </div>
      </div>
      <div class="vl-ways-item">
        <div class="vl-ways-icon">
          <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="vl-ways-content">
          <h3>Fundraising Support</h3>
          <p>Help us raise the funds needed to keep our programs running and growing. From online campaigns to community drives, your energy and creativity can open doors for many more families.</p>
        </div>
      </div>
      <div class="vl-ways-item">
        <div class="vl-ways-icon">
          <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="vl-ways-content">
          <h3>Event Management</h3>
          <p>Put your organisational skills to work by helping plan and run our charity events, awareness drives, and community gatherings that bring people together for a shared purpose.</p>
        </div>
      </div>
      <div class="vl-ways-item">
        <div class="vl-ways-icon">
          <svg viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
        </div>
        <div class="vl-ways-content">
          <h3>Social Media and Admin</h3>
          <p>Help spread the word about our work through social media, content creation, and administrative support so more people can discover and support the Almarah Foundation mission.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- WHO CAN VOLUNTEER -->
<section class="vl-who">
  <div class="vl-wrap">
    <div class="vl-who-inner">
      <div class="vl-who-text">
        <div class="vl-tag">Open to Everyone</div>
        <h2 class="vl-section-title">Who Can <span>Volunteer?</span></h2>
        <p class="vl-section-desc">We do not believe in barriers. If you have a willing heart and a few spare hours, you are welcome here. Our volunteers come from all walks of life and bring with them a wonderful mix of skills, experiences, and perspectives that make our work richer and more effective.</p>
        <p class="vl-section-desc" style="margin-top:12px;">No prior experience is required for most roles. All we ask is that you come with sincerity, respect for every child and family we serve, and a genuine desire to contribute to something greater than yourself.</p>
        <div class="vl-chips" style="margin-top:24px;">
          <span class="vl-chip">Students</span>
          <span class="vl-chip">Working Professionals</span>
          <span class="vl-chip">Retirees</span>
          <span class="vl-chip">Homemakers</span>
          <span class="vl-chip">Corporate Teams</span>
          <span class="vl-chip">International Volunteers</span>
        </div>
      </div>
      <div class="vl-who-img">
        <img src="https://almarah.org/wp-content/uploads/2026/04/558493899_756282497392438_8291981798387155468_n.jpg" alt="Almarah Foundation Volunteers">
      </div>
    </div>
  </div>
</section>

<!-- STEPS -->
<section class="vl-steps">
  <div class="vl-wrap">
    <div class="vl-steps-header">
      <div class="vl-tag">How It Works</div>
      <h2 class="vl-section-title">Joining Us is <span>Simple</span></h2>
    </div>
    <div class="vl-steps-grid">
      <div class="vl-step">
        <div class="vl-step-num">1</div>
        <h3>Submit Your Application</h3>
        <p>Fill out our volunteer registration form below. Tell us a little about yourself, your skills, and which areas you would like to contribute to.</p>
      </div>
      <div class="vl-step">
        <div class="vl-step-num">2</div>
        <h3>Attend an Orientation</h3>
        <p>We will invite you to a short orientation session where you can learn about our mission, meet the team, and understand your role before you begin.</p>
      </div>
      <div class="vl-step">
        <div class="vl-step-num">3</div>
        <h3>Start Making a Difference</h3>
        <p>Once you are onboard, you can start contributing right away. Our team will be with you every step of the way to guide and support you throughout your journey.</p>
      </div>
    </div>
  </div>
</section>

<!-- FORM -->
<section class="vl-form-section" id="volunteer-form">
  <div class="vl-wrap">
    <div class="vl-form-inner">
      <div class="vl-form-left">
        <div class="vl-tag">Register Now</div>
        <h2 class="vl-section-title">Ready to <span>Get Started?</span></h2>
        <p class="vl-section-desc">Fill out the form and one of our volunteer coordinators will reach out to you within 48 hours to confirm your registration and walk you through the next steps.</p>
        <ul class="vl-form-perks">
          <li>Your role will be matched to your skills and interests</li>
          <li>Flexible time commitments that fit your schedule</li>
          <li>Full onboarding and support from our dedicated team</li>
          <li>A certificate of volunteering upon successful completion</li>
          <li>An opportunity to be part of a UN-recognised foundation</li>
          <li>Monthly volunteer meetups to connect and share experiences</li>
        </ul>
      </div>
      <div class="vl-form-box">
        <h3>Volunteer Registration</h3>
        <p>We are excited to have you on board. Please complete all fields so we can find the best role for you.</p>
        [forminator_form id="1204"]
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="vl-cta">
  <div class="vl-cta-inner">
    <h2>Together We Can Do <span>So Much More</span></h2>
    <p>Every hour you give is an hour of hope for a child who needs it. Join the Almarah Foundation volunteer family today and help us build a kinder, more compassionate Pakistan for the next generation.</p>
    <div class="vl-cta-btns">
      <a href="#volunteer-form" class="vl-cta-btn yellow">Become a Volunteer</a>
      <a href="https://almarah.org/donate/" class="vl-cta-btn ghost">Support With a Donation</a>
    </div>
  </div>
</section>