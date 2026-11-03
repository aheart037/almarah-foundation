<?php
/**
 * Migrated page body: home-sections (source: almarah-foundation/index.php).
 * Markup preserved from the original site; styles live in assets/css/pages/home-sections.css.
 */
?>
<section class="hp-about">
    <div class="hp-about-container">

        <!-- ══ IMAGE ══ -->
        <div class="hp-about-img-wrap">
            <div class="hp-about-img-main">
                <img loading="lazy" decoding="async"
                    src="<?= e(asset('assets/images/homepage/About-Us-rkzrl0xnoqpjqnb41bv8n53ozept5hbagvbmbh3row.jpg')) ?>"
                    alt="About Almarah Foundation — Children in Care">
            </div>
        </div>

        <!-- ══ CONTENT ══ -->
        <div class="hp-about-content">

            <div class="hp-about-eyebrow">About Us</div>

            <h2>Every Child Deserves a Place to Call <em>"Apna Ghar"</em></h2>

            <div class="hp-about-divider"></div>

            <p class="hp-about-text">
                At Almarah Foundation, we believe that a child's beginning shouldn't determine their end. Founded on June 4th, 2021, we believe in lasting transformation. Our purpose is to walk alongside every child who passes through our doors — offering a safe atmosphere where children can simply be children, the tools and knowledge they need to break cycles and dream bigger, and lasting empowerment to lead, thrive, and shape a brighter future. Kindness is our foundation; dedication is our heartbeat. Together, we aren't just changing lives — we are rewriting stories.
            </p>

            <div class="hp-about-v-m">

                <!-- Vision -->
                <div class="hp-about-card">
                    <div class="hp-about-card-icon"
                         style="background:rgba(242,194,0,.12); color:#C9A000;">
                        <svg viewBox="0 0 24 24">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </div>
                    <h4>Our Vision</h4>
                    <p>A compassionate and inclusive society where every vulnerable child and family thrives with dignity and the opportunity to fulfill their potential.</p>
                </div>

                <!-- Mission -->
                <div class="hp-about-card">
                    <div class="hp-about-card-icon"
                         style="background:rgba(169,45,99,.08); color:#A92D63;">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                    </div>
                    <h4>Our Mission</h4>
                    <p>Alleviating hunger, nurturing orphaned children in home-like environments, delivering accessible education and healthcare, and strengthening families through empowerment.</p>
                </div>

            </div>

            <a href="<?= e(base_url('about')) ?>" class="hp-about-cta">
                Discover Our Story
                <svg viewBox="0 0 24 24">
                    <line x1="5" y1="12" x2="19" y2="12"/>
                    <polyline points="12 5 19 12 12 19"/>
                </svg>
            </a>

        </div>

    </div>
</section>

<!-- ══════════════════════════════════════
     OUR PROGRAMS SECTION
══════════════════════════════════════ -->
<section class="hp-programs">
    <div class="hp-programs-container">

        <!-- ══ HEADER ══ -->
        <div class="hp-programs-header">
            <div class="hp-programs-eyebrow hp-reveal fade-up">Our Programs</div>
            <h2 class="hp-reveal fade-up delay-1">Projects and <em>Initiatives</em></h2>
            <p class="hp-programs-intro hp-reveal fade-up delay-2">
                From feeding families to educating children and providing safe homes, every program at Almarah Foundation is designed to create lasting change for those who need it most.
            </p>
        </div>

        <!-- ══ FEATURED 1: PARENT THE ORPHAN ══ -->
        <div class="hp-featured">
            <div class="hp-featured-img hp-reveal fade-left">
                <span class="hp-featured-badge">Flagship Program</span>
                <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/homepage/Parent-the-Orphan.jpg')) ?>"
                     alt="Parent the Orphan — Almarah Foundation">
            </div>
            <div class="hp-featured-body">
                <div class="hp-featured-num hp-reveal fade-right delay-1">Program 01</div>
                <h3 class="hp-reveal fade-right delay-2">Parent the Orphan</h3>
                <p class="hp-reveal fade-right delay-3">
                    Our flagship program provides orphaned and vulnerable children with a safe, nurturing home environment that meets internationally recognized standards of care. We attend to every child's health, hygiene, mental well-being, education, and recreation.
                </p>

                <!-- Stats: NO entrance animation -->
                <div class="hp-featured-stats">
                    <div class="hp-featured-stat">
                        <div class="hp-featured-stat-value">
                            <span class="hp-stat-num" data-target="275">0</span><span class="hp-featured-stat-suffix">+</span>
                        </div>
                        <div class="hp-featured-stat-label">Children in Care</div>
                    </div>
                    <div class="hp-featured-stat">
                        <div class="hp-featured-stat-value">
                            <span class="hp-stat-num" data-target="13">0</span>
                        </div>
                        <div class="hp-featured-stat-label">Care Homes</div>
                    </div>
                    <div class="hp-featured-stat">
                        <div class="hp-featured-stat-value">
                            <span class="hp-stat-num" data-target="4.6">0</span><span class="hp-featured-stat-suffix">M</span>
                        </div>
                        <div class="hp-featured-stat-label">Orphans in Pakistan</div>
                    </div>
                </div>

                <a href="<?= e(base_url('parent-the-orphan')) ?>" class="hp-featured-btn hp-reveal fade-up delay-4">
                    Learn More
                    <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </div>

        <!-- ══ FEATURED 2: FEED THE HUNGRY (REVERSED) ══ -->
        <div class="hp-featured reverse">
            <div class="hp-featured-img hp-reveal fade-right">
                <span class="hp-featured-badge">Core Initiative</span>
                <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/homepage/Feed-the-Hungry-1.jpg')) ?>"
                     alt="Feed the Hungry — Almarah Foundation">
            </div>
            <div class="hp-featured-body">
                <div class="hp-featured-num hp-reveal fade-left delay-1">Program 02</div>
                <h3 class="hp-reveal fade-left delay-2">Feed the Hungry</h3>
                <p class="hp-reveal fade-left delay-3">
                    We provide daily meals and monthly food rations to individuals and families from lower-income sectors, addressing the most fundamental human need, nourishment, while also offering financial assistance to those who need it most.
                </p>

                <!-- Stats: NO entrance animation -->
                <div class="hp-featured-stats">
                    <div class="hp-featured-stat">
                        <div class="hp-featured-stat-value">
                            <span class="hp-stat-num" data-target="852">0</span><span class="hp-featured-stat-suffix">+</span>
                        </div>
                        <div class="hp-featured-stat-label">Daily Breakfasts</div>
                    </div>
                    <div class="hp-featured-stat">
                        <div class="hp-featured-stat-value">
                            <span class="hp-stat-num" data-target="425">0</span><span class="hp-featured-stat-suffix">+</span>
                        </div>
                        <div class="hp-featured-stat-label">Daily Lunches</div>
                    </div>
                    <div class="hp-featured-stat">
                        <div class="hp-featured-stat-value">
                            <span class="hp-stat-num" data-target="76">0</span><span class="hp-featured-stat-suffix">+</span>
                        </div>
                        <div class="hp-featured-stat-label">Ration Packs / Mo</div>
                    </div>
                </div>

                <a href="<?= e(base_url('feed-the-hungry-2')) ?>" class="hp-featured-btn hp-reveal fade-up delay-4">
                    Learn More
                    <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </div>

        <!-- ══ GRID: 3 CARDS ══ -->
        <div class="hp-programs-grid">

            <!-- Educate Pakistan -->
            <a href="<?= e(base_url('educate-pakistan')) ?>" class="hp-card hp-reveal pop-in delay-1">
                <div class="hp-card-img">
                    <span class="hp-card-badge">03</span>
                    <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/homepage/Educate-Pakistan.jpg')) ?>"
                         alt="Educate Pakistan — Almarah Grammar School">
                </div>
                <div class="hp-card-body">
                    <h3>Educate Pakistan</h3>
                    <p>
                        Almarah Grammar School on Canal Road, Lahore offers Playgroup through High School education, empowering underprivileged children to break the cycle of poverty.
                    </p>

                    <div class="hp-card-stats">
                        <div class="hp-card-stat">
                            <div class="hp-card-stat-value">
                                <span class="hp-stat-num" data-target="300">0</span><span class="hp-card-stat-suffix">+</span>
                            </div>
                            <div class="hp-card-stat-label">Students</div>
                        </div>
                        <div class="hp-card-stat">
                            <div class="hp-card-stat-value">
                                <span class="hp-stat-num" data-target="23">0</span>
                            </div>
                            <div class="hp-card-stat-label">Teachers</div>
                        </div>
                    </div>

                    <span class="hp-card-link">
                        Explore Program
                        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                </div>
            </a>

            <!-- Izzat ki Roti -->
            <a href="<?= e(base_url('izat-ki-roti')) ?>" class="hp-card hp-reveal pop-in delay-2">
                <div class="hp-card-img">
                    <span class="hp-card-badge">04</span>
                    <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/projects/DSC07430-scaled.jpg')) ?>"
                         alt="Izzat ki Roti — Dignified Food Initiative">
                </div>
                <div class="hp-card-body">
                    <h3>Izzat ki Roti</h3>
                    <p>
                        A dignified food initiative that allows individuals to receive a meal without the stigma of charity. We preserve the self-respect of every person we serve.
                    </p>

                    <div class="hp-card-stats">
                        <div class="hp-card-stat">
                            <div class="hp-card-stat-value">
                                <span class="hp-stat-num" data-target="100">0</span>
                            </div>
                            <div class="hp-card-stat-label">Tandoors Active</div>
                        </div>
                        <div class="hp-card-stat">
                            <div class="hp-card-stat-value">
                                <span class="hp-stat-num" data-target="100">0</span><span class="hp-card-stat-suffix">%</span>
                            </div>
                            <div class="hp-card-stat-label">Dignity Kept</div>
                        </div>
                    </div>

                    <span class="hp-card-link">
                        Explore Program
                        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                </div>
            </a>

            <!-- Compassionate Haven -->
            <a href="https://almarahhaven.org/" class="hp-card hp-reveal pop-in delay-3">
                <div class="hp-card-img">
                    <span class="hp-card-badge">05</span>
                    <img loading="lazy" decoding="async" src="<?= e(asset('assets/images/homepage/comp.jpeg')) ?>"
                         alt="Compassionate Haven — Special Needs Home">
                </div>
                <div class="hp-card-body">
                    <h3>Compassionate Haven</h3>
                    <p>
                        A dedicated home for children with special needs offering sensory-friendly spaces, therapy rooms, and specialized learning tools to help every child thrive.
                    </p>

                    <div class="hp-card-stats">
                        <div class="hp-card-stat">
                            <div class="hp-card-stat-value">
                                <span class="hp-stat-num" data-target="1">0</span>
                            </div>
                            <div class="hp-card-stat-label">Dedicated Home</div>
                        </div>
                        <div class="hp-card-stat">
                            <div class="hp-card-stat-value">
                                <span class="hp-stat-num" data-target="24">0</span><span class="hp-card-stat-suffix">/7</span>
                            </div>
                            <div class="hp-card-stat-label">Care Support</div>
                        </div>
                    </div>

                    <span class="hp-card-link">
                        Explore Program
                        <svg viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </span>
                </div>
            </a>

        </div>

    </div>
</section>
