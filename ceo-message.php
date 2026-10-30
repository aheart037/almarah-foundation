<style>
    .ceo-page,
    .ceo-page * {
        box-sizing: border-box;
    }

    :root {
        --ceo-pink: #A92D63;
        --ceo-pink-dark: #8A2050;
        --ceo-pink-soft: rgba(169,45,99,.08);
        --ceo-yellow: #F2C200;
        --ceo-white: #FFFFFF;
        --ceo-off-white: #FDF5F8;
        --ceo-text-dark: #1A0810;
        --ceo-text-mid: #5A2040;
        --ceo-text-light: #9A6080;
    }

    .ceo-page {
        font-family: 'Manrope', sans-serif;
        background: var(--ceo-off-white);
        color: var(--ceo-text-dark);
        max-width: 1120px;
        margin: 0 auto;
        padding: 44px 18px 70px;
    }

    @media (min-width: 768px) {
        .ceo-page {
            padding: 70px 32px 90px;
        }
    }

    .ceo-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 34px;
        align-items: start;
    }

    @media (min-width: 900px) {
        .ceo-layout {
            grid-template-columns: 360px 1fr;
            gap: 58px;
        }
    }

    .ceo-left {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    @media (max-width: 899px) {
        .ceo-left {
            max-width: 430px;
            width: 100%;
            margin: 0 auto;
        }
    }

    .ceo-photo {
        width: 100%;
        border-radius: 18px;
        overflow: hidden;
        background: var(--ceo-white);
        box-shadow:
            0 0 0 1px rgba(169,45,99,.10),
            0 12px 34px rgba(169,45,99,.13);
        position: relative;
    }

    .ceo-photo::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--ceo-pink-dark), var(--ceo-pink), #FFD740);
        z-index: 2;
    }

    .ceo-photo img {
        width: 100%;
        height: auto;
        display: block;
        object-fit: cover;
        object-position: center top;
    }

    .ceo-identity-card {
        background: var(--ceo-white);
        border-radius: 16px;
        padding: 22px 18px;
        box-shadow:
            0 0 0 1px rgba(169,45,99,.07),
            0 6px 20px rgba(169,45,99,.08);
        position: relative;
        overflow: hidden;
    }

    .ceo-identity-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--ceo-pink-dark), var(--ceo-pink), #FFD740);
    }

    .ceo-id-name {
        font-weight: 800;
        font-size: 1.25rem;
        color: var(--ceo-text-dark);
        letter-spacing: -.4px;
        line-height: 1.25;
        margin-bottom: 5px;
    }

    .ceo-id-role {
        font-size: .58rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--ceo-pink);
        line-height: 1.45;
        margin-bottom: 16px;
    }

    .ceo-id-divider {
        height: 1px;
        background: linear-gradient(90deg, rgba(169,45,99,.16), transparent);
        margin-bottom: 16px;
    }

    .ceo-socials {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .ceo-social-link {
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

    .ceo-social-link svg {
        width: 16px;
        height: 16px;
        min-width: 16px;
        flex-shrink: 0;
    }

    .ceo-social-link:hover {
        transform: translateX(4px);
    }

    .ceo-social-link.ig {
        background: rgba(220,39,67,.07);
        border-color: rgba(220,39,67,.12);
        color: #dc2743;
    }

    .ceo-social-link.ig:hover {
        background: rgba(220,39,67,.13);
        border-color: #dc2743;
    }

    .ceo-social-link.linkedin {
        background: rgba(10,102,194,.07);
        border-color: rgba(10,102,194,.12);
        color: #0A66C2;
    }

    .ceo-social-link.linkedin:hover {
        background: rgba(10,102,194,.13);
        border-color: #0A66C2;
    }

    .ceo-right {
        min-width: 0;
    }

    .ceo-section-label {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 3.5px;
        text-transform: uppercase;
        color: var(--ceo-pink);
        margin-bottom: 13px;
        line-height: 1.4;
    }

    .ceo-section-label::before,
    .ceo-section-label::after {
        content: '';
        display: block;
        width: 14px;
        height: 1px;
        background: var(--ceo-pink);
        opacity: .4;
    }

    @media (max-width: 480px) {
        .ceo-section-label {
            letter-spacing: 2.5px;
            font-size: .50rem;
        }

        .ceo-section-label::before,
        .ceo-section-label::after {
            width: 10px;
        }
    }

    .ceo-heading {
        font-weight: 800;
        font-size: clamp(1.65rem, 7vw, 2.45rem);
        color: var(--ceo-text-dark);
        letter-spacing: -1.2px;
        line-height: 1.12;
        margin: 0 0 10px;
    }

    .ceo-heading span {
        color: var(--ceo-pink);
    }

    .ceo-heading-line {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 28px;
    }

    .ceo-heading-line::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, rgba(169,45,99,.20), transparent);
    }

    .ceo-heading-line .dot {
        width: 7px;
        height: 7px;
        background: var(--ceo-yellow);
        border-radius: 50%;
        box-shadow: 0 0 8px rgba(242,194,0,.5);
        flex-shrink: 0;
    }

    .ceo-message-body {
        display: flex;
        flex-direction: column;
        gap: 17px;
    }

    .ceo-message-body p {
        margin: 0;
        font-size: .95rem;
        font-weight: 400;
        line-height: 1.85;
        color: var(--ceo-text-mid);
    }

    @media (max-width: 480px) {
        .ceo-message-body p {
            font-size: .90rem;
            line-height: 1.78;
        }
    }

    .ceo-message-body p.highlight {
        border-left: 3px solid var(--ceo-pink);
        padding-left: 16px;
        font-weight: 600;
        color: var(--ceo-text-dark);
        font-size: .97rem;
        line-height: 1.75;
        background: rgba(169,45,99,.035);
        padding-top: 10px;
        padding-bottom: 10px;
        padding-right: 12px;
        border-radius: 0 10px 10px 0;
    }

    @media (max-width: 480px) {
        .ceo-message-body p.highlight {
            font-size: .91rem;
            padding-left: 13px;
        }
    }

    .ceo-signature {
        margin-top: 34px;
        padding-top: 26px;
        border-top: 1px solid rgba(169,45,99,.10);
        display: flex;
        align-items: center;
        gap: 13px;
    }

    .ceo-sig-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid rgba(169,45,99,.18);
        box-shadow: 0 4px 14px rgba(169,45,99,.12);
        flex-shrink: 0;
    }

    .ceo-sig-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center top;
        display: block;
    }

    .ceo-sig-name {
        font-weight: 800;
        font-size: .96rem;
        color: var(--ceo-text-dark);
        letter-spacing: -.2px;
        margin-bottom: 3px;
        line-height: 1.25;
    }

    .ceo-sig-role {
        font-size: .54rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--ceo-pink);
        line-height: 1.45;
    }
</style>

<div class="ceo-page">
    <div class="ceo-layout">

        <div class="ceo-left">

            <div class="ceo-photo">
                <img src="https://almarah.org/wp-content/uploads/2026/05/ceo.jpeg"
                     alt="Ms. Mahnoor Lilla, CEO of Almarah Foundation">
            </div>

            <div class="ceo-identity-card">
                <div class="ceo-id-name">Ms. Mahnoor Lilla</div>
                <div class="ceo-id-role">CEO | Almarah Foundation</div>

                <div class="ceo-id-divider"></div>

                <div class="ceo-socials">

                    <a href="https://www.instagram.com/mahnoorlilla_/" target="_blank" rel="noopener noreferrer" class="ceo-social-link ig">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
                        </svg>
                        Instagram
                    </a>

                    <a href="https://www.linkedin.com/in/mahnoor-lilla-105117333/" target="_blank" rel="noopener noreferrer" class="ceo-social-link linkedin">
                        <svg viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.34V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.61 0 4.27 2.38 4.27 5.47v6.27zM5.32 7.43a2.06 2.06 0 1 1 0-4.12 2.06 2.06 0 0 1 0 4.12zM7.1 20.45H3.54V9H7.1v11.45zM22.23 0H1.77C.79 0 0 .77 0 1.72v20.56C0 23.23.79 24 1.77 24h20.46c.98 0 1.77-.77 1.77-1.72V1.72C24 .77 23.21 0 22.23 0z"/>
                        </svg>
                        LinkedIn
                    </a>

                </div>
            </div>

        </div>

        <div class="ceo-right">

            <div class="ceo-section-label">CEO's Message</div>

            <h2 class="ceo-heading">A Message from <span>Ms. Mahnoor Lilla</span></h2>
            <div class="ceo-heading-line"><div class="dot"></div></div>

            <div class="ceo-message-body">

                <p>At Almarah Foundation, we’re more than just a charity, we’re building futures. Through our Feed the Hungry, Parent an Orphan, and Educate Pakistan programs, we aim to give people the tools they need for a better life.</p>

                <p class="highlight">The Parent an Orphan initiative is especially close to our hearts. We aim to provide not just shelter and food, but a true sense of belonging for these children.</p>

                <p>Our vision is to give them every opportunity to thrive, ensuring they have access to education, emotional support, and the tools they need to build successful futures.</p>

                <p class="highlight">We see these children as the future of Pakistan, and we are committed to helping them become leaders who will bring positive change to our nation.</p>

            </div>

            <div class="ceo-signature">
                <div class="ceo-sig-avatar">
                    <img src="https://almarah.org/wp-content/uploads/2026/04/mahnoor-lillah-1023x1536-1-e1775069767617.jpg"
                         alt="Ms. Mahnoor Lilla">
                </div>
                <div>
                    <div class="ceo-sig-name">Ms. Mahnoor Lilla</div>
                    <div class="ceo-sig-role">CEO | Almarah Foundation</div>
                </div>
            </div>

        </div>

    </div>
</div>