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
        --green:        #1B7A3E;
        --green-soft:   rgba(27,122,62,.08);
        --green-light:  #2EA55A;
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
    .bnk-hero {
        background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 30%, var(--pink) 65%, var(--pink-mid) 100%);
        padding: 120px 24px 110px;
        text-align: center;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 520px;
    }
    @media (min-width: 768px) { .bnk-hero { padding: 160px 40px 140px; min-height: 600px; } }

    .bnk-hero::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 70% 60% at 10% 60%, rgba(242,194,0,.22) 0%, transparent 60%),
            radial-gradient(ellipse 50% 50% at 90% 15%, rgba(255,255,255,.10) 0%, transparent 55%),
            radial-gradient(ellipse 40% 40% at 50% 100%, rgba(0,0,0,.18) 0%, transparent 60%);
        pointer-events: none;
    }
    .bnk-hero::after {
        content: '';
        position: absolute;
        bottom: -130px; right: -130px;
        width: 450px; height: 450px;
        border-radius: 50%;
        border: 60px solid rgba(242,194,0,.07);
        pointer-events: none;
    }
    @media (min-width: 768px) {
        .bnk-hero::after {
            bottom: -200px; right: -200px;
            width: 700px; height: 700px;
        }
    }

    .bnk-hero-circle {
        position: absolute;
        top: -90px; left: -90px;
        width: 300px; height: 300px;
        border-radius: 50%;
        border: 45px solid rgba(255,255,255,.04);
        pointer-events: none;
    }
    @media (min-width: 768px) {
        .bnk-hero-circle { top: -150px; left: -150px; width: 550px; height: 550px; }
    }

    .bnk-hero-inner {
        position: relative;
        z-index: 2;
        max-width: 800px;
        margin: 0 auto;
    }

    .bnk-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 26px;
    }
    .bnk-eyebrow::before,
    .bnk-eyebrow::after {
        content: '';
        display: block;
        width: 40px; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(242,194,0,.7));
    }
    .bnk-eyebrow::after {
        background: linear-gradient(90deg, rgba(242,194,0,.7), transparent);
    }
    .bnk-eyebrow span {
        font-size: .58rem;
        font-weight: 700;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--yellow);
        text-shadow: 0 0 20px rgba(242,194,0,.4);
    }

    .bnk-hero h1 {
        font-weight: 800;
        font-size: clamp(2.4rem, 8vw, 5.2rem);
        color: var(--white);
        letter-spacing: -2px;
        line-height: 1.0;
        text-shadow: 0 4px 40px rgba(0,0,0,.25);
    }
    .bnk-hero h1 em {
        font-style: normal;
        color: var(--yellow);
        display: block;
        margin-top: 6px;
        filter: drop-shadow(0 0 30px rgba(242,194,0,.35));
    }

    .bnk-hero-sub {
        margin-top: 20px;
        font-size: .88rem;
        font-weight: 400;
        color: rgba(255,255,255,.55);
        line-height: 1.7;
        max-width: 560px;
        margin-left: auto;
        margin-right: auto;
    }

    .bnk-hero-ornament {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        margin-top: 34px;
    }
    .bnk-hero-ornament .bar {
        width: 50px; height: 2px;
        background: linear-gradient(90deg, transparent, var(--yellow));
        border-radius: 2px;
    }
    .bnk-hero-ornament .bar.r {
        background: linear-gradient(90deg, var(--yellow), transparent);
    }
    .bnk-hero-ornament .diamond {
        width: 10px; height: 10px;
        background: var(--yellow);
        transform: rotate(45deg);
        border-radius: 2px;
        box-shadow: 0 0 16px rgba(242,194,0,.6);
    }

    /* ═══════════════════════════════════════════
       TRUST STRIP
    ═══════════════════════════════════════════ */
    .bnk-trust-strip {
        background: linear-gradient(90deg, #6B0F35, var(--pink-dark), var(--pink));
        position: relative;
        z-index: 10;
    }
    .bnk-trust-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
    }
    @media (min-width: 640px) {
        .bnk-trust-wrap { grid-template-columns: repeat(4, 1fr); }
    }
    .bnk-trust-item {
        padding: 26px 16px;
        text-align: center;
        border-right: 1px solid rgba(255,255,255,.10);
    }
    .bnk-trust-item:last-child { border-right: none; }
    @media (max-width: 639px) {
        .bnk-trust-item:nth-child(even) { border-right: none; }
        .bnk-trust-item:nth-child(1),
        .bnk-trust-item:nth-child(2) { border-bottom: 1px solid rgba(255,255,255,.10); }
    }
    .bnk-trust-val {
        font-weight: 800;
        font-size: clamp(1.6rem, 3.5vw, 2.4rem);
        color: var(--yellow);
        letter-spacing: -1px;
        line-height: 1;
        text-shadow: 0 0 20px rgba(242,194,0,.35);
    }
    .bnk-trust-label {
        font-size: .56rem;
        font-weight: 600;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: rgba(255,255,255,.52);
        margin-top: 7px;
        line-height: 1.4;
    }

    /* ═══════════════════════════════════════════
       SHARED LAYOUT
    ═══════════════════════════════════════════ */
    .bnk-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    @media (min-width: 768px) { .bnk-wrap { padding: 0 36px; } }

    .bnk-section-header {
        text-align: center;
        margin-bottom: 56px;
    }

    .bnk-tag {
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
    .bnk-tag::before, .bnk-tag::after {
        content: '';
        display: block;
        width: 18px; height: 1px;
        background: var(--pink);
        opacity: .45;
    }

    .bnk-section-header h2 {
        font-weight: 800;
        font-size: clamp(1.9rem, 5.5vw, 3.2rem);
        color: var(--text-dark);
        letter-spacing: -1.5px;
        line-height: 1.08;
    }
    .bnk-section-header h2 .accent { color: var(--pink); }

    .bnk-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin: 20px 0 16px;
    }
    .bnk-divider::before, .bnk-divider::after {
        content: '';
        width: 55px; height: 2px;
        background: linear-gradient(90deg, transparent, var(--pink));
        border-radius: 2px;
    }
    .bnk-divider::after {
        background: linear-gradient(90deg, var(--pink), transparent);
    }
    .bnk-divider .gem {
        width: 9px; height: 9px;
        background: var(--yellow);
        transform: rotate(45deg);
        border-radius: 1px;
        box-shadow: 0 0 14px rgba(242,194,0,.55);
    }

    .bnk-section-sub {
        font-size: .90rem;
        color: var(--text-light);
        line-height: 1.65;
        max-width: 520px;
        margin: 0 auto;
    }

    /* ═══════════════════════════════════════════
       HOW TO DONATE INTRO
    ═══════════════════════════════════════════ */
    .bnk-intro {
        background: var(--off-white);
        padding: 80px 0 70px;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .bnk-intro { padding: 100px 0 90px; } }

    .bnk-intro::before {
        content: '';
        position: absolute;
        top: -180px; right: -180px;
        width: 500px; height: 500px;
        border-radius: 50%;
        border: 70px solid rgba(169,45,99,.04);
        pointer-events: none;
    }

    .bnk-methods {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
    }
    @media (min-width: 640px) {
        .bnk-methods { grid-template-columns: repeat(3, 1fr); }
    }

    .bnk-method-card {
        background: var(--white);
        border-radius: 18px;
        padding: 28px 24px;
        text-align: center;
        position: relative;
        overflow: hidden;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.06),
            0 8px 24px rgba(169,45,99,.07);
        transition: transform .35s cubic-bezier(.22,.9,.36,1), box-shadow .35s;
    }
    .bnk-method-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light));
        border-radius: 18px 18px 0 0;
    }
    .bnk-method-card:hover {
        transform: translateY(-6px);
        box-shadow:
            0 0 0 1px rgba(169,45,99,.10),
            0 18px 45px rgba(169,45,99,.13);
    }

    .bnk-method-icon {
        width: 60px; height: 60px;
        background: var(--pink-soft);
        border: 1.5px solid rgba(169,45,99,.14);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 18px;
        transition: background .3s;
    }
    .bnk-method-card:hover .bnk-method-icon {
        background: linear-gradient(135deg, var(--pink), var(--pink-mid));
    }

    .bnk-method-card h3 {
        font-weight: 800;
        font-size: 1.05rem;
        color: var(--text-dark);
        letter-spacing: -.3px;
        margin-bottom: 10px;
    }
    .bnk-method-card p {
        font-size: .83rem;
        font-weight: 400;
        line-height: 1.75;
        color: var(--text-mid);
    }

    /* ═══════════════════════════════════════════
       ZAKAT NOTICE BANNER
    ═══════════════════════════════════════════ */
    .bnk-zakat-banner {
        background: linear-gradient(135deg, #0a3d1f, var(--green), #2EA55A);
        padding: 28px 24px;
        position: relative;
        overflow: hidden;
        z-index: 5;
    }
    .bnk-zakat-banner::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 60% 60% at 10% 50%, rgba(255,255,255,.08) 0%, transparent 60%),
            radial-gradient(ellipse 40% 40% at 90% 50%, rgba(0,0,0,.15) 0%, transparent 60%);
        pointer-events: none;
    }

    .bnk-zakat-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }
    @media (min-width: 768px) { .bnk-zakat-inner { padding: 0 36px; flex-wrap: nowrap; } }

    .bnk-zakat-icon-wrap {
        width: 52px; height: 52px;
        min-width: 52px;
        background: rgba(255,255,255,.15);
        border: 1.5px solid rgba(255,255,255,.25);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        flex-shrink: 0;
        backdrop-filter: blur(6px);
    }

    .bnk-zakat-text { flex: 1; min-width: 0; }
    .bnk-zakat-text strong {
        display: block;
        font-weight: 800;
        font-size: .95rem;
        color: var(--white);
        letter-spacing: -.2px;
        margin-bottom: 4px;
    }
    .bnk-zakat-text p {
        font-size: .80rem;
        font-weight: 400;
        color: rgba(255,255,255,.72);
        line-height: 1.5;
    }
    .bnk-zakat-text p span {
        color: #90EE90;
        font-weight: 700;
    }

    .bnk-zakat-badge {
        background: rgba(255,255,255,.15);
        border: 1.5px solid rgba(255,255,255,.30);
        border-radius: 100px;
        padding: 8px 18px;
        font-size: .60rem;
        font-weight: 700;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: rgba(255,255,255,.90);
        white-space: nowrap;
        backdrop-filter: blur(6px);
        flex-shrink: 0;
    }

    /* ═══════════════════════════════════════════
       SECTION DIVIDER LABEL
    ═══════════════════════════════════════════ */
    .bnk-sub-label {
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 28px;
    }
    .bnk-sub-label-text {
        font-size: .58rem;
        font-weight: 700;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: var(--pink);
        white-space: nowrap;
    }
    .bnk-sub-label-line {
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(169,45,99,.25), transparent);
    }

    /* ═══════════════════════════════════════════
       MAIN BANKS SECTION
    ═══════════════════════════════════════════ */
    .bnk-main {
        background: var(--off-white);
        padding: 80px 0 90px;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .bnk-main { padding: 100px 0 110px; } }

    .bnk-main::before {
        content: '';
        position: absolute;
        bottom: -200px; left: -200px;
        width: 600px; height: 600px;
        border-radius: 50%;
        border: 80px solid rgba(169,45,99,.03);
        pointer-events: none;
    }

    .bnk-cards-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }
    @media (min-width: 640px) {
        .bnk-cards-grid { grid-template-columns: repeat(2, 1fr); gap: 22px; }
    }
    @media (min-width: 1024px) {
        .bnk-cards-grid { grid-template-columns: repeat(3, 1fr); gap: 24px; }
    }

    /* International section spacing */
    .bnk-intl-block {
        margin-top: 64px;
    }

    .bnk-cards-grid-intl {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }
    @media (min-width: 640px) {
        .bnk-cards-grid-intl { grid-template-columns: repeat(2, 1fr); gap: 22px; }
    }

    .bnk-card {
        background: var(--white);
        border-radius: 20px;
        overflow: hidden;
        position: relative;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.07),
            0 8px 24px rgba(169,45,99,.08),
            0 20px 50px rgba(169,45,99,.04);
        transition: transform .4s cubic-bezier(.22,.9,.36,1),
                    box-shadow .4s cubic-bezier(.22,.9,.36,1);
    }
    .bnk-card:hover {
        transform: translateY(-8px);
        box-shadow:
            0 0 0 1px rgba(169,45,99,.12),
            0 20px 50px rgba(169,45,99,.15),
            0 40px 80px rgba(169,45,99,.08);
    }
    .bnk-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light), var(--pink));
        z-index: 2;
    }

    /* International card accent */
    .bnk-card.intl::before {
        background: linear-gradient(90deg, #003580, #0057b8, #60a8e8, #0057b8);
    }
    .bnk-card.intl {
        box-shadow:
            0 0 0 1px rgba(0,53,128,.07),
            0 8px 24px rgba(0,53,128,.08),
            0 20px 50px rgba(0,53,128,.04);
    }
    .bnk-card.intl:hover {
        box-shadow:
            0 0 0 1px rgba(0,53,128,.12),
            0 20px 50px rgba(0,53,128,.15),
            0 40px 80px rgba(0,53,128,.08);
    }

    .bnk-card-logo-area {
        padding: 28px 24px 22px;
        display: flex;
        align-items: center;
        gap: 16px;
        border-bottom: 1px solid rgba(169,45,99,.06);
    }

    .bnk-card.intl .bnk-card-logo-area {
        border-bottom-color: rgba(0,53,128,.06);
    }

    .bnk-logo-box {
        width: 64px; height: 64px;
        min-width: 64px;
        border-radius: 14px;
        overflow: hidden;
        background: var(--white);
        border: 1.5px solid rgba(169,45,99,.10);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 14px rgba(0,0,0,.08);
        flex-shrink: 0;
    }
    .bnk-card.intl .bnk-logo-box {
        border-color: rgba(0,53,128,.10);
    }
    .bnk-logo-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 6px;
    }

    .bnk-logo-fallback {
        width: 64px; height: 64px;
        min-width: 64px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.1rem;
        color: var(--white);
        letter-spacing: -.5px;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(0,0,0,.15);
    }

    .bnk-card-name-wrap { flex: 1; min-width: 0; }
    .bnk-card-name {
        font-weight: 800;
        font-size: 1.0rem;
        color: var(--text-dark);
        letter-spacing: -.3px;
        line-height: 1.2;
        margin-bottom: 4px;
    }
    .bnk-card-type {
        font-size: .56rem;
        font-weight: 700;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: var(--pink);
    }
    .bnk-card.intl .bnk-card-type {
        color: #0057b8;
    }

    .bnk-card-details { padding: 20px 24px 24px; }

    .bnk-detail-row {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .bnk-detail-item {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    .bnk-detail-label {
        font-size: .52rem;
        font-weight: 700;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: var(--text-light);
    }
    .bnk-detail-val {
        font-weight: 700;
        font-size: .88rem;
        color: var(--text-dark);
        letter-spacing: .2px;
        line-height: 1.3;
    }

    .bnk-copy-btn {
        background: var(--pink-soft);
        border: 1px solid rgba(169,45,99,.12);
        border-radius: 8px;
        padding: 8px 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        font-family: 'Courier New', Courier, monospace;
        font-size: .88rem;
        font-weight: 700;
        color: var(--pink);
        letter-spacing: .8px;
        transition: background .25s, border-color .25s;
        text-align: left;
        word-break: break-all;
    }
    .bnk-copy-btn:hover {
        background: rgba(169,45,99,.14);
        border-color: rgba(169,45,99,.22);
    }

    /* International copy button */
    .bnk-copy-btn.intl-btn {
        background: rgba(0,53,128,.07);
        border-color: rgba(0,53,128,.12);
        color: #003580;
    }
    .bnk-copy-btn.intl-btn:hover {
        background: rgba(0,53,128,.13);
        border-color: rgba(0,53,128,.22);
    }

    .bnk-copy-btn svg {
        width: 16px; height: 16px;
        min-width: 16px;
        opacity: .5;
        flex-shrink: 0;
        transition: opacity .2s;
    }
    .bnk-copy-btn:hover svg { opacity: 1; }

    .bnk-card-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(169,45,99,.10), transparent);
    }
    .bnk-card.intl .bnk-card-divider {
        background: linear-gradient(90deg, transparent, rgba(0,53,128,.10), transparent);
    }

    /* Toast */
    .bnk-toast {
        position: fixed;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%) translateY(100px);
        background: var(--text-dark);
        color: var(--white);
        font-family: 'Manrope', sans-serif;
        font-size: .75rem;
        font-weight: 600;
        padding: 12px 24px;
        border-radius: 100px;
        box-shadow: 0 8px 28px rgba(0,0,0,.25);
        z-index: 9999;
        transition: transform .35s cubic-bezier(.22,.9,.36,1);
        pointer-events: none;
        white-space: nowrap;
    }
    .bnk-toast.show {
        transform: translateX(-50%) translateY(0);
    }

    /* ═══════════════════════════════════════════
       MEEZAN ZAKAT FEATURED SECTION
    ═══════════════════════════════════════════ */
    .bnk-zakat-section {
        background: var(--cream);
        padding: 80px 0 90px;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .bnk-zakat-section { padding: 100px 0 110px; } }

    .bnk-zakat-section::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 55% 50% at 5% 35%, rgba(27,122,62,.06) 0%, transparent 60%),
            radial-gradient(ellipse 45% 40% at 95% 65%, rgba(27,122,62,.04) 0%, transparent 60%);
        pointer-events: none;
    }

    .bnk-zakat-section .bnk-tag { color: var(--green); }
    .bnk-zakat-section .bnk-tag::before,
    .bnk-zakat-section .bnk-tag::after { background: var(--green); }
    .bnk-zakat-section .bnk-section-header h2 .accent { color: var(--green); }
    .bnk-zakat-section .bnk-divider::before {
        background: linear-gradient(90deg, transparent, var(--green));
    }
    .bnk-zakat-section .bnk-divider::after {
        background: linear-gradient(90deg, var(--green), transparent);
    }

    .bnk-meezan-featured {
        background: var(--white);
        border-radius: 24px;
        overflow: hidden;
        position: relative;
        box-shadow:
            0 0 0 2px rgba(27,122,62,.15),
            0 20px 60px rgba(27,122,62,.14),
            0 8px 24px rgba(0,0,0,.06);
        transition: transform .4s cubic-bezier(.22,.9,.36,1), box-shadow .4s;
    }
    .bnk-meezan-featured:hover {
        transform: translateY(-6px);
        box-shadow:
            0 0 0 2px rgba(27,122,62,.22),
            0 30px 80px rgba(27,122,62,.18);
    }
    .bnk-meezan-featured::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 5px;
        background: linear-gradient(90deg, #0a3d1f, var(--green), #90EE90, var(--green), #0a3d1f);
        z-index: 2;
    }

    .bnk-meezan-inner {
        display: flex;
        flex-direction: column;
    }
    @media (min-width: 768px) {
        .bnk-meezan-inner { flex-direction: row; }
    }

    .bnk-meezan-left {
        background: linear-gradient(145deg, #0a3d1f, #1B7A3E, #2EA55A);
        padding: 40px 32px;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
        position: relative;
        overflow: hidden;
        min-width: 260px;
    }
    @media (min-width: 768px) { .bnk-meezan-left { padding: 44px 36px; } }

    .bnk-meezan-left::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 70% 60% at 0% 40%, rgba(255,255,255,.10) 0%, transparent 60%),
            radial-gradient(ellipse 50% 50% at 100% 80%, rgba(0,0,0,.18) 0%, transparent 60%);
        pointer-events: none;
    }

    .bnk-meezan-logo-wrap {
        width: 80px; height: 80px;
        background: var(--white);
        border-radius: 18px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 24px rgba(0,0,0,.20);
        position: relative; z-index: 1;
    }
    .bnk-meezan-logo-wrap img {
        width: 100%; height: 100%;
        object-fit: contain;
        padding: 8px;
    }

    .bnk-meezan-bank-name {
        font-weight: 800;
        font-size: 1.4rem;
        color: var(--white);
        letter-spacing: -.5px;
        line-height: 1.1;
        position: relative; z-index: 1;
    }
    .bnk-meezan-bank-name span {
        display: block;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: rgba(255,255,255,.60);
        margin-top: 4px;
    }

    .bnk-meezan-zakat-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,.15);
        border: 1.5px solid rgba(255,255,255,.30);
        border-radius: 100px;
        padding: 8px 18px;
        font-size: .60rem;
        font-weight: 700;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: rgba(255,255,255,.90);
        position: relative; z-index: 1;
    }

    .bnk-meezan-desc {
        font-size: .82rem;
        font-weight: 400;
        color: rgba(255,255,255,.60);
        line-height: 1.7;
        position: relative; z-index: 1;
        max-width: 260px;
    }

    .bnk-meezan-right {
        flex: 1;
        padding: 36px 32px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    @media (min-width: 768px) { .bnk-meezan-right { padding: 40px 36px; } }

    .bnk-meezan-right-title {
        font-weight: 800;
        font-size: 1.3rem;
        color: var(--text-dark);
        letter-spacing: -.5px;
        margin-bottom: 4px;
    }
    .bnk-meezan-right-sub {
        font-size: .84rem;
        color: var(--text-light);
        line-height: 1.6;
    }

    .bnk-meezan-details-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 14px;
    }
    @media (min-width: 480px) {
        .bnk-meezan-details-grid { grid-template-columns: repeat(2, 1fr); }
    }

    .bnk-meezan-detail {
        background: var(--green-soft);
        border: 1px solid rgba(27,122,62,.12);
        border-radius: 12px;
        padding: 14px 16px;
    }
    .bnk-meezan-detail-label {
        font-size: .50rem;
        font-weight: 700;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: var(--green);
        margin-bottom: 5px;
    }
    .bnk-meezan-detail-val {
        font-weight: 700;
        font-size: .88rem;
        color: var(--text-dark);
        letter-spacing: .2px;
        line-height: 1.3;
    }

    .bnk-meezan-copy-btn {
        background: var(--green-soft);
        border: 1px solid rgba(27,122,62,.15);
        border-radius: 8px;
        padding: 10px 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        font-family: 'Courier New', Courier, monospace;
        font-size: .88rem;
        font-weight: 700;
        color: var(--green);
        letter-spacing: .8px;
        transition: background .25s, border-color .25s;
        text-align: left;
        word-break: break-all;
    }
    .bnk-meezan-copy-btn:hover {
        background: rgba(27,122,62,.14);
        border-color: rgba(27,122,62,.28);
    }
    .bnk-meezan-copy-btn svg {
        width: 16px; height: 16px;
        min-width: 16px;
        opacity: .5;
        flex-shrink: 0;
        transition: opacity .2s;
    }
    .bnk-meezan-copy-btn:hover svg { opacity: 1; }

    .bnk-meezan-notice {
        background: linear-gradient(135deg, rgba(27,122,62,.06), rgba(27,122,62,.10));
        border: 1px solid rgba(27,122,62,.18);
        border-radius: 12px;
        padding: 16px 18px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .bnk-meezan-notice-icon {
        font-size: 1.3rem;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .bnk-meezan-notice p {
        font-size: .80rem;
        font-weight: 500;
        line-height: 1.65;
        color: var(--green);
    }
    .bnk-meezan-notice p strong {
        font-weight: 800;
        color: #0a3d1f;
    }

    /* ═══════════════════════════════════════════
       IMPORTANT NOTES
    ═══════════════════════════════════════════ */
    .bnk-notes {
        background: linear-gradient(145deg, #5A0A2A 0%, var(--pink-dark) 35%, var(--pink) 70%, var(--pink-mid) 100%);
        padding: 80px 0 90px;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .bnk-notes { padding: 100px 0 110px; } }

    .bnk-notes::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 65% 60% at 5% 40%, rgba(242,194,0,.14) 0%, transparent 60%),
            radial-gradient(ellipse 50% 50% at 95% 60%, rgba(0,0,0,.20) 0%, transparent 55%);
        pointer-events: none;
    }

    .bnk-notes-deco1 {
        position: absolute;
        top: -150px; left: -150px;
        width: 500px; height: 500px;
        border-radius: 50%;
        border: 65px solid rgba(242,194,0,.05);
        pointer-events: none;
    }
    .bnk-notes-deco2 {
        position: absolute;
        bottom: -200px; right: -200px;
        width: 650px; height: 650px;
        border-radius: 50%;
        border: 80px solid rgba(255,255,255,.04);
        pointer-events: none;
    }

    .bnk-notes-inner { position: relative; z-index: 2; }

    .bnk-notes-header {
        text-align: center;
        margin-bottom: 48px;
    }
    .bnk-notes-tag {
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
    .bnk-notes-tag::before, .bnk-notes-tag::after {
        content: '';
        display: block;
        width: 18px; height: 1px;
        background: var(--yellow);
        opacity: .5;
    }
    .bnk-notes-header h2 {
        font-weight: 800;
        font-size: clamp(1.8rem, 5vw, 3rem);
        color: var(--white);
        letter-spacing: -1.5px;
        line-height: 1.08;
    }
    .bnk-notes-header h2 span { color: var(--yellow); }

    .bnk-notes-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin: 18px 0;
    }
    .bnk-notes-divider::before,
    .bnk-notes-divider::after {
        content: '';
        width: 55px; height: 2px;
        background: linear-gradient(90deg, transparent, var(--yellow));
        border-radius: 2px;
    }
    .bnk-notes-divider::after {
        background: linear-gradient(90deg, var(--yellow), transparent);
    }
    .bnk-notes-divider .gem {
        width: 9px; height: 9px;
        background: var(--yellow);
        transform: rotate(45deg);
        border-radius: 1px;
        box-shadow: 0 0 14px rgba(242,194,0,.55);
    }

    .bnk-notes-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
    }
    @media (min-width: 640px) {
        .bnk-notes-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (min-width: 1024px) {
        .bnk-notes-grid { grid-template-columns: repeat(3, 1fr); }
    }

    .bnk-note-card {
        background: rgba(255,255,255,.07);
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 16px;
        padding: 24px 22px;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        transition: background .3s, border-color .3s, transform .35s;
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    .bnk-note-card:hover {
        background: rgba(255,255,255,.12);
        border-color: rgba(242,194,0,.35);
        transform: translateY(-5px);
    }

    .bnk-note-icon {
        width: 44px; height: 44px;
        min-width: 44px;
        background: rgba(242,194,0,.12);
        border: 1.5px solid rgba(242,194,0,.22);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .bnk-note-body strong {
        display: block;
        font-weight: 800;
        font-size: .92rem;
        color: var(--white);
        margin-bottom: 7px;
        letter-spacing: -.2px;
        line-height: 1.3;
    }
    .bnk-note-body p {
        font-size: .80rem;
        font-weight: 400;
        line-height: 1.7;
        color: rgba(255,255,255,.52);
    }

    /* ═══════════════════════════════════════════
       CTA
    ═══════════════════════════════════════════ */
    .bnk-cta {
        background: var(--off-white);
        padding: 80px 24px 90px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .bnk-cta { padding: 100px 40px 110px; } }

    .bnk-cta::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 50% 50% at 90% 10%, rgba(169,45,99,.05) 0%, transparent 60%),
            radial-gradient(ellipse 40% 40% at 10% 90%, rgba(242,194,0,.04) 0%, transparent 60%);
        pointer-events: none;
    }

    .bnk-cta-inner {
        position: relative;
        z-index: 2;
        max-width: 680px;
        margin: 0 auto;
    }

    .bnk-cta-tag {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--pink);
        margin-bottom: 18px;
    }
    .bnk-cta-tag::before, .bnk-cta-tag::after {
        content: '';
        display: block;
        width: 18px; height: 1px;
        background: var(--pink);
        opacity: .4;
    }

    .bnk-cta h2 {
        font-weight: 800;
        font-size: clamp(1.8rem, 5vw, 3rem);
        color: var(--text-dark);
        letter-spacing: -1.5px;
        line-height: 1.08;
        margin-bottom: 16px;
    }
    .bnk-cta h2 .accent { color: var(--pink); }

    .bnk-cta p {
        font-size: .92rem;
        color: var(--text-light);
        line-height: 1.75;
        margin-bottom: 36px;
    }

    .bnk-cta-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        justify-content: center;
    }

    .bnk-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(135deg, var(--pink-dark), var(--pink), var(--pink-mid));
        color: var(--white);
        font-family: 'Manrope', sans-serif;
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 16px 30px;
        border-radius: 100px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: transform .3s, box-shadow .3s;
        box-shadow: 0 8px 24px rgba(169,45,99,.35);
        position: relative;
        overflow: hidden;
    }
    .bnk-btn-primary::before {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,.15) 0%, transparent 60%);
        border-radius: inherit;
        pointer-events: none;
    }
    .bnk-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 40px rgba(169,45,99,.45);
    }
    .bnk-btn-primary svg { width: 18px; height: 18px; flex-shrink: 0; }

    .bnk-btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: transparent;
        color: var(--pink);
        font-family: 'Manrope', sans-serif;
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        padding: 15px 28px;
        border-radius: 100px;
        border: 2px solid rgba(169,45,99,.25);
        cursor: pointer;
        text-decoration: none;
        transition: all .3s;
    }
    .bnk-btn-secondary:hover {
        background: var(--pink-soft);
        border-color: var(--pink);
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(169,45,99,.15);
    }
    .bnk-btn-secondary svg { width: 17px; height: 17px; flex-shrink: 0; }
</style>

<!-- Toast -->
<div class="bnk-toast" id="bnkToast">Account number copied!</div>

<!-- HERO -->
<section class="bnk-hero">
    <div class="bnk-hero-circle"></div>
    <div class="bnk-hero-inner">
        <h1>Donate via<em>Bank Transfer</em></h1>
        <p class="bnk-hero-sub">
            Transfer your donation securely to any of our verified bank accounts. Every rupee goes directly to our programs across Pakistan.
        </p>
        <div class="bnk-hero-ornament">
            <div class="bar"></div>
            <div class="diamond"></div>
            <div class="bar r"></div>
        </div>
    </div>
</section>

<!-- HOW TO DONATE -->
<section class="bnk-intro">
    <div class="bnk-wrap">
        <div class="bnk-section-header">
            <div class="bnk-tag">Simple Process</div>
            <h2>How to <span class="accent">Donate</span></h2>
            <div class="bnk-divider"><div class="gem"></div></div>
            <p class="bnk-section-sub">Three easy ways to get your donation to us</p>
        </div>
        <div class="bnk-methods">
            <div class="bnk-method-card">
                <div class="bnk-method-icon">🏦</div>
                <h3>Branch Deposit</h3>
                <p>Walk into any branch of our partner banks and deposit directly into the Almarah Foundation account. Show the account number below to the bank teller.</p>
            </div>
            <div class="bnk-method-card">
                <div class="bnk-method-icon">📱</div>
                <h3>Online Transfer</h3>
                <p>Use your bank app or internet banking to send funds. For transfers between different banks, use the IBAN number through RAAST or IBFT.</p>
            </div>
            <div class="bnk-method-card">
                <div class="bnk-method-icon">📧</div>
                <h3>Send Your Receipt</h3>
                <p>Once you have transferred, please send your payment receipt to info@almarah.org or WhatsApp us so we can confirm and acknowledge your donation.</p>
            </div>
        </div>
    </div>
</section>

<!-- ZAKAT NOTICE BANNER -->
<div class="bnk-zakat-banner">
    <div class="bnk-zakat-inner">
        <div class="bnk-zakat-text">
            <strong>Paying Zakat? Please use the Meezan Bank account.</strong>
            <p>Meezan Bank is Pakistan's largest Islamic bank. Your Zakat will be received and distributed in full accordance with Shariah. The dedicated <span>Meezan Bank Zakat account</span> is listed in the section below.</p>
        </div>
    </div>
</div>

<!-- MAIN BANK ACCOUNTS -->
<section class="bnk-main">
    <div class="bnk-wrap">

        <div class="bnk-section-header">
            <div class="bnk-tag">Partner Banks</div>
            <h2>Our <span class="accent">Bank Accounts</span></h2>
            <div class="bnk-divider"><div class="gem"></div></div>
            <p class="bnk-section-sub">All accounts are registered under Almarah Foundation. Tap any account number to copy it.</p>
        </div>

        <!-- Pakistan Accounts Label -->
        <div class="bnk-sub-label">
            <span class="bnk-sub-label-text">🇵🇰 &nbsp;Pakistan Accounts</span>
            <div class="bnk-sub-label-line"></div>
        </div>

        <div class="bnk-cards-grid">

            <!-- JS Bank -->
            <div class="bnk-card">
                <div class="bnk-card-logo-area">
                    <div class="bnk-logo-box">
                        <img src="https://almarah.org/wp-content/uploads/2026/05/jsbank.png" alt="JS Bank">
                    </div>
                    <div class="bnk-card-name-wrap">
                        <div class="bnk-card-name">JS Bank Limited</div>
                        <div class="bnk-card-type">JSB · Conventional Banking</div>
                    </div>
                </div>
                <div class="bnk-card-details">
                    <div class="bnk-detail-row">
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Title</div>
                            <div class="bnk-detail-val">Almarah Foundation</div>
                        </div>
                        <div class="bnk-card-divider"></div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Number (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'1828004')">
                                <span>1828004</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">IBAN (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'PK65JSBL9561000001828004')">
                                <span>PK65JSBL9561000001828004</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- UBL -->
            <div class="bnk-card">
                <div class="bnk-card-logo-area">
                    <div class="bnk-logo-box">
                        <img src="https://almarah.org/wp-content/uploads/2026/05/ubl.jpg" alt="UBL">
                    </div>
                    <div class="bnk-card-name-wrap">
                        <div class="bnk-card-name">United Bank Limited</div>
                        <div class="bnk-card-type">UBL · Conventional Banking</div>
                    </div>
                </div>
                <div class="bnk-card-details">
                    <div class="bnk-detail-row">
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Title</div>
                            <div class="bnk-detail-val">Almarah Foundation</div>
                        </div>
                        <div class="bnk-card-divider"></div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Number (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'1121289906918')">
                                <span>1121289906918</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">IBAN (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'PK54UNIL0109000289906918')">
                                <span>PK54UNIL0109000289906918</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- National Bank -->
            <div class="bnk-card">
                <div class="bnk-card-logo-area">
                    <div class="bnk-logo-box">
                        <img src="https://almarah.org/wp-content/uploads/2026/05/nationalbank.png" alt="National Bank of Pakistan">
                    </div>
                    <div class="bnk-card-name-wrap">
                        <div class="bnk-card-name">National Bank of Pakistan</div>
                        <div class="bnk-card-type">NBP · Conventional Banking</div>
                    </div>
                </div>
                <div class="bnk-card-details">
                    <div class="bnk-detail-row">
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Title</div>
                            <div class="bnk-detail-val">Almarah Foundation</div>
                        </div>
                        <div class="bnk-card-divider"></div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Number (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'3002565431')">
                                <span>3002565431</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">IBAN (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'PK80NBPA2142003002565431')">
                                <span>PK80NBPA2142003002565431</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Soneri Bank -->
            <div class="bnk-card">
                <div class="bnk-card-logo-area">
                    <div class="bnk-logo-box">
                        <img src="https://almarah.org/wp-content/uploads/2026/05/Soneri_Bank.png" alt="Soneri Bank">
                    </div>
                    <div class="bnk-card-name-wrap">
                        <div class="bnk-card-name">Soneri Bank Limited</div>
                        <div class="bnk-card-type">SNBL · Conventional Banking</div>
                    </div>
                </div>
                <div class="bnk-card-details">
                    <div class="bnk-detail-row">
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Title</div>
                            <div class="bnk-detail-val">Almarah Foundation</div>
                        </div>
                        <div class="bnk-card-divider"></div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Number (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'20010876495')">
                                <span>20010876495</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">IBAN (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'PK76SONE0025320010876495')">
                                <span>PK76SONE0025320010876495</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Allied Bank -->
            <div class="bnk-card">
                <div class="bnk-card-logo-area">
                    <div class="bnk-logo-box">
                        <img src="https://almarah.org/wp-content/uploads/2026/05/allied-bank.jpg" alt="Allied Bank">
                    </div>
                    <div class="bnk-card-name-wrap">
                        <div class="bnk-card-name">Allied Bank Limited</div>
                        <div class="bnk-card-type">ABL · Conventional Banking</div>
                    </div>
                </div>
                <div class="bnk-card-details">
                    <div class="bnk-detail-row">
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Title</div>
                            <div class="bnk-detail-val">Almarah Foundation</div>
                        </div>
                        <div class="bnk-card-divider"></div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Number (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'0010129951530014')">
                                <span>0010129951530014</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">IBAN (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'PK16ABPA0010129951530014')">
                                <span>PK16ABPA0010129951530014</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Alfalah -->
            <div class="bnk-card">
                <div class="bnk-card-logo-area">
                    <div class="bnk-logo-box">
                        <img src="https://almarah.org/wp-content/uploads/2026/05/Bank-Alfalah.jpg" alt="Bank Alfalah">
                    </div>
                    <div class="bnk-card-name-wrap">
                        <div class="bnk-card-name">Bank Alfalah</div>
                        <div class="bnk-card-type">BAFL · Conventional Banking</div>
                    </div>
                </div>
                <div class="bnk-card-details">
                    <div class="bnk-detail-row">
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Title</div>
                            <div class="bnk-detail-val">Almarah Foundation</div>
                        </div>
                        <div class="bnk-card-divider"></div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Number (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'56885002076517')">
                                <span>56885002076517</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">IBAN (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'PK98ALFH5688005002076517')">
                                <span>PK98ALFH5688005002076517</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Islami -->
            <div class="bnk-card">
                <div class="bnk-card-logo-area">
                    <div class="bnk-logo-box">
                        <img src="https://almarah.org/wp-content/uploads/2026/05/bankislami.png" alt="Bank Islami">
                    </div>
                    <div class="bnk-card-name-wrap">
                        <div class="bnk-card-name">Bank Islami Pakistan</div>
                        <div class="bnk-card-type">BIPL · Islamic Banking</div>
                    </div>
                </div>
                <div class="bnk-card-details">
                    <div class="bnk-detail-row">
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Title</div>
                            <div class="bnk-detail-val">Almarah Foundation</div>
                        </div>
                        <div class="bnk-card-divider"></div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Number (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'216300085400001')">
                                <span>216300085400001</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">IBAN (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'PK11BKIP0216300085400001')">
                                <span>PK11BKIP0216300085400001</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Meezan Bank General -->
            <div class="bnk-card">
                <div class="bnk-card-logo-area">
                    <div class="bnk-logo-box">
                        <img src="https://almarah.org/wp-content/uploads/2026/05/meezan-logo.png" alt="Meezan Bank">
                    </div>
                    <div class="bnk-card-name-wrap">
                        <div class="bnk-card-name">Meezan Bank</div>
                        <div class="bnk-card-type">MEBL · General Donations</div>
                    </div>
                </div>
                <div class="bnk-card-details">
                    <div class="bnk-detail-row">
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Title</div>
                            <div class="bnk-detail-val">Almarah Foundation</div>
                        </div>
                        <div class="bnk-card-divider"></div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">Account Number (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'02160113593207')">
                                <span>02160113593207</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                        <div class="bnk-detail-item">
                            <div class="bnk-detail-label">IBAN (tap to copy)</div>
                            <button class="bnk-copy-btn" onclick="bnkCopy(this,'PK71MEZN0002160113593207')">
                                <span>PK71MEZN0002160113593207</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ═══ INTERNATIONAL DONATIONS ═══ -->
        <div class="bnk-intl-block">

            <div class="bnk-sub-label">
                <span class="bnk-sub-label-text">🌍 &nbsp;International Donations</span>
                <div class="bnk-sub-label-line" style="background:linear-gradient(90deg,rgba(0,53,128,.25),transparent);"></div>
            </div>

            <div class="bnk-cards-grid-intl">

                <!-- Metro Bank -->
                <div class="bnk-card intl">
                    <div class="bnk-card-logo-area">
                        <div class="bnk-logo-box">
                            <img src="https://almarah.org/wp-content/uploads/2026/05/Metro_Bank_Logo.png" alt="Metro Bank">
                        </div>
                        <div class="bnk-card-name-wrap">
                            <div class="bnk-card-name">Metro Bank</div>
                            <div class="bnk-card-type">United Kingdom · GBP</div>
                        </div>
                    </div>
                    <div class="bnk-card-details">
                        <div class="bnk-detail-row">
                            <div class="bnk-detail-item">
                                <div class="bnk-detail-label">Account Title</div>
                                <div class="bnk-detail-val">Almarah Foundation</div>
                            </div>
                            <div class="bnk-card-divider"></div>
                            <div class="bnk-detail-item">
                                <div class="bnk-detail-label">Account Number (tap to copy)</div>
                                <button class="bnk-copy-btn intl-btn" onclick="bnkCopy(this,'54233566')">
                                    <span>54233566</span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                </button>
                            </div>
                            <div class="bnk-detail-item">
                                <div class="bnk-detail-label">Sort Code (tap to copy)</div>
                                <button class="bnk-copy-btn intl-btn" onclick="bnkCopy(this,'23-05-80')">
                                    <span>23-05-80</span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                </button>
                            </div>
                            <div class="bnk-detail-item">
                                <div class="bnk-detail-label">IBAN (tap to copy)</div>
                                <button class="bnk-copy-btn intl-btn" onclick="bnkCopy(this,'GB83MYMB23058054233566')">
                                    <span>GB83MYMB23058054233566</span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Zelle -->
                <div class="bnk-card intl">
                    <div class="bnk-card-logo-area">
                        <div class="bnk-logo-box">
                            <img src="https://almarah.org/wp-content/uploads/2026/05/image-600x338-1.jpg" alt="Zelle">
                        </div>
                        <div class="bnk-card-name-wrap">
                            <div class="bnk-card-name">Zelle</div>
                            <div class="bnk-card-type">United States · USD</div>
                        </div>
                    </div>
                    <div class="bnk-card-details">
                        <div class="bnk-detail-row">
                            <div class="bnk-detail-item">
                                <div class="bnk-detail-label">Account Title</div>
                                <div class="bnk-detail-val">Almarah Foundation</div>
                            </div>
                            <div class="bnk-card-divider"></div>
                            <div class="bnk-detail-item">
                                <div class="bnk-detail-label">Zelle Phone (tap to copy)</div>
                                <button class="bnk-copy-btn intl-btn" onclick="bnkCopy(this,'+1 571 300 4508')">
                                    <span>+1 571 300 4508</span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                </button>
                            </div>
                            <div class="bnk-detail-item">
                                <div class="bnk-detail-label">How to send</div>
                                <div class="bnk-detail-val" style="font-size:.80rem;color:var(--text-light);font-weight:500;">Open your Zelle app or bank's Zelle feature and send to the phone number above. Available through most US banks.</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- end international block -->

    </div>
</section>

<!-- MEEZAN ZAKAT FEATURED -->
<section class="bnk-zakat-section">
    <div class="bnk-wrap">

        <div class="bnk-section-header">
            <div class="bnk-tag">Islamic Banking</div>
            <h2>Meezan Bank <span class="accent">Zakat Account</span></h2>
            <div class="bnk-divider"><div class="gem"></div></div>
            <p class="bnk-section-sub">For Zakat payments only. Handled with full Shariah compliance through Pakistan's largest Islamic bank.</p>
        </div>

        <div class="bnk-meezan-featured">
            <div class="bnk-meezan-inner">

                <div class="bnk-meezan-left">
                    <div class="bnk-meezan-logo-wrap">
                        <img src="https://almarah.org/wp-content/uploads/2026/05/meezan-logo.png"
                             alt="Meezan Bank"
                             onerror="this.parentElement.innerHTML='<span style=\'font-weight:800;font-size:1rem;color:#1B7A3E;\'>MEBL</span>'">
                    </div>
                    <div class="bnk-meezan-bank-name">
                        Meezan Bank
                        <span>Pakistan's No.1 Islamic Bank</span>
                    </div>
                    <div class="bnk-meezan-zakat-pill">Zakat Specific Account</div>
                    <p class="bnk-meezan-desc">
                        This account is for Zakat only. Meezan Bank runs on full Islamic banking principles so your Zakat reaches the right people the right way.
                    </p>
                </div>

                <div class="bnk-meezan-right">
                    <div>
                        <div class="bnk-meezan-right-title">Zakat Account Details</div>
                        <p class="bnk-meezan-right-sub">Use these details when sending your Zakat. Tap any field to copy.</p>
                    </div>

                    <div class="bnk-meezan-details-grid">
                        <div class="bnk-meezan-detail">
                            <div class="bnk-meezan-detail-label">Account Title</div>
                            <div class="bnk-meezan-detail-val">Almarah Foundation</div>
                        </div>
                        <div class="bnk-meezan-detail">
                            <div class="bnk-meezan-detail-label">Bank Name</div>
                            <div class="bnk-meezan-detail-val">Meezan Bank Limited</div>
                        </div>
                        <div class="bnk-meezan-detail" style="grid-column: 1 / -1;">
                            <div class="bnk-meezan-detail-label">Account Number (tap to copy)</div>
                            <button class="bnk-meezan-copy-btn" onclick="bnkCopy(this,'02160113593207')">
                                <span>02160113593207</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                        <div class="bnk-meezan-detail" style="grid-column: 1 / -1;">
                            <div class="bnk-meezan-detail-label">IBAN (tap to copy)</div>
                            <button class="bnk-meezan-copy-btn" onclick="bnkCopy(this,'PK71MEZN0002160113593207')">
                                <span>PK71MEZN0002160113593207</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="bnk-meezan-notice">
                        <p>
                            <strong>Shariah Compliant:</strong> Meezan Bank is Pakistan's first and largest dedicated Islamic bank. All Zakat received through this account is managed under Islamic finance principles and distributed to eligible recipients as defined by Shariah law.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- IMPORTANT NOTES -->
<section class="bnk-notes">
    <div class="bnk-notes-deco1"></div>
    <div class="bnk-notes-deco2"></div>
    <div class="bnk-wrap bnk-notes-inner">

        <div class="bnk-notes-header">
            <div class="bnk-notes-tag">Please Read</div>
            <h2>A Few <span>Important Points</span></h2>
            <div class="bnk-notes-divider"><div class="gem"></div></div>
        </div>

        <div class="bnk-notes-grid">

            <div class="bnk-note-card">
                                <div class="bnk-note-body">
                    <strong>Always Send Your Receipt</strong>
                    <p>After completing your transfer, please email or WhatsApp your receipt to info@almarah.org. This helps us confirm your donation and send you an acknowledgement.</p>
                </div>
            </div>

            <div class="bnk-note-card">
                                <div class="bnk-note-body">
                    <strong>Zakat Goes to Meezan Only</strong>
                    <p>Please use the Meezan Bank Zakat account for all Zakat payments. This keeps your Zakat fully Shariah compliant from the moment it leaves your account.</p>
                </div>
            </div>

            <div class="bnk-note-card">
                              <div class="bnk-note-body">
                    <strong>Use Only These Accounts</strong>
                    <p>All accounts on this page are officially registered to Almarah Foundation. Please do not transfer to any other account that claims to be ours.</p>
                </div>
            </div>

            <div class="bnk-note-card">
                               <div class="bnk-note-body">
                    <strong>Every Rupee Reaches the Program</strong>
                    <p>We keep our operations lean so your donation goes where it matters. Almarah Foundation publishes its financials annually for full transparency.</p>
                </div>
            </div>

            <div class="bnk-note-card">
                               <div class="bnk-note-body">
                    <strong>Donating from Abroad</strong>
                    <p>Use Metro Bank (UK) or Zelle (US) for international transfers. For wire transfers or SWIFT details, write to us at info@almarah.org and we will assist you.</p>
                </div>
            </div>

            <div class="bnk-note-card">
                <div class="bnk-note-body">
                    <strong>We Are Here to Help</strong>
                    <p>If you are unsure about anything or need help completing your donation, get in touch with us. Our team is happy to walk you through the process.</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- CTA -->
<section class="bnk-cta">
    <div class="bnk-cta-inner">
        <div class="bnk-cta-tag">Make a Difference</div>
        <h2>Your Donation <span class="accent">Changes Lives</span></h2>
        <p>Every contribution helps Almarah Foundation deliver dignity, education, and opportunity to the people who need it most across Pakistan.</p>
        <div class="bnk-cta-buttons">
            <a href="https://almarah.org/donate/" class="bnk-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                Donate Online
            </a>
            <a href="https://almarah.org/contact/" class="bnk-btn-secondary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.58 3.49 2 2 0 0 1 3.55 1.27h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6 6l.9-.9a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 16z"/>
                </svg>
                Contact Us
            </a>
        </div>
    </div>
</section>

<script>
function bnkCopy(btn, text) {
    var str = text || btn.querySelector('span').textContent.trim();
    navigator.clipboard.writeText(str).then(function() {
        showToast(str);
    }).catch(function() {
        var ta = document.createElement('textarea');
        ta.value = str;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        showToast(str);
    });
}

function showToast(text) {
    var toast = document.getElementById('bnkToast');
    toast.textContent = 'Copied: ' + text;
    toast.classList.add('show');
    setTimeout(function() {
        toast.classList.remove('show');
    }, 2500);
}
</script>