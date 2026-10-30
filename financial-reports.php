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
        background-color: var(--off-white);
        color: var(--text-dark);
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
    }

    /* ═══════════════════════════════════════════
       HERO
    ═══════════════════════════════════════════ */
    .fr-hero {
        background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 30%, var(--pink) 65%, var(--pink-mid) 100%);
        padding: 110px 24px 80px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .fr-hero { padding: 130px 40px 90px; } }

    .fr-hero::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 70% 60% at 10% 60%, rgba(242,194,0,.18) 0%, transparent 60%),
            radial-gradient(ellipse 50% 50% at 90% 15%, rgba(255,255,255,.09) 0%, transparent 55%),
            radial-gradient(ellipse 40% 40% at 50% 100%, rgba(0,0,0,.14) 0%, transparent 60%);
        pointer-events: none;
    }

    .fr-hero-circle-tl {
        position: absolute;
        top: -100px; left: -100px;
        width: 380px; height: 380px;
        border-radius: 50%;
        border: 55px solid rgba(255,255,255,.04);
        pointer-events: none;
    }
    .fr-hero-circle-br {
        position: absolute;
        bottom: -80px; right: -160px;
        width: 500px; height: 500px;
        border-radius: 50%;
        border: 70px solid rgba(242,194,0,.05);
        pointer-events: none;
    }
    @media (min-width: 768px) {
        .fr-hero-circle-tl { top: -150px; left: -150px; width: 600px; height: 600px; }
        .fr-hero-circle-br { bottom: -120px; right: -220px; width: 750px; height: 750px; }
    }

    .fr-hero-inner {
        position: relative;
        z-index: 2;
        max-width: 700px;
        margin: 0 auto;
    }

    .fr-hero h1 {
        font-weight: 800;
        font-size: clamp(2.2rem, 7vw, 4.2rem);
        color: var(--white);
        letter-spacing: -2px;
        line-height: 1.05;
        text-shadow: 0 4px 40px rgba(0,0,0,.22);
        margin-bottom: 16px;
    }
    .fr-hero h1 em {
        font-style: normal;
        color: var(--yellow);
        filter: drop-shadow(0 0 24px rgba(242,194,0,.30));
    }

    .fr-hero-sub {
        font-size: .92rem;
        font-weight: 400;
        color: rgba(255,255,255,.55);
        line-height: 1.75;
        max-width: 500px;
        margin: 0 auto 36px;
    }

    .fr-hero-badges {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .fr-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 18px;
        border-radius: 100px;
        background: rgba(255,255,255,.10);
        border: 1.5px solid rgba(255,255,255,.16);
        backdrop-filter: blur(8px);
        font-size: .66rem;
        font-weight: 700;
        color: rgba(255,255,255,.85);
        letter-spacing: .5px;
    }
    .fr-hero-badge svg {
        width: 15px; height: 15px;
        flex-shrink: 0;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .fr-hero-badge.gold {
        background: rgba(242,194,0,.15);
        border-color: rgba(242,194,0,.30);
        color: var(--yellow-light);
    }

    /* ═══════════════════════════════════════════
       REPORTS SECTION
    ═══════════════════════════════════════════ */
    .fr-main {
        background: var(--off-white);
        padding: 80px 0 100px;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .fr-main { padding: 100px 0 120px; } }

    .fr-main::before {
        content: '';
        position: absolute;
        top: -200px; right: -200px;
        width: 600px; height: 600px;
        border-radius: 50%;
        border: 80px solid rgba(169,45,99,.03);
        pointer-events: none;
    }
    .fr-main::after {
        content: '';
        position: absolute;
        bottom: -180px; left: -180px;
        width: 500px; height: 500px;
        border-radius: 50%;
        border: 65px solid rgba(242,194,0,.03);
        pointer-events: none;
    }

    .fr-wrap {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 1;
    }
    @media (min-width: 768px) { .fr-wrap { padding: 0 36px; } }

    .fr-section-header {
        text-align: center;
        margin-bottom: 48px;
    }
    .fr-section-tag {
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
    .fr-section-tag::before, .fr-section-tag::after {
        content: '';
        display: block;
        width: 18px; height: 1px;
        background: var(--pink);
        opacity: .45;
    }
    .fr-section-title {
        font-weight: 800;
        font-size: clamp(1.8rem, 4.5vw, 2.8rem);
        color: var(--text-dark);
        letter-spacing: -1.5px;
        line-height: 1.1;
        margin-bottom: 14px;
    }
    .fr-section-title .accent { color: var(--pink); }
    .fr-section-desc {
        font-size: .92rem;
        color: var(--text-light);
        line-height: 1.75;
        max-width: 520px;
        margin: 0 auto;
    }

    /* Year filter tabs */
    .fr-year-tabs {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 44px;
    }

    .fr-year-tab {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 20px;
        border-radius: 100px;
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: 1px;
        border: 1.5px solid rgba(169,45,99,.14);
        background: var(--white);
        color: var(--text-light);
        cursor: pointer;
        transition: all .25s;
        font-family: 'Manrope', sans-serif;
    }
    .fr-year-tab:hover {
        border-color: var(--pink);
        color: var(--pink);
        background: var(--pink-soft);
    }
    .fr-year-tab.active {
        background: linear-gradient(135deg, var(--pink-dark), var(--pink));
        border-color: transparent;
        color: var(--white);
        box-shadow: 0 4px 14px rgba(169,45,99,.30);
    }

    /* Reports grid */
    .fr-reports-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
        max-width: 820px;
        margin: 0 auto;
    }
    @media (min-width: 600px) {
        .fr-reports-grid { grid-template-columns: repeat(2, 1fr); }
    }

    /* Report card */
    .fr-report-card {
        background: var(--white);
        border-radius: 20px;
        padding: 26px 24px;
        display: flex;
        align-items: center;
        gap: 18px;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.06),
            0 8px 28px rgba(169,45,99,.07);
        text-decoration: none;
        transition: transform .35s cubic-bezier(.22,.9,.36,1), box-shadow .35s;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    .fr-report-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, var(--pink-dark), var(--pink));
        border-radius: 20px 0 0 20px;
        transition: width .3s;
    }
    .fr-report-card:hover {
        transform: translateY(-5px);
        box-shadow:
            0 0 0 1px rgba(169,45,99,.10),
            0 20px 50px rgba(169,45,99,.12);
    }
    .fr-report-card:hover::before { width: 6px; }

    /* Latest card */
    .fr-report-card.latest {
        background: linear-gradient(135deg, rgba(242,194,0,.05), rgba(169,45,99,.03));
        border: 1.5px solid rgba(242,194,0,.18);
        box-shadow:
            0 0 0 1px rgba(242,194,0,.10),
            0 12px 40px rgba(169,45,99,.09);
    }
    .fr-report-card.latest::before {
        background: linear-gradient(180deg, var(--yellow-dark), var(--yellow));
    }

    .fr-report-icon-wrap {
        width: 52px; height: 52px;
        border-radius: 14px;
        background: var(--pink-soft);
        border: 1.5px solid rgba(169,45,99,.12);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all .3s;
    }
    .fr-report-icon-wrap svg {
        width: 22px; height: 22px;
        stroke: var(--pink);
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        transition: stroke .3s;
    }
    .fr-report-card:hover .fr-report-icon-wrap {
        background: linear-gradient(135deg, var(--pink-dark), var(--pink));
        border-color: transparent;
    }
    .fr-report-card:hover .fr-report-icon-wrap svg { stroke: white; }

    .fr-report-card.latest .fr-report-icon-wrap {
        background: var(--yellow-soft);
        border-color: rgba(242,194,0,.22);
    }
    .fr-report-card.latest .fr-report-icon-wrap svg { stroke: var(--yellow-dark); }
    .fr-report-card.latest:hover .fr-report-icon-wrap {
        background: linear-gradient(135deg, var(--yellow-dark), var(--yellow));
        border-color: transparent;
    }
    .fr-report-card.latest:hover .fr-report-icon-wrap svg { stroke: var(--text-dark); }

    .fr-report-info { flex: 1; min-width: 0; }

    .fr-report-year-badge {
        font-size: .50rem;
        font-weight: 700;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: var(--pink);
        margin-bottom: 4px;
        display: block;
    }
    .fr-report-card.latest .fr-report-year-badge { color: var(--yellow-dark); }

    .fr-report-name {
        font-weight: 700;
        font-size: .90rem;
        color: var(--text-dark);
        letter-spacing: -.2px;
        line-height: 1.35;
        margin-bottom: 8px;
    }

    .fr-report-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .fr-report-type {
        font-size: .58rem;
        font-weight: 600;
        color: var(--text-light);
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .fr-report-type svg {
        width: 12px; height: 12px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .fr-latest-tag {
        display: inline-flex;
        align-items: center;
        font-size: .48rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        background: linear-gradient(135deg, var(--yellow-dark), var(--yellow));
        color: var(--text-dark);
        padding: 3px 10px;
        border-radius: 100px;
        box-shadow: 0 3px 10px rgba(242,194,0,.30);
    }

    .fr-report-arrow {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: var(--pink-soft);
        border: 1.5px solid rgba(169,45,99,.12);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all .3s;
    }
    .fr-report-arrow svg {
        width: 14px; height: 14px;
        stroke: var(--pink);
        fill: none;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
        transition: all .3s;
    }
    .fr-report-card:hover .fr-report-arrow {
        background: var(--pink);
        border-color: var(--pink);
    }
    .fr-report-card:hover .fr-report-arrow svg {
        stroke: white;
        transform: translateY(2px);
    }
    .fr-report-card.latest .fr-report-arrow {
        background: var(--yellow-soft);
        border-color: rgba(242,194,0,.22);
    }
    .fr-report-card.latest .fr-report-arrow svg { stroke: var(--yellow-dark); }
    .fr-report-card.latest:hover .fr-report-arrow {
        background: var(--yellow);
        border-color: var(--yellow);
    }
    .fr-report-card.latest:hover .fr-report-arrow svg {
        stroke: var(--text-dark);
        transform: translateY(2px);
    }

    .fr-report-card.hidden { display: none; }

    /* ═══════════════════════════════════════════
       TRANSPARENCY SECTION
    ═══════════════════════════════════════════ */
    .fr-commit {
        background: var(--white);
        padding: 80px 0 90px;
        position: relative;
        overflow: hidden;
    }
    .fr-commit::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--yellow-dark), var(--yellow), var(--yellow-light));
    }

    .fr-commit-inner {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 20px;
    }
    @media (min-width: 768px) { .fr-commit-inner { padding: 0 36px; } }

    .fr-commit-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 48px;
        align-items: center;
    }
    @media (min-width: 860px) {
        .fr-commit-layout { grid-template-columns: 1fr 1fr; gap: 64px; }
    }

    .fr-commit-tag {
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
    .fr-commit-tag::before {
        content: '';
        width: 16px; height: 1px;
        background: var(--pink);
        opacity: .45;
    }

    .fr-commit-title {
        font-weight: 800;
        font-size: clamp(1.6rem, 4vw, 2.4rem);
        color: var(--text-dark);
        letter-spacing: -1.2px;
        line-height: 1.1;
        margin-bottom: 16px;
    }
    .fr-commit-title .accent { color: var(--pink); }

    .fr-commit-body {
        font-size: .92rem;
        color: var(--text-mid);
        line-height: 1.85;
        margin-bottom: 32px;
    }

    .fr-commit-points {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .fr-commit-point {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .fr-commit-point-icon {
        width: 36px; height: 36px;
        border-radius: 10px;
        background: var(--pink-soft);
        border: 1.5px solid rgba(169,45,99,.12);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .fr-commit-point-icon svg {
        width: 16px; height: 16px;
        stroke: var(--pink);
        fill: none;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .fr-commit-point-title {
        font-weight: 700;
        font-size: .88rem;
        color: var(--text-dark);
        margin-bottom: 2px;
    }
    .fr-commit-point-desc {
        font-size: .78rem;
        color: var(--text-light);
        line-height: 1.55;
    }

    /* Visual side */
    .fr-commit-visual { position: relative; }

    .fr-commit-img-wrap {
        border-radius: 24px;
        overflow: hidden;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.07),
            0 20px 60px rgba(169,45,99,.12);
        position: relative;
    }
    .fr-commit-img-wrap::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light));
        z-index: 2;
    }
    .fr-commit-img-wrap img {
        width: 100%;
        aspect-ratio: 4/3;
        object-fit: cover;
        display: block;
    }

    .fr-commit-img-badge {
        position: absolute;
        bottom: -16px; right: 24px;
        background: var(--white);
        border-radius: 16px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,.12);
        border: 1px solid rgba(169,45,99,.08);
        z-index: 3;
    }
    .fr-commit-img-badge-icon {
        width: 40px; height: 40px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--yellow-dark), var(--yellow));
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .fr-commit-img-badge-icon svg {
        width: 20px; height: 20px;
        stroke: var(--text-dark);
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .fr-commit-img-badge-val {
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--text-dark);
        letter-spacing: -.5px;
        line-height: 1;
        margin-bottom: 2px;
    }
    .fr-commit-img-badge-label {
        font-size: .55rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--text-light);
    }

    /* ═══════════════════════════════════════════
       CTA
    ═══════════════════════════════════════════ */
    .fr-cta {
        background: linear-gradient(145deg, #5A0A2A 0%, var(--pink-dark) 35%, var(--pink) 70%, var(--pink-mid) 100%);
        padding: 80px 24px 90px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .fr-cta { padding: 100px 40px 110px; } }

    .fr-cta::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 65% 60% at 5% 40%, rgba(242,194,0,.14) 0%, transparent 60%),
            radial-gradient(ellipse 50% 50% at 95% 60%, rgba(0,0,0,.20) 0%, transparent 55%);
        pointer-events: none;
    }
    .fr-cta-deco1 {
        position: absolute;
        top: -150px; left: -150px;
        width: 500px; height: 500px;
        border-radius: 50%;
        border: 65px solid rgba(242,194,0,.05);
        pointer-events: none;
    }
    .fr-cta-deco2 {
        position: absolute;
        bottom: -200px; right: -200px;
        width: 650px; height: 650px;
        border-radius: 50%;
        border: 80px solid rgba(255,255,255,.04);
        pointer-events: none;
    }

    .fr-cta-inner {
        position: relative;
        z-index: 2;
        max-width: 640px;
        margin: 0 auto;
    }

    .fr-cta-ornament {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        margin-bottom: 28px;
    }
    .fr-cta-ornament .bar {
        width: 50px; height: 2px;
        background: linear-gradient(90deg, transparent, var(--yellow));
        border-radius: 2px;
    }
    .fr-cta-ornament .bar.r {
        background: linear-gradient(90deg, var(--yellow), transparent);
    }
    .fr-cta-ornament .diamond {
        width: 10px; height: 10px;
        background: var(--yellow);
        transform: rotate(45deg);
        border-radius: 2px;
        box-shadow: 0 0 16px rgba(242,194,0,.6);
    }

    .fr-cta h2 {
        font-weight: 800;
        font-size: clamp(1.8rem, 5vw, 3rem);
        color: var(--white);
        letter-spacing: -1.5px;
        line-height: 1.08;
        margin-bottom: 14px;
    }
    .fr-cta h2 .accent { color: var(--yellow); }

    .fr-cta p {
        font-size: .92rem;
        color: rgba(255,255,255,.55);
        line-height: 1.75;
        margin-bottom: 36px;
    }

    .fr-cta-btns {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        justify-content: center;
    }

    .fr-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 30px;
        border-radius: 100px;
        background: linear-gradient(135deg, var(--yellow-dark), var(--yellow), var(--yellow-light));
        color: var(--text-dark);
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: 1px;
        text-decoration: none;
        transition: all .3s;
        box-shadow: 0 8px 28px rgba(242,194,0,.40);
        position: relative;
        overflow: hidden;
    }
    .fr-btn-primary::before {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,.20) 0%, transparent 60%);
        border-radius: inherit;
        pointer-events: none;
    }
    .fr-btn-primary svg {
        width: 18px; height: 18px;
        flex-shrink: 0;
        stroke: currentColor;
        fill: none;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .fr-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 44px rgba(242,194,0,.50);
    }

    .fr-btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 15px 28px;
        border-radius: 100px;
        background: rgba(255,255,255,.10);
        border: 1.5px solid rgba(255,255,255,.20);
        color: rgba(255,255,255,.85);
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: 1px;
        text-decoration: none;
        transition: all .3s;
        backdrop-filter: blur(8px);
    }
    .fr-btn-ghost svg {
        width: 17px; height: 17px;
        flex-shrink: 0;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .fr-btn-ghost:hover {
        background: rgba(255,255,255,.18);
        border-color: rgba(255,255,255,.35);
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(0,0,0,.20);
    }
</style>

<!-- HERO -->
<section class="fr-hero">
    <div class="fr-hero-circle-tl"></div>
    <div class="fr-hero-circle-br"></div>
    <div class="fr-hero-inner">
        <h1>Financial <em>Reports</em></h1>
        <p class="fr-hero-sub">
            At Almarah Foundation, transparency is the foundation of trust. Every donation is used responsibly and every rupee is accounted for publicly.
        </p>
        <div class="fr-hero-badges">
            <div class="fr-hero-badge gold">
                <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Fully Audited
            </div>
            <div class="fr-hero-badge">
                <svg viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                100% Transparent
            </div>
            <div class="fr-hero-badge">
                <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Free to Download
            </div>
        </div>
    </div>
</section>

<!-- REPORTS -->
<section class="fr-main">
    <div class="fr-wrap">

        <div class="fr-section-header">
            <div class="fr-section-tag">Download Reports</div>
            <h2 class="fr-section-title">Audit and Financial <span class="accent">Reports</span></h2>
            <p class="fr-section-desc">
                All reports are independently audited and publicly available. Click any report to download the PDF directly.
            </p>
        </div>

        <!-- Year tabs -->
        <div class="fr-year-tabs">
            <button class="fr-year-tab active" data-filter="all">All Years</button>
            <button class="fr-year-tab" data-filter="2026">2025 – 26</button>
            <button class="fr-year-tab" data-filter="2025">2024 – 25</button>
            <button class="fr-year-tab" data-filter="2024">2024</button>
            <button class="fr-year-tab" data-filter="2023">2023</button>
            <button class="fr-year-tab" data-filter="2022">2022</button>
            <button class="fr-year-tab" data-filter="2021">2021</button>
        </div>

        <!-- Cards (descending order) -->
        <div class="fr-reports-grid">

            <!-- 2025-26 LATEST -->
            <a href="https://almarah.org/wp-content/uploads/2026/09/Audit-Report-Almarah-Foundation-2025-2026.pdf"
               target="_blank" rel="noopener noreferrer"
               class="fr-report-card latest"
               data-year="2026">
                <div class="fr-report-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="fr-report-info">
                    <span class="fr-report-year-badge">Financial Year 2025 – 26</span>
                    <div class="fr-report-name">Audit Report Almarah Foundation 2025–2026</div>
                    <div class="fr-report-meta">
                        <span class="fr-report-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            PDF Download
                        </span>
                        <span class="fr-latest-tag">Latest</span>
                    </div>
                </div>
                <div class="fr-report-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>

            <!-- 2024-25 -->
            <a href="https://almarah.org/wp-content/uploads/2026/04/Alamarah-Foundation-Financial-Report-24-25.pdf"
               target="_blank" rel="noopener noreferrer"
               class="fr-report-card"
               data-year="2025">
                <div class="fr-report-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="fr-report-info">
                    <span class="fr-report-year-badge">Financial Year 2024 – 25</span>
                    <div class="fr-report-name">Almarah Foundation Financial Report 2024–25</div>
                    <div class="fr-report-meta">
                        <span class="fr-report-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            PDF Download
                        </span>
                    </div>
                </div>
                <div class="fr-report-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>

            <!-- 2024 -->
            <a href="https://almarah.org/wp-content/uploads/2026/04/Audit-Report-and-Financial-June-2024.pdf"
               target="_blank" rel="noopener noreferrer"
               class="fr-report-card"
               data-year="2024">
                <div class="fr-report-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="fr-report-info">
                    <span class="fr-report-year-badge">Financial Year 2024</span>
                    <div class="fr-report-name">Audit &amp; Financial Report June 2024</div>
                    <div class="fr-report-meta">
                        <span class="fr-report-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            PDF Download
                        </span>
                    </div>
                </div>
                <div class="fr-report-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>

            <!-- 2023 -->
            <a href="https://almarah.org/wp-content/uploads/2026/04/Audit-Report-and-Financial-June-2023.pdf"
               target="_blank" rel="noopener noreferrer"
               class="fr-report-card"
               data-year="2023">
                <div class="fr-report-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="fr-report-info">
                    <span class="fr-report-year-badge">Financial Year 2023</span>
                    <div class="fr-report-name">Audit &amp; Financial Report June 2023</div>
                    <div class="fr-report-meta">
                        <span class="fr-report-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            PDF Download
                        </span>
                    </div>
                </div>
                <div class="fr-report-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>

            <!-- 2022 -->
            <a href="https://almarah.org/wp-content/uploads/2026/04/Audit-Report-and-Financial-June-2022.pdf"
               target="_blank" rel="noopener noreferrer"
               class="fr-report-card"
               data-year="2022">
                <div class="fr-report-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="fr-report-info">
                    <span class="fr-report-year-badge">Financial Year 2022</span>
                    <div class="fr-report-name">Audit &amp; Financial Report June 2022</div>
                    <div class="fr-report-meta">
                        <span class="fr-report-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            PDF Download
                        </span>
                    </div>
                </div>
                <div class="fr-report-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>

            <!-- 2021 -->
            <a href="https://almarah.org/wp-content/uploads/2026/04/Audit-Report-and-Financial-June-2021.pdf"
               target="_blank" rel="noopener noreferrer"
               class="fr-report-card"
               data-year="2021">
                <div class="fr-report-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="fr-report-info">
                    <span class="fr-report-year-badge">Financial Year 2021</span>
                    <div class="fr-report-name">Audit &amp; Financial Report June 2021</div>
                    <div class="fr-report-meta">
                        <span class="fr-report-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            PDF Download
                        </span>
                    </div>
                </div>
                <div class="fr-report-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>

        </div>
    </div>
</section>

<!-- TRANSPARENCY COMMITMENT -->
<section class="fr-commit">
    <div class="fr-commit-inner">
        <div class="fr-commit-layout">

            <div>
                <div class="fr-commit-tag">Our Commitment</div>
                <h2 class="fr-commit-title">Why <span class="accent">Transparency</span> Matters to Us</h2>
                <p class="fr-commit-body">
                    At Almarah Foundation, we hold ourselves to the highest standards of financial accountability. Every donation carries a responsibility and we take that seriously. Our independently audited reports are published openly so that our donors, partners, and the communities we serve can always see exactly how funds are used.
                </p>
                <div class="fr-commit-points">
                    <div class="fr-commit-point">
                        <div class="fr-commit-point-icon">
                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div>
                            <div class="fr-commit-point-title">Independent Auditors</div>
                            <div class="fr-commit-point-desc">All reports are reviewed and verified by independent certified auditors every year.</div>
                        </div>
                    </div>
                    <div class="fr-commit-point">
                        <div class="fr-commit-point-icon">
                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div>
                            <div class="fr-commit-point-title">Publicly Available</div>
                            <div class="fr-commit-point-desc">Anyone can download and review our complete financial records free of charge at any time.</div>
                        </div>
                    </div>
                    <div class="fr-commit-point">
                        <div class="fr-commit-point-icon">
                            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        <div>
                            <div class="fr-commit-point-title">Annual Reporting Cycle</div>
                            <div class="fr-commit-point-desc">We publish a complete audit and financial report every year without exception since 2021.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fr-commit-visual">
                <div class="fr-commit-img-wrap">
                    <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800&q=80"
                         alt="Financial Transparency at Almarah Foundation">
                </div>
                <div class="fr-commit-img-badge">
                    <div class="fr-commit-img-badge-icon">
                        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div>
                        <div class="fr-commit-img-badge-val">2M+ Beneficiaries</div>
                        <div class="fr-commit-img-badge-label">Supported Since 2021</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- CTA -->
<section class="fr-cta">
    <div class="fr-cta-deco1"></div>
    <div class="fr-cta-deco2"></div>
    <div class="fr-cta-inner">
        <div class="fr-cta-ornament">
            <div class="bar"></div>
            <div class="diamond"></div>
            <div class="bar r"></div>
        </div>
        <h2>Your Donation is in <span class="accent">Safe Hands</span></h2>
        <p>
            We have published every rupee of income and expenditure since 2021. Join thousands of donors who trust Almarah Foundation to make a real difference across Pakistan.
        </p>
        <div class="fr-cta-btns">
            <a href="https://almarah.org/donate/" class="fr-btn-primary">
                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                Donate Now
            </a>
            <a href="https://almarah.org/contact/" class="fr-btn-ghost">
                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Ask a Question
            </a>
        </div>
    </div>
</section>

<script>
(function() {
    const tabs  = document.querySelectorAll('.fr-year-tab');
    const cards = document.querySelectorAll('.fr-report-card');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const filter = tab.dataset.filter;
            cards.forEach(card => {
                if (filter === 'all' || card.dataset.year === filter) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });
    });
})();
</script>