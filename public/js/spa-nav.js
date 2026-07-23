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
    var CONTENT_SEL = "#pageContent";
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
        return !!url && (url.protocol === "http:" || url.protocol === "https:");
    }

    function isEligibleLink(a) {
        if (!a || !a.hasAttribute("href")) return false;
        var href = a.getAttribute("href");
        if (!href || href.charAt(0) === "#") return false;
        if (/^(javascript:|mailto:|tel:)/i.test(href)) return false;
        if (a.hasAttribute("download")) return false;
        if (a.dataset.noSpa !== undefined) return false;
        if (a.target && a.target !== "" && a.target !== "_self") return false;
        return isHttpUrl(resolveUrl(href));
    }

    function isEligibleForm(form) {
        if (!form) return false;
        if (form.dataset.noSpa !== undefined) return false;
        if (form.target && form.target !== "" && form.target !== "_self")
            return false;
        return isHttpUrl(resolveUrl(form.action));
    }

    function getContentEl() {
        return document.querySelector(CONTENT_SEL);
    }

    function startLoading(el) {
        el.style.transition = "opacity .12s ease";
        el.style.opacity = "0.45";
        el.setAttribute("aria-busy", "true");
    }

    function endLoading(el) {
        el.style.opacity = "";
        el.removeAttribute("aria-busy");
    }

    function runScripts(container) {
        container.querySelectorAll("script").forEach(function (oldScript) {
            var newScript = document.createElement("script");
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
        document
            .querySelectorAll(
                "#appSidebar a[href], #mobileSidebar a[href], [data-spa-nav] a[href]",
            )
            .forEach(function (a) {
                var linkUrl = resolveUrl(a.getAttribute("href"));
                if (!linkUrl) return;
                var active = linkUrl.pathname === targetPath;
                if (active) {
                    a.style.backgroundColor = "#2563EB";
                    a.style.color = "#fff";
                    a.style.fontWeight = "600";
                    a.classList.remove(
                        "text-slate-600",
                        "hover:bg-blue-100",
                        "hover:text-blue-700",
                        "hover:shadow-sm",
                        "hover:translate-x-0.5",
                    );
                } else {
                    a.style.backgroundColor = "";
                    a.style.color = "";
                    a.style.fontWeight = "";
                    if (!a.classList.contains("text-slate-600")) {
                        a.classList.add(
                            "text-slate-600",
                            "hover:bg-blue-100",
                            "hover:text-blue-700",
                            "hover:shadow-sm",
                            "hover:translate-x-0.5",
                        );
                    }
                }
            });
    }

    // --- inti: ambil halaman lewat fetch, tempel #pageContent ------

    // Nama file script "global" yang sudah jalan sekali di document awal dan
    // TIDAK BOLEH dimasukkan/dijalankan ulang setiap navigasi SPA -- kalau
    // dijalankan ulang, event listener di document (click/submit di file ini
    // sendiri, juga sidebar-toggle.js) akan terpasang dobel/berkali-kali.
    var PERSISTENT_SCRIPT_RE = /sidebar-toggle\.js|spa-nav\.js/;

    function isPersistentScript(el) {
        if (!el || el.tagName !== "SCRIPT") return false;
        var src = el.getAttribute("src") || "";
        return PERSISTENT_SCRIPT_RE.test(src);
    }

    // Elemen "shell" aplikasi (sidebar + header + <main id="pageContent">)
    // yang selalu ada persis 1x di setiap halaman dan tidak boleh dibuang -
    // hanya isi #pageContent di dalamnya yang diganti.
    function isAppShell(el) {
        return !!(
            el &&
            el.nodeType === 1 &&
            typeof el.querySelector === "function" &&
            el.querySelector(CONTENT_SEL)
        );
    }

    // Setiap halaman di aplikasi ini adalah dokumen HTML penuh (bukan cuma
    // fragment), sehingga tiap halaman biasanya punya modal (Tambah/Edit/
    // Hapus/Detail) + <script> khususnya sendiri yang diletakkan di luar
    // <main id="pageContent"> (supaya tidak ikut acak-acak isi tabel saat
    // di-render ulang). Sebelumnya elemen² ini TIDAK PERNAH ikut ter-update
    // saat pindah halaman lewat SPA nav (cuma #pageContent yang diganti),
    // sehingga tombol Tambah/Edit/Hapus/Detail/Validasi jadi tidak berfungsi
    // begitu masuk ke halaman itu lewat klik menu (bukan refresh manual).
    // Fungsi ini menyinkronkan ulang elemen² tsb dari dokumen hasil fetch.
    function syncPageExtras(doc) {
        // Buang semua "extra" milik halaman sebelumnya (modal & script khusus
        // halaman itu), tapi jangan sentuh shell (sidebar/header/#pageContent)
        // ataupun script global yang persisten.
        Array.prototype.slice
            .call(document.body.children)
            .forEach(function (el) {
                if (isAppShell(el) || isPersistentScript(el)) return;
                el.parentNode.removeChild(el);
            });

        var scriptsToRun = [];
        Array.prototype.forEach.call(doc.body.children, function (el) {
            if (isAppShell(el) || isPersistentScript(el)) return;
            var clone = el.cloneNode(true);
            document.body.appendChild(clone);
            if (clone.tagName === "SCRIPT") {
                scriptsToRun.push(clone);
            } else if (clone.querySelectorAll) {
                Array.prototype.forEach.call(
                    clone.querySelectorAll("script"),
                    function (s) {
                        scriptsToRun.push(s);
                    },
                );
            }
        });

        scriptsToRun.forEach(function (oldScript) {
            var newScript = document.createElement("script");
            Array.prototype.forEach.call(oldScript.attributes, function (attr) {
                newScript.setAttribute(attr.name, attr.value);
            });
            newScript.text = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    // Beberapa halaman memuat library pihak-ketiga (mis. Chart.js) lewat
    // <script src> di <head>-nya sendiri. Karena <head> tidak pernah ikut
    // di-refresh oleh SPA nav, halaman yang di-buka lewat klik menu (bukan
    // reload) bisa kehilangan library itu -> grafik/visualisasi gagal jalan.
    // Fungsi ini memuat <script src> yang ada di <head> hasil fetch tapi
    // belum ada di <head> dokumen sekarang, sebelum script halaman dijalankan.
    function loadMissingHeadScripts(doc) {
        var currentSrcs = Array.prototype.map.call(
            document.head.querySelectorAll("script[src]"),
            function (s) {
                return s.getAttribute("src");
            },
        );
        var missing = [];
        Array.prototype.forEach.call(
            doc.head.querySelectorAll("script[src]"),
            function (s) {
                var src = s.getAttribute("src");
                if (src && currentSrcs.indexOf(src) === -1) missing.push(src);
            },
        );

        if (missing.length === 0) return Promise.resolve();

        return missing.reduce(function (chain, src) {
            return chain.then(function () {
                return new Promise(function (resolve) {
                    var s = document.createElement("script");
                    s.src = src;
                    s.onload = resolve;
                    s.onerror = resolve; // jangan sampai 1 library gagal bikin navigasi macet
                    document.head.appendChild(s);
                });
            });
        }, Promise.resolve());
    }

    function swapFromResponse(res, html, pushState) {
        var contentEl = getContentEl();
        var doc = new DOMParser().parseFromString(html, "text/html");
        var newContent = doc.querySelector(CONTENT_SEL);

        if (!newContent) {
            // Halaman hasil (mis. login, atau halaman lama yang belum
            // dikasih #pageContent) -> lakukan navigasi normal saja.
            window.location.href = res.url || window.location.href;
            return;
        }

        loadMissingHeadScripts(doc).then(function () {
            contentEl.innerHTML = newContent.innerHTML;
            runScripts(contentEl);
            syncPageExtras(doc);
            endLoading(contentEl);

            var newTitleEl = doc.getElementById("pageHeaderTitle");
            var curTitleEl = document.getElementById("pageHeaderTitle");
            if (newTitleEl && curTitleEl)
                curTitleEl.textContent = newTitleEl.textContent;
            if (doc.title) document.title = doc.title;

            var finalUrl = res.url || window.location.href;
            if (pushState) {
                window.history.pushState(
                    { spaNav: true },
                    doc.title || "",
                    finalUrl,
                );
            }

            setActiveMenu(finalUrl);
            contentEl.scrollTop = 0;
            window.scrollTo(0, 0);

            document.dispatchEvent(
                new CustomEvent("spa:navigated", { detail: { url: finalUrl } }),
            );
        });
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
            credentials: "same-origin",
            headers: Object.assign(
                { "X-Requested-With": "XMLHttpRequest" },
                (fetchOptions && fetchOptions.headers) || {},
            ),
            signal: currentController.signal,
        });

        fetch(url, opts)
            .then(function (res) {
                var contentType = res.headers.get("content-type") || "";
                if (!res.ok) throw new Error("HTTP " + res.status);
                if (contentType.indexOf("text/html") === -1) {
                    // Bukan halaman HTML (misalnya file export PDF/Excel) -> buka di tab
                    // baru. Sebelumnya pakai window.location.href, yang menavigasi TAB
                    // INI ke file-nya sehingga sidebar/navbar SPA ikut hilang. Dengan
                    // tab baru, halaman asal (dan navbar-nya) tetap utuh.
                    window.open(url, "_blank");
                    endLoading(contentEl);
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
                if (err && err.name === "AbortError") return;
                // Gagal (network error / CORS / dll) -> fallback navigasi biasa supaya tidak "nyangkut".
                window.location.href = url;
            });
    }

    // --- event handlers --------------------------------------------

    function handleLinkClick(e) {
        var a = e.target.closest ? e.target.closest("a[href]") : null;
        if (!a || !isEligibleLink(a)) return;
        if (
            e.defaultPrevented ||
            e.metaKey ||
            e.ctrlKey ||
            e.shiftKey ||
            e.altKey ||
            e.button !== 0
        )
            return;

        var target = resolveUrl(a.getAttribute("href"));
        if (!target) return;

        e.preventDefault();
        if (
            target.pathname === window.location.pathname &&
            target.search === window.location.search
        )
            return;

        request(target.href, { method: "GET" }, true);
    }

    function handleFormSubmit(e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (e.defaultPrevented) return; // sudah dibatalkan (mis. lewat confirm() yang return false)
        if (!isEligibleForm(form)) return;

        var method = (form.getAttribute("method") || "GET").toUpperCase();

        if (method === "GET") {
            var params = new URLSearchParams(new FormData(form));
            var url = resolveUrl(form.action);
            if (!url) return;
            url.search = params.toString();
            e.preventDefault();
            request(url.href, { method: "GET" }, true);
            return;
        }

        // POST (termasuk yang di-spoof jadi PUT/PATCH/DELETE lewat @method())
        e.preventDefault();
        request(
            form.action,
            { method: "POST", body: new FormData(form) },
            true,
        );
    }

    // Delegasi di level document supaya otomatis "nyantol" ke konten baru
    // hasil swap tanpa perlu bind ulang manual.
    document.addEventListener("click", handleLinkClick);
    document.addEventListener("submit", handleFormSubmit);

    window.addEventListener("popstate", function () {
        request(window.location.href, { method: "GET" }, false);
    });
})();
