/* Privaris — interactions front (filtre journal, menu mobile, reveal, barre de progression, retour-haut, copie) */
(function () {
  'use strict';
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hasIO = 'IntersectionObserver' in window;

  /* ---------- Filtre du journal ---------- */
  var cats = document.querySelectorAll('.cat');
  var ledger = document.getElementById('ledger');
  var rows = document.querySelectorAll('#ledger .row[data-k]');
  var emptyMsg = null;
  if (ledger && rows.length) {
    emptyMsg = document.createElement('div');
    emptyMsg.className = 'ledger-empty';
    emptyMsg.hidden = true;
    emptyMsg.textContent = "Aucune entrée dans cette catégorie pour l'instant.";
    ledger.appendChild(emptyMsg);
  }
  cats.forEach(function (c) {
    c.addEventListener('click', function () {
      cats.forEach(function (x) { x.classList.remove('on'); });
      c.classList.add('on');
      var k = c.dataset.k;
      var visible = 0;
      rows.forEach(function (r) {
        var hide = (k !== 'all' && r.dataset.k !== k);
        r.hidden = hide;
        if (!hide) { visible++; }
      });
      if (emptyMsg) { emptyMsg.hidden = (visible !== 0); }
    });
  });

  /* ---------- Menu mobile (burger) ---------- */
  var nav = document.querySelector('nav');
  var burger = document.getElementById('navBurger');
  var navLinks = document.getElementById('navLinks');
  if (nav && burger && navLinks) {
    var setOpen = function (open) {
      nav.classList.toggle('open', open);
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
      burger.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
    };
    burger.addEventListener('click', function () { setOpen(!nav.classList.contains('open')); });
    navLinks.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { setOpen(false); });
    });
    window.addEventListener('resize', function () { if (window.innerWidth > 980) { setOpen(false); } });
  }

  /* ---------- Révélation au scroll ---------- */
  var revs = document.querySelectorAll('.reveal');
  if (revs.length) {
    if (reduce || !hasIO) {
      revs.forEach(function (el) { el.classList.add('in'); });
    } else {
      var ro = new IntersectionObserver(function (es) {
        es.forEach(function (e) {
          if (e.isIntersecting) { e.target.classList.add('in'); ro.unobserve(e.target); }
        });
      }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
      revs.forEach(function (el) { ro.observe(el); });
    }
  }

  /* ---------- Barre de progression (page article) ---------- */
  var prog = document.getElementById('prog');
  if (prog) {
    var upd = function () {
      var h = document.documentElement, sc = h.scrollTop || document.body.scrollTop, max = h.scrollHeight - h.clientHeight;
      prog.style.width = (max > 0 ? (sc / max * 100) : 0) + '%';
    };
    document.addEventListener('scroll', upd, { passive: true });
    upd();
  }

  /* ---------- Bouton « remonter en haut » ---------- */
  var toTop = document.getElementById('toTop');
  if (toTop) {
    var toggle = function () {
      if (window.pageYOffset > 640) { toTop.classList.add('show'); }
      else { toTop.classList.remove('show'); }
    };
    document.addEventListener('scroll', toggle, { passive: true });
    toggle();
    toTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
    });
  }

  /* ---------- Copier le lien (boutons de partage) ---------- */
  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var url = btn.getAttribute('data-copy');
      var restore = btn.textContent;
      var done = function () {
        btn.textContent = 'Copié !';
        setTimeout(function () { btn.textContent = restore; }, 1600);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(done, done);
      } else { done(); }
    });
  });
})();
