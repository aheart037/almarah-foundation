<style>
    .cp-page,
    .cp-page * {
        box-sizing: border-box;
    }

    :root {
        --cp-pink: #A92D63;
        --cp-pink-dark: #8A2050;
        --cp-pink-soft: rgba(169,45,99,.08);
        --cp-yellow: #F2C200;
        --cp-white: #FFFFFF;
        --cp-off-white: #FDF5F8;
        --cp-text-dark: #1A0810;
        --cp-text-mid: #5A2040;
        --cp-text-light: #9A6080;
    }

    .cp-page {
        font-family: 'Manrope', sans-serif;
        background: var(--cp-off-white);
        color: var(--cp-text-dark);
        max-width: 1120px;
        margin: 0 auto;
        padding: 44px 18px 70px;
    }

    @media (min-width: 768px) {
        .cp-page {
            padding: 70px 32px 90px;
        }
    }

    .cp-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 34px;
        align-items: start;
    }

    @media (min-width: 900px) {
        .cp-layout {
            grid-template-columns: 360px 1fr;
            gap: 58px;
        }
    }

    .cp-left {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    @media (max-width: 899px) {
        .cp-left {
            max-width: 430px;
            width: 100%;
            margin: 0 auto;
        }
    }

    .cp-photo {
        width: 100%;
        border-radius: 18px;
        overflow: hidden;
        background: var(--cp-white);
        box-shadow:
            0 0 0 1px rgba(169,45,99,.10),
            0 12px 34px rgba(169,45,99,.13);
        position: relative;
    }

    .cp-photo::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--cp-pink-dark), var(--cp-pink), #FFD740);
        z-index: 2;
    }

    .cp-photo img {
        width: 100%;
        height: auto;
        display: block;
        object-fit: cover;
        object-position: center top;
    }

    .cp-identity-card {
        background: var(--cp-white);
        border-radius: 16px;
        padding: 22px 18px;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.07),
            0 6px 20px rgba(169,45,99,.08);
        position: relative;
        overflow: hidden;
    }

    .cp-identity-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--cp-pink-dark), var(--cp-pink), #FFD740);
    }

    .cp-id-name {
        font-weight: 800;
        font-size: 1.25rem;
        color: var(--cp-text-dark);
        letter-spacing: -.4px;
        line-height: 1.25;
        margin-bottom: 5px;
    }

    .cp-id-role {
        font-size: .58rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--cp-pink);
        line-height: 1.45;
        margin-bottom: 16px;
    }

    .cp-id-divider {
        height: 1px;
        background: linear-gradient(90deg, rgba(169,45,99,.16), transparent);
        margin-bottom: 16px;
    }

    .cp-socials {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .cp-social-link {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 11px 13px;
        border-radius: 10px;
        text-decoration: none;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .2px;
        border: 1.5px solid transparent;
        transition: transform .25s, background .25s, border-color .25s;
        word-break: break-word;
    }

    .cp-social-link svg {
        width: 16px;
        height: 16px;
        min-width: 16px;
        flex-shrink: 0;
    }

    .cp-social-link:hover {
        transform: translateX(4px);
    }

    .cp-social-link.web {
        background: var(--cp-pink-soft);
        border-color: rgba(169,45,99,.12);
        color: var(--cp-pink);
    }

    .cp-social-link.web:hover {
        background: rgba(169,45,99,.14);
        border-color: var(--cp-pink);
    }

    .cp-social-link.fb {
        background: rgba(24,119,242,.07);
        border-color: rgba(24,119,242,.12);
        color: #1877F2;
    }

    .cp-social-link.fb:hover {
        background: rgba(24,119,242,.13);
        border-color: #1877F2;
    }

    .cp-social-link.ig {
        background: rgba(220,39,67,.07);
        border-color: rgba(220,39,67,.12);
        color: #dc2743;
    }

    .cp-social-link.ig:hover {
        background: rgba(220,39,67,.13);
        border-color: #dc2743;
    }

    .cp-right {
        min-width: 0;
    }

    .cp-section-label {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 3.5px;
        text-transform: uppercase;
        color: var(--cp-pink);
        margin-bottom: 13px;
        line-height: 1.4;
    }

    .cp-section-label::before,
    .cp-section-label::after {
        content: '';
        display: block;
        width: 14px;
        height: 1px;
        background: var(--cp-pink);
        opacity: .4;
    }

    @media (max-width: 480px) {
        .cp-section-label {
            letter-spacing: 2.5px;
            font-size: .50rem;
        }

        .cp-section-label::before,
        .cp-section-label::after {
            width: 10px;
        }
    }

    .cp-heading {
        font-weight: 800;
        font-size: clamp(1.65rem, 7vw, 2.45rem);
        color: var(--cp-text-dark);
        letter-spacing: -1.2px;
        line-height: 1.12;
        margin: 0 0 10px;
    }

    .cp-heading span {
        color: var(--cp-pink);
    }

    .cp-heading-line {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
    }

    .cp-heading-line::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(169,45,99,.20), transparent);
    }

    .cp-heading-line .dot {
        width: 7px;
        height: 7px;
        background: var(--cp-yellow);
        border-radius: 50%;
        box-shadow: 0 0 8px rgba(242,194,0,.5);
        flex-shrink: 0;
    }

    .cp-message-body {
        display: flex;
        flex-direction: column;
        gap: 17px;
    }

    .cp-message-body p {
        margin: 0;
        font-size: .95rem;
        font-weight: 400;
        line-height: 1.85;
        color: var(--cp-text-mid);
    }

    @media (max-width: 480px) {
        .cp-message-body p {
            font-size: .90rem;
            line-height: 1.78;
        }
    }

    .cp-message-body p.opening {
        font-size: 1rem;
        font-weight: 700;
        color: var(--cp-text-dark);
    }

    .cp-message-body p.highlight {
        border-left: 3px solid var(--cp-pink);
        padding-left: 16px;
        font-weight: 600;
        color: var(--cp-text-dark);
        font-size: .97rem;
        line-height: 1.75;
        background: rgba(169,45,99,.035);
        padding-top: 10px;
        padding-bottom: 10px;
        padding-right: 12px;
        border-radius: 0 10px 10px 0;
    }

    @media (max-width: 480px) {
        .cp-message-body p.highlight {
            font-size: .91rem;
            padding-left: 13px;
        }
    }

    .cp-signature {
        margin-top: 34px;
        padding-top: 26px;
        border-top: 1px solid rgba(169,45,99,.10);
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .cp-sig-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid rgba(169,45,99,.18);
        box-shadow: 0 4px 14px rgba(169,45,99,.12);
        flex-shrink: 0;
    }

    .cp-sig-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
        display: block;
    }

    .cp-sig-name {
        font-weight: 800;
        font-size: .96rem;
        color: var(--cp-text-dark);
        letter-spacing: -.2px;
        margin-bottom: 3px;
        line-height: 1.25;
    }

    .cp-sig-role {
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--cp-pink);
        line-height: 1.45;
    }
</style>

<div class="cp-page">
    <div class="cp-layout">

        <div class="cp-left">

            <div class="cp-photo">
                <img src="https://almarah.org/wp-content/uploads/2026/05/chairperson.jpg"
                     alt="Sophiya Waraich, Chairperson of Almarah Foundation">
            </div>

            <div class="cp-identity-card">
                <div class="cp-id-name">Sophiya Waraich</div>
                <div class="cp-id-role">Chairperson | Almarah Foundation</div>

                <div class="cp-id-divider"></div>

                <div class="cp-socials">

                    <a href="https://sophiyawaraich.com" target="_blank" rel="noopener noreferrer" class="cp-social-link web">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="2" y1="12" x2="22" y2="12"/>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                        </svg>
                        sophiyawaraich.com
                    </a>

                    <a href="https://www.facebook.com/sophiyawaraich/" target="_blank" rel="noopener noreferrer" class="cp-social-link fb">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>
                        </svg>
                        Facebook
                    </a>

                    <a href="https://www.instagram.com/sophiyawaraich/" target="_blank" rel="noopener noreferrer" class="cp-social-link ig">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                        </svg>
                        Instagram
                    </a>

                </div>
            </div>

        </div>

        <div class="cp-right">

            <div class="cp-section-label">Chairperson's Message</div>

            <h2 class="cp-heading">A Message from <span>Sophiya Waraich</span></h2>
            <div class="cp-heading-line"><div class="dot"></div></div>

            <div class="cp-message-body">

                <p class="opening">Assalam-o-Alaikum wa Rahmatullahi wa Barakatuh,</p>

                <p>It is with the deepest sense of gratitude to Allah Subhanahu wa Ta'ala that I write to you today. Every step Almarah Foundation has taken, every meal distributed, every child enrolled in school, and every widow supported has been made possible only through His mercy and the generosity of people like you.</p>

                <p class="highlight">Almarah was born from a simple but profound belief: those who are blessed have a responsibility toward those who are not. That belief continues to guide everything we do.</p>

                <p>When we started this journey, we began with a handful of volunteers and an unshakeable conviction that change was possible. We worked quietly in forgotten alleys, in flood affected villages, and in communities that rarely made headlines. We listened before we acted. We learned before we led. In doing so, we built something that has lasted.</p>

                <p>Today, Almarah Foundation runs programs across education, food security, women's empowerment, orphan care, and emergency relief. But our work is far from done. The needs of our people are vast, and the urgency is real. Millions of children in Pakistan still have no access to quality education. Millions of families go to sleep hungry. Millions of women have never been given the chance to realise their potential.</p>

                <p class="highlight">This is why we work. This is why we ask for your partnership. Not because charity is our goal, but because dignity, opportunity, and justice are.</p>

                <p>I am endlessly inspired by the spirit of giving that runs through our community. Whether you have donated a small amount or volunteered your time, whether you have shared our message or simply made dua for us, you are part of this mission. This belongs to all of us.</p>

                <p>To our dedicated team, our volunteers, our partners, and our donors near and far: thank you. You make this possible. You are the reason we persist. With the help of Allah, we will continue to serve with sincerity, accountability, and love.</p>

                <p>May Allah accept our collective efforts, bless your families, and grant relief to all those who are suffering. Ameen.</p>

            </div>

            <div class="cp-signature">
                <div class="cp-sig-avatar">
                    <img src="https://almarah.org/wp-content/uploads/2026/03/522075580_602997796196541_465002084556126553_n.jpg"
                         alt="Sophiya Waraich">
                </div>
                <div>
                    <div class="cp-sig-name">Sophiya Waraich</div>
                    <div class="cp-sig-role">Chairperson | Almarah Foundation</div>
                </div>
            </div>

        </div>

    </div>
</div>
```