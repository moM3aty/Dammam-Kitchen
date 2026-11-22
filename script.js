// script.js
(function () {
  'use strict';

  var html = document.documentElement;
  var hdr = document.getElementById('hdr');
  var burger = document.getElementById('burger');
  var nav = document.getElementById('nav');
  var veil = document.getElementById('veil');
  var links = Array.prototype.slice.call(document.querySelectorAll('.lnk'));
  var up = document.getElementById('fxUp');
  var ticking = false;

  if (!hdr || !burger || !nav || !veil) {
    return;
  }

  function openMenu() {
    nav.classList.add('open');
    veil.classList.add('show');
    burger.classList.add('open');
    burger.setAttribute('aria-expanded', 'true');
    burger.setAttribute('aria-label', 'إغلاق القائمة');
    html.classList.add('lock');
  }

  function closeMenu() {
    nav.classList.remove('open');
    veil.classList.remove('show');
    burger.classList.remove('open');
    burger.setAttribute('aria-expanded', 'false');
    burger.setAttribute('aria-label', 'فتح القائمة');
    html.classList.remove('lock');
  }

  burger.addEventListener('click', function () {
    if (nav.classList.contains('open')) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  veil.addEventListener('click', closeMenu);

  links.forEach(function (link) {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && nav.classList.contains('open')) {
      closeMenu();
    }
  });

  window.addEventListener('resize', function () {
    if (window.innerWidth > 1180) {
      closeMenu();
    }
  });

  function setActive(target) {
    links.forEach(function (link) {
      link.classList.toggle('act', link === target);
    });
  }

  function update() {
    ticking = false;

    hdr.classList.toggle('sc', window.scrollY > 30);

    if (up) {
      up.classList.toggle('show', window.scrollY > 600);
    }

    var y = window.scrollY + window.innerHeight * 0.35;
    var current = links[0];
    var last = links[0];

    links.forEach(function (link) {
      var id = link.getAttribute('href');
      var section = id && id.charAt(0) === '#' ? document.querySelector(id) : null;
      if (section) {
        last = link;
        if (section.offsetTop <= y) {
          current = link;
        }
      }
    });

    var atBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4;
    if (atBottom && window.scrollY > 0) {
      current = last;
    }

    setActive(current);
  }

  function onScroll() {
    if (!ticking) {
      ticking = true;
      window.requestAnimationFrame(update);
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  window.addEventListener('load', update);
  update();
})();

(function () {
  'use strict';

  var items = Array.prototype.slice.call(document.querySelectorAll('.rv'));

  function reveal(el) {
    el.classList.add('in');
  }

  if (!('IntersectionObserver' in window)) {
    items.forEach(reveal);
    return;
  }

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        reveal(entry.target);
        io.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -5% 0px' });

  items.forEach(function (el) {
    io.observe(el);
  });
})();

(function () {
  'use strict';

  var html = document.documentElement;
  var grid = document.getElementById('glGrid');
  var tabs = Array.prototype.slice.call(document.querySelectorAll('.gt-b'));
  var lb = document.getElementById('lb');
  var lbImg = document.getElementById('lbImg');
  var lbVid = document.getElementById('lbVid');
  var cnt = document.getElementById('lbCnt');
  var closeBtn = document.getElementById('lbClose');
  var prevBtn = document.getElementById('lbPrev');
  var nextBtn = document.getElementById('lbNext');
  var cur = 0;
  var sx = 0;

  if (!grid || !lb || !lbImg || !lbVid) {
    return;
  }

  var items = Array.prototype.slice.call(grid.querySelectorAll('.gi'));

  function media(i) {
    return items[i].querySelector('img,video');
  }

  function visibleList() {
    var list = [];
    items.forEach(function (it, i) {
      if (!it.classList.contains('off') && media(i)) {
        list.push(i);
      }
    });
    return list;
  }

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      var key = tab.getAttribute('data-f');
      tabs.forEach(function (t) {
        var on = t === tab;
        t.classList.toggle('on', on);
        t.setAttribute('aria-pressed', on ? 'true' : 'false');
      });
      items.forEach(function (it) {
        var match = key === 'all' || it.getAttribute('data-t') === key;
        it.classList.toggle('off', !match);
        it.classList.remove('pop');
        if (match) {
          it.classList.add('in');
          void it.offsetWidth;
          it.classList.add('pop');
        }
      });
    });
  });

  var vids = Array.prototype.slice.call(grid.querySelectorAll('video'));

  if ('IntersectionObserver' in window) {
    var vo = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        var v = entry.target;
        if (entry.isIntersecting) {
          var p = v.play();
          if (p && p.catch) {
            p.catch(function () {});
          }
        } else {
          v.pause();
        }
      });
    }, { threshold: 0.4 });

    vids.forEach(function (v) {
      vo.observe(v);
    });
  }

  function stopVid() {
    lbVid.pause();
    lbVid.removeAttribute('src');
    lbVid.load();
  }

  function show(idx) {
    var m = media(idx);
    if (!m) {
      return;
    }
    cur = idx;
    lbImg.classList.remove('on');
    lbVid.classList.remove('on');
    stopVid();

    if (m.tagName === 'VIDEO') {
      lbVid.src = m.getAttribute('src');
      lbVid.classList.add('on');
      var p = lbVid.play();
      if (p && p.catch) {
        p.catch(function () {});
      }
    } else {
      lbImg.src = m.getAttribute('src');
      lbImg.alt = m.getAttribute('alt') || '';
      lbImg.classList.add('on');
    }

    var list = visibleList();
    if (cnt) {
      cnt.textContent = (list.indexOf(cur) + 1) + ' / ' + list.length;
    }
  }

  function step(dir) {
    var list = visibleList();
    if (!list.length) {
      return;
    }
    var pos = list.indexOf(cur);
    var next = list[(pos + dir + list.length) % list.length];
    show(next);
  }

  function open(i) {
    if (!media(i)) {
      return;
    }
    show(i);
    lb.classList.add('show');
    lb.setAttribute('aria-hidden', 'false');
    html.classList.add('lock');
  }

  function close() {
    lb.classList.remove('show');
    lb.setAttribute('aria-hidden', 'true');
    html.classList.remove('lock');
    stopVid();
  }

  items.forEach(function (item, i) {
    item.addEventListener('click', function () {
      open(i);
    });
  });

  if (closeBtn) {
    closeBtn.addEventListener('click', close);
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', function () {
      step(-1);
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', function () {
      step(1);
    });
  }

  lb.addEventListener('click', function (e) {
    if (e.target === lb) {
      close();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (!lb.classList.contains('show')) {
      return;
    }
    if (e.key === 'Escape') {
      close();
    } else if (e.key === 'ArrowLeft') {
      step(1);
    } else if (e.key === 'ArrowRight') {
      step(-1);
    }
  });

  lb.addEventListener('touchstart', function (e) {
    sx = e.changedTouches[0].clientX;
  }, { passive: true });

  lb.addEventListener('touchend', function (e) {
    var dx = e.changedTouches[0].clientX - sx;
    if (Math.abs(dx) > 60) {
      step(dx < 0 ? 1 : -1);
    }
  }, { passive: true });
})();

(function () {
  'use strict';

  var list = document.getElementById('fqList');

  if (!list) {
    return;
  }

  var items = Array.prototype.slice.call(list.querySelectorAll('.fq-q'));

  items.forEach(function (item) {
    item.addEventListener('toggle', function () {
      if (!item.open) {
        return;
      }
      items.forEach(function (other) {
        if (other !== item && other.open) {
          other.open = false;
        }
      });
    });
  });
})();

(function () {
  'use strict';

  var canHover = window.matchMedia && window.matchMedia('(hover:hover) and (pointer:fine)').matches;
  var hero = document.getElementById('home');
  var art = document.getElementById('artW');

  if (!canHover || !hero || !art) {
    return;
  }

  hero.addEventListener('pointermove', function (e) {
    var x = (e.clientX / window.innerWidth - 0.5) * 16;
    var y = (e.clientY / window.innerHeight - 0.5) * 10;
    art.style.transform = 'translate(' + (-x).toFixed(1) + 'px,' + (-y).toFixed(1) + 'px)';
  });

  hero.addEventListener('pointerleave', function () {
    art.style.transform = 'translate(0,0)';
  });
})();

(function () {
  'use strict';

  var yr = document.getElementById('ftYear');
  var form = document.getElementById('ctForm');
  var note = document.getElementById('ctNote');

  if (yr) {
    yr.textContent = new Date().getFullYear();
  }

  if (!form || !note) {
    return;
  }

  var nameEl = document.getElementById('ctName');
  var phoneEl = document.getElementById('ctPhone');
  var cityEl = document.getElementById('ctCity');
  var areaEl = document.getElementById('ctArea');
  var serviceEl = document.getElementById('ctService');
  var msgEl = document.getElementById('ctMsg');

  function mark(el, bad) {
    var box = el.closest('.ct-f');
    if (box) {
      box.classList.toggle('bad', bad);
    }
  }

  function show(text, type) {
    note.textContent = text;
    note.className = 'ct-note ' + type;
  }

  [nameEl, phoneEl].forEach(function (el) {
    el.addEventListener('input', function () {
      mark(el, false);
      note.textContent = '';
      note.className = 'ct-note';
    });
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    var name = nameEl.value.trim();
    var phone = phoneEl.value.trim();
    var digits = phone.replace(/[^0-9٠-٩]/g, '');
    var okName = name.length >= 2;
    var okPhone = digits.length >= 8;

    mark(nameEl, !okName);
    mark(phoneEl, !okPhone);

    if (!okName) {
      show('من فضلك اكتب اسمك.', 'err');
      nameEl.focus();
      return;
    }

    if (!okPhone) {
      show('من فضلك اكتب رقم جوال صحيح.', 'err');
      phoneEl.focus();
      return;
    }

    var lines = [
      'السلام عليكم شبح المطابخ، أرغب في طلب خدمة.',
      '',
      'الاسم: ' + name,
      'رقم الجوال: ' + phone,
      'المدينة: ' + cityEl.value
    ];

    var area = areaEl.value.trim();
    if (area) {
      lines.push('الحي: ' + area);
    }

    lines.push('الخدمة المطلوبة: ' + serviceEl.value);

    var msg = msgEl.value.trim();
    if (msg) {
      lines.push('التفاصيل: ' + msg);
    }

    var url = 'https://wa.me/966507023985?text=' + encodeURIComponent(lines.join('\n'));
    var win = window.open(url, '_blank', 'noopener');

    if (!win) {
      window.location.href = url;
    }

    show('تم تجهيز طلبك، أكمل الإرسال من واتساب. شكراً لتواصلك معنا.', 'ok');
    form.reset();
  });
})();