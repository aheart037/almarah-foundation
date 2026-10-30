<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════
   ABOUT SECTION
═══════════════════════════════════════ */
.hp-about {
    padding: 100px 24px;
    background-color: #FDF5F8;
    overflow: hidden;
    position: relative;
    font-family: 'Manrope', sans-serif;
}

/* Background soft orbs */
.hp-about::before {
    content: '';
    position: absolute;
    top: -150px; right: -150px;
    width: 500px; height: 500px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(169,45,99,.04) 0%, transparent 70%);
    pointer-events: none;
}
.hp-about::after {
    content: '';
    position: absolute;
    bottom: -100px; left: -100px;
    width: 350px; height: 350px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(242,194,0,.05) 0%, transparent 70%);
    pointer-events: none;
}

.hp-about-container {
    max-width: 100%;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: center;
}

/* ═══════════════════════
   IMAGE — FLOATING STYLE
═══════════════════════ */
.hp-about-img-wrap {
    position: relative;
    z-index: 1;
    padding: 30px 30px 30px 0;
}

/* Back yellow tilted card */
.hp-about-img-wrap::before {
    content: '';
    position: absolute;
    top: 0; left: 20px;
    width: 85%; height: 96%;
    background: linear-gradient(135deg, #F2C200, #FFD740);
    border-radius: 28px;
    z-index: 0;
    transform: rotate(-4deg);
    box-shadow: 0 10px 30px rgba(242,194,0,.25);
}

/* Second pink tilted card */
.hp-about-img-wrap::after {
    content: '';
    position: absolute;
    top: 10px; left: 10px;
    width: 85%; height: 96%;
    background: linear-gradient(135deg, #A92D63, #C4386F);
    border-radius: 28px;
    z-index: 0;
    transform: rotate(-2deg);
    box-shadow: 0 10px 30px rgba(169,45,99,.20);
}

/* Image itself floats */
.hp-about-img-main {
    position: relative;
    z-index: 2;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 30px 70px rgba(169,45,99,.18);
    animation: hpImgFloat 5s ease-in-out infinite;
    will-change: transform;
}

@keyframes hpImgFloat {
    0%, 100% { transform: translateY(0px); }
    50%       { transform: translateY(-14px); }
}

.hp-about-img-main img {
    display: block;
    width: 100%;
    height: 540px;
    object-fit: cover;
    object-position: center top;
}

/* ═══════════════════════
   CONTENT
═══════════════════════ */
.hp-about-content {}

/* Small eyebrow tag */
.hp-about-eyebrow {
    font-size: 0.60rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 5px;
    color: #A92D63;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}
.hp-about-eyebrow::before {
    content: '';
    width: 36px; height: 1.5px;
    background: #A92D63;
    opacity: .5;
}

/* ── MAIN HEADING: Large, heavy, tight ── */
.hp-about h2 {
    font-family: 'Manrope', sans-serif;
    font-size: clamp(2.2rem, 4.5vw, 3.2rem);   /* Bigger than before */
    font-weight: 800;                             /* Maximum bold */
    line-height: 1.08;
    color: #1A0810;
    margin-bottom: 22px;
    letter-spacing: -2px;
}
.hp-about h2 em {
    font-style: normal;
    color: #A92D63;
}

/* Gradient divider line */
.hp-about-divider {
    width: 48px; height: 3px;
    background: linear-gradient(90deg, #F2C200, #A92D63);
    border-radius: 2px;
    margin-bottom: 28px;
}

/* ── BODY TEXT: Larger, more readable ── */
.hp-about-text {
    font-family: 'Open Sans', sans-serif;
    font-size: 1.08rem;         /* Was .93rem — now matches screenshot */
    line-height: 1.85;          /* Comfortable reading rhythm */
    color: #3D1A2A;             /* Slightly darker for better contrast */
    margin-bottom: 40px;
    font-weight: 400;
}

/* ═══════════════════════
   VISION & MISSION CARDS
═══════════════════════ */
.hp-about-v-m {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 40px;
}

.hp-about-card {
    background: #fff;
    padding: 30px 28px;
    border-radius: 20px;
    border: 1px solid rgba(169,45,99,.08);
    transition: all .35s cubic-bezier(.22,.9,.36,1);
    position: relative;
    overflow: hidden;
    cursor: default;
}
.hp-about-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #F2C200, #A92D63);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform .4s ease;
}
.hp-about-card:hover::before { transform: scaleX(1); }
.hp-about-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 40px rgba(169,45,99,.10);
    border-color: rgba(169,45,99,.16);
}

.hp-about-card-icon {
    width: 48px; height: 48px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    transition: all .35s ease;
}
.hp-about-card-icon svg {
    width: 22px; height: 22px;
    stroke: currentColor; fill: none;
    stroke-width: 2;
    stroke-linecap: round; stroke-linejoin: round;
}

/* ── Card Heading: Bold and clear ── */
.hp-about-card h4 {
    font-family: 'Manrope', sans-serif;
    font-size: 1.05rem;          /* Was .95rem — larger */
    font-weight: 800;             /* Maximum bold */
    color: #1A0810;
    margin-bottom: 10px;
    letter-spacing: -.3px;
}

/* ── Card Body Text: Larger, easier to read ── */
.hp-about-card p {
    font-family: 'Open Sans', sans-serif;
    font-size: .92rem;           /* Was .80rem — much larger */
    line-height: 1.75;
    color: #7A4060;
    font-weight: 400;
}

/* ═══════════════════════
   CTA BUTTON
═══════════════════════ */
.hp-about-cta {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #8A2050, #A92D63);
    color: white;
    font-family: 'Manrope', sans-serif;
    font-size: .92rem;           /* Slightly larger button text */
    font-weight: 800;
    padding: 17px 40px;
    border-radius: 100px;
    text-decoration: none;
    transition: all .35s ease;
    box-shadow: 0 8px 24px rgba(169,45,99,.28);
    position: relative;
    overflow: hidden;
    letter-spacing: .3px;
}
.hp-about-cta::before {
    content: '';
    position: absolute; top: 0; left: -100%;
    width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.20), transparent);
    transition: left .5s ease;
}
.hp-about-cta:hover::before { left: 100%; }
.hp-about-cta svg {
    width: 15px; height: 15px;
    stroke: currentColor; fill: none;
    stroke-width: 2.5;
    stroke-linecap: round; stroke-linejoin: round;
    transition: transform .3s;
}
.hp-about-cta:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 40px rgba(169,45,99,.38);
    text-decoration: none;
    color: white;
}
.hp-about-cta:hover svg { transform: translateX(4px); }

/* ═══════════════════════
   RESPONSIVE
═══════════════════════ */
@media (max-width: 1024px) {
    .hp-about-container {
        grid-template-columns: 1fr;
        gap: 80px;
    }
    .hp-about-img-wrap {
        max-width: 540px;
        margin: 0 auto;
    }
}
@media (max-width: 768px) {
    .hp-about-text {
        font-size: 1.05rem;     /* Slightly smaller on tablet but still large */
    }
    .hp-about-card p {
        font-size: .90rem;
    }
}
@media (max-width: 600px) {
    .hp-about { padding: 70px 20px; }
    .hp-about h2 {
        font-size: 2.1rem;
        letter-spacing: -1px;
    }
    .hp-about-text {
        font-size: 1rem;         /* Still comfortable on mobile */
    }
    .hp-about-v-m { grid-template-columns: 1fr; }
    .hp-about-img-main img { height: 380px; }
    .hp-about-img-wrap { padding: 20px 20px 20px 0; }
    .hp-about-card p { font-size: .88rem; }
}
</style>

<section class="hp-about">
    <div class="hp-about-container">

        <!-- ══ IMAGE ══ -->
        <div class="hp-about-img-wrap">
            <div class="hp-about-img-main">
                <img
                    src="https://almarah.org/wp-content/uploads/elementor/thumbs/About-Us-rkzrl0xnoqpjqnb41bv8n53ozept5hbagvbmbh3row.png"
                    alt="About Almarah Foundation — Children in Care">
            </div>
        </div>

        <!-- ══ CONTENT ══ -->
        <div class="hp-about-content">

            <div class="hp-about-eyebrow">About Us</div>

            <h2>Every Child Deserves a Place to Call <em>"Apna Ghar"</em></h2>

            <div class="hp-about-divider"></div>

            <p class="hp-about-text">
                At Almarah Foundation, we believe that a child's beginning shouldn't determine their end. Founded on June 4th, 2021, we believe in lasting transformation. Our purpose is to walk alongside every child who passes through our doors — offering a safe atmosphere where children can simply be children, the tools and knowledge they need to break cycles and dream bigger, and lasting empowerment to lead, thrive, and shape a brighter future. Kindness is our foundation; dedication is our heartbeat. Together, we aren't just changing lives — we are rewriting stories.
            </p>

            <div class="hp-about-v-m">

                <!-- Vision -->
                <div class="hp-about-card">
                    <div class="hp-about-card-icon"
                         style="background:rgba(242,194,0,.12); color:#C9A000;">
                        <svg viewBox="0 0 24 24">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </div>
                    <h4>Our Vision</h4>
                    <p>A compassionate and inclusive society where every vulnerable child and family thrives with dignity and the opportunity to fulfill their potential.</p>
                </div>

                <!-- Mission -->
                <div class="hp-about-card">
                    <div class="hp-about-card-icon"
                         style="background:rgba(169,45,99,.08); color:#A92D63;">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <h4>Our Mission</h4>
                    <p>Alleviating hunger, nurturing orphaned children in home-like environments, delivering accessible education and healthcare, and strengthening families through empowerment.</p>
                </div>

            </div>

            <a href="/about" class="hp-about-cta">
                Discover Our Story
                <svg viewBox="0 0 24 24">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>

        </div>

    </div>
</section>

<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════
   OUR PROGRAMS — REDESIGNED + SLOWER FX
═══════════════════════════════════════ */
.hp-programs {
    width: 100%;
    max-width: 100%;
    padding: 120px 0;
    background: linear-gradient(180deg, #FDF5F8 0%, #FFFFFF 40%, #FFFFFF 100%);
    position: relative;
    overflow: hidden;
    font-family: 'Manrope', sans-serif;
    box-sizing: border-box;
}
.hp-programs *, .hp-programs *::before, .hp-programs *::after {
    box-sizing: border-box;
}

.hp-programs::before {
    content: '';
    position: absolute;
    top: -250px; left: -180px;
    width: 700px; height: 700px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(242,194,0,.06) 0%, transparent 70%);
    pointer-events: none;
}
.hp-programs::after {
    content: '';
    position: absolute;
    bottom: -250px; right: -200px;
    width: 800px; height: 800px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(169,45,99,.05) 0%, transparent 70%);
    pointer-events: none;
}

.hp-programs-container {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 40px;
    position: relative;
    z-index: 1;
}

/* ═══════════════════════════════════════
   ENTRANCE ANIMATIONS (SLOWER)
═══════════════════════════════════════ */
@keyframes hpFadeUp {
    from { opacity: 0; transform: translateY(45px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes hpFadeLeft {
    from { opacity: 0; transform: translateX(-55px); }
    to   { opacity: 1; transform: translateX(0); }
}
@keyframes hpFadeRight {
    from { opacity: 0; transform: translateX(55px); }
    to   { opacity: 1; transform: translateX(0); }
}
@keyframes hpPopIn {
    0%   { opacity: 0; transform: scale(.85); }
    60%  { opacity: 1; transform: scale(1.02); }
    100% { opacity: 1; transform: scale(1); }
}
@keyframes hpPulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50%      { transform: scale(1.3); opacity: .7; }
}

/* Base state */
.hp-reveal {
    opacity: 0;
    will-change: opacity, transform;
}
.hp-reveal.is-visible {
    opacity: 1;
}

/* Slower durations + softer easing */
.hp-reveal.fade-up.is-visible {
    animation: hpFadeUp 1.4s cubic-bezier(.19,1,.22,1) forwards;
}
.hp-reveal.fade-left.is-visible {
    animation: hpFadeLeft 1.4s cubic-bezier(.19,1,.22,1) forwards;
}
.hp-reveal.fade-right.is-visible {
    animation: hpFadeRight 1.4s cubic-bezier(.19,1,.22,1) forwards;
}
.hp-reveal.pop-in.is-visible {
    animation: hpPopIn 1.2s cubic-bezier(.19,1,.22,1) forwards;
}

/* Staggered delays (slightly wider spacing for smooth cascade) */
.hp-reveal.delay-1.is-visible { animation-delay: .18s; }
.hp-reveal.delay-2.is-visible { animation-delay: .36s; }
.hp-reveal.delay-3.is-visible { animation-delay: .54s; }
.hp-reveal.delay-4.is-visible { animation-delay: .72s; }
.hp-reveal.delay-5.is-visible { animation-delay: .90s; }
.hp-reveal.delay-6.is-visible { animation-delay: 1.08s; }

/* ═══════════════════════
   HEADER
═══════════════════════ */
.hp-programs-header {
    text-align: center;
    max-width: 780px;
    margin: 0 auto 80px;
}

.hp-programs-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 14px;
    font-size: 0.65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 6px;
    color: #A92D63;
    margin-bottom: 22px;
}
.hp-programs-eyebrow::before,
.hp-programs-eyebrow::after {
    content: '';
    width: 30px; height: 2px;
    background: #A92D63;
    opacity: .6;
    border-radius: 2px;
}

.hp-programs h2 {
    font-family: 'Manrope', sans-serif;
    font-size: clamp(2.4rem, 5.5vw, 3.6rem);
    font-weight: 800;
    line-height: 1.02;
    color: #1A0810;
    letter-spacing: -2.5px;
    margin-bottom: 24px;
}
.hp-programs h2 em {
    font-style: normal;
    background: linear-gradient(135deg, #8A2050, #A92D63 50%, #C4386F);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    position: relative;
    display: inline-block;
}

.hp-programs-intro {
    font-family: 'Open Sans', sans-serif;
    font-size: 1.05rem;
    line-height: 1.8;
    color: #7A4060;
    font-weight: 400;
}

/* ═══════════════════════
   FEATURED CARD
═══════════════════════ */
.hp-featured {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 0;
    background: #FFFFFF;
    border-radius: 36px;
    overflow: hidden;
    margin-bottom: 32px;
    box-shadow:
        0 0 0 1px rgba(169,45,99,.06),
        0 30px 80px -20px rgba(169,45,99,.14);
    transition: transform 0.8s cubic-bezier(.19,1,.22,1), box-shadow 0.8s ease;
    position: relative;
}
.hp-featured:hover {
    transform: translateY(-8px);
    box-shadow:
        0 0 0 1px rgba(169,45,99,.10),
        0 40px 100px -20px rgba(169,45,99,.22);
}

.hp-featured.reverse {
    grid-template-columns: 1.2fr 1fr;
}
.hp-featured.reverse .hp-featured-img { order: 2; }
.hp-featured.reverse .hp-featured-body { order: 1; }

.hp-featured-img {
    position: relative;
    overflow: hidden;
    min-height: 500px;
}
.hp-featured-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 1.6s cubic-bezier(.19,1,.22,1);
}
.hp-featured:hover .hp-featured-img img {
    transform: scale(1.05);
}
.hp-featured-img::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(58,0,24,.30) 0%, transparent 55%);
    pointer-events: none;
}

.hp-featured-badge {
    position: absolute;
    top: 24px; left: 24px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    border-radius: 100px;
    background: rgba(255,255,255,.95);
    backdrop-filter: blur(12px);
    font-size: 0.60rem;
    font-weight: 800;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: #A92D63;
    box-shadow: 0 8px 24px rgba(0,0,0,.18);
}
.hp-featured-badge::before {
    content: '';
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #F2C200;
    box-shadow: 0 0 12px rgba(242,194,0,.9);
    animation: hpPulse 3s ease-in-out infinite;
}

.hp-featured-body {
    padding: 56px 56px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    background: #FFFFFF;
}

.hp-featured-num {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 0.58rem;
    font-weight: 800;
    letter-spacing: 4px;
    text-transform: uppercase;
    color: #A92D63;
    margin-bottom: 14px;
}
.hp-featured-num::before {
    content: '';
    width: 24px; height: 1.5px;
    background: #A92D63;
}

.hp-featured-body h3 {
    font-family: 'Manrope', sans-serif;
    font-size: clamp(1.8rem, 3vw, 2.4rem);
    font-weight: 800;
    color: #1A0810;
    letter-spacing: -1.2px;
    line-height: 1.05;
    margin-bottom: 16px;
}

.hp-featured-body > p {
    font-family: 'Open Sans', sans-serif;
    font-size: 0.98rem;
    line-height: 1.8;
    color: #5A2040;
    margin-bottom: 32px;
}

/* ═══════════════════════
   BIG STATS GRID (NO ENTRANCE ANIMATION)
═══════════════════════ */
.hp-featured-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    margin-bottom: 32px;
    border-radius: 24px;
    overflow: hidden;
    background: linear-gradient(135deg, #6B0F35 0%, #8A2050 50%, #A92D63 100%);
    position: relative;
    box-shadow: 0 20px 50px -12px rgba(169,45,99,.30);
    opacity: 1;
    transform: none;
}
.hp-featured-stats::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 60% 80% at 10% 50%, rgba(242,194,0,.18) 0%, transparent 60%),
        radial-gradient(ellipse 50% 60% at 90% 30%, rgba(255,255,255,.06) 0%, transparent 60%);
    pointer-events: none;
}

.hp-featured-stat {
    padding: 28px 20px;
    text-align: center;
    position: relative;
    z-index: 1;
}
.hp-featured-stat::after {
    content: '';
    position: absolute;
    right: 0; top: 25%; bottom: 25%;
    width: 1px;
    background: rgba(255,255,255,.15);
}
.hp-featured-stat:last-child::after { display: none; }

.hp-featured-stat-value {
    font-family: 'Manrope', sans-serif;
    font-weight: 800;
    font-size: clamp(1.8rem, 3.5vw, 2.6rem);
    color: #F2C200;
    letter-spacing: -2px;
    line-height: 1;
    margin-bottom: 8px;
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 2px;
    text-shadow: 0 0 30px rgba(242,194,0,.4);
}
.hp-featured-stat-value .hp-featured-stat-suffix {
    font-size: 0.7em;
    color: #FFD740;
    font-weight: 800;
}

.hp-featured-stat-label {
    font-family: 'Manrope', sans-serif;
    font-size: 0.58rem;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: rgba(255,255,255,.72);
    line-height: 1.4;
}

/* ═══════════════════════
   GRID CARDS
═══════════════════════ */
.hp-programs-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
    margin-top: 32px;
}

.hp-card {
    background: #FFFFFF;
    border-radius: 28px;
    overflow: hidden;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    position: relative;
    box-shadow:
        0 0 0 1px rgba(169,45,99,.06),
        0 12px 40px -8px rgba(169,45,99,.10);
    transition: transform 0.8s cubic-bezier(.19,1,.22,1), box-shadow 0.8s ease;
}
.hp-card:hover {
    transform: translateY(-10px);
    box-shadow:
        0 0 0 1px rgba(169,45,99,.12),
        0 32px 70px -12px rgba(169,45,99,.22);
}

.hp-card-img {
    position: relative;
    overflow: hidden;
    aspect-ratio: 16 / 11;
}
.hp-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 1.3s cubic-bezier(.19,1,.22,1);
}
.hp-card:hover .hp-card-img img {
    transform: scale(1.10);
}
.hp-card-img::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 50%, rgba(26,8,16,.55) 100%);
    pointer-events: none;
}

.hp-card-badge {
    position: absolute;
    top: 20px; right: 20px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px; height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,.95);
    backdrop-filter: blur(10px);
    font-family: 'Manrope', sans-serif;
    font-size: 0.72rem;
    font-weight: 800;
    color: #A92D63;
    box-shadow: 0 6px 20px rgba(0,0,0,.15);
    letter-spacing: -.5px;
}

.hp-card-body {
    padding: 32px 30px 30px;
    display: flex;
    flex-direction: column;
    flex: 1;
    background: #FFFFFF;
}

.hp-card-body h3 {
    font-family: 'Manrope', sans-serif;
    font-size: 1.25rem;
    font-weight: 800;
    color: #1A0810;
    letter-spacing: -.5px;
    line-height: 1.2;
    margin-bottom: 12px;
}

.hp-card-body p {
    font-family: 'Open Sans', sans-serif;
    font-size: 0.88rem;
    line-height: 1.75;
    color: #7A4060;
    margin-bottom: 24px;
    flex: 1;
}

.hp-card-stats {
    display: flex;
    gap: 20px;
    padding: 20px 0 0;
    border-top: 2px dashed rgba(169,45,99,.10);
    margin-bottom: 22px;
}

.hp-card-stat {
    flex: 1;
    text-align: left;
    position: relative;
    padding-left: 14px;
}
.hp-card-stat::before {
    content: '';
    position: absolute;
    left: 0; top: 6px;
    width: 3px; height: 28px;
    background: linear-gradient(180deg, #A92D63, #C4386F);
    border-radius: 2px;
}

.hp-card-stat-value {
    font-family: 'Manrope', sans-serif;
    font-weight: 800;
    font-size: 1.5rem;
    color: #1A0810;
    letter-spacing: -1px;
    line-height: 1;
    margin-bottom: 6px;
    display: flex;
    align-items: baseline;
    gap: 2px;
}
.hp-card-stat-value .hp-card-stat-suffix {
    font-size: 0.75em;
    color: #A92D63;
    font-weight: 800;
}

.hp-card-stat-label {
    font-family: 'Manrope', sans-serif;
    font-size: 0.52rem;
    font-weight: 800;
    letter-spacing: 1.8px;
    text-transform: uppercase;
    color: #9A6080;
    line-height: 1.3;
}

.hp-card-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-family: 'Manrope', sans-serif;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #A92D63;
    text-decoration: none;
    padding-top: 4px;
    transition: gap 0.5s ease, color 0.5s ease;
    width: fit-content;
}
.hp-card-link svg {
    width: 16px; height: 16px;
    stroke: currentColor; fill: none;
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
    transition: transform 0.5s cubic-bezier(.19,1,.22,1);
}
.hp-card:hover .hp-card-link {
    gap: 14px;
    color: #8A2050;
}
.hp-card:hover .hp-card-link svg { transform: translateX(4px); }

/* ═══════════════════════
   BUTTON
═══════════════════════ */
.hp-featured-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, #8A2050, #A92D63);
    color: #fff;
    font-family: 'Manrope', sans-serif;
    font-size: 0.78rem;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 16px 32px;
    border-radius: 100px;
    text-decoration: none;
    transition: all 0.7s cubic-bezier(.19,1,.22,1);
    box-shadow: 0 10px 30px rgba(169,45,99,.30);
    position: relative;
    overflow: hidden;
    width: fit-content;
}
.hp-featured-btn::before {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.25), transparent);
    transition: left 1s ease;
}
.hp-featured-btn:hover::before { left: 100%; }
.hp-featured-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 18px 44px rgba(169,45,99,.40);
    color: #fff;
    text-decoration: none;
}
.hp-featured-btn svg {
    width: 16px; height: 16px;
    stroke: currentColor; fill: none;
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
    transition: transform 0.5s cubic-bezier(.19,1,.22,1);
}
.hp-featured-btn:hover svg { transform: translateX(5px); }

/* ═══════════════════════
   RESPONSIVE
═══════════════════════ */
@media (max-width: 1100px) {
    .hp-programs-grid { grid-template-columns: repeat(2, 1fr); gap: 24px; }
}

@media (max-width: 960px) {
    .hp-featured,
    .hp-featured.reverse {
        grid-template-columns: 1fr;
    }
    .hp-featured.reverse .hp-featured-img,
    .hp-featured.reverse .hp-featured-body {
        order: unset;
    }
    .hp-featured-img { min-height: 340px; }
    .hp-featured-body { padding: 44px 36px; }
}

@media (max-width: 768px) {
    .hp-programs { padding: 90px 0; }
    .hp-programs-container { padding: 0 28px; }
    .hp-programs-header { margin-bottom: 56px; }
    .hp-programs-grid { grid-template-columns: 1fr; gap: 22px; }
    .hp-featured-stats { grid-template-columns: repeat(3, 1fr); }
    .hp-featured-stat { padding: 22px 12px; }
    .hp-featured-stat-value { font-size: 1.6rem; letter-spacing: -1.5px; }
    .hp-featured-stat-label { font-size: 0.50rem; letter-spacing: 1.5px; }
}

@media (max-width: 600px) {
    .hp-programs { padding: 80px 0; }
    .hp-programs-container { padding: 0 20px; }
    .hp-programs h2 {
        font-size: 2.2rem;
        letter-spacing: -1.5px;
    }
    .hp-programs-intro { font-size: 0.95rem; }
    .hp-featured-body { padding: 36px 26px; }
    .hp-featured-body h3 { font-size: 1.55rem; }
    .hp-featured-stat-value { font-size: 1.4rem; }
    .hp-featured-stat-label { font-size: 0.46rem; letter-spacing: 1.2px; }
    .hp-featured-btn { padding: 14px 26px; font-size: 0.72rem; }
    .hp-card-body { padding: 28px 24px 26px; }
    .hp-card-body h3 { font-size: 1.15rem; }
    .hp-card-stat-value { font-size: 1.35rem; }
}

/* Respect reduced motion */
@media (prefers-reduced-motion: reduce) {
    .hp-reveal,
    .hp-reveal.is-visible {
        animation: none !important;
        opacity: 1 !important;
        transform: none !important;
    }
    .hp-featured-badge::before { animation: none; }
}
</style>

<!-- ══════════════════════════════════════
     OUR PROGRAMS SECTION
══════════════════════════════════════ -->
<section class="hp-programs">
    <div class="hp-programs-container">

        <!-- ══ HEADER ══ -->
        <div class="hp-programs-header">
            <div class="hp-programs-eyebrow hp-reveal fade-up">Our Programs</div>
            <h2 class="hp-reveal fade-up delay-1">Projects and <em>Initiatives</em></h2>
            <p class="hp-programs-intro hp-reveal fade-up delay-2">
                From feeding families to educating children and providing safe homes, every program at Almarah Foundation is designed to create lasting change for those who need it most.
            </p>
        </div>

        <!-- ══ FEATURED 1: PARENT THE ORPHAN ══ -->
        <div class="hp-featured">
            <div class="hp-featured-img hp-reveal fade-left">
                <span class="hp-featured-badge">Flagship Program</span>
                <img src="https://almarah.org/wp-content/uploads/2026/03/Parent-the-Orphan.png"
                     alt="Parent the Orphan — Almarah Foundation">
            </div>
            <div class="hp-featured-body">
                <div class="hp-featured-num hp-reveal fade-right delay-1">Program 01</div>
                <h3 class="hp-reveal fade-right delay-2">Parent the Orphan</h3>
                <p class="hp-reveal fade-right delay-3">
                    Our flagship program provides orphaned and vulnerable children with a safe, nurturing home environment that meets internationally recognized standards of care. We attend to every child's health, hygiene, mental well-being, education, and recreation.
                </p>

                <!-- Stats: NO entrance animation -->
                <div class="hp-featured-stats">
                    <div class="hp-featured-stat">
                        <div class="hp-featured-stat-value">
                            <span class="hp-stat-num" data-target="275">0</span><span class="hp-featured-stat-suffix">+</span>
                        </div>
                        <div class="hp-featured-stat-label">Children in Care</div>
                    </div>
                    <div class="hp-featured-stat">
                        <div class="hp-featured-stat-value">
                            <span class="hp-stat-num" data-target="13">0</span>
                        </div>
                        <div class="hp-featured-stat-label">Care Homes</div>
                    </div>
                    <div class="hp-featured-stat">
                        <div class="hp-featured-stat-value">
                            <span class="hp-stat-num" data-target="4.6">0</span><span class="hp-featured-stat-suffix">M</span>
                        </div>
                        <div class="hp-featured-stat-label">Orphans in Pakistan</div>
                    </div>
                </div>

                <a href="https://almarah.org/parent-the-orphan/" class="hp-featured-btn hp-reveal fade-up delay-4">
                    Learn More
                    <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </div>

        <!-- ══ FEATURED 2: FEED THE HUNGRY (REVERSED) ══ -->
        <div class="hp-featured reverse">
            <div class="hp-featured-img hp-reveal fade-right">
                <span class="hp-featured-badge">Core Initiative</span>
                <img src="https://almarah.org/wp-content/uploads/2026/03/Feed-the-Hungry-1.png"
                     alt="Feed the Hungry — Almarah Foundation">
            </div>
            <div class="hp-featured-body">
                <div class="hp-featured-num hp-reveal fade-left delay-1">Program 02</div>
                <h3 class="hp-reveal fade-left delay-2">Feed the Hungry</h3>
                <p class="hp-reveal fade-left delay-3">
                    We provide daily meals and monthly food rations to individuals and families from lower-income sectors, addressing the most fundamental human need, nourishment, while also offering financial assistance to those who need it most.
                </p>

                <!-- Stats: NO entrance animation -->
                <div class="hp-featured-stats">
                    <div class="hp-featured-stat">
                        <div class="hp-featured-stat-value">
                            <span class="hp-stat-num" data-target="852">0</span><span class="hp-featured-stat-suffix">+</span>
                        </div>
                        <div class="hp-featured-stat-label">Daily Breakfasts</div>
                    </div>
                    <div class="hp-featured-stat">
                        <div class="hp-featured-stat-value">
                            <span class="hp-stat-num" data-target="425">0</span><span class="hp-featured-stat-suffix">+</span>
                        </div>
                        <div class="hp-featured-stat-label">Daily Lunches</div>
                    </div>
                    <div class="hp-featured-stat">
                        <div class="hp-featured-stat-value">
                            <span class="hp-stat-num" data-target="76">0</span><span class="hp-featured-stat-suffix">+</span>
                        </div>
                        <div class="hp-featured-stat-label">Ration Packs / Mo</div>
                    </div>
                </div>

                <a href="https://almarah.org/feed-the-hungry-2/" class="hp-featured-btn hp-reveal fade-up delay-4">
                    Learn More
                    <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </div>

        <!-- ══ GRID: 3 CARDS ══ -->
        <div class="hp-programs-grid">

            <!-- Educate Pakistan -->
            <a href="https://almarah.org/educate-pakistan/" class="hp-card hp-reveal pop-in delay-1">
                <div class="hp-card-img">
                    <span class="hp-card-badge">03</span>
                    <img src="https://almarah.org/wp-content/uploads/2026/03/Educate-Pakistan.png"
                         alt="Educate Pakistan — Almarah Grammar School">
                </div>
                <div class="hp-card-body">
                    <h3>Educate Pakistan</h3>
                    <p>
                        Almarah Grammar School on Canal Road, Lahore offers Playgroup through High School education, empowering underprivileged children to break the cycle of poverty.
                    </p>

                    <div class="hp-card-stats">
                        <div class="hp-card-stat">
                            <div class="hp-card-stat-value">
                                <span class="hp-stat-num" data-target="300">0</span><span class="hp-card-stat-suffix">+</span>
                            </div>
                            <div class="hp-card-stat-label">Students</div>
                        </div>
                        <div class="hp-card-stat">
                            <div class="hp-card-stat-value">
                                <span class="hp-stat-num" data-target="23">0</span>
                            </div>
                            <div class="hp-card-stat-label">Teachers</div>
                        </div>
                    </div>

                    <span class="hp-card-link">
                        Explore Program
                        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                </div>
            </a>

            <!-- Izzat ki Roti -->
            <a href="https://almarah.org/izat-ki-roti/" class="hp-card hp-reveal pop-in delay-2">
                <div class="hp-card-img">
                    <span class="hp-card-badge">04</span>
                    <img src="https://almarah.org/wp-content/uploads/2026/05/DSC07430-scaled.jpg"
                         alt="Izzat ki Roti — Dignified Food Initiative">
                </div>
                <div class="hp-card-body">
                    <h3>Izzat ki Roti</h3>
                    <p>
                        A dignified food initiative that allows individuals to receive a meal without the stigma of charity. We preserve the self-respect of every person we serve.
                    </p>

                    <div class="hp-card-stats">
                        <div class="hp-card-stat">
                            <div class="hp-card-stat-value">
                                <span class="hp-stat-num" data-target="100">0</span>
                            </div>
                            <div class="hp-card-stat-label">Tandoors Active</div>
                        </div>
                        <div class="hp-card-stat">
                            <div class="hp-card-stat-value">
                                <span class="hp-stat-num" data-target="100">0</span><span class="hp-card-stat-suffix">%</span>
                            </div>
                            <div class="hp-card-stat-label">Dignity Kept</div>
                        </div>
                    </div>

                    <span class="hp-card-link">
                        Explore Program
                        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                </div>
            </a>

            <!-- Compassionate Haven -->
            <a href="https://almarahhaven.org/" class="hp-card hp-reveal pop-in delay-3">
                <div class="hp-card-img">
                    <span class="hp-card-badge">05</span>
                    <img src="https://almarah.org/wp-content/uploads/2026/05/comp.jpeg"
                         alt="Compassionate Haven — Special Needs Home">
                </div>
                <div class="hp-card-body">
                    <h3>Compassionate Haven</h3>
                    <p>
                        A dedicated home for children with special needs offering sensory-friendly spaces, therapy rooms, and specialized learning tools to help every child thrive.
                    </p>

                    <div class="hp-card-stats">
                        <div class="hp-card-stat">
                            <div class="hp-card-stat-value">
                                <span class="hp-stat-num" data-target="1">0</span>
                            </div>
                            <div class="hp-card-stat-label">Dedicated Home</div>
                        </div>
                        <div class="hp-card-stat">
                            <div class="hp-card-stat-value">
                                <span class="hp-stat-num" data-target="24">0</span><span class="hp-card-stat-suffix">/7</span>
                            </div>
                            <div class="hp-card-stat-label">Care Support</div>
                        </div>
                    </div>

                    <span class="hp-card-link">
                        Explore Program
                        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                </div>
            </a>

        </div>

    </div>
</section>

<script>
(function() {
    /* ═════════════════════════════════════
       1. SCROLL-REVEAL ENTRANCE EFFECTS
    ═════════════════════════════════════ */
    const revealEls = document.querySelectorAll('.hp-programs .hp-reveal');

    if (revealEls.length) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -80px 0px'
        });

        revealEls.forEach(el => revealObserver.observe(el));
    }

    /* ═════════════════════════════════════
       2. DYNAMIC NUMBER COUNTERS (slower)
    ═════════════════════════════════════ */
    const statNumbers = document.querySelectorAll('.hp-programs .hp-stat-num');
    if (!statNumbers.length) return;

    const animated = new WeakSet();

    function animateNumber(el) {
        const target = parseFloat(el.getAttribute('data-target')) || 0;
        const isDecimal = target % 1 !== 0;
        const duration = 2800; /* slower count-up */
        const startTime = performance.now();

        function step(now) {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            const current = eased * target;

            el.textContent = isDecimal ? current.toFixed(1) : Math.floor(current);

            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = isDecimal ? target.toFixed(1) : target;
            }
        }
        requestAnimationFrame(step);
    }

    const numberObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !animated.has(entry.target)) {
                animated.add(entry.target);
                // small delay so user sees the number before it counts
                setTimeout(() => animateNumber(entry.target), 200);
                numberObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.4 });

    statNumbers.forEach(el => numberObserver.observe(el));
})();
</script>