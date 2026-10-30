<style>
    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
        --pink:         #A92D63;
        --pink-dark:    #8A2050;
        --pink-mid:     #C4386F;
        --pink-soft:    rgba(169,45,99,.08);
        --pink-glow:    rgba(169,45,99,.18);
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
        background-color: var(--off-white);
        color: var(--text-dark);
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
    }

    img { max-width: 100%; display: block; }

    /* ═══════════════════════════════════════════
       HERO
    ═══════════════════════════════════════════ */
    .ikr-hero {
        background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 30%, var(--pink) 65%, var(--pink-mid) 100%);
        padding: 120px 24px 110px;
        text-align: center;
        position: relative;
        overflow: hidden;
        min-height: 560px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    @media (min-width: 768px) { .ikr-hero { padding: 160px 40px 140px; min-height: 640px; } }

    .ikr-hero::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 70% 60% at 10% 60%, rgba(242,194,0,.22) 0%, transparent 60%),
            radial-gradient(ellipse 50% 50% at 90% 15%, rgba(255,255,255,.10) 0%, transparent 55%),
            radial-gradient(ellipse 40% 40% at 50% 100%, rgba(0,0,0,.18) 0%, transparent 60%);
        pointer-events: none;
    }
    .ikr-hero::after {
        content: '';
        position: absolute;
        bottom: -130px; right: -130px;
        width: 450px; height: 450px;
        border-radius: 50%;
        border: 60px solid rgba(242,194,0,.07);
        pointer-events: none;
    }
    @media (min-width: 768px) {
        .ikr-hero::after {
            bottom: -210px; right: -210px;
            width: 750px; height: 750px;
            border-width: 90px;
        }
    }

    .ikr-hero-circle {
        position: absolute;
        top: -90px; left: -90px;
        width: 300px; height: 300px;
        border-radius: 50%;
        border: 45px solid rgba(255,255,255,.04);
        pointer-events: none;
    }
    @media (min-width: 768px) {
        .ikr-hero-circle { top: -150px; left: -150px; width: 550px; height: 550px; }
    }

    .ikr-hero-dots {
        position: absolute; inset: 0;
        pointer-events: none;
        overflow: hidden;
    }
    .ikr-hero-dots span {
        position: absolute;
        border-radius: 50%;
        background: rgba(242,194,0,.10);
        animation: floatDot 6s ease-in-out infinite;
    }
    .ikr-hero-dots span:nth-child(1) { width:12px;height:12px; top:15%;left:8%;  animation-delay:0s; }
    .ikr-hero-dots span:nth-child(2) { width:8px; height:8px;  top:25%;left:18%; animation-delay:1s; }
    .ikr-hero-dots span:nth-child(3) { width:16px;height:16px; top:60%;left:5%;  animation-delay:2s; }
    .ikr-hero-dots span:nth-child(4) { width:10px;height:10px; top:10%;right:12%;animation-delay:.5s; }
    .ikr-hero-dots span:nth-child(5) { width:14px;height:14px; top:40%;right:7%; animation-delay:1.5s; }
    .ikr-hero-dots span:nth-child(6) { width:8px; height:8px;  top:70%;right:15%;animation-delay:2.5s; }

    @keyframes floatDot {
        0%,100% { transform: translateY(0px) scale(1); opacity:.6; }
        50%     { transform: translateY(-14px) scale(1.15); opacity:1; }
    }

    .ikr-hero-inner {
        position: relative;
        z-index: 2;
        max-width: 820px;
        margin: 0 auto;
    }

    .ikr-hero h1 {
        font-weight: 800;
        font-size: clamp(2.6rem, 9vw, 5.8rem);
        color: var(--white);
        letter-spacing: -2px;
        line-height: 1.0;
        text-shadow: 0 4px 40px rgba(0,0,0,.25);
    }
    .ikr-hero h1 em {
        font-style: normal;
        color: var(--yellow);
        display: block;
        margin-top: 6px;
        filter: drop-shadow(0 0 30px rgba(242,194,0,.35));
    }

    .ikr-hero-desc {
        margin-top: 22px;
        font-size: .95rem;
        font-weight: 400;
        color: rgba(255,255,255,.60);
        line-height: 1.7;
        max-width: 580px;
        margin-left: auto;
        margin-right: auto;
    }

    .ikr-hero-ornament {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        margin-top: 36px;
    }
    .ikr-hero-ornament .bar {
        width: 50px; height: 2px;
        background: linear-gradient(90deg, transparent, var(--yellow));
        border-radius: 2px;
    }
    .ikr-hero-ornament .bar.r {
        background: linear-gradient(90deg, var(--yellow), transparent);
    }
    .ikr-hero-ornament .diamond {
        width: 10px; height: 10px;
        background: var(--yellow);
        transform: rotate(45deg);
        border-radius: 2px;
        box-shadow: 0 0 16px rgba(242,194,0,.6);
    }

    .ikr-hero-scroll {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-top: 40px;
        text-decoration: none;
        color: rgba(255,255,255,.55);
        font-size: .64rem;
        font-weight: 600;
        letter-spacing: 3px;
        text-transform: uppercase;
        transition: color .3s;
    }
    .ikr-hero-scroll:hover { color: var(--yellow); }
    .ikr-hero-scroll svg {
        width: 18px; height: 18px;
        animation: bounceDown 2s ease-in-out infinite;
    }
    @keyframes bounceDown {
        0%,100% { transform: translateY(0); }
        50%     { transform: translateY(6px); }
    }

    /* ═══════════════════════════════════════════
       STATS STRIP
    ═══════════════════════════════════════════ */
    .ikr-stats-strip {
        background: linear-gradient(90deg, #6B0F35, var(--pink-dark), var(--pink));
        position: relative;
        z-index: 10;
    }

    .ikr-stats-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
    }
    @media (min-width: 640px) {
        .ikr-stats-wrap { grid-template-columns: repeat(4, 1fr); }
    }

    .ikr-stat-item {
        padding: 28px 16px;
        text-align: center;
        border-right: 1px solid rgba(255,255,255,.10);
    }
    .ikr-stat-item:last-child { border-right: none; }
    @media (max-width: 639px) {
        .ikr-stat-item:nth-child(even) { border-right: none; }
        .ikr-stat-item:nth-child(1),
        .ikr-stat-item:nth-child(2) { border-bottom: 1px solid rgba(255,255,255,.10); }
    }

    .ikr-stat-val {
        font-weight: 800;
        font-size: clamp(1.8rem, 4vw, 2.6rem);
        color: var(--yellow);
        letter-spacing: -1px;
        line-height: 1;
        text-shadow: 0 0 20px rgba(242,194,0,.4);
    }
    .ikr-stat-label {
        font-size: .58rem;
        font-weight: 600;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: rgba(255,255,255,.55);
        margin-top: 8px;
    }

    /* ═══════════════════════════════════════════
       SHARED LAYOUT
    ═══════════════════════════════════════════ */
    .ikr-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    @media (min-width: 768px) { .ikr-wrap { padding: 0 36px; } }

    .ikr-section-header {
        text-align: center;
        margin-bottom: 56px;
    }

    .ikr-tag {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--pink);
        margin-bottom: 14px;
    }
    .ikr-tag::before, .ikr-tag::after {
        content: '';
        display: block;
        width: 18px; height: 1px;
        background: var(--pink);
        opacity: .45;
    }

    .ikr-section-header h2 {
        font-weight: 800;
        font-size: clamp(1.9rem, 5.5vw, 3.4rem);
        color: var(--text-dark);
        letter-spacing: -1.5px;
        line-height: 1.08;
    }
    .ikr-section-header h2 .accent { color: var(--pink); }

    .ikr-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin: 20px 0 16px;
    }
    .ikr-divider::before, .ikr-divider::after {
        content: '';
        width: 55px; height: 2px;
        background: linear-gradient(90deg, transparent, var(--pink));
        border-radius: 2px;
    }
    .ikr-divider::after {
        background: linear-gradient(90deg, var(--pink), transparent);
    }
    .ikr-divider .gem {
        width: 9px; height: 9px;
        background: var(--yellow);
        transform: rotate(45deg);
        border-radius: 1px;
        box-shadow: 0 0 14px rgba(242,194,0,.55);
    }

    .ikr-section-sub {
        font-size: .90rem;
        color: var(--text-light);
        line-height: 1.65;
        max-width: 520px;
        margin: 0 auto;
    }

    /* ═══════════════════════════════════════════
       ABOUT SECTION
    ═══════════════════════════════════════════ */
    .ikr-about {
        padding: 90px 0 80px;
        background: var(--off-white);
        overflow: hidden;
        position: relative;
    }
    .ikr-about::before {
        content: '';
        position: absolute;
        top: -180px; right: -180px;
        width: 550px; height: 550px;
        border-radius: 50%;
        border: 70px solid rgba(169,45,99,.04);
        pointer-events: none;
    }
    @media (min-width: 768px) { .ikr-about { padding: 110px 0 100px; } }

    .ikr-about-grid {
        display: flex;
        flex-direction: column;
        gap: 52px;
        align-items: center;
    }
    @media (min-width: 900px) {
        .ikr-about-grid {
            flex-direction: row;
            align-items: flex-start;
            gap: 72px;
        }
    }

    .ikr-about-img-col {
        width: 100%;
        max-width: 480px;
        flex-shrink: 0;
        position: relative;
    }
    @media (min-width: 900px) { .ikr-about-img-col { max-width: 440px; } }
    @media (min-width: 1100px) { .ikr-about-img-col { max-width: 480px; } }

    .ikr-img-main {
        width: 100%;
        aspect-ratio: 4 / 3;
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.08),
            0 20px 60px rgba(169,45,99,.18),
            0 8px 24px rgba(0,0,0,.10);
    }

    /* ── Image inside .ikr-img-main fills the container ── */
    .ikr-img-main img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        transition: transform .7s cubic-bezier(.22,.9,.36,1);
    }
    .ikr-img-main:hover img { transform: scale(1.04); }

    .ikr-img-main::before {
        content: '';
        position: absolute; inset: 0;
        border-radius: inherit;
        box-shadow: inset 0 0 0 3px rgba(169,45,99,.15);
        z-index: 2;
        pointer-events: none;
    }
    .ikr-img-main::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light));
        z-index: 3;
        border-radius: 20px 20px 0 0;
        pointer-events: none;
    }

    .ikr-img-badge {
        position: absolute;
        bottom: -18px;
        right: 24px;
        background: var(--white);
        border-radius: 16px;
        padding: 14px 20px;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.10),
            0 12px 36px rgba(169,45,99,.18),
            0 4px 12px rgba(0,0,0,.08);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 4;
        min-width: 180px;
    }
    .ikr-img-badge-icon {
        width: 42px; height: 42px;
        background: linear-gradient(135deg, var(--pink), var(--pink-mid));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
        box-shadow: 0 6px 16px rgba(169,45,99,.3);
    }
    .ikr-img-badge-text strong {
        display: block;
        font-weight: 800;
        font-size: 1.15rem;
        color: var(--pink);
        letter-spacing: -.5px;
        line-height: 1;
    }
    .ikr-img-badge-text span {
        font-size: .60rem;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--text-light);
        margin-top: 3px;
        display: block;
    }

    .ikr-about-text-col { flex: 1; min-width: 0; }

    .ikr-about-label {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--pink);
        margin-bottom: 16px;
    }
    .ikr-about-label::before, .ikr-about-label::after {
        content: '';
        display: block;
        width: 16px; height: 1px;
        background: var(--pink);
        opacity: .4;
    }

    .ikr-about-title {
        font-weight: 800;
        font-size: clamp(1.7rem, 4vw, 2.7rem);
        color: var(--text-dark);
        letter-spacing: -1.5px;
        line-height: 1.1;
        margin-bottom: 8px;
    }
    .ikr-about-title .accent { color: var(--pink); }

    .ikr-about-subtitle {
        font-size: .96rem;
        font-weight: 500;
        color: var(--text-light);
        margin-bottom: 22px;
        line-height: 1.55;
    }

    .ikr-about-accent {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 22px;
    }
    .ikr-about-accent::before {
        content: '';
        width: 36px; height: 3px;
        background: linear-gradient(90deg, var(--pink), var(--pink-mid));
        border-radius: 2px;
    }
    .ikr-about-accent::after {
        content: '';
        width: 8px; height: 8px;
        background: var(--yellow);
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(242,194,0,.6);
    }

    .ikr-about-text {
        font-size: .92rem;
        font-weight: 400;
        line-height: 1.95;
        color: var(--text-mid);
        margin-bottom: 18px;
    }

    .ikr-quote-block {
        background: linear-gradient(135deg, var(--pink-soft), var(--yellow-soft));
        border-left: 4px solid var(--pink);
        border-radius: 0 14px 14px 0;
        padding: 18px 22px;
        margin: 24px 0;
        position: relative;
    }
    .ikr-quote-block::before {
        content: '"';
        position: absolute;
        top: -8px; left: 12px;
        font-size: 4rem;
        color: var(--pink);
        opacity: .15;
        line-height: 1;
        font-family: Georgia, serif;
    }
    .ikr-quote-block p {
        font-size: .92rem;
        font-weight: 600;
        color: var(--pink-dark);
        line-height: 1.7;
        font-style: italic;
    }

    /* ═══════════════════════════════════════════
       THE DIGNITY MODEL
    ═══════════════════════════════════════════ */
    .ikr-dignity {
        background: linear-gradient(145deg, #5A0A2A 0%, var(--pink-dark) 35%, var(--pink) 70%, var(--pink-mid) 100%);
        padding: 90px 0 100px;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .ikr-dignity { padding: 110px 0 120px; } }

    .ikr-dignity::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 65% 60% at 5% 40%, rgba(242,194,0,.15) 0%, transparent 60%),
            radial-gradient(ellipse 50% 50% at 95% 60%, rgba(0,0,0,.22) 0%, transparent 55%),
            radial-gradient(ellipse 80% 30% at 50% 100%, rgba(0,0,0,.18) 0%, transparent 60%);
        pointer-events: none;
    }

    .ikr-dignity-deco1 {
        position: absolute;
        top: -150px; left: -150px;
        width: 500px; height: 500px;
        border-radius: 50%;
        border: 65px solid rgba(242,194,0,.05);
        pointer-events: none;
    }
    .ikr-dignity-deco2 {
        position: absolute;
        bottom: -200px; right: -200px;
        width: 650px; height: 650px;
        border-radius: 50%;
        border: 80px solid rgba(255,255,255,.04);
        pointer-events: none;
    }

    .ikr-dignity-inner { position: relative; z-index: 2; }

    .ikr-dignity-header {
        text-align: center;
        margin-bottom: 60px;
    }
    .ikr-dignity-tag {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--yellow);
        margin-bottom: 14px;
    }
    .ikr-dignity-tag::before, .ikr-dignity-tag::after {
        content: '';
        display: block;
        width: 18px; height: 1px;
        background: var(--yellow);
        opacity: .5;
    }
    .ikr-dignity-header h2 {
        font-weight: 800;
        font-size: clamp(1.9rem, 5.5vw, 3.4rem);
        color: var(--white);
        letter-spacing: -1.5px;
        line-height: 1.08;
        margin-bottom: 14px;
    }
    .ikr-dignity-header h2 span { color: var(--yellow); }

    .ikr-dignity-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 14px;
    }
    .ikr-dignity-divider::before,
    .ikr-dignity-divider::after {
        content: '';
        width: 55px; height: 2px;
        background: linear-gradient(90deg, transparent, var(--yellow));
        border-radius: 2px;
    }
    .ikr-dignity-divider::after {
        background: linear-gradient(90deg, var(--yellow), transparent);
    }
    .ikr-dignity-divider .gem {
        width: 9px; height: 9px;
        background: var(--yellow);
        transform: rotate(45deg);
        border-radius: 1px;
        box-shadow: 0 0 14px rgba(242,194,0,.55);
    }
    .ikr-dignity-sub {
        font-size: .90rem;
        color: rgba(255,255,255,.45);
        line-height: 1.65;
        max-width: 520px;
        margin: 0 auto;
    }

    .ikr-dignity-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
    }
    @media (min-width: 640px) {
        .ikr-dignity-grid { grid-template-columns: repeat(2, 1fr); gap: 20px; }
    }
    @media (min-width: 1024px) {
        .ikr-dignity-grid { grid-template-columns: repeat(3, 1fr); gap: 22px; }
    }

    .ikr-dignity-card {
        background: rgba(255,255,255,.07);
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 18px;
        padding: 30px 26px;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        transition: background .3s, border-color .3s,
                    transform .4s cubic-bezier(.22,.9,.36,1),
                    box-shadow .4s;
        position: relative;
        overflow: hidden;
    }
    .ikr-dignity-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--pink-dark), var(--yellow-light), var(--pink));
        border-radius: 18px 18px 0 0;
    }
    .ikr-dignity-card:hover {
        background: rgba(255,255,255,.13);
        border-color: rgba(242,194,0,.45);
        transform: translateY(-8px);
        box-shadow:
            0 0 0 1px rgba(242,194,0,.15),
            0 24px 60px rgba(0,0,0,.28),
            0 8px 20px rgba(0,0,0,.16);
    }

    .ikr-dignity-card h3 {
        font-weight: 800;
        font-size: 1.08rem;
        color: var(--white);
        letter-spacing: -.3px;
        margin-bottom: 10px;
        line-height: 1.3;
    }

    .ikr-dignity-card-divider {
        width: 24px; height: 2px;
        background: linear-gradient(90deg, var(--yellow), rgba(242,194,0,.3));
        border-radius: 2px;
        margin-bottom: 12px;
    }

    .ikr-dignity-card p {
        font-size: .84rem;
        font-weight: 400;
        line-height: 1.75;
        color: rgba(255,255,255,.55);
    }

    /* ═══════════════════════════════════════════
       HOW IT WORKS
    ═══════════════════════════════════════════ */
    .ikr-how {
        background: var(--off-white);
        padding: 90px 0 100px;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .ikr-how { padding: 110px 0 120px; } }

    .ikr-how::before {
        content: '';
        position: absolute;
        bottom: -200px; left: -200px;
        width: 600px; height: 600px;
        border-radius: 50%;
        border: 80px solid rgba(169,45,99,.04);
        pointer-events: none;
    }

    .ikr-steps {
        display: grid;
        grid-template-columns: 1fr;
        position: relative;
    }
    @media (min-width: 768px) {
        .ikr-steps { grid-template-columns: repeat(2, 1fr); gap: 28px; }
    }
    @media (min-width: 1024px) {
        .ikr-steps { grid-template-columns: repeat(4, 1fr); gap: 0; }
    }

    @media (min-width: 1024px) {
        .ikr-steps::before {
            content: '';
            position: absolute;
            top: 40px;
            left: calc(12.5% + 20px);
            right: calc(12.5% + 20px);
            height: 2px;
            background: linear-gradient(90deg, var(--pink), rgba(169,45,99,.3), var(--pink));
            z-index: 0;
        }
    }

    .ikr-step {
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        gap: 20px;
        padding: 24px 0;
        border-bottom: 1px solid rgba(169,45,99,.08);
        position: relative;
        z-index: 1;
    }
    .ikr-step:last-child { border-bottom: none; }

    @media (min-width: 768px) {
        .ikr-step {
            flex-direction: column;
            align-items: flex-start;
            gap: 14px;
            padding: 0;
            border-bottom: none;
        }
    }
    @media (min-width: 1024px) {
        .ikr-step {
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 0 20px;
        }
    }

    .ikr-step-num {
        width: 56px; height: 56px;
        min-width: 56px;
        background: linear-gradient(135deg, var(--pink-dark), var(--pink), var(--pink-mid));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.2rem;
        color: var(--white);
        box-shadow:
            0 6px 20px rgba(169,45,99,.35),
            0 0 0 4px rgba(169,45,99,.12);
        position: relative;
        z-index: 2;
        flex-shrink: 0;
    }
    @media (min-width: 768px) {
        .ikr-step-num { width: 64px; height: 64px; min-width: 64px; font-size: 1.35rem; }
    }

    .ikr-step-body { flex: 1; }
    @media (min-width: 1024px) { .ikr-step-body { flex: unset; } }

    .ikr-step h3 {
        font-weight: 800;
        font-size: 1.05rem;
        color: var(--text-dark);
        letter-spacing: -.3px;
        margin-bottom: 8px;
        line-height: 1.3;
    }
    .ikr-step p {
        font-size: .84rem;
        font-weight: 400;
        line-height: 1.75;
        color: var(--text-mid);
    }

    /* ═══════════════════════════════════════════
       IMAGE GALLERY — FIXED
    ═══════════════════════════════════════════ */
    .ikr-gallery {
        background: var(--cream);
        padding: 90px 0 100px;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .ikr-gallery { padding: 110px 0 120px; } }

    .ikr-gallery::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 50% 40% at 90% 10%, rgba(169,45,99,.06) 0%, transparent 60%),
            radial-gradient(ellipse 40% 35% at 10% 90%, rgba(242,194,0,.05) 0%, transparent 60%);
        pointer-events: none;
    }

    .ikr-gallery-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }
    @media (min-width: 580px) {
        .ikr-gallery-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
    }
    @media (min-width: 900px) {
        .ikr-gallery-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }
        .ikr-gallery-item:first-child {
            grid-column: span 2;
            grid-row: span 2;
        }
    }

    .ikr-gallery-item {
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.07),
            0 8px 28px rgba(169,45,99,.12);
        transition: transform .4s cubic-bezier(.22,.9,.36,1),
                    box-shadow .4s;
    }
    .ikr-gallery-item:hover {
        transform: translateY(-6px);
        box-shadow:
            0 0 0 1px rgba(169,45,99,.12),
            0 20px 50px rgba(169,45,99,.20);
    }

    /* ── Gallery images fill their container ── */
    .ikr-gallery-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        aspect-ratio: 4 / 3;
        transition: transform .6s cubic-bezier(.22,.9,.36,1);
    }
    /* Featured (first) item — taller on desktop */
    @media (min-width: 900px) {
        .ikr-gallery-item:first-child img {
            aspect-ratio: unset;
            height: 100%;
            min-height: 100%;
        }
    }
    .ikr-gallery-item:hover img { transform: scale(1.06); }

    .ikr-gallery-caption {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        padding: 28px 20px 18px;
        background: linear-gradient(to top,
            rgba(106,15,53,.85) 0%,
            rgba(169,45,99,.20) 60%,
            transparent 100%);
        z-index: 2;
        opacity: 0;
        transform: translateY(8px);
        transition: opacity .35s, transform .35s;
    }
    .ikr-gallery-item:hover .ikr-gallery-caption {
        opacity: 1;
        transform: translateY(0);
    }
    .ikr-gallery-caption p {
        font-size: .78rem;
        font-weight: 600;
        color: var(--white);
        letter-spacing: .5px;
    }

    .ikr-gallery-item::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light));
        z-index: 3;
    }

    /* ═══════════════════════════════════════════
       COMMUNITY SECTION — FIXED
    ═══════════════════════════════════════════ */
    .ikr-community {
        background: var(--off-white);
        padding: 90px 0 100px;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .ikr-community { padding: 110px 0 120px; } }

    .ikr-community::before {
        content: '';
        position: absolute;
        top: -180px; right: -180px;
        width: 580px; height: 580px;
        border-radius: 50%;
        border: 75px solid rgba(169,45,99,.04);
        pointer-events: none;
    }

    .ikr-community-grid {
        display: flex;
        flex-direction: column;
        gap: 52px;
        align-items: center;
    }
    @media (min-width: 900px) {
        .ikr-community-grid {
            flex-direction: row-reverse;
            align-items: flex-start;
            gap: 72px;
        }
    }

    .ikr-community-img-col {
        width: 100%;
        max-width: 460px;
        flex-shrink: 0;
    }
    @media (min-width: 900px) { .ikr-community-img-col { max-width: 420px; } }

    .ikr-community-img {
        width: 100%;
        aspect-ratio: 4 / 3;
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.08),
            0 20px 60px rgba(169,45,99,.16),
            0 8px 24px rgba(0,0,0,.08);
    }

    /* ── Community image fills container ── */
    .ikr-community-img img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        transition: transform .7s cubic-bezier(.22,.9,.36,1);
    }
    .ikr-community-img:hover img { transform: scale(1.04); }
    .ikr-community-img::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light));
        z-index: 2;
        border-radius: 20px 20px 0 0;
    }

    .ikr-community-text { flex: 1; min-width: 0; }

    .ikr-community-text h2 {
        font-weight: 800;
        font-size: clamp(1.7rem, 4vw, 2.7rem);
        color: var(--text-dark);
        letter-spacing: -1.5px;
        line-height: 1.1;
        margin-bottom: 8px;
    }
    .ikr-community-text h2 .accent { color: var(--pink); }

    .ikr-community-text .sub {
        font-size: .96rem;
        font-weight: 500;
        color: var(--text-light);
        margin-bottom: 22px;
        line-height: 1.55;
    }

    .ikr-community-text p {
        font-size: .92rem;
        font-weight: 400;
        line-height: 1.95;
        color: var(--text-mid);
        margin-bottom: 16px;
    }

    .ikr-community-features {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-top: 28px;
    }

    .ikr-community-feat {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        background: var(--white);
        border-radius: 14px;
        padding: 16px 18px;
        border: 1px solid rgba(169,45,99,.08);
        box-shadow: 0 4px 16px rgba(169,45,99,.06);
        transition: transform .3s, box-shadow .3s;
    }
    .ikr-community-feat:hover {
        transform: translateX(6px);
        box-shadow: 0 8px 28px rgba(169,45,99,.12);
    }

    .ikr-feat-body strong {
        display: block;
        font-weight: 800;
        font-size: .92rem;
        color: var(--text-dark);
        letter-spacing: -.2px;
        margin-bottom: 4px;
    }
    .ikr-feat-body p {
        font-size: .80rem;
        font-weight: 400;
        line-height: 1.65;
        color: var(--text-mid);
        margin: 0;
    }

    /* ═══════════════════════════════════════════
       CTA / DONATE
    ═══════════════════════════════════════════ */
    .ikr-cta {
        background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 35%, var(--pink) 70%, var(--pink-mid) 100%);
        padding: 90px 24px 100px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .ikr-cta { padding: 110px 40px 120px; } }

    .ikr-cta::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 70% 60% at 15% 50%, rgba(242,194,0,.20) 0%, transparent 60%),
            radial-gradient(ellipse 50% 50% at 85% 20%, rgba(255,255,255,.08) 0%, transparent 55%);
        pointer-events: none;
    }
    .ikr-cta-deco1 {
        position: absolute;
        bottom: -120px; right: -120px;
        width: 400px; height: 400px;
        border-radius: 50%;
        border: 55px solid rgba(242,194,0,.07);
        pointer-events: none;
    }
    .ikr-cta-deco2 {
        position: absolute;
        top: -90px; left: -90px;
        width: 280px; height: 280px;
        border-radius: 50%;
        border: 40px solid rgba(255,255,255,.04);
        pointer-events: none;
    }

    .ikr-cta-inner {
        position: relative;
        z-index: 2;
        max-width: 700px;
        margin: 0 auto;
    }

    .ikr-cta-tag {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--yellow);
        margin-bottom: 20px;
    }
    .ikr-cta-tag::before, .ikr-cta-tag::after {
        content: '';
        display: block;
        width: 18px; height: 1px;
        background: var(--yellow);
        opacity: .5;
    }

    .ikr-cta h2 {
        font-weight: 800;
        font-size: clamp(2rem, 6vw, 3.4rem);
        color: var(--white);
        letter-spacing: -1.5px;
        line-height: 1.08;
        margin-bottom: 18px;
        text-shadow: 0 4px 30px rgba(0,0,0,.2);
    }
    .ikr-cta h2 em {
        font-style: normal;
        color: var(--yellow);
    }

    .ikr-cta p {
        font-size: .92rem;
        color: rgba(255,255,255,.55);
        line-height: 1.75;
        margin-bottom: 38px;
        max-width: 540px;
        margin-left: auto;
        margin-right: auto;
    }

    .ikr-cta-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        justify-content: center;
        align-items: center;
    }

    .ikr-btn-main {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: var(--yellow);
        color: var(--pink-dark);
        font-family: 'Manrope', sans-serif;
        font-size: .78rem;
        font-weight: 800;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 18px 34px;
        border-radius: 100px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: transform .3s, box-shadow .3s, background .3s;
        box-shadow: 0 8px 28px rgba(242,194,0,.45), 0 3px 8px rgba(0,0,0,.2);
    }
    .ikr-btn-main:hover {
        transform: translateY(-4px);
        background: var(--yellow-light);
        box-shadow: 0 16px 45px rgba(242,194,0,.55), 0 5px 14px rgba(0,0,0,.25);
    }
    .ikr-btn-main svg { width: 20px; height: 20px; flex-shrink: 0; }

    .ikr-btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: rgba(255,255,255,.08);
        color: var(--white);
        font-family: 'Manrope', sans-serif;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 17px 30px;
        border-radius: 100px;
        border: 1.5px solid rgba(255,255,255,.25);
        cursor: pointer;
        text-decoration: none;
        transition: all .3s;
        backdrop-filter: blur(6px);
    }
    .ikr-btn-ghost:hover {
        background: rgba(255,255,255,.15);
        border-color: rgba(255,255,255,.5);
        transform: translateY(-4px);
        box-shadow: 0 12px 35px rgba(0,0,0,.2);
    }
    .ikr-btn-ghost svg { width: 18px; height: 18px; flex-shrink: 0; }

    .ikr-cta-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,.08);
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 100px;
        padding: 10px 20px;
        font-size: .68rem;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: rgba(255,255,255,.60);
        margin-bottom: 32px;
    }
    .ikr-cta-pill strong {
        color: var(--yellow);
        font-weight: 800;
        font-size: .80rem;
    }
</style>

<!-- ═══════ HERO ═══════ -->
<section class="ikr-hero">
    <div class="ikr-hero-circle"></div>
    <div class="ikr-hero-dots">
        <span></span><span></span><span></span>
        <span></span><span></span><span></span>
    </div>
    <div class="ikr-hero-inner">
        <h1>Izzat Ki<em>Roti</em></h1>
        <p class="ikr-hero-desc">
            Feeding the hungry with dignity — no queues, no cameras, no stigma.
            Just a meal served with respect at your neighborhood tandoor.
        </p>
        <div class="ikr-hero-ornament">
            <div class="bar"></div>
            <div class="diamond"></div>
            <div class="bar r"></div>
        </div>
        <a href="#ikr-about" class="ikr-hero-scroll">
            Discover the Program
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14M5 12l7 7 7-7"/>
            </svg>
        </a>
    </div>
</section>

<!-- ═══════ ABOUT ═══════ -->
<section class="ikr-about" id="ikr-about">
    <div class="ikr-wrap">
        <div class="ikr-about-grid">

            <div class="ikr-about-img-col">
                <div class="ikr-img-main">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/DSC06030-scaled.jpg" alt="Izzat Ki Roti Program">
                </div>
                <div class="ikr-img-badge">
                    <div class="ikr-img-badge-icon">🕌</div>
                    <div class="ikr-img-badge-text">
                        <strong>100</strong>
                        <span>Tandoors Active</span>
                    </div>
                </div>
            </div>

            <div class="ikr-about-text-col">
                <div class="ikr-about-label">About the Program</div>
                <h2 class="ikr-about-title">
                    Food Security Meets<br>
                    <span class="accent">Human Dignity</span>
                </h2>
                <p class="ikr-about-subtitle">
                    A groundbreaking initiative that feeds the hungry without making them feel like they are begging
                </p>
                <div class="ikr-about-accent"></div>

                <p class="ikr-about-text">
                    The "Izzat Ki Roti" program addresses a critical intersection of food insecurity and human psychology. In a fluctuating economy, the need isn't just for food — it's for a system that preserves the recipient's self-worth.
                </p>

                <p class="ikr-about-text">
                    With rising inflation and the cost of flour (atta) hitting record highs, a basic staple like roti has become a luxury for daily wage laborers and low-income families. Traditional food lines often come with a social stigma that can discourage those who are struggling but too proud to beg from seeking help.
                </p>

                <div class="ikr-quote-block">
                    <p>By using existing neighborhood tandoors, the program integrates aid into the normal fabric of daily life — no one knows who paid and who didn't.</p>
                </div>

                <p class="ikr-about-text">
                    Currently, there are <strong>100 tandoors</strong> that are part of this initiative, each serving as a quiet, dignified access point for those in need across the community.
                </p>
            </div>

        </div>
    </div>
</section>

<!-- ═══════ THE DIGNITY MODEL ═══════ -->
<section class="ikr-dignity">
    <div class="ikr-dignity-deco1"></div>
    <div class="ikr-dignity-deco2"></div>
    <div class="ikr-wrap ikr-dignity-inner">

        <div class="ikr-dignity-header">
            <div class="ikr-dignity-tag">The Izzat Model</div>
            <h2>Eliminating the <span>"Handout" Feel</span></h2>
            <div class="ikr-dignity-divider"><div class="gem"></div></div>
            <p class="ikr-dignity-sub">
                Every element of this program is designed to protect the dignity and self-worth of the people it serves
            </p>
        </div>

        <div class="ikr-dignity-grid">
            <div class="ikr-dignity-card">
                <h3>Walk Up Like Any Customer</h3>
                <div class="ikr-dignity-card-divider"></div>
                <p>Deserving individuals can walk up to a local tandoor and receive their meal just like any other paying customer. There is no difference in treatment, no special line, and no marking.</p>
            </div>
            <div class="ikr-dignity-card">
                <h3>No Cameras, No Queues</h3>
                <div class="ikr-dignity-card-divider"></div>
                <p>There are no cameras documenting recipients, no long charity queues, and no visible branding that marks anyone as needy. The entire process is private, quiet, and respectful.</p>
            </div>
            <div class="ikr-dignity-card">
                <h3>Neighborhood Tandoors</h3>
                <div class="ikr-dignity-card-divider"></div>
                <p>By partnering with existing local tandoors, the program integrates seamlessly into the daily rhythm of community life. Aid is delivered through a trusted, familiar institution.</p>
            </div>
            <div class="ikr-dignity-card">
                <h3>Community Donation Boxes</h3>
                <div class="ikr-dignity-card-divider"></div>
                <p>Donation boxes placed at each tandoor site allow local community members to contribute directly. Neighbors helping neighbors — giving back to those on their very own street.</p>
            </div>
            <div class="ikr-dignity-card">
                <h3>Neighbor Helping Neighbor</h3>
                <div class="ikr-dignity-card-divider"></div>
                <p>The program fosters a grassroots ecosystem of local responsibility. It empowers communities to take immediate ownership of hunger on their specific street or block.</p>
            </div>
            <div class="ikr-dignity-card">
                <h3>Self-Worth Preserved</h3>
                <div class="ikr-dignity-card-divider"></div>
                <p>The greatest gift of this program isn't the roti — it is the dignity that comes with it. Recipients leave with their heads held high, their self-respect fully intact.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══════ HOW IT WORKS ═══════ -->
<section class="ikr-how">
    <div class="ikr-wrap">
        <div class="ikr-section-header">
            <div class="ikr-tag">Process</div>
            <h2>How <span class="accent">It Works</span></h2>
            <div class="ikr-divider"><div class="gem"></div></div>
            <p class="ikr-section-sub">A simple, seamless system built on trust, community, and compassion</p>
        </div>

        <div class="ikr-steps">
            <div class="ikr-step">
                <div class="ikr-step-num">1</div>
                <div class="ikr-step-body">
                    <h3>Tandoor Partnership</h3>
                    <p>Almarah Foundation partners with local neighborhood tandoors across the city, equipping them with donation boxes and a simple, confidential system to serve registered beneficiaries.</p>
                </div>
            </div>
            <div class="ikr-step">
                <div class="ikr-step-num">2</div>
                <div class="ikr-step-body">
                    <h3>Community Donates</h3>
                    <p>Neighbors, residents, and well-wishers contribute through the on-site donation boxes. Larger donations from organizations and individuals fund the program at scale.</p>
                </div>
            </div>
            <div class="ikr-step">
                <div class="ikr-step-num">3</div>
                <div class="ikr-step-body">
                    <h3>Beneficiary Visits</h3>
                    <p>Deserving families and individuals visit the tandoor just like any regular customer. No queues, no registration cards, no public identification — just a normal transaction.</p>
                </div>
            </div>
            <div class="ikr-step">
                <div class="ikr-step-num">4</div>
                <div class="ikr-step-body">
                    <h3>Dignity Delivered</h3>
                    <p>A meal is received with full dignity intact. The recipient walks away with roti — and more importantly — with their self-worth, pride, and confidence fully preserved.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════ GALLERY — FIXED HTML ═══════ -->
<section class="ikr-gallery">
    <div class="ikr-wrap">
        <div class="ikr-section-header">
            <div class="ikr-tag">Program in Action</div>
            <h2>Glimpses from the <span class="accent">Ground</span></h2>
            <div class="ikr-divider"><div class="gem"></div></div>
            <p class="ikr-section-sub">Real moments from the tandoors and communities we serve</p>
        </div>

        <div class="ikr-gallery-grid">

            <div class="ikr-gallery-item">
                <img src="https://almarah.org/wp-content/uploads/2026/05/WhatsApp-Image-2026-05-13-at-2.22.18-PM-1-copy-scaled.jpg" alt="Izzat Ki Roti Tandoor">
                <div class="ikr-gallery-caption"><p>A neighborhood tandoor serving the community with dignity</p></div>
            </div>

            <div class="ikr-gallery-item">
                <img src="https://almarah.org/wp-content/uploads/2026/05/DSC07430-scaled.jpg" alt="Partner Tandoor">
                <div class="ikr-gallery-caption"><p>One of 100+ active partner tandoors</p></div>
            </div>

            <div class="ikr-gallery-item">
                <img src="https://almarah.org/wp-content/uploads/2026/05/WhatsApp-Image-2026-05-13-at-2.22.24-PM-scaled.jpeg" alt="Donation Boxes">
                <div class="ikr-gallery-caption"><p>Community donation boxes at each site</p></div>
            </div>

            <div class="ikr-gallery-item">
                <img src="https://almarah.org/wp-content/uploads/2026/05/WhatsApp-Image-2026-05-13-at-2.22.15-PM-1-scaled.jpeg" alt="Families Served">
                <div class="ikr-gallery-caption"><p>Families served with dignity every day</p></div>
            </div>

            <div class="ikr-gallery-item">
                <img src="https://almarah.org/wp-content/uploads/2026/05/WhatsApp-Image-2026-05-13-at-2.22.22-PM-2-copy-scaled.jpg" alt="Community Volunteers">
                <div class="ikr-gallery-caption"><p>Community volunteers supporting the program</p></div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════ COMMUNITY — FIXED HTML ═══════ -->
<section class="ikr-community">
    <div class="ikr-wrap">
        <div class="ikr-community-grid">

            <div class="ikr-community-img-col">
                <div class="ikr-community-img">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/WhatsApp-Image-2026-05-13-at-2.22.15-PM-2-copy-scaled.jpg" alt="Community Ecosystem">
                </div>
            </div>

            <div class="ikr-community-text">
                <div class="ikr-about-label">Neighbor Helping Neighbor</div>
                <h2>A Community-Owned <span class="accent">Ecosystem</span></h2>
                <p class="sub">Where every donation stays on your street and feeds your neighbor</p>
                <div class="ikr-about-accent"></div>

                <p>By placing donation boxes at each tandoor site, the program fosters a "neighbor helping neighbor" ecosystem. It allows the local community to take immediate responsibility for the hungry in their specific street or block and give back to those who need it most.</p>

                <p>This hyper-local model means that money donated at a tandoor in your neighbourhood directly feeds someone from that same neighbourhood — creating a cycle of care, trust, and community ownership that no large institution can replicate.</p>

                <div class="ikr-community-features">
                    <div class="ikr-community-feat">
                        <div class="ikr-feat-body">
                            <strong>On-Site Donation Boxes</strong>
                            <p>Every partner tandoor has a donation box where anyone can contribute any amount at any time.</p>
                        </div>
                    </div>
                    <div class="ikr-community-feat">
                        <div class="ikr-feat-body">
                            <strong>Hyper-Local Impact</strong>
                            <p>Donations made at your local tandoor feed your actual neighbors — not strangers in another city.</p>
                        </div>
                    </div>
                    <div class="ikr-community-feat">
                        <div class="ikr-feat-body">
                            <strong>Complete Anonymity</strong>
                            <p>Neither donors nor recipients are publicly identified — pure giving with zero ego, zero display.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════ CTA ═══════ -->
<section class="ikr-cta">
    <div class="ikr-cta-deco1"></div>
    <div class="ikr-cta-deco2"></div>
    <div class="ikr-cta-inner">
        <div class="ikr-cta-tag">Make a Difference</div>
        <div class="ikr-cta-pill">
            <strong>100</strong> tandoors active across Pakistan
        </div>
        <h2>Help Us Feed More Families<br>with <em>Dignity</em></h2>
        <p>Every rupee you donate through Izzat Ki Roti goes directly toward providing roti at a local tandoor — preserving the self-worth of a family that is hungry but too proud to beg. Your contribution makes dignity possible.</p>
        <div class="ikr-cta-buttons">
            <a href="https://almarah.org/donate/" class="ikr-btn-main">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                Donate Now
            </a>
            <a href="https://almarah.org/contact/" class="ikr-btn-ghost">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Get Involved
            </a>
        </div>
    </div>
</section>
