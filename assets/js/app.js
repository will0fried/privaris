/* Privaris — interactions front (radar, terminal, filtre journal, barre de progression) */
(function () {
  'use strict';
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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

  /* ---------- Radar des compétences ---------- */
  var cv = document.getElementById('radar');
  var dataEl = document.getElementById('skills-data');
  if (cv && dataEl) {
    var skills = [];
    try { skills = JSON.parse(dataEl.textContent); } catch (e) { skills = []; }
    if (skills.length >= 3) {
      var ctx = cv.getContext('2d');
      var W = cv.width, H = cv.height, cx = W / 2, cy = H / 2, R = W / 2 - 72, N = skills.length;
      var pt = function (i, val) {
        var a = (i / N) * Math.PI * 2 - Math.PI / 2;
        var r = R * (val / 5);
        return [cx + Math.cos(a) * r, cy + Math.sin(a) * r];
      };
      var poly = function (vals, stroke, fill, lw) {
        ctx.beginPath();
        vals.forEach(function (v, i) { var p = pt(i, v); i ? ctx.lineTo(p[0], p[1]) : ctx.moveTo(p[0], p[1]); });
        ctx.closePath();
        if (fill) { ctx.fillStyle = fill; ctx.fill(); }
        ctx.strokeStyle = stroke; ctx.lineWidth = lw; ctx.stroke();
      };
      ctx.clearRect(0, 0, W, H);
      for (var g = 1; g <= 5; g++) {
        ctx.beginPath();
        for (var i = 0; i <= N; i++) {
          var a = (i / N) * Math.PI * 2 - Math.PI / 2, r = R * g / 5;
          var x = cx + Math.cos(a) * r, y = cy + Math.sin(a) * r;
          i ? ctx.lineTo(x, y) : ctx.moveTo(x, y);
        }
        ctx.closePath();
        ctx.strokeStyle = g === 5 ? 'rgba(255,255,255,.16)' : 'rgba(255,255,255,.07)';
        ctx.lineWidth = 1; ctx.stroke();
      }
      ctx.font = '600 10.5px "JetBrains Mono", monospace';
      for (var j = 0; j < N; j++) {
        var an = (j / N) * Math.PI * 2 - Math.PI / 2;
        ctx.beginPath(); ctx.moveTo(cx, cy); ctx.lineTo(cx + Math.cos(an) * R, cy + Math.sin(an) * R);
        ctx.strokeStyle = 'rgba(255,255,255,.06)'; ctx.stroke();
        var lx = cx + Math.cos(an) * (R + 13), ly = cy + Math.sin(an) * (R + 13);
        ctx.fillStyle = 'rgba(160,166,176,.95)';
        ctx.textAlign = Math.abs(Math.cos(an)) < 0.3 ? 'center' : (Math.cos(an) > 0 ? 'left' : 'right');
        ctx.textBaseline = Math.sin(an) > 0.3 ? 'top' : (Math.sin(an) < -0.3 ? 'bottom' : 'middle');
        ctx.fillText(skills[j].s || '', lx, ly);
      }
      // Carte des domaines : forme régulière (couverture), sans notation.
      var COV = 4.3;
      poly(skills.map(function () { return COV; }), '#F6A733', 'rgba(246,167,51,.22)', 2);
      skills.forEach(function (s, i) {
        var p = pt(i, COV);
        ctx.beginPath(); ctx.arc(p[0], p[1], 3, 0, Math.PI * 2); ctx.fillStyle = '#F6A733'; ctx.fill();
      });
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

  /* ---------- Barre de progression (page article) ---------- */
  var prog = document.getElementById('prog');
  if (prog) {
    var upd = function () {
      var h = document.documentElement, s = h.scrollTop || document.body.scrollTop, max = h.scrollHeight - h.clientHeight;
      prog.style.width = (max > 0 ? (s / max * 100) : 0) + '%';
    };
    document.addEventListener('scroll', upd, { passive: true });
    upd();
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
      } else {
        done();
      }
    });
  });
})();
