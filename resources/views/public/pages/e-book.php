<?php
/**
 * Migrated page body: e-book (source: almarah-foundation/ebook.php).
 * Markup preserved from the original site; styles live in assets/css/pages/e-book.css.
 */
?>
<!-- ═══════ EBOOK SHOWCASE ═══════ -->
<section class="eb-showcase">
    <div class="eb-wrap">
        <div class="eb-main">

            <!-- Book Cover -->
            <div class="eb-cover-col">
                <div class="eb-book-3d">
                    <div class="eb-book-wrap">
<img loading="lazy" decoding="async" class="eb-cover-img"
                             src="<?= e(asset('assets/images/media/bookcover.jpg')) ?>"
                             alt="Almarah Profile 2026">
                        <!--
                        ══════════════════════════════════════
                        TO USE YOUR OWN COVER IMAGE:
                        Remove the .eb-cover-placeholder div
                        and replace with:
                        <img loading="lazy" decoding="async" class="eb-cover-img"
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
                    <a href="<?= e(asset('assets/documents/Almarah-Book.pdf')) ?>"
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
                    <a href="<?= e(asset('assets/documents/Almarah-Book.pdf')) ?>"
                       target="_blank"
                       class="eb-toolbar-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                        Open Full
                    </a>
                    <a href="<?= e(asset('assets/documents/Almarah-Book.pdf')) ?>"
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
                src="<?= e(asset('assets/documents/Almarah-Book.pdf')) ?>"
                title="Almarah Foundation Profile 2026"
                allow="fullscreen">
                <p style="padding:32px;color:#fff;text-align:center;font-family:Manrope,sans-serif;">
                    Your browser does not support embedded PDFs.
                    <a href="<?= e(asset('assets/documents/Almarah-Profile-2026-new.pdf')) ?>"
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
