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
    .eb-hero {
        background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 30%, var(--pink) 65%, var(--pink-mid) 100%);
        padding: 110px 24px 100px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .eb-hero { padding: 150px 40px 130px; } }

    .eb-hero::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 70% 60% at 10% 60%, rgba(242,194,0,.22) 0%, transparent 60%),
            radial-gradient(ellipse 50% 50% at 90% 15%, rgba(255,255,255,.10) 0%, transparent 55%),
            radial-gradient(ellipse 40% 40% at 50% 100%, rgba(0,0,0,.15) 0%, transparent 60%);
        pointer-events: none;
    }
    .eb-hero::after {
        content: '';
        position: absolute;
        bottom: -120px; right: -120px;
        width: 420px; height: 420px;
        border-radius: 50%;
        border: 55px solid rgba(242,194,0,.07);
        pointer-events: none;
    }
    @media (min-width: 768px) {
        .eb-hero::after {
            bottom: -200px; right: -200px;
            width: 700px; height: 700px;
            border-width: 80px;
        }
    }

    .eb-hero-circle {
        position: absolute;
        top: -80px; left: -80px;
        width: 280px; height: 280px;
        border-radius: 50%;
        border: 40px solid rgba(255,255,255,.04);
        pointer-events: none;
    }
    @media (min-width: 768px) {
        .eb-hero-circle {
            top: -130px; left: -130px;
            width: 500px; height: 500px;
        }
    }

    .eb-hero-inner { position: relative; z-index: 2; }

    .eb-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 28px;
    }
    .eb-eyebrow::before,
    .eb-eyebrow::after {
        content: '';
        display: block;
        width: 36px; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(242,194,0,.7));
    }
    .eb-eyebrow::after {
        background: linear-gradient(90deg, rgba(242,194,0,.7), transparent);
    }
    @media (min-width: 768px) {
        .eb-eyebrow::before,
        .eb-eyebrow::after { width: 60px; }
    }
    .eb-eyebrow span {
        font-size: .58rem;
        font-weight: 700;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--yellow);
        text-shadow: 0 0 20px rgba(242,194,0,.4);
    }

    .eb-hero h1 {
        font-weight: 800;
        font-size: clamp(2.4rem, 9vw, 5.5rem);
        color: var(--white);
        letter-spacing: -2px;
        line-height: 1.0;
        text-shadow: 0 4px 40px rgba(0,0,0,.25);
    }
    .eb-hero h1 em {
        font-style: normal;
        color: var(--yellow);
        display: block;
        margin-top: 6px;
        filter: drop-shadow(0 0 30px rgba(242,194,0,.35));
    }

    .eb-hero-sub {
        margin-top: 20px;
        font-size: .72rem;
        font-weight: 500;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: rgba(255,255,255,.45);
    }

    .eb-hero-ornament {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 14px;
        margin-top: 36px;
    }
    .eb-hero-ornament .bar {
        width: 50px; height: 2px;
        background: linear-gradient(90deg, transparent, var(--yellow));
        border-radius: 2px;
    }
    .eb-hero-ornament .bar.r {
        background: linear-gradient(90deg, var(--yellow), transparent);
    }
    .eb-hero-ornament .diamond {
        width: 10px; height: 10px;
        background: var(--yellow);
        transform: rotate(45deg);
        border-radius: 2px;
        box-shadow: 0 0 16px rgba(242,194,0,.6);
    }

    /* ═══════════════════════════════════════════
       SHARED LAYOUT
    ═══════════════════════════════════════════ */
    .eb-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    @media (min-width: 768px) { .eb-wrap { padding: 0 36px; } }

    /* ═══════════════════════════════════════════
       EBOOK SHOWCASE SECTION
    ═══════════════════════════════════════════ */
    .eb-showcase {
        background: var(--off-white);
        padding: 80px 0 90px;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .eb-showcase { padding: 110px 0 120px; } }

    .eb-showcase::before {
        content: '';
        position: absolute;
        top: -200px; right: -200px;
        width: 600px; height: 600px;
        border-radius: 50%;
        border: 80px solid rgba(169,45,99,.04);
        pointer-events: none;
    }

    /* Main layout: cover + info */
    .eb-main {
        display: flex;
        flex-direction: column;
        gap: 48px;
        align-items: center;
    }
    @media (min-width: 900px) {
        .eb-main {
            flex-direction: row;
            align-items: flex-start;
            gap: 64px;
        }
    }

    /* ── Book Cover ── */
    .eb-cover-col {
        width: 100%;
        max-width: 300px;
        flex-shrink: 0;
    }
    @media (min-width: 900px) { .eb-cover-col { max-width: 280px; } }
    @media (min-width: 1100px) { .eb-cover-col { max-width: 310px; } }

    /* 3D book tilt effect */
    .eb-book-3d {
        position: relative;
        width: 100%;
        filter: drop-shadow(0 40px 60px rgba(169,45,99,.35))
                drop-shadow(0 20px 30px rgba(0,0,0,.2));
        transition: transform .5s cubic-bezier(.22,.9,.36,1), filter .5s;
        transform: perspective(1000px) rotateY(-8deg) rotateX(2deg);
    }
    .eb-book-3d:hover {
        transform: perspective(1000px) rotateY(-3deg) rotateX(1deg) translateY(-8px);
        filter: drop-shadow(0 55px 80px rgba(169,45,99,.40))
                drop-shadow(0 25px 40px rgba(0,0,0,.25));
    }

    .eb-book-wrap {
        position: relative;
        border-radius: 4px 12px 12px 4px;
        overflow: hidden;
        background: linear-gradient(160deg, var(--pink-dark), #6B0F35);
    }

    /* Left spine shadow */
    .eb-book-wrap::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 26px; height: 100%;
        background: linear-gradient(90deg,
            rgba(0,0,0,.38) 0%,
            rgba(0,0,0,.12) 55%,
            transparent 100%);
        z-index: 2;
        border-radius: 4px 0 0 4px;
    }

    /* Shine overlay */
    .eb-book-wrap::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(135deg,
            rgba(255,255,255,.16) 0%,
            transparent 40%,
            transparent 70%,
            rgba(0,0,0,.10) 100%);
        z-index: 3;
        pointer-events: none;
        border-radius: inherit;
    }

    /* Actual cover image — replace src with your thumbnail */
    .eb-cover-img {
        width: 100%;
        display: block;
        border-radius: 2px 10px 10px 2px;
    }

    /* Placeholder cover (used until real image is set) */
    .eb-cover-placeholder {
        width: 100%;
        aspect-ratio: 3 / 4;
        background: linear-gradient(155deg, #6B0F35 0%, var(--pink-dark) 40%, var(--pink) 100%);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 18px;
        padding: 32px 20px;
        text-align: center;
        position: relative;
        border-radius: 2px 10px 10px 2px;
    }
    .eb-cover-placeholder::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse at 30% 20%, rgba(242,194,0,.22) 0%, transparent 60%),
            radial-gradient(ellipse at 70% 80%, rgba(0,0,0,.22) 0%, transparent 60%);
        border-radius: inherit;
    }

    .eb-cover-icon {
        width: 52px; height: 52px;
        background: rgba(242,194,0,.14);
        border: 2px solid rgba(242,194,0,.32);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.7rem;
        position: relative; z-index: 1;
        backdrop-filter: blur(6px);
    }

    .eb-cover-ph-title {
        position: relative; z-index: 1;
        font-weight: 800;
        font-size: 1.4rem;
        color: var(--white);
        letter-spacing: -1px;
        line-height: 1.18;
        text-shadow: 0 2px 18px rgba(0,0,0,.3);
    }
    .eb-cover-ph-title span {
        color: var(--yellow);
        display: block;
    }

    .eb-cover-ph-line {
        width: 36px; height: 2px;
        background: linear-gradient(90deg, var(--yellow), rgba(242,194,0,.3));
        border-radius: 2px;
        position: relative; z-index: 1;
    }

    .eb-cover-ph-author {
        position: relative; z-index: 1;
        font-size: .68rem;
        font-weight: 600;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: rgba(255,255,255,.50);
    }

    /* FREE badge */
    .eb-cover-badge {
        position: absolute;
        top: 16px; right: 16px;
        width: 54px; height: 54px;
        background: var(--yellow);
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 5;
        box-shadow: 0 4px 18px rgba(242,194,0,.5);
    }
    .eb-cover-badge span:first-child {
        font-size: .42rem;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: var(--pink-dark);
        line-height: 1;
    }
    .eb-cover-badge span:last-child {
        font-size: .60rem;
        font-weight: 800;
        color: var(--pink-dark);
        line-height: 1;
        margin-top: 1px;
    }

    /* Stats row */
    .eb-cover-stats {
        display: flex;
        align-items: center;
        margin-top: 24px;
        background: var(--white);
        border-radius: 14px;
        overflow: hidden;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.08),
            0 8px 24px rgba(169,45,99,.08);
    }

    .eb-stat {
        flex: 1;
        padding: 14px 8px;
        text-align: center;
        position: relative;
    }
    .eb-stat + .eb-stat::before {
        content: '';
        position: absolute;
        left: 0; top: 22%; bottom: 22%;
        width: 1px;
        background: rgba(169,45,99,.12);
    }

    .eb-stat-val {
        font-weight: 800;
        font-size: 1.15rem;
        color: var(--pink);
        letter-spacing: -.5px;
        line-height: 1;
    }
    .eb-stat-label {
        font-size: .55rem;
        font-weight: 600;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--text-light);
        margin-top: 5px;
    }

    /* ── Book Info ── */
    .eb-info-col { flex: 1; min-width: 0; }

    .eb-info-tag {
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
    .eb-info-tag::before, .eb-info-tag::after {
        content: '';
        display: block;
        width: 16px; height: 1px;
        background: var(--pink);
        opacity: .4;
    }

    .eb-info-title {
        font-weight: 800;
        font-size: clamp(1.8rem, 4vw, 3rem);
        color: var(--text-dark);
        letter-spacing: -1.5px;
        line-height: 1.08;
        margin-bottom: 8px;
    }
    .eb-info-title .accent { color: var(--pink); }

    .eb-info-subtitle {
        font-size: .98rem;
        font-weight: 500;
        color: var(--text-light);
        margin-bottom: 24px;
        line-height: 1.55;
    }

    .eb-accent-line {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 22px;
    }
    .eb-accent-line::before {
        content: '';
        width: 36px; height: 3px;
        background: linear-gradient(90deg, var(--pink), var(--pink-mid));
        border-radius: 2px;
    }
    .eb-accent-line::after {
        content: '';
        width: 8px; height: 8px;
        background: var(--yellow);
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(242,194,0,.6);
    }

    .eb-info-desc {
        font-size: .92rem;
        font-weight: 400;
        line-height: 1.9;
        color: var(--text-mid);
        margin-bottom: 30px;
    }

    /* What's inside */
    .eb-inside-title {
        font-size: .56rem;
        font-weight: 700;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: var(--pink);
        margin-bottom: 16px;
    }

    .eb-inside-list {
        list-style: none;
        display: grid;
        grid-template-columns: 1fr;
        gap: 10px;
        margin-bottom: 34px;
    }
    @media (min-width: 480px) {
        .eb-inside-list { grid-template-columns: repeat(2, 1fr); }
    }

    .eb-inside-list li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: .86rem;
        font-weight: 500;
        color: var(--text-mid);
        line-height: 1.5;
    }

    .eb-check-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px; height: 20px;
        min-width: 20px;
        background: linear-gradient(135deg, var(--pink), var(--pink-mid));
        border-radius: 50%;
        margin-top: 1px;
        box-shadow: 0 3px 10px rgba(169,45,99,.3);
        flex-shrink: 0;
    }
    .eb-check-icon svg {
        width: 10px; height: 10px;
        fill: none;
        stroke: var(--white);
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    /* CTA buttons */
    .eb-cta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        align-items: center;
    }

    .eb-btn-primary {
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
        padding: 16px 28px;
        border-radius: 100px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: transform .3s, box-shadow .3s, filter .3s;
        box-shadow:
            0 8px 24px rgba(169,45,99,.35),
            0 2px 6px rgba(169,45,99,.2);
        position: relative;
        overflow: hidden;
    }
    .eb-btn-primary::before {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(135deg,
            rgba(255,255,255,.15) 0%,
            transparent 60%);
        border-radius: inherit;
        pointer-events: none;
    }
    .eb-btn-primary:hover {
        transform: translateY(-3px);
        box-shadow:
            0 16px 40px rgba(169,45,99,.45),
            0 4px 10px rgba(169,45,99,.25);
        filter: brightness(1.05);
    }
    .eb-btn-primary svg {
        width: 18px; height: 18px;
        flex-shrink: 0;
    }

    .eb-btn-secondary {
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
        padding: 15px 26px;
        border-radius: 100px;
        border: 2px solid rgba(169,45,99,.25);
        cursor: pointer;
        text-decoration: none;
        transition: all .3s;
    }
    .eb-btn-secondary:hover {
        background: var(--pink-soft);
        border-color: var(--pink);
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(169,45,99,.15);
    }
    .eb-btn-secondary svg {
        width: 17px; height: 17px;
        flex-shrink: 0;
    }

    .eb-free-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: var(--yellow-soft);
        border: 1px solid rgba(242,194,0,.28);
        border-radius: 100px;
        padding: 8px 16px;
        font-size: .60rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--yellow-dark);
    }
    .eb-free-badge::before {
        content: '★';
        font-size: .68rem;
        color: var(--yellow);
    }

    /* ═══════════════════════════════════════════
       PDF VIEWER SECTION
    ═══════════════════════════════════════════ */
    .eb-viewer-section {
        background: linear-gradient(145deg, #5A0A2A 0%, var(--pink-dark) 35%, var(--pink) 70%, var(--pink-mid) 100%);
        position: relative;
        overflow: hidden;
        padding: 80px 0 90px;
    }
    @media (min-width: 768px) { .eb-viewer-section { padding: 110px 0 120px; } }

    .eb-viewer-section::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 60% 55% at 5% 35%, rgba(242,194,0,.14) 0%, transparent 60%),
            radial-gradient(ellipse 45% 45% at 95% 65%, rgba(0,0,0,.20) 0%, transparent 55%);
        pointer-events: none;
    }

    .eb-viewer-deco {
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }
    .eb-viewer-deco.d1 {
        top: -150px; left: -150px;
        width: 500px; height: 500px;
        border: 60px solid rgba(242,194,0,.05);
    }
    .eb-viewer-deco.d2 {
        bottom: -200px; right: -200px;
        width: 600px; height: 600px;
        border: 70px solid rgba(255,255,255,.04);
    }

    .eb-viewer-inner { position: relative; z-index: 2; }

    .eb-viewer-header {
        text-align: center;
        margin-bottom: 52px;
    }

    .eb-viewer-tag {
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
    .eb-viewer-tag::before, .eb-viewer-tag::after {
        content: '';
        display: block;
        width: 16px; height: 1px;
        background: var(--yellow);
        opacity: .5;
    }

    .eb-viewer-header h2 {
        font-weight: 800;
        font-size: clamp(1.8rem, 5vw, 3rem);
        color: var(--white);
        letter-spacing: -1.5px;
        line-height: 1.08;
        margin-bottom: 14px;
    }
    .eb-viewer-header h2 span { color: var(--yellow); }

    .eb-viewer-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-bottom: 14px;
    }
    .eb-viewer-divider::before,
    .eb-viewer-divider::after {
        content: '';
        width: 50px; height: 2px;
        background: linear-gradient(90deg, transparent, var(--yellow));
        border-radius: 2px;
    }
    .eb-viewer-divider::after {
        background: linear-gradient(90deg, var(--yellow), transparent);
    }
    .eb-viewer-divider .gem {
        width: 8px; height: 8px;
        background: var(--yellow);
        transform: rotate(45deg);
        border-radius: 1px;
        box-shadow: 0 0 12px rgba(242,194,0,.55);
    }

    .eb-viewer-sub {
        font-size: .88rem;
        color: rgba(255,255,255,.45);
        line-height: 1.6;
    }

    /* PDF outer container */
    .eb-pdf-outer {
        background: rgba(255,255,255,.06);
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 20px;
        overflow: hidden;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow:
            0 0 0 1px rgba(242,194,0,.08),
            0 30px 80px rgba(0,0,0,.30),
            0 10px 30px rgba(0,0,0,.20);
    }

    /* Toolbar */
    .eb-pdf-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 20px;
        background: rgba(0,0,0,.28);
        border-bottom: 1px solid rgba(255,255,255,.10);
        gap: 12px;
        flex-wrap: wrap;
    }

    .eb-pdf-toolbar-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .eb-pdf-dot {
        width: 11px; height: 11px;
        border-radius: 50%;
    }
    .eb-pdf-dot.red   { background: #FF5F57; }
    .eb-pdf-dot.amber { background: #FFBD2E; }
    .eb-pdf-dot.green { background: #28C840; }

    .eb-pdf-title {
        font-size: .70rem;
        font-weight: 600;
        color: rgba(255,255,255,.50);
        letter-spacing: .8px;
        margin-left: 8px;
    }

    .eb-pdf-toolbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .eb-toolbar-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255,255,255,.10);
        border: 1px solid rgba(255,255,255,.16);
        color: rgba(255,255,255,.72);
        font-family: 'Manrope', sans-serif;
        font-size: .60rem;
        font-weight: 600;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 7px 14px;
        border-radius: 100px;
        cursor: pointer;
        text-decoration: none;
        transition: background .25s, border-color .25s, color .25s;
    }
    .eb-toolbar-btn:hover {
        background: rgba(242,194,0,.18);
        border-color: rgba(242,194,0,.4);
        color: var(--yellow);
    }
    .eb-toolbar-btn svg {
        width: 13px; height: 13px;
        flex-shrink: 0;
    }

    .eb-toolbar-btn.dl-btn {
        background: var(--yellow);
        border-color: var(--yellow);
        color: var(--pink-dark);
        font-weight: 800;
    }
    .eb-toolbar-btn.dl-btn:hover {
        background: var(--yellow-light);
        border-color: var(--yellow-light);
        color: var(--pink-dark);
        box-shadow: 0 4px 16px rgba(242,194,0,.4);
    }

    /* ── PDF iframe sized for 18 pages ──
       Each A4 page ≈ 120px tall at standard zoom
       18 pages × 120 = 2160px — we cap with a
       comfortable scrollable height instead       */
    .eb-pdf-frame {
        width: 100%;
        height: 700px;
        border: none;
        display: block;
        background: #ebebeb;
    }
    @media (min-width: 768px)  { .eb-pdf-frame { height: 900px;  } }
    @media (min-width: 1024px) { .eb-pdf-frame { height: 1050px; } }

    /* Page count info bar */
    .eb-pdf-infobar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 24px;
        padding: 12px 20px;
        background: rgba(0,0,0,.18);
        border-top: 1px solid rgba(255,255,255,.08);
        flex-wrap: wrap;
    }

    .eb-pdf-info-item {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: .62rem;
        font-weight: 600;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: rgba(255,255,255,.45);
    }
    .eb-pdf-info-item svg {
        width: 13px; height: 13px;
        flex-shrink: 0;
        opacity: .6;
    }
    .eb-pdf-info-item strong {
        color: var(--yellow);
        font-weight: 700;
    }
</style>


<!-- ═══════ EBOOK SHOWCASE ═══════ -->
<section class="eb-showcase">
    <div class="eb-wrap">
        <div class="eb-main">

            <!-- Book Cover -->
            <div class="eb-cover-col">
                <div class="eb-book-3d">
                    <div class="eb-book-wrap">
<img class="eb-cover-img"
                             src="https://almarah.org/wp-content/uploads/2026/08/bookcover.jpg"
                             alt="Almarah Profile 2026">
                        <!--
                        ══════════════════════════════════════
                        TO USE YOUR OWN COVER IMAGE:
                        Remove the .eb-cover-placeholder div
                        and replace with:
                        <img class="eb-cover-img"
                             src="YOUR-COVER-THUMBNAIL.jpg"
                             alt="Almarah Profile 2026">
                        ══════════════════════════════════════
                        -->
                        
                     
                            </div>
                            <div class="eb-cover-ph-line"></div>
                            <div 
                        </div>
                   </div>
                </div>

                <!-- Stats -->
                <div class="eb-cover-stats">
                    <div class="eb-stat">
                        <div class="eb-stat-val">18</div>
                        <div class="eb-stat-label">Pages</div>
                    </div>
                    <div class="eb-stat">
                        <div class="eb-stat-val">PDF</div>
                        <div class="eb-stat-label">Format</div>
                    </div>
                    <div class="eb-stat">
                        <div class="eb-stat-val">Free</div>
                        <div class="eb-stat-label">Download</div>
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="eb-info-col">
                <div class="eb-info-tag">Official Publication</div>
                <h2 class="eb-info-title">
                    Almarah Foundation<br>
                    <span class="accent">Profile 2026</span>
                </h2>
                <p class="eb-info-subtitle">
                    A comprehensive overview of our vision, leadership, programs, and impact across Pakistan
                </p>
                <div class="eb-accent-line"></div>
                <p class="eb-info-desc">
                    The Almarah Foundation Profile 2026 brings together our complete organizational story — from founding values and distinguished leadership to flagship programs and measurable community impact. This 18-page document serves as an authoritative reference for partners, donors, and everyone who believes in the power of purposeful change across Pakistan.
                </p>

                <div class="eb-inside-title">What's Inside</div>
                <ul class="eb-inside-list">
                    <li>
                        <span class="eb-check-icon">
                            <svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg>
                        </span>
                        Foundation Vision &amp; Mission
                    </li>
                    <li>
                        <span class="eb-check-icon">
                            <svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg>
                        </span>
                        Board of Directors
                    </li>
                    <li>
                        <span class="eb-check-icon">
                            <svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg>
                        </span>
                        Honorary Members
                    </li>
                    <li>
                        <span class="eb-check-icon">
                            <svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg>
                        </span>
                        Brand &amp; Goodwill Ambassadors
                    </li>
                    <li>
                        <span class="eb-check-icon">
                            <svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg>
                        </span>
                        Community Programs
                    </li>
                    <li>
                        <span class="eb-check-icon">
                            <svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg>
                        </span>
                        Impact &amp; Achievements
                    </li>
                    <li>
                        <span class="eb-check-icon">
                            <svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg>
                        </span>
                        Partnership Opportunities
                    </li>
                    <li>
                        <span class="eb-check-icon">
                            <svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg>
                        </span>
                        How to Get Involved
                    </li>
                </ul>

                <div class="eb-cta-row">
                    <a href="#eb-viewer" class="eb-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        Read Online
                    </a>
                    <a href="https://almarah.org/wp-content/uploads/2026/08/Almarah-Book.pdf"
                       download
                       target="_blank"
                       class="eb-btn-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download PDF
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ═══════ PDF VIEWER ═══════ -->
<section class="eb-viewer-section" id="eb-viewer">
    <div class="eb-viewer-deco d1"></div>
    <div class="eb-viewer-deco d2"></div>
    <div class="eb-wrap eb-viewer-inner">

        <div class="eb-viewer-header">
            <div class="eb-viewer-tag">Read Online</div>
            <h2>Browse the Full <span>Profile</span></h2>
            <div class="eb-viewer-divider"><div class="gem"></div></div>
            <p class="eb-viewer-sub">18 pages — scroll inside the viewer or download for offline reading</p>
        </div>

        <div class="eb-pdf-outer">

            <!-- Toolbar -->
            <div class="eb-pdf-toolbar">
                <div class="eb-pdf-toolbar-left">
                    <div class="eb-pdf-dot red"></div>
                    <div class="eb-pdf-dot amber"></div>
                    <div class="eb-pdf-dot green"></div>
                    <span class="eb-pdf-title">Almarah-Profile-2026.pdf — 18 Pages</span>
                </div>
                <div class="eb-pdf-toolbar-right">
                    <a href="https://almarah.org/wp-content/uploads/2026/08/Almarah-Book.pdf"
                       target="_blank"
                       class="eb-toolbar-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                        Open Full
                    </a>
                    <a href="https://almarah.org/wp-content/uploads/2026/08/Almarah-Book.pdf"
                       download
                       target="_blank"
                       class="eb-toolbar-btn dl-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download
                    </a>
                </div>
            </div>

            <!-- PDF Embed -->
            <iframe
                class="eb-pdf-frame"
                src="https://almarah.org/wp-content/uploads/2026/08/Almarah-Book.pdf#toolbar=1&navpanes=1&scrollbar=1&view=FitH"
                title="Almarah Foundation Profile 2026"
                allow="fullscreen">
                <p style="padding:32px;color:#fff;text-align:center;font-family:Manrope,sans-serif;">
                    Your browser does not support embedded PDFs.
                    <a href="https://almarah.org/wp-content/uploads/2026/05/Almarah-Profile-2026-new.pdf"
                       style="color:var(--yellow);" target="_blank">
                        Click here to download the PDF.
                    </a>
                </p>
            </iframe>

            <!-- Info bar -->
            <div class="eb-pdf-infobar">
                <div class="eb-pdf-info-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <strong>18</strong> Pages
                </div>
                <div class="eb-pdf-info-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2"/>
                        <path d="M3 9h18M9 21V9"/>
                    </svg>
                    PDF Format
                </div>
                <div class="eb-pdf-info-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Almarah Foundation — 2026
                </div>
                <div class="eb-pdf-info-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Updated <strong>2026</strong>
                </div>
            </div>

        </div>
    </div>
</section>