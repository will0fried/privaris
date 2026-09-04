/* Privaris — interactions front (radar sonar, terminal, filtre, reveal, retour-haut) */
(function () {
  'use strict';
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var hasIO = 'IntersectionObserver' in window;

  /* ---------- Filtre du journal ---------- */
  var cats = document.querySelectorAll('.cat');
  var rows = document.querySelectorAll('#ledger .row[data-k]');
  cats.forEach(function (c) {
    c.addEventListener('click', function () {
      cats.forEach(function (x) { x.classList.remove('on'); });
      c.classList.add('on');
      var k = c.dataset.k;
      rows.forEach(function (r) { r.hidden = (k !== 'all' && r.dataset.k !== k); });
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

  /* ---------- Radar des domaines (sonar animé) ---------- */
  var cv = document.getElementById('radar');
  var dataEl = document.getElementById('skills-data');
  if (cv && dataEl) {
    var skills = [];
    try { skills = JSON.parse(dataEl.textContent); } catch (e) { skills = []; }
    if (skills.length >= 3) {
      var ctx = cv.getContext('2d');
      var W = cv.width, H = cv.height, cx = W / 2, cy = H / 2, R = W / 2 - 72, N = skills.length;
      var COV = 4.3;
      var ang = function (i) { return (i / N) * Math.PI * 2 - Math.PI / 2; };
      var pt = function (i, val) { var a = ang(i), r = R * (val / 5); return [cx + Math.cos(a) * r, cy + Math.sin(a) * r]; };

      /* Calque de base pré-rendu une seule fois (grille + axes + libellés + couverture) */
      var base = document.createElement('canvas'); base.width = W; base.height = H;
      var bx = base.getContext('2d');
      for (var g = 1; g <= 5; g++) {
        bx.beginPath();
        for (var i = 0; i <= N; i++) {
          var a = ang(i), r = R * g / 5, x = cx + Math.cos(a) * r, y = cy + Math.sin(a) * r;
          i ? bx.lineTo(x, y) : bx.moveTo(x, y);
        }
        bx.closePath();
        bx.strokeStyle = g === 5 ? 'rgba(255,255,255,.16)' : 'rgba(255,255,255,.07)';
        bx.lineWidth = 1; bx.stroke();
      }
      bx.font = '600 10.5px "JetBrains Mono", monospace';
      for (var j = 0; j < N; j++) {
        var an = ang(j);
        bx.beginPath(); bx.moveTo(cx, cy); bx.lineTo(cx + Math.cos(an) * R, cy + Math.sin(an) * R);
        bx.strokeStyle = 'rgba(255,255,255,.06)'; bx.stroke();
        var lx = cx + Math.cos(an) * (R + 13), ly = cy + Math.sin(an) * (R + 13);
        bx.fillStyle = 'rgba(160,166,176,.95)';
        bx.textAlign = Math.abs(Math.cos(an)) < 0.3 ? 'center' : (Math.cos(an) > 0 ? 'left' : 'right');
        bx.textBaseline = Math.sin(an) > 0.3 ? 'top' : (Math.sin(an) < -0.3 ? 'bottom' : 'middle');
        bx.fillText(skills[j].s || '', lx, ly);
      }
      bx.beginPath();
      for (var k = 0; k < N; k++) { var pc = pt(k, COV); k ? bx.lineTo(pc[0], pc[1]) : bx.moveTo(pc[0], pc[1]); }
      bx.closePath();
      bx.fillStyle = 'rgba(246,167,51,.16)'; bx.fill();
      bx.strokeStyle = '#F6A733'; bx.lineWidth = 2; bx.stroke();

      /* Blips = les sommets de la couverture, avec leur angle normalisé [0,2π[ */
      var dots = skills.map(function (s, i) {
        var p = pt(i, COV);
        return { x: p[0], y: p[1], a: ((ang(i)) % (Math.PI * 2) + Math.PI * 2) % (Math.PI * 2) };
      });

      var drawDots = function (sweep) {
        dots.forEach(function (d) {
          var glow = 0;
          if (sweep >= 0) {
            var diff = ((sweep - d.a) % (Math.PI * 2) + Math.PI * 2) % (Math.PI * 2);
            glow = diff < 0.7 ? (1 - diff / 0.7) : 0; // s'allume quand le balayage vient de passer
          }
          var rad = 3 + glow * 3.5;
          if (glow > 0) {
            ctx.beginPath(); ctx.arc(d.x, d.y, rad + 6, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(246,167,51,' + (0.28 * glow) + ')'; ctx.fill();
          }
          ctx.beginPath(); ctx.arc(d.x, d.y, rad, 0, Math.PI * 2);
          ctx.fillStyle = '#F6A733'; ctx.fill();
        });
      };

      var paintStatic = function () { ctx.clearRect(0, 0, W, H); ctx.drawImage(base, 0, 0); drawDots(-1); };

      if (reduce) {
        paintStatic();
      } else {
        var sweep = -Math.PI / 2, running = false, raf = null, last = 0;
        var frame = function (t) {
          if (!last) last = t;
          var dt = (t - last) / 1000; last = t;
          sweep += dt * (Math.PI * 2 / 4.6); // un tour ≈ 4,6 s
          if (sweep > Math.PI * 2) sweep -= Math.PI * 2;
          ctx.clearRect(0, 0, W, H);
          ctx.drawImage(base, 0, 0);
          // traînée du balayage (dégradé en éventail)
          for (var s = 0; s < 14; s++) {
            var a0 = sweep - s * 0.035;
            ctx.beginPath(); ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, R, a0 - 0.02, a0 + 0.02); ctx.closePath();
            ctx.fillStyle = 'rgba(246,167,51,' + (0.09 * (1 - s / 14)) + ')'; ctx.fill();
          }
          // ligne de tête
          ctx.beginPath(); ctx.moveTo(cx, cy);
          ctx.lineTo(cx + Math.cos(sweep) * R, cy + Math.sin(sweep) * R);
          ctx.strokeStyle = 'rgba(246,167,51,.55)'; ctx.lineWidth = 1.5; ctx.stroke();
          drawDots(sweep);
          raf = requestAnimationFrame(frame);
        };
        var start = function () { if (!running) { running = true; last = 0; raf = requestAnimationFrame(frame); } };
        var stop = function () { if (running) { running = false; cancelAnimationFrame(raf); } };
        paintStatic(); // évite le canvas vide avant l'entrée à l'écran
        if (hasIO) {
          new IntersectionObserver(function (es) {
            es.forEach(function (e) { e.isIntersecting ? start() : stop(); });
          }, { threshold: 0.15 }).observe(cv);
        } else { start(); }
      }
    }
  }

  /* ---------- Terminal (animation de démarrage) ---------- */
  var term = document.getElementById('term');
  if (term) {
    var lines = [
      { c: 'm', t: '$ ' }, { c: 'w', t: 'whoami', br: 1 },
      { c: 'a', t: '> Will — sécurité offensive · Master Cyber & Cyberdéfense 2026', br: 1 },
      { c: 'a', t: "> je teste, je documente, j'explique", br: 2 },
      { c: 'm', t: '$ ' }, { c: 'w', t: 'cat approche.txt', br: 1 },
      { c: 'c', t: '> repérer les failles avant qu\'on en profite,', br: 1 },
      { c: 'c', t: '  et les rendre compréhensibles par tous', br: 2 },
      { c: 'm', t: '$ ' }, { c: 'w', t: './etat', br: 1 },
      { c: 'w', t: '> domaine ...... ', cont: 1 }, { c: 'a', t: 'réseaux, wi-fi, web', br: 1 },
      { c: 'w', t: '> méthode ...... ', cont: 1 }, { c: 'a', t: "du premier scan jusqu'au rapport", br: 1 },
      { c: 'w', t: '> règle ........ ', cont: 1 }, { c: 'r', t: 'aucun test sans accord écrit', br: 2 },
      { c: 'm', t: '$ ', cursor: 1 }
    ];
    var esc = function (s) { return s.replace(/&/g, '&amp;').replace(/</g, '&lt;'); };
    if (reduce) {
      term.innerHTML = lines.map(function (l) {
        return '<span class="' + l.c + '">' + esc(l.t) + '</span>' + (l.cursor ? '<span class="cursor"></span>' : '') + (l.br ? '\n'.repeat(l.br) : '');
      }).join('');
    } else {
      var li = 0, ci = 0, cur = null;
      var step = function () {
        if (li >= lines.length) { return; }
        var l = lines[li];
        if (ci === 0) { cur = document.createElement('span'); cur.className = l.c; term.appendChild(cur); }
        if (ci < l.t.length) {
          cur.textContent += l.t[ci++];
          term.scrollTop = term.scrollHeight;
          setTimeout(step, l.t[ci - 1] === ' ' ? 12 : (18 + Math.random() * 26));
        } else {
          if (l.br) { term.appendChild(document.createTextNode('\n'.repeat(l.br))); }
          if (l.cursor) { var c = document.createElement('span'); c.className = 'cursor'; term.appendChild(c); }
          li++; ci = 0;
          setTimeout(step, l.cont ? 60 : (l.br ? 260 : 120));
        }
      };
      step();
    }
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
