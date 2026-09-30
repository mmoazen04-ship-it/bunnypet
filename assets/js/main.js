/* ==========================================================================
   بانی‌پت — اسکریپت اصلی
   هیچ کتابخانه بیرونی لازم نیست
   ========================================================================== */
(function () {
  'use strict';
  var reduce = window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- ۱) غبار شناور ---------- */
  function makeDust(box, count) {
    if (reduce) return;
    var frag = document.createDocumentFragment();
    for (var i = 0; i < count; i++) {
      var m = document.createElement('i');
      m.className = 'mote';
      var s = (Math.random() * 4 + 1.5).toFixed(1);
      m.style.width = s + 'px';
      m.style.height = s + 'px';
      m.style.right = (Math.random() * 100).toFixed(2) + '%';
      m.style.bottom = (Math.random() * 40 - 10).toFixed(2) + '%';
      m.style.animationDuration = (Math.random() * 12 + 11).toFixed(1) + 's';
      m.style.animationDelay = (-Math.random() * 18).toFixed(1) + 's';
      if (Math.random() > 0.65) {
        m.style.background = Math.random() > 0.5 ? '#F9B8D0' : '#AEDCF2';
      }
      frag.appendChild(m);
    }
    box.appendChild(frag);
  }
  var dustBoxes = document.querySelectorAll('.dust');
  for (var d = 0; d < dustBoxes.length; d++) {
    makeDust(dustBoxes[d], window.innerWidth < 700 ? 16 : 30);
  }

  /* ---------- ۲) ظاهر شدن بخش‌ها موقع اسکرول ---------- */
  var items = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && !reduce) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) {
          en.target.classList.add('in');
          io.unobserve(en.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    for (var i = 0; i < items.length; i++) {
      items[i].style.transitionDelay = ((i % 4) * 70) + 'ms';
      io.observe(items[i]);
    }
  } else {
    for (var j = 0; j < items.length; j++) items[j].classList.add('in');
  }

  /* ---------- ۳) انتقال نرم بین صفحه‌ها ---------- */
  var fader = document.createElement('div');
  fader.className = 'fader';
  document.body.appendChild(fader);

  document.addEventListener('click', function (ev) {
    if (reduce || ev.defaultPrevented || ev.button !== 0) return;
    if (ev.metaKey || ev.ctrlKey || ev.shiftKey || ev.altKey) return;
    var a = ev.target.closest ? ev.target.closest('a') : null;
    if (!a) return;
    var href = a.getAttribute('href');
    if (!href || href.charAt(0) === '#' || a.target === '_blank') return;
    if (a.hasAttribute('download') || href.indexOf('mailto:') === 0 ||
        href.indexOf('tel:') === 0) return;
    if (a.hostname && a.hostname !== window.location.hostname) return;
    ev.preventDefault();
    document.body.classList.add('leaving');
    setTimeout(function () { window.location.href = a.href; }, 280);
  });
  window.addEventListener('pageshow', function (e) {
    if (e.persisted) document.body.classList.remove('leaving');
  });

  /* ---------- ۴) تعداد در صفحه محصول ---------- */
  var qtyBox = document.querySelector('.qty');
  if (qtyBox) {
    var out = qtyBox.querySelector('span');
    var inp = document.querySelector('input[name="qty"]');
    qtyBox.addEventListener('click', function (e) {
      var b = e.target.closest('button');
      if (!b) return;
      var v = parseInt(out.textContent.replace(/[^0-9]/g, ''), 10) || 1;
      v += (b.dataset.step === 'up' ? 1 : -1);
      v = Math.max(1, Math.min(20, v));
      out.textContent = String(v).replace(/[0-9]/g, function (c) {
        return '۰۱۲۳۴۵۶۷۸۹'[c];
      });
      if (inp) inp.value = v;
    });
  }

  /* ---------- ۵) انتخاب درگاه پرداخت ---------- */
  var pays = document.querySelectorAll('.pay');
  function syncPay() {
    for (var p = 0; p < pays.length; p++) {
      var r = pays[p].querySelector('input');
      pays[p].classList.toggle('on', r && r.checked);
    }
  }
  for (var p2 = 0; p2 < pays.length; p2++) {
    pays[p2].addEventListener('change', syncPay);
    pays[p2].addEventListener('click', function () { setTimeout(syncPay, 0); });
  }
  syncPay();

  /* ---------- ۶) اسلایدر نقطه‌های هیرو ---------- */
  var dots = document.querySelectorAll('.dots .dot');
  if (dots.length > 1 && !reduce) {
    var cur = 0;
    setInterval(function () {
      dots[cur].classList.remove('on');
      cur = (cur + 1) % dots.length;
      dots[cur].classList.add('on');
    }, 4200);
  }

  /* ---------- ۷) جلوگیری از ارسال دوباره فرم ---------- */
  var forms = document.querySelectorAll('form[data-once]');
  for (var f = 0; f < forms.length; f++) {
    forms[f].addEventListener('submit', function (e) {
      var btn = e.target.querySelector('[type="submit"]');
      if (btn) {
        if (btn.dataset.sent) { e.preventDefault(); return; }
        btn.dataset.sent = '1';
        btn.textContent = 'یه لحظه صبر کن…';
      }
    });
  }
})();
