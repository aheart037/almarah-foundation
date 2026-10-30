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
        --green:        #25D366;
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
    .ct-hero {
        background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 30%, var(--pink) 65%, var(--pink-mid) 100%);
        padding: 110px 24px 80px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .ct-hero { padding: 130px 40px 90px; } }

    .ct-hero::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 70% 60% at 10% 60%, rgba(242,194,0,.18) 0%, transparent 60%),
            radial-gradient(ellipse 50% 50% at 90% 15%, rgba(255,255,255,.09) 0%, transparent 55%),
            radial-gradient(ellipse 40% 40% at 50% 100%, rgba(0,0,0,.14) 0%, transparent 60%);
        pointer-events: none;
    }

    .ct-hero-circle-tl {
        position: absolute;
        top: -100px; left: -100px;
        width: 380px; height: 380px;
        border-radius: 50%;
        border: 55px solid rgba(255,255,255,.04);
        pointer-events: none;
    }
    .ct-hero-circle-br {
        position: absolute;
        bottom: -80px; right: -160px;
        width: 500px; height: 500px;
        border-radius: 50%;
        border: 70px solid rgba(242,194,0,.05);
        pointer-events: none;
    }
    @media (min-width: 768px) {
        .ct-hero-circle-tl { top: -150px; left: -150px; width: 600px; height: 600px; }
        .ct-hero-circle-br { bottom: -120px; right: -220px; width: 750px; height: 750px; }
    }

    .ct-hero-inner {
        position: relative;
        z-index: 2;
        max-width: 680px;
        margin: 0 auto;
    }

    .ct-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
    }
    .ct-eyebrow::before, .ct-eyebrow::after {
        content: '';
        display: block;
        width: 36px; height: 1px;
        background: linear-gradient(90deg, transparent, rgba(242,194,0,.7));
    }
    .ct-eyebrow::after {
        background: linear-gradient(90deg, rgba(242,194,0,.7), transparent);
    }
    .ct-eyebrow span {
        font-size: .58rem;
        font-weight: 700;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--yellow);
        text-shadow: 0 0 20px rgba(242,194,0,.4);
    }

    .ct-hero h1 {
        font-weight: 800;
        font-size: clamp(2.2rem, 7vw, 4.2rem);
        color: var(--white);
        letter-spacing: -2px;
        line-height: 1.05;
        text-shadow: 0 4px 40px rgba(0,0,0,.22);
        margin-bottom: 16px;
    }
    .ct-hero h1 em {
        font-style: normal;
        color: var(--yellow);
        filter: drop-shadow(0 0 24px rgba(242,194,0,.30));
    }

    .ct-hero-sub {
        font-size: .92rem;
        font-weight: 400;
        color: rgba(255,255,255,.55);
        line-height: 1.75;
        max-width: 480px;
        margin: 0 auto;
    }

    /* ═══════════════════════════════════════════
       MAIN LAYOUT
    ═══════════════════════════════════════════ */
    .ct-main {
        padding: 80px 0 90px;
        background: var(--off-white);
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .ct-main { padding: 100px 0 110px; } }

    .ct-main::before {
        content: '';
        position: absolute;
        top: -200px; right: -200px;
        width: 600px; height: 600px;
        border-radius: 50%;
        border: 80px solid rgba(169,45,99,.03);
        pointer-events: none;
    }
    .ct-main::after {
        content: '';
        position: absolute;
        bottom: -180px; left: -180px;
        width: 500px; height: 500px;
        border-radius: 50%;
        border: 65px solid rgba(242,194,0,.03);
        pointer-events: none;
    }

    .ct-wrap {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 20px;
    }
    @media (min-width: 768px) { .ct-wrap { padding: 0 36px; } }

    .ct-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 40px;
    }
    @media (min-width: 960px) {
        .ct-layout {
            grid-template-columns: 1fr 360px;
            gap: 56px;
            align-items: start;
        }
    }

    /* ═══════════════════════════════════════════
       FORM CARD
    ═══════════════════════════════════════════ */
    .ct-form-card {
        background: var(--white);
        border-radius: 24px;
        padding: 48px 40px;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.06),
            0 16px 48px rgba(169,45,99,.08);
        position: relative;
        overflow: hidden;
    }
    @media (max-width: 600px) { .ct-form-card { padding: 32px 20px; } }

    .ct-form-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light), var(--pink));
    }
    .ct-form-card::after {
        content: '';
        position: absolute;
        bottom: -120px; right: -120px;
        width: 380px; height: 380px;
        border-radius: 50%;
        background: radial-gradient(ellipse at center, rgba(169,45,99,.04) 0%, transparent 70%);
        pointer-events: none;
    }

    .ct-section-tag {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--pink);
        margin-bottom: 10px;
    }
    .ct-section-tag::before {
        content: '';
        display: block;
        width: 18px; height: 1px;
        background: var(--pink);
        opacity: .45;
    }

    .ct-form-title {
        font-weight: 800;
        font-size: clamp(1.6rem, 4vw, 2.2rem);
        color: var(--text-dark);
        letter-spacing: -1.2px;
        line-height: 1.1;
        margin-bottom: 8px;
    }
    .ct-form-title .accent { color: var(--pink); }

    .ct-form-desc {
        font-size: .88rem;
        color: var(--text-light);
        line-height: 1.7;
        margin-bottom: 36px;
    }

    .ct-form-wrap { position: relative; z-index: 1; }

    /* WhatsApp button below form */
    .ct-or-divider {
        display: flex;
        align-items: center;
        gap: 16px;
        margin: 32px 0;
    }
    .ct-or-divider::before, .ct-or-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: rgba(169,45,99,.10);
    }
    .ct-or-divider span {
        font-size: .60rem;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: var(--text-light);
        white-space: nowrap;
    }

    .ct-wa-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        width: 100%;
        padding: 16px 28px;
        border-radius: 14px;
        background: linear-gradient(135deg, #128C7E, var(--green), #25D366);
        color: var(--white);
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: .5px;
        text-decoration: none;
        transition: transform .3s, box-shadow .3s;
        box-shadow: 0 8px 24px rgba(37,211,102,.28);
        position: relative;
        overflow: hidden;
    }
    .ct-wa-btn::before {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,.15) 0%, transparent 60%);
        pointer-events: none;
    }
    .ct-wa-btn svg { width: 22px; height: 22px; flex-shrink: 0; }
    .ct-wa-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 16px 40px rgba(37,211,102,.38);
    }

    .ct-wa-note {
        text-align: center;
        font-size: .68rem;
        color: var(--text-light);
        margin-top: 10px;
        line-height: 1.5;
    }

    /* ═══════════════════════════════════════════
       SIDEBAR
    ═══════════════════════════════════════════ */
    .ct-sidebar {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Contact details card */
    .ct-details-card {
        background: var(--white);
        border-radius: 20px;
        overflow: hidden;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.06),
            0 8px 24px rgba(169,45,99,.07);
        position: relative;
    }
    .ct-details-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light));
        z-index: 1;
    }

    .ct-details-header {
        padding: 28px 26px 20px;
        border-bottom: 1px solid rgba(169,45,99,.06);
    }
    .ct-details-title {
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: var(--pink);
        margin-bottom: 6px;
    }
    .ct-details-subtitle {
        font-size: .82rem;
        color: var(--text-light);
        line-height: 1.5;
    }

    .ct-detail-list { padding: 8px 0; }

    .ct-detail-row {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 16px 26px;
        text-decoration: none;
        transition: background .2s;
        border-bottom: 1px solid rgba(169,45,99,.05);
    }
    .ct-detail-row:last-child { border-bottom: none; }
    .ct-detail-row:hover { background: var(--pink-soft); }

    .ct-detail-icon {
        width: 42px; height: 42px;
        border-radius: 12px;
        background: var(--pink-soft);
        border: 1.5px solid rgba(169,45,99,.10);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background .25s, border-color .25s;
    }
    .ct-detail-row:hover .ct-detail-icon {
        background: var(--pink);
        border-color: var(--pink);
    }
    .ct-detail-icon svg {
        width: 18px; height: 18px;
        stroke: var(--pink);
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        transition: stroke .25s;
    }
    .ct-detail-row:hover .ct-detail-icon svg { stroke: white; }

    .ct-detail-content {}
    .ct-detail-label {
        font-size: .52rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--text-light);
        margin-bottom: 3px;
    }
    .ct-detail-val {
        font-size: .84rem;
        font-weight: 600;
        color: var(--text-dark);
        line-height: 1.45;
    }
    .ct-detail-val small {
        display: block;
        font-size: .72rem;
        font-weight: 400;
        color: var(--text-light);
        margin-top: 2px;
    }

    /* ═══════════════════════════════════════════
       SOCIAL CARD
    ═══════════════════════════════════════════ */
    .ct-social-card {
        background: linear-gradient(145deg, #6B0F35, var(--pink-dark), var(--pink));
        border-radius: 20px;
        padding: 28px 26px;
        position: relative;
        overflow: hidden;
    }
    .ct-social-card::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 70% 60% at 0% 30%, rgba(242,194,0,.14) 0%, transparent 60%),
            radial-gradient(ellipse 50% 50% at 100% 80%, rgba(0,0,0,.18) 0%, transparent 60%);
        pointer-events: none;
    }

    .ct-social-title {
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 4px;
        text-transform: uppercase;
        color: var(--yellow);
        margin-bottom: 6px;
        position: relative; z-index: 1;
    }
    .ct-social-desc {
        font-size: .80rem;
        color: rgba(255,255,255,.55);
        line-height: 1.55;
        margin-bottom: 20px;
        position: relative; z-index: 1;
    }

    .ct-social-links {
        display: flex;
        flex-direction: column;
        gap: 10px;
        position: relative; z-index: 1;
    }

    .ct-social-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 12px;
        text-decoration: none;
        font-size: .78rem;
        font-weight: 700;
        transition: all .25s;
        border: 1.5px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.08);
        color: rgba(255,255,255,.88);
        backdrop-filter: blur(4px);
    }
    .ct-social-link svg {
        width: 18px; height: 18px;
        flex-shrink: 0;
    }
    .ct-social-link span { flex: 1; }
    .ct-social-link small {
        font-size: .60rem;
        font-weight: 500;
        opacity: .60;
    }
    .ct-social-link:hover {
        background: rgba(255,255,255,.16);
        border-color: rgba(255,255,255,.28);
        transform: translateX(4px);
        color: white;
        text-decoration: none;
    }

    /* Individual social brand hover colours */
    .ct-social-link.fb:hover   { background: rgba(24,119,242,.30); border-color: rgba(24,119,242,.50); }
    .ct-social-link.ig:hover   { background: rgba(225,48,108,.30);  border-color: rgba(225,48,108,.50); }
    .ct-social-link.yt:hover   { background: rgba(255,0,0,.25);     border-color: rgba(255,0,0,.45); }
    .ct-social-link.li:hover   { background: rgba(0,119,181,.30);   border-color: rgba(0,119,181,.50); }
    .ct-social-link.tt:hover   { background: rgba(0,0,0,.35);       border-color: rgba(255,255,255,.35); }

    /* ═══════════════════════════════════════════
       MAP
    ═══════════════════════════════════════════ */
    .ct-map-section {
        background: var(--white);
        padding: 0 0 80px;
    }
    @media (min-width: 768px) { .ct-map-section { padding: 0 0 90px; } }

    .ct-map-inner {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 20px;
    }
    @media (min-width: 768px) { .ct-map-inner { padding: 0 36px; } }

    .ct-map-header {
        padding: 60px 0 28px;
    }
    .ct-map-tag {
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 5px;
        text-transform: uppercase;
        color: var(--pink);
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .ct-map-tag::before {
        content: '';
        width: 16px; height: 1px;
        background: var(--pink);
        opacity: .45;
    }
    .ct-map-title {
        font-weight: 800;
        font-size: clamp(1.5rem, 3.5vw, 2.2rem);
        color: var(--text-dark);
        letter-spacing: -1.2px;
        line-height: 1.1;
    }
    .ct-map-title .accent { color: var(--pink); }

    .ct-map-frame {
        border-radius: 20px;
        overflow: hidden;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.07),
            0 16px 48px rgba(169,45,99,.09);
        position: relative;
    }
    .ct-map-frame::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--pink-dark), var(--pink), var(--yellow-light));
        z-index: 2;
    }
    .ct-map-frame iframe {
        width: 100%;
        height: 400px;
        display: block;
        border: none;
        filter: grayscale(15%) contrast(1.05);
    }
    @media (min-width: 768px) { .ct-map-frame iframe { height: 460px; } }

    /* ═══════════════════════════════════════════
       FLOATING WHATSAPP
    ═══════════════════════════════════════════ */
    .ct-wa-float {
        position: fixed;
        bottom: 28px;
        right: 28px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 10px;
    }

    .ct-wa-float-tooltip {
        background: var(--white);
        border-radius: 12px;
        padding: 11px 16px;
        box-shadow: 0 8px 32px rgba(0,0,0,.13);
        font-size: .76rem;
        font-weight: 600;
        color: var(--text-dark);
        max-width: 190px;
        line-height: 1.45;
        position: relative;
        animation: ctTooltipBob 3s ease-in-out infinite;
        border: 1px solid rgba(37,211,102,.15);
    }
    .ct-wa-float-tooltip::after {
        content: '';
        position: absolute;
        bottom: -7px; right: 20px;
        width: 13px; height: 13px;
        background: var(--white);
        border-right: 1px solid rgba(37,211,102,.15);
        border-bottom: 1px solid rgba(37,211,102,.15);
        transform: rotate(45deg);
    }
    @keyframes ctTooltipBob {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }

    .ct-wa-float-btn {
        width: 60px; height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #128C7E, var(--green));
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 28px rgba(37,211,102,.40);
        text-decoration: none;
        transition: transform .3s, box-shadow .3s;
        position: relative;
    }
    .ct-wa-float-btn svg { width: 30px; height: 30px; }
    .ct-wa-float-btn:hover {
        transform: scale(1.08);
        box-shadow: 0 14px 40px rgba(37,211,102,.50);
    }
    .ct-wa-float-btn::before {
        content: '';
        position: absolute; inset: -6px;
        border-radius: 50%;
        border: 2px solid rgba(37,211,102,.35);
        animation: ctWaPulse 2.2s ease-out infinite;
    }
    .ct-wa-float-btn::after {
        content: '';
        position: absolute; inset: -13px;
        border-radius: 50%;
        border: 2px solid rgba(37,211,102,.18);
        animation: ctWaPulse 2.2s ease-out infinite .55s;
    }
    @keyframes ctWaPulse {
        0% { transform: scale(1); opacity: 1; }
        100% { transform: scale(1.5); opacity: 0; }
    }

    /* ═══════════════════════════════════════════
       FORMINATOR OVERRIDES
    ═══════════════════════════════════════════ */
    .ct-form-wrap .forminator-ui.forminator-custom-form {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    .ct-form-wrap .forminator-row { margin-bottom: 18px !important; }
    .ct-form-wrap .forminator-input,
    .ct-form-wrap .forminator-textarea,
    .ct-form-wrap select {
        border: 1.5px solid rgba(169,45,99,.15) !important;
        border-radius: 12px !important;
        padding: 14px 18px !important;
        font-size: .88rem !important;
        font-family: 'Manrope', sans-serif !important;
        color: var(--text-dark) !important;
        background: var(--off-white) !important;
        transition: border-color .25s, box-shadow .25s !important;
        width: 100% !important;
    }
    .ct-form-wrap .forminator-input:focus,
    .ct-form-wrap .forminator-textarea:focus,
    .ct-form-wrap select:focus {
        border-color: var(--pink) !important;
        box-shadow: 0 0 0 4px rgba(169,45,99,.08) !important;
        outline: none !important;
    }
    .ct-form-wrap .forminator-label {
        font-size: .72rem !important;
        font-weight: 700 !important;
        letter-spacing: .5px !important;
        color: var(--text-dark) !important;
        font-family: 'Manrope', sans-serif !important;
        margin-bottom: 7px !important;
    }
    .ct-form-wrap .forminator-btn-submit {
        background: linear-gradient(135deg, var(--pink-dark), var(--pink), var(--pink-mid)) !important;
        color: var(--white) !important;
        font-family: 'Manrope', sans-serif !important;
        font-size: .78rem !important;
        font-weight: 700 !important;
        letter-spacing: 1.5px !important;
        text-transform: uppercase !important;
        padding: 16px 36px !important;
        border-radius: 100px !important;
        border: none !important;
        box-shadow: 0 8px 24px rgba(169,45,99,.35) !important;
        transition: transform .3s, box-shadow .3s !important;
        cursor: pointer !important;
    }
    .ct-form-wrap .forminator-btn-submit:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 16px 40px rgba(169,45,99,.45) !important;
    }
    .ct-form-wrap .forminator-error-message {
        color: #dc2743 !important;
        font-size: .72rem !important;
        font-weight: 600 !important;
        margin-top: 4px !important;
    }
    .ct-form-wrap .forminator-response-output {
        border-radius: 12px !important;
        font-family: 'Manrope', sans-serif !important;
        font-size: .84rem !important;
    }
</style>

<!-- HERO -->
<section class="ct-hero">
    <div class="ct-hero-circle-tl"></div>
    <div class="ct-hero-circle-br"></div>
    <div class="ct-hero-inner">
        <h1>Get in <em>Touch</em> With Us</h1>
        <p class="ct-hero-sub">
            We would love to hear from you. Send us a message or reach out on WhatsApp and our team will get back to you as soon as possible.
        </p>
    </div>
</section>

<!-- MAIN: FORM + SIDEBAR -->
<section class="ct-main">
    <div class="ct-wrap">
        <div class="ct-layout">

            <!-- Form Card -->
            <div class="ct-form-card">
                <div class="ct-section-tag">Send a Message</div>
                <h2 class="ct-form-title">We'd Love to <span class="accent">Hear From You</span></h2>
                <p class="ct-form-desc">
                    Fill in the form below and our team will get back to you within 24 hours. You can also reach us on WhatsApp for a faster response.
                </p>

                <div class="ct-form-wrap">
                    [forminator_form id="1203"]
                </div>

                <div class="ct-or-divider"><span>Or reach us instantly on WhatsApp</span></div>

                <a href="https://wa.me/923268050116?text=Assalam%20o%20Alaikum%2C%20I%20would%20like%20to%20get%20in%20touch%20with%20Almarah%20Foundation."
                   target="_blank" rel="noopener noreferrer" class="ct-wa-btn">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Start a WhatsApp Chat
                </a>
                <p class="ct-wa-note">Typically replies within a few minutes during working hours.</p>
            </div>

            <!-- Sidebar -->
            <div class="ct-sidebar">

                <!-- Contact Details -->
                <div class="ct-details-card">
                    <div class="ct-details-header">
                        <div class="ct-details-title">Contact Information</div>
                        <p class="ct-details-subtitle">All the ways you can reach the Almarah Foundation team.</p>
                    </div>
                    <div class="ct-detail-list">

                        <a href="mailto:info@almarah.org" class="ct-detail-row">
                            <div class="ct-detail-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </div>
                            <div class="ct-detail-content">
                                <div class="ct-detail-label">Email</div>
                                <div class="ct-detail-val">info@almarah.org</div>
                            </div>
                        </a>

<!-- WhatsApp / Mobile -->
<a href="https://wa.me/923268050116" target="_blank" rel="noopener noreferrer" class="ct-detail-row">
    <div class="ct-detail-icon">
        <svg viewBox="0 0 24 24" fill="currentColor" stroke="none">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </div>
    <div class="ct-detail-content">
        <div class="ct-detail-label">WhatsApp / Mobile</div>
        <div class="ct-detail-val">+92 326 8050116</div>
    </div>
</a>

<!-- UAN -->
<a href="tel:+9242111111130" class="ct-detail-row">
    <div class="ct-detail-icon">
        <svg viewBox="0 0 24 24">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.58 3.49 2 2 0 0 1 3.55 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6 6l.9-.9a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.73 16z"/>
        </svg>
    </div>
    <div class="ct-detail-content">
        <div class="ct-detail-label">UAN</div>
        <div class="ct-detail-val">(042) 111 111 130</div>
    </div>
</a>

                        <div class="ct-detail-row">
                            <div class="ct-detail-icon">
                                <svg viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </div>
                            <div class="ct-detail-content">
                                <div class="ct-detail-label">Office Hours</div>
                                <div class="ct-detail-val">Monday to Saturday
                                    <small>9:00 AM to 6:00 PM PST</small>
                                </div>
                            </div>
                        </div>

                        <div class="ct-detail-row">
                            <div class="ct-detail-icon">
                                <svg viewBox="0 0 24 24">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                    <circle cx="12" cy="10" r="3"/>
                                </svg>
                            </div>
                            <div class="ct-detail-content">
                                <div class="ct-detail-label">Location</div>
                                <div class="ct-detail-val">3 Canal Park, Gulberg-II, Lahore
                                    <small>Punjab, Pakistan</small>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ═══ SOCIAL LINKS CARD ═══ -->
                <div class="ct-social-card">
                    <div class="ct-social-title">Follow Us</div>
                    <p class="ct-social-desc">Stay connected and follow our work across social media.</p>
                    <div class="ct-social-links">

                        <!-- Facebook -->
                        <a href="https://www.facebook.com/almarahfoundation"
                           target="_blank" rel="noopener noreferrer"
                           class="ct-social-link fb">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                            </svg>
                            <span>Facebook</span>
                            <small>almarahfoundation</small>
                        </a>

                        <!-- Instagram -->
                        <a href="https://www.instagram.com/almarahfoundation"
                           target="_blank" rel="noopener noreferrer"
                           class="ct-social-link ig">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                            </svg>
                            <span>Instagram</span>
                            <small>almarahfoundation</small>
                        </a>

                        <!-- YouTube -->
                        <a href="https://www.youtube.com/@almarahfoundation"
                           target="_blank" rel="noopener noreferrer"
                           class="ct-social-link yt">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/>
                                <polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" fill="white"/>
                            </svg>
                            <span>YouTube</span>
                            <small>almarahfoundation</small>
                        </a>

                        <!-- LinkedIn — NEW -->
                        <a href="https://pk.linkedin.com/company/almarahfoundation/"
                           target="_blank" rel="noopener noreferrer"
                           class="ct-social-link li">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/>
                                <rect x="2" y="9" width="4" height="12"/>
                                <circle cx="4" cy="4" r="2"/>
                            </svg>
                            <span>LinkedIn</span>
                            <small>almarahfoundation</small>
                        </a>

                        <!-- TikTok — NEW -->
                        <a href="https://www.tiktok.com/@almarahfoundation1"
                           target="_blank" rel="noopener noreferrer"
                           class="ct-social-link tt">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-2.88 2.5 2.89 2.89 0 0 1-2.89-2.89 2.89 2.89 0 0 1 2.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 0 0-.79-.05 6.34 6.34 0 0 0-6.34 6.34 6.34 6.34 0 0 0 6.34 6.34 6.34 6.34 0 0 0 6.33-6.34V8.69a8.18 8.18 0 0 0 4.78 1.52V6.75a4.85 4.85 0 0 1-1.01-.06z"/>
                            </svg>
                            <span>TikTok</span>
                            <small>@almarahfoundation1</small>
                        </a>

                    </div>
                </div>

            </div><!-- /.ct-sidebar -->
        </div>
    </div>
</section>

<!-- MAP -->
<div class="ct-map-frame">
    <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3402.6!2d74.32!3d31.52!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x391904f02bffffff%3A0x8bc8e3950651cb34!2sAlmarah%20Foundation!5e0!3m2!1sen!2s!4v1700000000000"
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        title="Almarah Foundation - Canal Park, Lahore">
    </iframe>
</div>

<!-- FLOATING WHATSAPP -->
<div class="ct-wa-float">
    <div class="ct-wa-float-tooltip">
        Need help? Chat with us on WhatsApp!
    </div>
    <a href="https://wa.me/923268050116?text=Assalam%20o%20Alaikum%2C%20I%20would%20like%20to%20get%20in%20touch%20with%20Almarah%20Foundation."
       target="_blank" rel="noopener noreferrer"
       class="ct-wa-float-btn" aria-label="Chat on WhatsApp">
        <svg viewBox="0 0 24 24" fill="white">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>
</div>