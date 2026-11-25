/* Footer v4.0 effects. Progressive enhancement: with no JavaScript (or reduced motion) the footer is fully
   visible and static. Handles: reveal-on-scroll, pointer spotlight on the subscribe card, button ripple,
   and the scroll-progress ring on the back-to-top button. */
(function () {
  'use strict';
  var d = document, w = window;
  var footer = d.querySelector('.site-footer.sf4');
  if (!footer) return;
  var reduce = w.matchMedia && w.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // 1. Reveal on scroll (staggered by --i / nth-child in CSS)
  var items = [].slice.call(footer.querySelectorAll('[data-sf-reveal]'));
  if (!reduce && 'IntersectionObserver' in w && items.length) {
    footer.classList.add('sf4-js');
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('is-in'); io.unobserve(e.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    items.forEach(function (el) { io.observe(el); });
  }

  // 2. Spotlight that follows the pointer over the subscribe card (mouse / pen only)
  var cta = footer.querySelector('.sf4-cta');
  if (cta && !reduce && w.matchMedia && w.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    cta.addEventListener('pointermove', function (e) {
      var r = cta.getBoundingClientRect();
      cta.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      cta.style.setProperty('--my', (e.clientY - r.top) + 'px');
    }, { passive: true });
  }

  // 3. Ripple on the gold / donate buttons
  if (!reduce) {
    footer.addEventListener('click', function (e) {
      var b = e.target.closest ? e.target.closest('.sf4 .btn-gold, .sf4 .sf4-donate') : null;
      if (!b) return;
      var r = b.getBoundingClientRect(), s = Math.max(r.width, r.height);
      var ink = d.createElement('span');
      ink.className = 'sf4-ripple';
      ink.style.cssText = 'width:' + s + 'px;height:' + s + 'px;left:' + (e.clientX - r.left - s / 2) + 'px;top:' + (e.clientY - r.top - s / 2) + 'px';
      b.appendChild(ink);
      setTimeout(function () { if (ink.parentNode) ink.parentNode.removeChild(ink); }, 650);
    });
  }

  // 4. Scroll-progress ring around the back-to-top button
  var btt = d.getElementById('backToTop');
  if (btt) {
    var ticking = false;
    var update = function () {
      var h = d.documentElement.scrollHeight - w.innerHeight;
      var p = h > 0 ? Math.min(1, Math.max(0, w.pageYOffset / h)) : 0;
      btt.style.setProperty('--sf-p', (p * 360).toFixed(1) + 'deg');
      ticking = false;
    };
    w.addEventListener('scroll', function () { if (!ticking) { ticking = true; w.requestAnimationFrame(update); } }, { passive: true });
    w.addEventListener('resize', update);
    update();
  }
})();
