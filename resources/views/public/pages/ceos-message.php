<?php
/**
 * Migrated page body: ceos-message (source: almarah-foundation/ceo-message.php).
 * Markup preserved from the original site; styles live in assets/css/pages/ceos-message.css.
 */
?>
<?php
$hero = [
    'eyebrow' => "CEO's Message",
    'title'   => 'A Message from',
    'accent'  => 'Our CEO',
    'sub'     => 'Words of vision and gratitude from the leadership steering Almarah Foundation’s mission across Pakistan.',
    'chips'   => ['Ms. Mahnoor Lilla', 'CEO | Almarah Foundation'],
];
require __DIR__ . '/../../partials/page-hero.php';
?>
<div class="ceo-page">
    <div class="ceo-layout">

        <div class="ceo-left">

            <div class="ceo-photo">
                <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/about/ceo.jpeg')) ?>"
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
                    <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/about/mahnoor-lillah-1023x1536-1-e1775069767617.jpg')) ?>"
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
