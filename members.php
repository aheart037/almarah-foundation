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
        -moz-osx-font-smoothing: grayscale;
        text-rendering: optimizeLegibility;
    }

    img {
        max-width: 100%;
        height: auto;
        display: block;
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }

    /* ---------- HERO ---------- */
    .page-hero {
        background: linear-gradient(150deg, var(--pink-dark) 0%, var(--pink) 50%, var(--pink-mid) 100%);
        padding: 100px 24px 90px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) {
        .page-hero { padding: 130px 40px 110px; }
    }
    .page-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse at 15% 50%, rgba(242,194,0,.18) 0%, transparent 55%),
            radial-gradient(ellipse at 85% 20%, rgba(255,255,255,.08) 0%, transparent 50%);
    }
    .page-hero::after {
        content: '';
        position: absolute;
        bottom: -100px;
        right: -100px;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        border: 40px solid rgba(242,194,0,.08);
    }
    @media (min-width: 768px) {
        .page-hero::after {
            bottom: -180px;
            right: -180px;
            width: 500px;
            height: 500px;
            border: 60px solid rgba(242,194,0,.08);
        }
    }
    .hero-inner { position: relative; z-index: 2; }

    .hero-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
    }
    .hero-eyebrow::before,
    .hero-eyebrow::after {
        content: '';
        display: block;
        width: 30px;
        height: 1px;
        background: rgba(242,194,0,.6);
    }
    @media (min-width: 768px) {
        .hero-eyebrow::before,
        .hero-eyebrow::after { width: 50px; }
    }
    .hero-eyebrow span {
        font-size: .6rem;
        font-weight: 700;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: var(--yellow);
        text-shadow: 0 1px 2px rgba(0,0,0,.2);
    }

    .page-hero h1 {
        font-family: 'Manrope', sans-serif;
        font-weight: 800;
        font-size: clamp(2.2rem, 8vw, 5rem);
        color: var(--white);
        letter-spacing: -1px;
        line-height: 1.05;
        text-shadow: 0 2px 10px rgba(0,0,0,.25);
    }
    .page-hero h1 em {
        font-style: normal;
        color: var(--yellow);
        display: block;
        margin-top: 8px;
        text-shadow: 0 2px 12px rgba(242,194,0,.4);
    }
    .page-hero .hero-sub {
        margin-top: 18px;
        font-size: .7rem;
        font-weight: 500;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: rgba(255,255,255,.5);
        text-shadow: 0 1px 2px rgba(0,0,0,.15);
    }
    @media (min-width: 768px) {
        .page-hero .hero-sub {
            font-size: .8rem;
            letter-spacing: 4px;
        }
    }

    .hero-ornament {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 32px;
    }
    @media (min-width: 768px) { margin-top: 44px; }
    .hero-ornament .bar {
        width: 40px; height: 2px;
        background: linear-gradient(90deg, transparent, var(--yellow));
        border-radius: 2px;
    }
    @media (min-width: 768px) { .hero-ornament .bar { width: 60px; } }
    .hero-ornament .bar.r { background: linear-gradient(90deg, var(--yellow), transparent); }
    .hero-ornament .diamond {
        width: 8px; height: 8px;
        background: var(--yellow);
        transform: rotate(45deg);
        border-radius: 1px;
        box-shadow: 0 0 8px rgba(242,194,0,.7);
    }
    @media (min-width: 768px) {
        .hero-ornament .diamond { width: 10px; height: 10px; }
    }

    /* ---------- SECTIONS ---------- */
    .section-wrap {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 20px;
    }
    @media (min-width: 768px) { .section-wrap { padding: 0 36px; } }

    .section-header {
        text-align: center;
        padding: 70px 0 48px;
    }
    @media (min-width: 768px) { .section-header { padding: 100px 0 64px; } }

    .s-tag {
        display: inline-block;
        font-size: .55rem;
        font-weight: 700;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: var(--pink);
        margin-bottom: 12px;
        text-shadow: 0 1px 1px rgba(169,45,99,.15);
    }
    @media (min-width: 768px) { .s-tag { font-size: .58rem; letter-spacing: 5px; } }

    .section-header h2 {
        font-family: 'Manrope', sans-serif;
        font-weight: 800;
        font-size: clamp(1.8rem, 6vw, 3.5rem);
        color: var(--text-dark);
        letter-spacing: -1px;
        line-height: 1.1;
        text-shadow: 0 1px 3px rgba(26,8,16,.1);
    }
    .section-header h2 .accent { color: var(--pink); }

    .divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin: 20px auto 0;
    }
    .divider::before,
    .divider::after {
        content: '';
        width: 40px; height: 2px;
        background: linear-gradient(90deg, transparent, var(--pink));
        border-radius: 2px;
    }
    @media (min-width: 768px) { .divider::before, .divider::after { width: 60px; } }
    .divider::after { background: linear-gradient(90deg, var(--pink), transparent); }
    .divider .gem {
        width: 8px; height: 8px;
        background: var(--yellow);
        transform: rotate(45deg);
        border-radius: 1px;
        box-shadow: 0 0 12px rgba(242,194,0,.5);
    }

    .section-sub {
        font-size: .85rem;
        font-weight: 400;
        color: var(--text-light);
        margin-top: 14px;
        letter-spacing: .3px;
        line-height: 1.5;
    }
    @media (min-width: 768px) { .section-sub { font-size: .9rem; margin-top: 16px; } }

    /* ---------- BOARD OF DIRECTORS ---------- */
    .board-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 28px;
        padding-bottom: 80px;
    }
    @media (min-width: 768px) {
        .board-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 36px;
            padding-bottom: 110px;
        }
    }

    .board-card {
        background: var(--white);
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        box-shadow:
            0 2px 0 var(--pink),
            0 14px 40px rgba(169,45,99,.12);
        transition: transform .4s cubic-bezier(.25,.8,.25,1),
                    box-shadow .4s cubic-bezier(.25,.8,.25,1);
        border: 1px solid rgba(169,45,99,.08);
    }
    @media (min-width: 768px) {
        .board-card {
            border-radius: 20px;
            box-shadow: 0 2px 0 var(--pink), 0 22px 60px rgba(169,45,99,.12);
        }
    }
    .board-card:hover {
        transform: translateY(-8px);
        box-shadow:
            0 2px 0 var(--pink),
            0 30px 60px rgba(169,45,99,.2),
            0 0 30px rgba(242,194,0,.12);
    }
    @media (min-width: 768px) {
        .board-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 2px 0 var(--pink), 0 40px 80px rgba(169,45,99,.25), 0 0 50px rgba(242,194,0,.12);
        }
    }

    .board-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow), var(--pink));
        z-index: 3;
        opacity: .9;
    }
    @media (min-width: 768px) { .board-card::before { height: 5px; } }

    .board-photo-wrap {
        position: relative;
        height: 360px;
        overflow: hidden;
        background: var(--pink-dark);
    }
    @media (min-width: 640px) { .board-photo-wrap { height: 400px; } }
    @media (min-width: 768px) { .board-photo-wrap { height: 440px; } }

    .board-photo-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: 50% 15%;
        transition: transform .6s ease;
        filter: brightness(1.02) contrast(1.03) saturate(1.1);
    }
    .board-card:hover .board-photo-wrap img {
        transform: scale(1.05);
        filter: brightness(1.05) contrast(1.05) saturate(1.15);
    }

    .board-photo-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top,
            rgba(169,45,99,.75) 0%,
            rgba(169,45,99,.2) 45%,
            transparent 70%);
    }

    .board-photo-name {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        padding: 24px 20px;
        z-index: 3;
    }
    @media (min-width: 768px) { .board-photo-name { padding: 28px 28px 24px; } }

    .board-photo-role {
        font-size: .55rem;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--yellow);
        margin-bottom: 6px;
        text-shadow: 0 1px 3px rgba(0,0,0,.3);
    }
    @media (min-width: 768px) { .board-photo-role { font-size: .58rem; letter-spacing: 4px; } }

    .board-photo-name h3 {
        font-family: 'Manrope', sans-serif;
        font-weight: 800;
        font-size: 1.4rem;
        color: var(--white);
        letter-spacing: -.5px;
        line-height: 1.2;
        text-shadow: 0 2px 6px rgba(0,0,0,.4);
    }
    @media (min-width: 768px) { .board-photo-name h3 { font-size: 1.7rem; } }

    .board-info {
        padding: 24px 20px 28px;
    }
    @media (min-width: 768px) { .board-info { padding: 28px 30px 34px; } }

    .board-accent {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
    }
    @media (min-width: 768px) { .board-accent { margin-bottom: 18px; } }

    .board-accent::before {
        content: '';
        display: block;
        width: 32px; height: 3px;
        background: var(--pink);
        border-radius: 2px;
    }
    @media (min-width: 768px) { .board-accent::before { width: 36px; } }

    .board-accent::after {
        content: '';
        display: block;
        width: 7px; height: 7px;
        background: var(--yellow);
        border-radius: 50%;
        box-shadow: 0 0 8px rgba(242,194,0,.6);
    }
    @media (min-width: 768px) { .board-accent::after { width: 8px; height: 8px; } }

    .board-bio {
        font-size: .9rem;
        font-weight: 400;
        line-height: 1.8;
        color: var(--text-mid);
        text-shadow: 0 1px 0 rgba(255,255,255,.5);
    }
    @media (min-width: 768px) { .board-bio { font-size: .95rem; line-height: 1.9; } }

    /* ---------- HONORARY MEMBERS (UPDATED PHOTO WIDTHS) ---------- */
    .honorary-section {
        background: linear-gradient(150deg, var(--pink-dark) 0%, var(--pink) 60%, #C4386F 100%);
        position: relative;
        overflow: hidden;
    }
    .honorary-section::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse at 0% 30%, rgba(242,194,0,.12) 0%, transparent 55%),
            radial-gradient(ellipse at 100% 70%, rgba(0,0,0,.15) 0%, transparent 55%);
    }
    .honorary-section::after {
        content: '';
        position: absolute;
        top: -100px; left: -100px;
        width: 300px; height: 300px;
        border-radius: 50%;
        border: 40px solid rgba(242,194,0,.06);
    }
    @media (min-width: 768px) {
        .honorary-section::after {
            top: -200px; left: -200px;
            width: 600px; height: 600px;
            border: 80px solid rgba(242,194,0,.06);
        }
    }

    .honorary-section .section-header h2 { color: var(--white); text-shadow: 0 2px 6px rgba(0,0,0,.3); }
    .honorary-section .s-tag { color: var(--yellow); text-shadow: 0 1px 2px rgba(0,0,0,.2); }
    .honorary-section .section-sub { color: rgba(255,255,255,.45); }

    .honorary-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
        padding-bottom: 80px;
        position: relative;
        z-index: 2;
    }
    @media (min-width: 640px) {
        .honorary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
    }
    @media (min-width: 1024px) {
        .honorary-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding-bottom: 110px;
        }
    }

    .hon-card {
        display: flex;
        flex-direction: row;
        align-items: stretch;
        background: rgba(255,255,255,.07);
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 14px;
        overflow: hidden;
        transition: background .35s, border-color .35s, transform .35s;
        backdrop-filter: blur(6px);
    }
    @media (min-width: 768px) { .hon-card { border-radius: 16px; } }

    .hon-card:hover {
        background: rgba(255,255,255,.14);
        border-color: rgba(242,194,0,.45);
        transform: translateY(-4px);
        box-shadow: 0 20px 50px rgba(0,0,0,.2);
    }

    /* Increased widths to prevent face cropping on mobile */
    .hon-photo-wrap {
        width: 150px;
        min-width: 150px;
        min-height: 170px;
        overflow: hidden;
        position: relative;
        background: rgba(169,45,99,.4);
        flex-shrink: 0;
    }
    @media (min-width: 480px) {
        .hon-photo-wrap {
            width: 180px;
            min-width: 180px;
            min-height: 200px;
        }
    }
    @media (min-width: 768px) {
        .hon-photo-wrap {
            width: 200px;
            min-width: 200px;
            min-height: 200px;
        }
    }

    .hon-photo-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: 50% 15%;        /* keeps faces centred */
        transition: transform .5s ease;
        filter: brightness(1.02) contrast(1.03) saturate(1.1);
    }
    .hon-card:hover .hon-photo-wrap img {
        transform: scale(1.08);
        filter: brightness(1.05) contrast(1.05) saturate(1.15);
    }

    .hon-photo-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to right,
            transparent 60%,
            rgba(169,45,99,.4) 100%);
    }

    .hon-body {
        flex: 1;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    @media (min-width: 480px) { .hon-body { padding: 22px; } }
    @media (min-width: 768px) { .hon-body { padding: 24px; } }

    .hon-name {
        font-family: 'Manrope', sans-serif;
        font-weight: 800;
        font-size: 1.05rem;
        color: var(--white);
        letter-spacing: -.3px;
        line-height: 1.25;
        margin-bottom: 10px;
        text-shadow: 0 1px 3px rgba(0,0,0,.3);
    }
    @media (min-width: 768px) { .hon-name { font-size: 1.15rem; } }

    .hon-divider {
        width: 24px; height: 2px;
        background: var(--yellow);
        border-radius: 2px;
        margin-bottom: 10px;
        box-shadow: 0 0 6px rgba(242,194,0,.5);
    }
    @media (min-width: 768px) { .hon-divider { width: 28px; } }

    .hon-bio {
        font-size: .8rem;
        font-weight: 400;
        line-height: 1.7;
        color: rgba(255,255,255,.55);
    }
    @media (min-width: 480px) { .hon-bio { font-size: .82rem; line-height: 1.75; } }
    @media (min-width: 768px) { .hon-bio { font-size: .84rem; } }

    /* ---------- AMBASSADORS ---------- */
    .amb-section {
        background: linear-gradient(180deg, var(--off-white) 0%, var(--cream) 100%);
        padding-bottom: 80px;
    }
    @media (min-width: 768px) { .amb-section { padding-bottom: 120px; } }

    .amb-grid {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    @media (min-width: 640px) {
        .amb-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }
    @media (min-width: 1024px) {
        .amb-grid { grid-template-columns: repeat(3, 1fr); gap: 28px; }
    }

    .amb-card {
        background: var(--white);
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 2px 0 var(--pink), 0 14px 40px rgba(169,45,99,.1);
        transition: transform .4s cubic-bezier(.25,.8,.25,1),
                    box-shadow .4s cubic-bezier(.25,.8,.25,1);
        display: flex;
        flex-direction: column;
        border: 1px solid rgba(169,45,99,.08);
    }
    @media (min-width: 768px) {
        .amb-card {
            border-radius: 20px;
            box-shadow: 0 2px 0 var(--pink), 0 22px 60px rgba(169,45,99,.1);
        }
    }
    .amb-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 2px 0 var(--pink), 0 30px 60px rgba(169,45,99,.2), 0 0 25px rgba(242,194,0,.1);
    }
    @media (min-width: 768px) {
        .amb-card:hover {
            box-shadow: 0 2px 0 var(--pink), 0 40px 80px rgba(169,45,99,.25), 0 0 40px rgba(242,194,0,.12);
        }
    }

    .amb-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow), var(--pink));
        z-index: 3;
        opacity: .9;
    }
    @media (min-width: 768px) { .amb-card::before { height: 5px; } }

    .amb-card.featured {
        grid-column: 1 / -1;
        flex-direction: column;
        border-radius: 18px;
        box-shadow: 0 3px 0 var(--pink), 0 20px 60px rgba(169,45,99,.18);
    }
    @media (min-width: 768px) {
        .amb-card.featured {
            flex-direction: row;
            border-radius: 24px;
            box-shadow: 0 4px 0 var(--pink), 0 30px 80px rgba(169,45,99,.18);
        }
    }
    .amb-card.featured:hover {
        box-shadow: 0 3px 0 var(--pink), 0 30px 80px rgba(169,45,99,.22);
    }
    @media (min-width: 768px) {
        .amb-card.featured:hover { box-shadow: 0 4px 0 var(--pink), 0 50px 100px rgba(169,45,99,.25), 0 0 50px rgba(242,194,0,.15); }
    }

    .amb-photo {
        position: relative;
        overflow: hidden;
        background: var(--pink-dark);
        height: 280px;
    }
    @media (min-width: 480px) { .amb-photo { height: 310px; } }
    @media (min-width: 640px) { .amb-photo { height: 290px; } }
    @media (min-width: 768px) { .amb-card:not(.featured) .amb-photo { height: 310px; } }

    .amb-card.featured .amb-photo { height: 280px; }
    @media (min-width: 480px) { .amb-card.featured .amb-photo { height: 320px; } }
    @media (min-width: 768px) {
        .amb-card.featured .amb-photo {
            width: 45%;
            min-width: 380px;
            max-width: 480px;
            height: auto;
            flex-shrink: 0;
        }
    }
    @media (min-width: 1024px) {
        .amb-card.featured .amb-photo {
            width: 460px;
            min-width: 460px;
        }
    }

    .amb-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: 50% 15%;
        transition: transform .6s ease;
        filter: brightness(1.02) contrast(1.03) saturate(1.1);
    }
    .amb-card:hover .amb-photo img {
        transform: scale(1.05);
        filter: brightness(1.05) contrast(1.05) saturate(1.15);
    }

    .amb-photo-overlay {
        position: absolute; inset: 0;
        background: linear-gradient(to top,
            rgba(169,45,99,.65) 0%,
            rgba(169,45,99,.1) 50%,
            transparent 75%);
    }
    @media (min-width: 768px) {
        .amb-card.featured .amb-photo-overlay {
            background: linear-gradient(to right,
                transparent 55%,
                rgba(169,45,99,.5) 100%);
        }
    }

    .amb-type-pill {
        position: absolute;
        bottom: 16px;
        left: 16px;
        font-size: .55rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        padding: 6px 14px;
        border-radius: 100px;
        z-index: 4;
    }
    @media (min-width: 768px) {
        .amb-type-pill {
            font-size: .54rem;
            letter-spacing: 2.5px;
            padding: 7px 16px;
            bottom: 18px;
            left: 18px;
        }
    }

    .amb-type-pill.brand {
        background: var(--yellow);
        color: var(--pink-dark);
        box-shadow: 0 4px 16px rgba(242,194,0,.45);
    }
    .amb-type-pill.goodwill {
        background: rgba(255,255,255,.15);
        color: var(--white);
        border: 1px solid rgba(255,255,255,.35);
        backdrop-filter: blur(4px);
    }

    .amb-info {
        padding: 24px 22px 28px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    @media (min-width: 480px) { .amb-info { padding: 26px 26px 30px; } }
    @media (min-width: 768px) { .amb-info { padding: 30px 32px 36px; } }

    .amb-card.featured .amb-info { justify-content: center; }
    @media (min-width: 768px) {
        .amb-card.featured .amb-info { padding: 40px 44px; }
    }
    @media (min-width: 1024px) {
        .amb-card.featured .amb-info { padding: 48px 52px; }
    }

    .amb-label {
        font-size: .55rem;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--pink);
        margin-bottom: 8px;
        text-shadow: 0 1px 2px rgba(169,45,99,.1);
    }
    @media (min-width: 768px) {
        .amb-label {
            font-size: .58rem;
            letter-spacing: 4px;
            margin-bottom: 10px;
        }
    }

    .amb-name {
        font-family: 'Manrope', sans-serif;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -.5px;
        line-height: 1.1;
        margin-bottom: 16px;
        font-size: 1.3rem;
        text-shadow: 0 1px 3px rgba(26,8,16,.1);
    }
    @media (min-width: 480px) { .amb-card:not(.featured) .amb-name { font-size: 1.4rem; } }
    @media (min-width: 768px) {
        .amb-card:not(.featured) .amb-name { font-size: 1.5rem; margin-bottom: 20px; }
    }

    .amb-card.featured .amb-name { font-size: 1.6rem; }
    @media (min-width: 480px) { .amb-card.featured .amb-name { font-size: 2rem; } }
    @media (min-width: 768px) { .amb-card.featured .amb-name { font-size: 2.4rem; } }
    @media (min-width: 1024px) { .amb-card.featured .amb-name { font-size: 2.8rem; } }

    .amb-accent {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }
    @media (min-width: 768px) { .amb-accent { margin-bottom: 20px; } }

    .amb-accent::before {
        content: '';
        display: block;
        width: 32px; height: 3px;
        background: var(--pink);
        border-radius: 2px;
    }
    @media (min-width: 768px) { .amb-accent::before { width: 36px; } }

    .amb-accent::after {
        content: '';
        display: block;
        width: 7px; height: 7px;
        background: var(--yellow);
        border-radius: 50%;
        box-shadow: 0 0 10px rgba(242,194,0,.6);
    }
    @media (min-width: 768px) { .amb-accent::after { width: 8px; height: 8px; } }

    .amb-bio {
        font-size: .88rem;
        font-weight: 400;
        line-height: 1.85;
        color: var(--text-mid);
        flex: 1;
        text-shadow: 0 1px 0 rgba(255,255,255,.5);
    }
    @media (min-width: 768px) { .amb-bio { font-size: .92rem; line-height: 1.9; } }

    .amb-card.featured .amb-bio { font-size: .95rem; }
    @media (min-width: 768px) { .amb-card.featured .amb-bio { font-size: 1.05rem; } }
</style>

<section class="page-hero">
    <div class="hero-inner">
        <h1>Our Distinguished<em>Leadership</em></h1>
        <p class="hero-sub">Board of Directors · Honorary Members · Ambassadors</p>
        <div class="hero-ornament">
            <div class="bar"></div>
            <div class="diamond"></div>
            <div class="bar r"></div>
        </div>
    </div>
</section>

<section>
    <div class="section-wrap">
        <div class="section-header">
            <div class="s-tag">Leadership &amp; Governance</div>
            <h2>Board of <span class="accent">Directors</span></h2>
            <div class="divider"><div class="gem"></div></div>
            <p class="section-sub">Visionary leaders steering Almarah Foundation's mission forward</p>
        </div>

        <div class="board-grid">
            <div class="board-card">
                <div class="board-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Sophira-Warraich.jpg" alt="Chairman">
                    <div class="board-photo-overlay"></div>
                    <div class="board-photo-name">
                        <div class="board-photo-role">Chairman</div>
                        <h3>Mrs. Sophiya Warraich</h3>
                    </div>
                </div>
                <div class="board-info">
                    <div class="board-accent"></div>
                    <p class="board-bio"></p>
                </div>
            </div>

            <div class="board-card">
                <div class="board-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Haroon-Shahzad.jpg" alt="President">
                    <div class="board-photo-overlay"></div>
                    <div class="board-photo-name">
                        <div class="board-photo-role">Board Member</div>
                        <h3>Mr. Haroon Shahzad</h3>
                    </div>
                </div>
                <div class="board-info">
                    <div class="board-accent"></div>
                    <p class="board-bio"></p>
                </div>
            </div>

            <div class="board-card">
                <div class="board-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Humayoun-Iqbal.jpg" alt="Vice President">
                    <div class="board-photo-overlay"></div>
                    <div class="board-photo-name">
                        <div class="board-photo-role">Board Member</div>
                        <h3>Mr. Humayoun Iqbal</h3>
                    </div>
                </div>
                <div class="board-info">
                    <div class="board-accent"></div>
                    <p class="board-bio"></p>
                </div>
            </div>
    
            </div>
        </div>
    </div>
</section>

<section class="honorary-section">
    <div class="section-wrap">
        <div class="section-header">
            <div class="s-tag">Distinguished Patrons</div>
            <h2>Honorary <span style="color:var(--yellow)">Members</span></h2>
            <div class="divider"><div class="gem"></div></div>
            <p class="section-sub">Distinguished leaders shaping Pakistan's future</p>
        </div>

        <div class="honorary-grid">

            <!-- 1. Sheikh Irfan Iqbal -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Sheikh-Irfan-Iqbal.jpg" alt="Sheikh Irfan Iqbal">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Sheikh Irfan Iqbal</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Chairman of Al-Fatah Stores, one of Pakistan's leading retail chains. Under his leadership, Al-Fatah has expanded across major cities, becoming a household name in quality retail.</p>
                </div>
            </div>

            <!-- 2. Mian Tariq Nisar -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Mian-Tariq-Nisar.png" alt="Mian Tariq Nisar">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Mian Tariq Nisar</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Distinguished Pakistani industrialist and Chairman of ATS Group of Industries with over five decades of legacy in manufacturing, textile, spinning, and chemical sectors.</p>
                </div>
            </div>

            <!-- 3. Mian Anjum Nisar -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Mian-Anjum-Nisar.png" alt="Mian Anjum Nisar">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Mian Anjum Nisar</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Prominent Pakistani industrialist and Chairman of ATS Group with 47+ years of experience in leather, petrochemicals, and spinning. Former President of FPCCI (2020).</p>
                </div>
            </div>

            <!-- 4. Amir Khan -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Amir-Khan.jpg" alt="Amir Khan">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Amir Khan</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Former world champion boxer, Olympic silver medalist, and internationally recognized British-Pakistani sports personality. He inspires millions through dedication to sports, youth empowerment, and community welfare.</p>
                </div>
            </div>

            <!-- 5. Dr. Yaqoob Qazi -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Dr.-Yaqoob-Qazi.jpg" alt="Dr. Yaqoob Qazi">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Dr. Yaqoob Qazi</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Dr. Muhammad Yaqoob Qazi is a highly respected pediatrician, academic, and child health specialist with decades of experience in pediatric medicine and medical education. Known for his dedication to child healthcare and development, he has served in leading academic and healthcare positions and continues to contribute towards the wellbeing and future of children through his professional and humanitarian efforts.</p>
                </div>
            </div>

            <!-- 6. Sheikh Anees Iqbal -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Sheikh-Anees-Iqbal.jpg" alt="Sheikh Anees Iqbal">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Sheikh Anees Iqbal</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Dynamic leader behind Al-Fateh Group with strong business acumen in retail and trade. Al-Fateh Stores have become a trusted name for quality and customer satisfaction.</p>
                </div>
            </div>

            <!-- 7. Ghulam Dastagir (G.D.) -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Ghulam-Dastgir-GD.jpg" alt="Ghulam Dastgir GD">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Ghulam Dastagir (G.D.)</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Co-founder of Galleria Designs (2012) and Managing Partner &amp; Principal Architect. His design philosophy unites luxury and functionality with a commitment to quality and solid research.</p>
                </div>
            </div>

            <!-- 8. Mian Tahir Javed -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Mian-Tahir-Javed.jpg" alt="Mian Tahir Javed">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Mian Tahir Javed</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Owner and Chairman of Urban Developers &amp; Central Park Housing Scheme Lahore, with over four decades of experience in land infrastructure and urban planning.</p>
                </div>
            </div>

            <!-- 9. Adnan Ijaz -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Adnan-Ijaz.jpg" alt="Adnan Ijaz">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Adnan Ijaz</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Managing Director of External and Corporate Affairs at Cheezious Pakistan. His strategic leadership has helped expand Cheezious' national footprint across Pakistan's fast-food landscape.</p>
                </div>
            </div>

            <!-- 10. Ibrahim Hasan Murad -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Ibrahim-Hasan-Murad.jpg" alt="Ibrahim Hasan Murad">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Ibrahim Hasan Murad</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">President of UMT Lahore and Chairman of the ILM Trust. A dedicated philanthropist leading efforts to provide over PKR 13 billion in scholarships through the ILM Fund.</p>
                </div>
            </div>

            <!-- 11. Awais Raoof -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Awais-Raoof.png" alt="Awais Raoof">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Awais Raoof</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Chairman of the Board of Governors at the University of Lahore and President of the Association of Private Sector Universities of Pakistan, promoting higher education standards nationwide.</p>
                </div>
            </div>

            <!-- 12. Danish Amman -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Danish-Aman.jpg" alt="Danish Amman">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Danish Amman</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">A respected business leader and entrepreneur contributing significantly to Pakistan's commercial landscape through innovation, vision, and committed community engagement.</p>
                </div>
            </div>

            <!-- 13. Dr. Farhan Essa -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Dr.-Farhan-Esaa.jpg" alt="Dr. Farhan Essa">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Dr. Farhan Essa</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Dr. Farhan Essa Abdullah is a renowned clinical microbiologist, healthcare entrepreneur, and philanthropist, serving as the CEO of Dr. Essa Laboratory & Diagnostic Centre, one of Pakistan’s leading diagnostic networks. Widely recognized for his contributions to healthcare, education, social welfare, and youth development, he has played an active role in numerous humanitarian and community initiatives across Pakistan.</p>
                </div>
            </div>

            <!-- 14. Mian Ghulam Mujtaba -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Mian-Ghulam-Mujtaba.png" alt="Mian Ghulam Mujtaba">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Mian Ghulam Mujtaba</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">CEO of Vital, known for strong leadership and vision. Instrumental in driving the company's growth and innovation across its sector.</p>
                </div>
            </div>

            <!-- 15. Khawaja Shahnawaz -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Khawaja-Shahnawaz.jpg" alt="Khawaja Shahnawaz">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Khawaja Shahnawaz</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Visionary founder and CEO of Uniworth, Pakistan's pioneering menswear brand known for timeless elegance. He redefined men's formal wear across the country.</p>
                </div>
            </div>

            <!-- 16. Atif Rana -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Atif-Rana.jpg" alt="Atif Rana">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Atif Rana</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">CEO of Lahore Qalandars, a visionary leader known for transforming the franchise into a hub of talent and opportunity in Pakistan's cricket landscape.</p>
                </div>
            </div>

            <!-- 17. Rao Usman Hashim Khan -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Rao-Usman-Hashim-Khan.png" alt="Rao Usman Hashim Khan">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Rao Usman Hashim Khan</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Chief Operating Officer of TransGroup, Pakistan's leading sports marketing and event-management firm. He oversees operational logistics, broadcast rights, and large-scale sports sponsorships.</p>
                </div>
            </div>

            <!-- 18. Shahid Khan -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Shahid-Ahmed-Khan.jpg" alt="Shahid Khan">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Shahid Khan</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Distinguished figure in diplomacy, governance, and community leadership. Commissioner and Governor of the Massachusetts Asian American Commission and former National Finance Co-Chair for Kerry for President.</p>
                </div>
            </div>

            <!-- 19. Haji Ishaq -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Haji-Ishaq.png" alt="Haji Ishaq">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Haji Ishaq</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Visionary founder and CEO of Goree Cosmetics Pvt. Ltd. Under his leadership the company expanded to over 19 countries with innovative skincare and cosmetic products.</p>
                </div>
            </div>

            <!-- 20. Salman Zameer -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Salman-Zameer.jpg" alt="Salman Zameer">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Salman Zameer</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">A dynamic entrepreneur and driving force behind Suffuse, one of Pakistan's leading luxury fashion houses.</p>
                </div>
            </div>

            <!-- 21. Khawaja Junaid -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Khawaja-Muhammad-Junaid.jpg" alt="Khawaja Junaid">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Khawaja Junaid</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Pakistani field hockey player and coach. Bronze medalist at the 1992 Summer Olympics in Barcelona and former Head Coach of the Pakistan national hockey team.</p>
                </div>
            </div>

            <!-- 22. Moazam Khan -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Moazzam-Khan.png" alt="Moazam Khan">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Moazam Khan</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Director and Owner of Jalal Sons, one of Lahore's most iconic retail chains. With visionary leadership, he has modernized the brand while preserving its trusted heritage.</p>
                </div>
            </div>

            <!-- 23. Dr. Saeed Elahi -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Dr-Saeed-Elahi.jpeg" alt="Dr. Saeed Elahi">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Dr. Saeed Elahi</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Dr. Saeed Elahi is a renowned humanitarian and former Chairman of the Pakistan Red Crescent Society. He has served as a Member of the Provincial Assembly and dedicated his career to public health and welfare initiatives. Known for his compassionate leadership, he continues to champion humanitarian causes across Pakistan.</p>
                </div>
            </div>

            <!-- 24. Irfan Ahsan -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Irfan-Ahson.jpg" alt="Irfan Ahsan">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Irfan Ahsan</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Renowned Pakistani photographer celebrated for exceptional work in wedding, portrait, and travel photography. His artistic approach to capturing emotions has earned him widespread acclaim.</p>
                </div>
            </div>

            <!-- 25. Hassan Nisar (NEW) -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Mr.-Hassan-Nisar.jpg" alt="Hassan Nisar">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Hassan Nisar</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Hassan Nisar is a renowned Pakistani columnist, intellectual, and political analyst known for his bold opinions, social commentary, and thought-provoking discussions on politics, society, and education. Through his writings and media appearances, he has inspired critical thinking and public awareness across Pakistan.</p>
                </div>
            </div>
			
            <!-- 26. Rohail Akhtar (NEW) -->
            <div class="hon-card">
                <div class="hon-photo-wrap">
                    <img src="https://almarah.org/wp-content/uploads/2026/07/Rohail-Akhtar.jpeg" alt="Rohail-Akhtar">
                    <div class="hon-photo-overlay"></div>
                </div>
                <div class="hon-body">
                    <div class="hon-name">Mr. Muhammad Rohail Akhtar</div>
                    <div class="hon-divider"></div>
                    <p class="hon-bio">Mr. Muhammad Rohail Akhtar is a visionary entrepreneur and accomplished business leader with over two decades of experience in Pakistan's telecommunications and technology industry. As the CEO of Pearl Comm Mobiles Plus Pvt. Ltd., he has led transformative initiatives in enterprise solutions, business automation, IT infrastructure, and sales operations, earning a reputation for innovation, integrity, and excellence</p>
                </div>
            </div>

        </div>
    </div>
</section>
<section class="amb-section">
    <div class="section-wrap">
        <div class="section-header">
            <div class="s-tag">Faces of Almarah</div>
            <h2>Brand &amp; Goodwill <span class="accent">Ambassadors</span></h2>
            <div class="divider"><div class="gem"></div></div>
            <p class="section-sub">Celebrated personalities championing our cause</p>
        </div>

        <div class="amb-grid">
            <div class="amb-card featured">
                <div class="amb-photo">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Hamza-Ali-Abbasi.jpg" alt="Hamza Ali Abbasi">
                    <div class="amb-photo-overlay"></div>
                    <span class="amb-type-pill brand">★ Brand Ambassador</span>
                </div>
                <div class="amb-info">
                    <div class="amb-label">Brand Ambassador</div>
                    <div class="amb-name">Mr. Hamza Ali Abbasi</div>
                    <div class="amb-accent"></div>
                    <p class="amb-bio">Hamza Ali Abbasi is a Pakistani actor, director, and former model known for his powerful performances in popular TV dramas and films. He is also recognized for his thoughtful public discussions on social, political, and religious topics — making him one of the most influential voices of his generation and the perfect face for Almarah Foundation's mission.</p>
                </div>
            </div>

            <div class="amb-card">
                <div class="amb-photo">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Humayun-Saeed.jpg" alt="Humayun Saeed">
                    <div class="amb-photo-overlay"></div>
                    <span class="amb-type-pill goodwill">Goodwill Ambassador</span>
                </div>
                <div class="amb-info">
                    <div class="amb-label">Goodwill Ambassador</div>
                    <div class="amb-name">Mr. Humayun Saeed</div>
                    <div class="amb-accent"></div>
                    <p class="amb-bio">One of Pakistan's most influential actors and producers, known for blockbuster dramas and internationally recognized films. A defining figure in Pakistani entertainment with decades of groundbreaking work.</p>
                </div>
            </div>

            <div class="amb-card">
                <div class="amb-photo">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Muneeb-Butt.jpg" alt="Muneeb Butt">
                    <div class="amb-photo-overlay"></div>
                    <span class="amb-type-pill goodwill">Goodwill Ambassador</span>
                </div>
                <div class="amb-info">
                    <div class="amb-label">Goodwill Ambassador</div>
                    <div class="amb-name">Mr. Muneeb Butt</div>
                    <div class="amb-accent"></div>
                    <p class="amb-bio">A popular Pakistani television actor and model, widely recognized for his leading roles in hit drama serials. He has built a devoted fan following through his versatile performances and dynamic screen presence.</p>
                </div>
            </div>

            <div class="amb-card">
                <div class="amb-photo">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Bushra-Ansari.jpg" alt="Bushra Ansari">
                    <div class="amb-photo-overlay"></div>
                    <span class="amb-type-pill goodwill">Goodwill Ambassador</span>
                </div>
                <div class="amb-info">
                    <div class="amb-label">Goodwill Ambassador</div>
                    <div class="amb-name">Ms. Bushra Ansari</div>
                    <div class="amb-accent"></div>
                    <p class="amb-bio">A legendary Pakistani actress, comedian, writer, singer, and television host with a remarkable career spanning several decades. Celebrated for iconic performances and immense contribution to Pakistan's entertainment industry.</p>
                </div>
            </div>

            <div class="amb-card">
                <div class="amb-photo">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Naimal-Khawar.jpg" alt="Naimal Khawar">
                    <div class="amb-photo-overlay"></div>
                    <span class="amb-type-pill goodwill">Goodwill Ambassador</span>
                </div>
                <div class="amb-info">
                    <div class="amb-label">Goodwill Ambassador</div>
                    <div class="amb-name">Ms. Naimal Khawar</div>
                    <div class="amb-accent"></div>
                    <p class="amb-bio">A Pakistani actress, painter, and digital influencer known for her graceful personality and artistic talent. She continues to inspire audiences through her creative work and compelling television appearances.</p>
                </div>
            </div>

            <div class="amb-card">
                <div class="amb-photo">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Iftikhar-Thakur.jpg" alt="Iftikhar Thakur">
                    <div class="amb-photo-overlay"></div>
                    <span class="amb-type-pill goodwill">Goodwill Ambassador</span>
                </div>
                <div class="amb-info">
                    <div class="amb-label">Goodwill Ambassador</div>
                    <div class="amb-name">Mr. Iftikhar Thakur</div>
                    <div class="amb-accent"></div>
                    <p class="amb-bio">A renowned Pakistani comedian, actor, and stage performer famous for his exceptional comic timing and entertaining performances. A cherished and prominent name in Pakistani theatre and television.</p>
                </div>
            </div>

            <div class="amb-card">
                <div class="amb-photo">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Naeem-bukhari.jpg" alt="Naeem Bukhari">
                    <div class="amb-photo-overlay"></div>
                    <span class="amb-type-pill goodwill">Goodwill Ambassador</span>
                </div>
                <div class="amb-info">
                    <div class="amb-label">Goodwill Ambassador</div>
                    <div class="amb-name">Mr. Naeem Bukhari</div>
                    <div class="amb-accent"></div>
                    <p class="amb-bio">A distinguished Pakistani lawyer, columnist, and television personality known for his expertise in legal and political affairs. Widely respected for analytical views, public speaking, and contributions to media and law.</p>
                </div>
            </div>

            <div class="amb-card">
                <div class="amb-photo">
                    <img src="https://almarah.org/wp-content/uploads/2026/05/Zeeshan-Habib.jpeg" alt="Zeeshan Habib">
                    <div class="amb-photo-overlay"></div>
                    <span class="amb-type-pill goodwill">Goodwill Ambassador</span>
                </div>
                <div class="amb-info">
                    <div class="amb-label">Goodwill Ambassador</div>
                    <div class="amb-name">Mr. Zeeshan Habib</div>
                    <div class="amb-accent"></div>
                    <p class="amb-bio">Zeeshan Habib is a Karachi-based entrepreneur and humanitarian who has spent over 15 years turning compassion into action. As CEO of Pelle Classics and an active member of more than ten civic and welfare boards, he has led initiatives spanning free education , clean water access, flood relief, prison welfare , qarze husna and inclusion for children with Down syndrome across Karachi, Sindh, and Baluchistan.
As Almarah.org's Goodwill Ambassador, Zeeshan brings not just his name, but his hands — and we are proud to have him with us.</p>
                </div>
            </div>
        </div>
    </div>
</section>