/**
 * spa-nav.js
 * ---------------------------------------------------------------
 * Membuat navigasi & aksi (pindah menu, filter, tambah, edit, hapus,
 * tandai notif, dll) terasa seperti SPA: yang di-refresh cuma
 * <main id="pageContent">, sidebar & <header> tidak ikut dibongkar.
 *
 * Menangani 2 hal:
 * 1. Klik <a href> apa pun di halaman (menu sidebar, pagination,
 *    tombol lihat detail, dsb) -> di-fetch, lalu #pageContent
 *    ditempel dari hasil fetch.
 * 2. Submit <form> apa pun (filter GET, tambah/edit POST, hapus
 *    method DELETE via @method spoofing) -> juga lewat fetch,
 *    termasuk kalau controller-nya redirect() setelah proses,
 *    fetch otomatis ngikutin redirect itu dan #pageContent
 *    diperbarui dari halaman hasil redirect (jadi flash message
 *    "berhasil ditambahkan/dihapus" dari session tetap muncul).
 *
 * Tidak perlu ubah route / controller sama sekali.
 * ---------------------------------------------------------------
 */
(function () {
  var CONTENT_SEL = '#pageContent';
  var currentController = null;

  // --- helper ---------------------------------------------------

  function resolveUrl(href) {
    try {
      return new URL(href, window.location.href);
    } catch (e) {
      return null;
    }
  }

  // Sengaja TIDAK membandingkan origin dengan window.location.origin.
  // Kadang APP_URL di .env beda dari domain yang benar-benar dipakai
  // di browser (localhost vs 127.0.0.1 vs *.test) -- kalau dicek ketat
  // pakai origin, link jadi dianggap "eksternal" padahal sebenarnya
  // halaman yang sama, dan berakhir full reload. Cukup pastikan
  // protokolnya http/https, sisanya biar fetch() yang urus (kalau
  // memang beda origin & gagal, otomatis fallback ke reload biasa).
  function isHttpUrl(url) {
    return !!url && (url.protocol === 'http:' || url.protocol === 'https:');
  }

  function isEligibleLink(a) {
    if (!a || !a.hasAttribute('href')) return false;
    var href = a.getAttribute('href');
    if (!href || href.charAt(0) === '#') return false;
    if (/^(javascript:|mailto:|tel:)/i.test(href)) return false;
    if (a.hasAttribute('download')) return false;
    if (a.dataset.noSpa !== undefined) return false;
    if (a.target && a.target !== '' && a.target !== '_self') return false;
    return isHttpUrl(resolveUrl(href));
  }

  function isEligibleForm(form) {
    if (!form) return false;
    if (form.dataset.noSpa !== undefined) return false;
    if (form.target && form.target !== '' && form.target !== '_self') return false;
    return isHttpUrl(resolveUrl(form.action));
  }

  function getContentEl() {
    return document.querySelector(CONTENT_SEL);
  }

  function startLoading(el) {
    el.style.transition = 'opacity .12s ease';
    el.style.opacity = '0.45';
    el.setAttribute('aria-busy', 'true');
  }

  function endLoading(el) {
    el.style.opacity = '';
    el.removeAttribute('aria-busy');
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

  function setActiveMenu(finalUrl) {
    var targetPath;
    try {
      targetPath = new URL(finalUrl, window.location.href).pathname;
    } catch (e) {
      return;
    }
    document.querySelectorAll('#appSidebar a[href], #mobileSidebar a[href], [data-spa-nav] a[href]').forEach(function (a) {
      var linkUrl = resolveUrl(a.getAttribute('href'));
      if (!linkUrl) return;
      var active = linkUrl.pathname === targetPath;
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

  // --- inti: ambil halaman lewat fetch, tempel #pageContent ------

  function swapFromResponse(res, html, pushState) {
    var contentEl = getContentEl();
    var doc = new DOMParser().parseFromString(html, 'text/html');
    var newContent = doc.querySelector(CONTENT_SEL);

    if (!newContent) {
      // Halaman hasil (mis. login, atau halaman lama yang belum
      // dikasih #pageContent) -> lakukan navigasi normal saja.
      window.location.href = res.url || window.location.href;
      return;
    }

    contentEl.innerHTML = newContent.innerHTML;
    runScripts(contentEl);
    endLoading(contentEl);

    var newTitleEl = doc.getElementById('pageHeaderTitle');
    var curTitleEl = document.getElementById('pageHeaderTitle');
    if (newTitleEl && curTitleEl) curTitleEl.textContent = newTitleEl.textContent;
    if (doc.title) document.title = doc.title;

    var finalUrl = res.url || window.location.href;
    if (pushState) {
      window.history.pushState({ spaNav: true }, doc.title || '', finalUrl);
    }

    setActiveMenu(finalUrl);
    contentEl.scrollTop = 0;
    window.scrollTo(0, 0);

    document.dispatchEvent(new CustomEvent('spa:navigated', { detail: { url: finalUrl } }));
  }

  function request(url, fetchOptions, pushState) {
    var contentEl = getContentEl();
    if (!contentEl) {
      window.location.href = url;
      return;
    }

    if (currentController) currentController.abort();
    currentController = new AbortController();
    startLoading(contentEl);

    var opts = Object.assign({}, fetchOptions || {}, {
      credentials: 'same-origin',
      headers: Object.assign({ 'X-Requested-With': 'XMLHttpRequest' }, (fetchOptions && fetchOptions.headers) || {}),
      signal: currentController.signal,
    });

    fetch(url, opts)
      .then(function (res) {
        var contentType = res.headers.get('content-type') || '';
        if (!res.ok) throw new Error('HTTP ' + res.status);
        if (contentType.indexOf('text/html') === -1) {
          // Bukan halaman HTML (misalnya file export/download) -> biarkan browser yang urus.
          window.location.href = url;
          return null;
        }
        return res.text().then(function (html) {
          return { res: res, html: html };
        });
      })
      .then(function (result) {
        if (!result) return;
        swapFromResponse(result.res, result.html, pushState);
      })
      .catch(function (err) {
        if (err && err.name === 'AbortError') return;
        // Gagal (network error / CORS / dll) -> fallback navigasi biasa supaya tidak "nyangkut".
        window.location.href = url;
      });
  }

  // --- event handlers --------------------------------------------

  function handleLinkClick(e) {
    var a = e.target.closest ? e.target.closest('a[href]') : null;
    if (!a || !isEligibleLink(a)) return;
    if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;

    var target = resolveUrl(a.getAttribute('href'));
    if (!target) return;

    e.preventDefault();
    if (target.pathname === window.location.pathname && target.search === window.location.search) return;

    request(target.href, { method: 'GET' }, true);
  }

  function handleFormSubmit(e) {
    var form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (e.defaultPrevented) return; // sudah dibatalkan (mis. lewat confirm() yang return false)
    if (!isEligibleForm(form)) return;

    var method = (form.getAttribute('method') || 'GET').toUpperCase();

    if (method === 'GET') {
      var params = new URLSearchParams(new FormData(form));
      var url = resolveUrl(form.action);
      if (!url) return;
      url.search = params.toString();
      e.preventDefault();
      request(url.href, { method: 'GET' }, true);
      return;
    }

    // POST (termasuk yang di-spoof jadi PUT/PATCH/DELETE lewat @method())
    e.preventDefault();
    request(form.action, { method: 'POST', body: new FormData(form) }, true);
  }

  // Delegasi di level document supaya otomatis "nyantol" ke konten baru
  // hasil swap tanpa perlu bind ulang manual.
  document.addEventListener('click', handleLinkClick);
  document.addEventListener('submit', handleFormSubmit);

  window.addEventListener('popstate', function () {
    request(window.location.href, { method: 'GET' }, false);
  });
})();
