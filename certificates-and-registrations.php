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
    .lc-hero {
        background: linear-gradient(135deg, #6B0F35 0%, var(--pink-dark) 30%, var(--pink) 65%, var(--pink-mid) 100%);
        padding: 110px 24px 80px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .lc-hero { padding: 130px 40px 90px; } }

    .lc-hero::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 70% 60% at 10% 60%, rgba(242,194,0,.18) 0%, transparent 60%),
            radial-gradient(ellipse 50% 50% at 90% 15%, rgba(255,255,255,.09) 0%, transparent 55%),
            radial-gradient(ellipse 40% 40% at 50% 100%, rgba(0,0,0,.14) 0%, transparent 60%);
        pointer-events: none;
    }

    .lc-hero-circle-tl {
        position: absolute;
        top: -100px; left: -100px;
        width: 380px; height: 380px;
        border-radius: 50%;
        border: 55px solid rgba(255,255,255,.04);
        pointer-events: none;
    }
    .lc-hero-circle-br {
        position: absolute;
        bottom: -80px; right: -160px;
        width: 500px; height: 500px;
        border-radius: 50%;
        border: 70px solid rgba(242,194,0,.05);
        pointer-events: none;
    }
    @media (min-width: 768px) {
        .lc-hero-circle-tl { top: -150px; left: -150px; width: 600px; height: 600px; }
        .lc-hero-circle-br { bottom: -120px; right: -220px; width: 750px; height: 750px; }
    }

    .lc-hero-inner {
        position: relative;
        z-index: 2;
        max-width: 700px;
        margin: 0 auto;
    }

    .lc-hero h1 {
        font-weight: 800;
        font-size: clamp(2.2rem, 7vw, 4.2rem);
        color: var(--white);
        letter-spacing: -2px;
        line-height: 1.05;
        text-shadow: 0 4px 40px rgba(0,0,0,.22);
        margin-bottom: 16px;
    }
    .lc-hero h1 em {
        font-style: normal;
        color: var(--yellow);
        filter: drop-shadow(0 0 24px rgba(242,194,0,.30));
    }

    .lc-hero-sub {
        font-size: .92rem;
        font-weight: 400;
        color: rgba(255,255,255,.60);
        line-height: 1.75;
        max-width: 600px;
        margin: 0 auto 36px;
    }

    .lc-hero-badges {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .lc-hero-badge {
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
    .lc-hero-badge svg {
        width: 15px; height: 15px;
        flex-shrink: 0;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .lc-hero-badge.gold {
        background: rgba(242,194,0,.15);
        border-color: rgba(242,194,0,.30);
        color: var(--yellow-light);
    }

    /* ═══════════════════════════════════════════
       CERTIFICATES SECTION
    ═══════════════════════════════════════════ */
    .lc-main {
        background: var(--off-white);
        padding: 80px 0 100px;
        position: relative;
        overflow: hidden;
    }
    @media (min-width: 768px) { .lc-main { padding: 100px 0 120px; } }

    .lc-main::before {
        content: '';
        position: absolute;
        top: -200px; right: -200px;
        width: 600px; height: 600px;
        border-radius: 50%;
        border: 80px solid rgba(169,45,99,.03);
        pointer-events: none;
    }
    .lc-main::after {
        content: '';
        position: absolute;
        bottom: -180px; left: -180px;
        width: 500px; height: 500px;
        border-radius: 50%;
        border: 65px solid rgba(242,194,0,.03);
        pointer-events: none;
    }

    .lc-wrap {
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 1;
    }
    @media (min-width: 768px) { .lc-wrap { padding: 0 36px; } }

    .lc-section-header {
        text-align: center;
        margin-bottom: 56px;
    }
    .lc-section-tag {
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
    .lc-section-tag::before, .lc-section-tag::after {
        content: '';
        display: block;
        width: 18px; height: 1px;
        background: var(--pink);
        opacity: .45;
    }
    .lc-section-title {
        font-weight: 800;
        font-size: clamp(1.8rem, 4.5vw, 2.8rem);
        color: var(--text-dark);
        letter-spacing: -1.5px;
        line-height: 1.1;
        margin-bottom: 14px;
    }
    .lc-section-title .accent { color: var(--pink); }
    .lc-section-desc {
        font-size: .92rem;
        color: var(--text-light);
        line-height: 1.75;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Grid */
    .lc-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
        max-width: 900px;
        margin: 0 auto;
    }
    @media (min-width: 640px) {
        .lc-grid { grid-template-columns: repeat(2, 1fr); }
    }

    /* Certificate Card */
    .lc-card {
        background: var(--white);
        border-radius: 20px;
        padding: 28px 26px;
        display: flex;
        align-items: flex-start;
        gap: 20px;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.06),
            0 8px 28px rgba(169,45,99,.07);
        text-decoration: none;
        transition: transform .35s cubic-bezier(.22,.9,.36,1), box-shadow .35s;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    .lc-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, var(--pink-dark), var(--pink));
        border-radius: 20px 0 0 20px;
        transition: width .3s;
    }
    .lc-card:hover {
        transform: translateY(-5px);
        box-shadow:
            0 0 0 1px rgba(169,45,99,.10),
            0 20px 50px rgba(169,45,99,.12);
    }
    .lc-card:hover::before { width: 6px; }

    .lc-card-icon-wrap {
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
    .lc-card-icon-wrap svg {
        width: 22px; height: 22px;
        stroke: var(--pink);
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        transition: stroke .3s;
    }
    .lc-card:hover .lc-card-icon-wrap {
        background: linear-gradient(135deg, var(--pink-dark), var(--pink));
        border-color: transparent;
    }
    .lc-card:hover .lc-card-icon-wrap svg { stroke: white; }

    .lc-card-info { flex: 1; min-width: 0; }

    .lc-card-title {
        font-weight: 700;
        font-size: .96rem;
        color: var(--text-dark);
        letter-spacing: -.2px;
        line-height: 1.4;
        margin-bottom: 10px;
    }

    .lc-card-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .lc-card-type {
        font-size: .58rem;
        font-weight: 600;
        color: var(--text-light);
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .lc-card-type svg {
        width: 12px; height: 12px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .lc-card-arrow {
        width: 34px; height: 34px;
        border-radius: 50%;
        background: var(--pink-soft);
        border: 1.5px solid rgba(169,45,99,.12);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        align-self: center;
        transition: all .3s;
    }
    .lc-card-arrow svg {
        width: 14px; height: 14px;
        stroke: var(--pink);
        fill: none;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
        transition: all .3s;
    }
    .lc-card:hover .lc-card-arrow {
        background: var(--pink);
        border-color: var(--pink);
    }
    .lc-card:hover .lc-card-arrow svg {
        stroke: white;
        transform: translateY(2px);
    }
</style>

<!-- HERO -->
<section class="lc-hero">
    <div class="lc-hero-circle-tl"></div>
    <div class="lc-hero-circle-br"></div>
    <div class="lc-hero-inner">
        <h1>Licenses, Certificates <em>& Registrations</em></h1>
        <p class="lc-hero-sub">
            Almarah Foundation is fully committed to transparency, compliance, and accountability. We operate under all required legal registrations and maintain up-to-date licenses and certifications issued by relevant authorities.
        </p>
        <div class="lc-hero-badges">
            <div class="lc-hero-badge gold">
                <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Fully Compliant
            </div>
            <div class="lc-hero-badge">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Government Registered
            </div>
            <div class="lc-hero-badge">
                <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Publicly Available
            </div>
        </div>
    </div>
</section>

<!-- CERTIFICATES GRID -->
<section class="lc-main">
    <div class="lc-wrap">

        <div class="lc-section-header">
            <div class="lc-section-tag">Official Documents</div>
            <h2 class="lc-section-title">Our <span class="accent">Registrations</span> and Certificates</h2>
            <p class="lc-section-desc">
                These documents reflect our dedication to maintaining high standards in our operations and ensuring trust with our partners, donors, and the communities we serve.
            </p>
        </div>

        <div class="lc-grid">

            <!-- 1. FBR Taxpayer -->
            <a href="https://almarah.org/wp-content/uploads/2026/04/TaxPayer-Registration-Certificate.pdf"
               target="_blank" rel="noopener noreferrer"
               class="lc-card">
                <div class="lc-card-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="lc-card-info">
                    <div class="lc-card-title">FBR Taxpayer Registration Certificate</div>
                    <div class="lc-card-meta">
                        <span class="lc-card-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            View PDF
                        </span>
                    </div>
                </div>
                <div class="lc-card-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>

            <!-- 2. FBR Tax Exemption -->
            <a href="https://almarah.org/wp-content/uploads/2026/10/Certificate-under-section-236-Tax-Exampation-FBR-30-6-2028.pdf"
               target="_blank" rel="noopener noreferrer"
               class="lc-card">
                <div class="lc-card-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="lc-card-info">
                    <div class="lc-card-title">FBR Tax Exemption Certificate</div>
                    <div class="lc-card-meta">
                        <span class="lc-card-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            View PDF
                        </span>
                    </div>
                </div>
                <div class="lc-card-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>

            <!-- 3. Child Protection Bureau -->
            <a href="https://almarah.org/wp-content/uploads/2026/10/Child-Protection-Certificate-2025.pdf"
               target="_blank" rel="noopener noreferrer"
               class="lc-card">
                <div class="lc-card-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="lc-card-info">
                    <div class="lc-card-title">Registration Certificate – Child Protection Bureau</div>
                    <div class="lc-card-meta">
                        <span class="lc-card-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            View PDF
                        </span>
                    </div>
                </div>
                <div class="lc-card-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>

            <!-- 4. Home Department -->
            <a href="https://almarah.org/wp-content/uploads/2026/04/home-department-donation.pdf"
               target="_blank" rel="noopener noreferrer"
               class="lc-card">
                <div class="lc-card-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="lc-card-info">
                    <div class="lc-card-title">Registration Certificate – Home Department (Donations)</div>
                    <div class="lc-card-meta">
                        <span class="lc-card-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            View PDF
                        </span>
                    </div>
                </div>
                <div class="lc-card-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>

            <!-- 5. Punjab Charity Commission -->
            <a href=https://almarah.org/wp-content/uploads/2026/10/PUNJAB-CHARITY-CERTIFICATE-22-9-2025-TO-22-9-2027.pdf"
               target="_blank" rel="noopener noreferrer"
               class="lc-card">
                <div class="lc-card-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="lc-card-info">
                    <div class="lc-card-title">Registration Certificate – Punjab Charity Commission</div>
                    <div class="lc-card-meta">
                        <span class="lc-card-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            View PDF
                        </span>
                    </div>
                </div>
                <div class="lc-card-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>

            <!-- 6. Joint Stock Companies -->
            <a href="https://almarah.org/wp-content/uploads/2026/10/Joint-stock-Certificate-2026.pdf" 
               target="_blank" rel="noopener noreferrer"
               class="lc-card">
                <div class="lc-card-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="lc-card-info">
                    <div class="lc-card-title">Registration Certificate – Joint Stock Companies</div>
                    <div class="lc-card-meta">
                        <span class="lc-card-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            View PDF
                        </span>
                    </div>
                </div>
                <div class="lc-card-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>
            
     <!-- 7. Joint Stock Companies -->
            <a href="https://almarah.org/wp-content/uploads/2026/10/iso.pdf" 
               target="_blank" rel="noopener noreferrer"
               class="lc-card">
                <div class="lc-card-icon-wrap">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="lc-card-info">
                    <div class="lc-card-title">Certificate of Registration for ISO 9001:2015 Quality Management System</div>
                    <div class="lc-card-meta">
                        <span class="lc-card-type">
                            <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            View PDF
                        </span>
                    </div>
                </div>
                <div class="lc-card-arrow">
                    <svg viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </div>
            </a>

        </div>
    </div>
</section>