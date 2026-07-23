/**
 * spa-nav.js
 * ---------------------------------------------------------------
 * Membuat navigasi sidebar terasa seperti SPA: saat menu di sidebar
 * diklik, hanya konten di dalam <main id="pageContent"> yang diganti.
 * Sidebar dan header (<header>) TIDAK ikut di-refresh / reload.
 *
 * Cara kerja:
 * 1. Klik link di dalam #appSidebar / #mobileSidebar di-intercept.
 * 2. Halaman tujuan diambil lewat fetch() (masih HTML biasa dari
 *    controller yang sama, tidak perlu ubah route/controller).
 * 3. Dari HTML hasil fetch, kita ambil elemen #pageContent lalu
 *    tempelkan innerHTML-nya ke #pageContent yang sedang tampil.
 * 4. Judul di header (#pageHeaderTitle) & <title> ikut diperbarui,
 *    tanpa membongkar ulang seluruh header.
 * 5. URL di address bar diperbarui via history.pushState, dan
 *    tombol back/forward browser tetap berfungsi (popstate).
 * ---------------------------------------------------------------
 */
(function () {
  var contentSelector = '#pageContent';
  var navContainerSelector = '#appSidebar, #mobileSidebar, [data-spa-nav]';
  var currentController = null;

  function isInternalLink(a) {
    if (!a.hasAttribute('href')) return false;
    var href = a.getAttribute('href');
    if (!href || href === '#' || href.indexOf('#') === 0) return false;
    if (a.target && a.target !== '' && a.target !== '_self') return false;
    if (a.hasAttribute('download')) return false;
    try {
      var url = new URL(href, window.location.href);
      return url.origin === window.location.origin;
    } catch (e) {
      return false;
    }
  }

  function bindNavLinks(root) {
    var scope = root || document;
    scope.querySelectorAll(navContainerSelector + ' a[href]').forEach(function (a) {
      if (a.dataset.spaBound === '1') return;
      if (!isInternalLink(a)) return;
      a.dataset.spaBound = '1';
      a.addEventListener('click', handleLinkClick);
    });
  }

  function handleLinkClick(e) {
    if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
    var href = this.getAttribute('href');
    e.preventDefault();
    if (href === window.location.pathname + window.location.search) return;
    navigate(href, true);
  }

  function runScripts(container) {
    container.querySelectorAll('script').forEach(function (oldScript) {
      var newScript = document.createElement('script');
      Array.prototype.forEach.call(oldScript.attributes, function (attr) {
        newScript.setAttribute(attr.name, attr.value);
      });
      newScript.text = oldScript.textContent;
      oldScript.parentNode.replaceChild(newScript, oldScript);
    });
  }

  function setActiveMenu(targetUrl) {
    var targetPath = new URL(targetUrl, window.location.href).pathname;
    document.querySelectorAll(navContainerSelector + ' a[href]').forEach(function (a) {
      var linkPath;
      try {
        linkPath = new URL(a.getAttribute('href'), window.location.href).pathname;
      } catch (e) {
        return;
      }
      var active = linkPath === targetPath;
      if (active) {
        a.style.backgroundColor = '#2563EB';
        a.style.color = '#fff';
        a.style.fontWeight = '600';
        a.classList.remove('text-slate-600', 'hover:bg-blue-100', 'hover:text-blue-700', 'hover:shadow-sm', 'hover:translate-x-0.5');
      } else {
        a.style.backgroundColor = '';
        a.style.color = '';
        a.style.fontWeight = '';
        if (!a.classList.contains('text-slate-600')) {
          a.classList.add('text-slate-600', 'hover:bg-blue-100', 'hover:text-blue-700', 'hover:shadow-sm', 'hover:translate-x-0.5');
        }
      }
    });
  }

  function startLoadingState(el) {
    el.style.transition = 'opacity .12s ease';
    el.style.opacity = '0.45';
    el.setAttribute('aria-busy', 'true');
  }

  function endLoadingState(el) {
    el.style.opacity = '';
    el.removeAttribute('aria-busy');
  }

  function navigate(url, pushState) {
    var contentEl = document.querySelector(contentSelector);
    if (!contentEl) {
      // Halaman ini belum punya #pageContent (belum di-setup) -> fallback normal.
      window.location.href = url;
      return;
    }

    if (currentController) currentController.abort();
    currentController = new AbortController();
    startLoadingState(contentEl);

    fetch(url, {
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      signal: currentController.signal,
    })
      .then(function (res) {
        if (!res.ok) throw new Error('Gagal memuat halaman (' + res.status + ')');
        return res.text();
      })
      .then(function (html) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var newContent = doc.querySelector(contentSelector);

        if (!newContent) {
          // Struktur halaman tujuan beda / tidak punya #pageContent -> fallback.
          window.location.href = url;
          return;
        }

        contentEl.innerHTML = newContent.innerHTML;
        runScripts(contentEl);
        endLoadingState(contentEl);

        var newTitleEl = doc.getElementById('pageHeaderTitle');
        var curTitleEl = document.getElementById('pageHeaderTitle');
        if (newTitleEl && curTitleEl) {
          curTitleEl.textContent = newTitleEl.textContent;
        }
        if (doc.title) document.title = doc.title;

        if (pushState) {
          window.history.pushState({ spaNav: true }, doc.title || '', url);
        }

        setActiveMenu(url);
        contentEl.scrollTop = 0;
        window.scrollTo(0, 0);

        document.dispatchEvent(new CustomEvent('spa:navigated', { detail: { url: url } }));
      })
      .catch(function (err) {
        if (err.name === 'AbortError') return;
        // Kalau gagal (network error dll), fallback ke navigasi normal supaya tidak "nyangkut".
        window.location.href = url;
      });
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindNavLinks(document);
  });

  // Setiap kali konten baru masuk, ada kemungkinan link baru muncul (mis. pagination) -> re-bind.
  document.addEventListener('spa:navigated', function () {
    bindNavLinks(document);
  });

  window.addEventListener('popstate', function () {
    navigate(window.location.href, false);
  });
})();
