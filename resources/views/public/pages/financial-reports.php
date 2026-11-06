<?php
/**
 * Migrated page body: financial-reports (source: almarah-foundation/financial-reports.php).
 * Markup preserved from the original site; styles live in assets/css/pages/financial-reports.css.
 */
?>
<!-- HERO -->
<section class="fr-hero">
    <div class="fr-hero-circle-tl"></div>
    <div class="fr-hero-circle-br"></div>
    <div class="fr-hero-inner">
        <span class="hx-eyebrow">Transparency &amp; Trust</span>
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
            <a href="<?= e(asset('assets/documents/Audit-Report-Almarah-Foundation-2025-2026.pdf')) ?>"
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
            <a href="<?= e(asset('assets/documents/Alamarah-Foundation-Financial-Report-24-25.pdf')) ?>"
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
            <a href="<?= e(asset('assets/documents/Audit-Report-and-Financial-June-2024.pdf')) ?>"
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
            <a href="<?= e(asset('assets/documents/Audit-Report-and-Financial-June-2023.pdf')) ?>"
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
            <a href="<?= e(asset('assets/documents/Audit-Report-and-Financial-June-2022.pdf')) ?>"
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
            <a href="<?= e(asset('assets/documents/Audit-Report-and-Financial-June-2021.pdf')) ?>"
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
                    <img loading="lazy" decoding="async" src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800&q=80"
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
            <a href="<?= e(base_url('donate')) ?>" class="fr-btn-primary">
                <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                Donate Now
            </a>
            <a href="<?= e(base_url('contact')) ?>" class="fr-btn-ghost">
                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Ask a Question
            </a>
        </div>
    </div>
</section>
