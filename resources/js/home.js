/* ==========================================================================
   Hero slider gunung
   - Kartu berikutnya terangkat, membesar jadi latar penuh (Web Animations API)
   - Judul muncul dari bawah (mask reveal), deretan kartu bergeser, penghitung berganti
   - Autoplay dengan progress bar emas di atas
   Tanpa dependensi.
   ========================================================================== */
(() => {
    "use strict";

    const hero = document.querySelector("[data-hero]");
    const dataEl = document.getElementById("hero-data");
    if (!hero || !dataEl) return;

    let slides = [];
    try {
        slides = JSON.parse(dataEl.textContent || "[]");
    } catch (e) {
        /* data rusak */
    }
    const n = slides.length;
    if (n < 1) return;

    const AUTOPLAY_MS = 7000;
    const DUR = 1150;
    const EASE = "cubic-bezier(.65,.05,.2,1)";
    const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

    const $ = (sel) => hero.querySelector(sel);
    const els = {
        bg: $(".hero__bg"),
        dim: $(".hero__dim"),
        shade: $(".hero__shade"),
        content: $(".hero__content"),
        place: $(".hero__place"),
        title: $(".hero__title"),
        meta: $(".hero__meta"),
        desc: $(".hero__desc"),
        detail: $(".btn-detail"),
        track: $(".hero__track"),
        count: $(".hero__count"),
        prev: $("[data-prev]"),
        next: $("[data-next]"),
        progress: $(".hero__progress"),
    };

    let current = 0;
    let busy = false;
    let ready = false;
    let autoTimer = null;

    /* ------------------------------------------------------------------
     Gambar pemandangan (SVG) dibuat di browser.
     Jika slide punya `image` asli, gambar itu yang dipakai.
     ------------------------------------------------------------------ */
    const PALETTES = {
        semeru: {
            sky: ["#1d2b4d", "#b4577f", "#ffb36b"],
            sun: "#ffe2b0",
            sunPos: [0.3, 0.6],
            ridge: ["#7a5278", "#5a3b66", "#3a2a4d", "#1d1730"],
            fog: "#ffc7a0",
            plume: "#e3cfc8",
        },
        arjuno: {
            sky: ["#41607a", "#8fb0c2", "#e8e4d4"],
            sun: "#fff6dc",
            sunPos: [0.26, 0.52],
            ridge: ["#94aab2", "#6f8c95", "#496a70", "#243d3d"],
            fog: "#f1f0e6",
        },
        panderman: {
            sky: ["#2f7ab8", "#86c1e6", "#e1f1f4"],
            sun: "#fffbe0",
            sunPos: [0.2, 0.3],
            ridge: ["#86b793", "#549468", "#336f49", "#173f2b"],
            fog: "#ffffff",
        },
        penanggungan: {
            sky: ["#241a4a", "#6a3d86", "#f28d6b"],
            sun: "#ffd1a0",
            sunPos: [0.34, 0.58],
            ridge: ["#7d4a85", "#58336e", "#3a2354", "#1c1233"],
            fog: "#f6a98d",
        },
        rinjani: {
            sky: ["#07122b", "#173a63", "#4d7fa3"],
            moon: "#f4f1e2",
            sunPos: [0.22, 0.2],
            stars: true,
            ridge: ["#35546e", "#223d57", "#142a3f", "#0a1826"],
            fog: "#8fb4cc",
        },
    };
    const PALETTE_ORDER = [
        "semeru",
        "arjuno",
        "panderman",
        "penanggungan",
        "rinjani",
    ];

    const hash = (str) => {
        let h = 2166136261;
        for (let i = 0; i < str.length; i++) {
            h ^= str.charCodeAt(i);
            h = Math.imul(h, 16777619);
        }
        return h >>> 0;
    };
    const rng = (seed) => () => {
        seed |= 0;
        seed = (seed + 0x6d2b79f5) | 0;
        let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
        t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
    const noise = (r, count) => {
        const v = Array.from({ length: count + 2 }, () => r());
        return (x) => {
            const i = Math.floor(x),
                f = x - i,
                s = f * f * (3 - 2 * f);
            return v[i] * (1 - s) + v[i + 1] * s;
        };
    };

    function ridge(r, o) {
        const n1 = noise(r, 60),
            n2 = noise(r, 120),
            n3 = noise(r, 240);
        const pts = [];
        let apex = [0, 1000];
        for (let x = 0; x <= 1600; x += 16) {
            const t = x / 1600;
            let h =
                o.amp *
                (0.55 * n1(t * o.freq) +
                    0.3 * n2(t * o.freq * 2.1) +
                    0.15 * n3(t * o.freq * 4.3));
            if (o.peakH) {
                const d = Math.abs(x - o.peakX) / o.peakW;
                h +=
                    o.peakH *
                    Math.pow(Math.max(0, 1 - d), 1.6) *
                    (0.9 + 0.2 * n3(t * 30));
            }
            const y = o.base - h;
            if (y < apex[1]) apex = [x, y];
            pts.push(x + " " + y.toFixed(1));
        }
        return { d: "M0 1000 L" + pts.join(" L") + " L1600 1000 Z", apex };
    }

    function buildScene(slide, index) {
        const key = PALETTES[slide.slug]
            ? slide.slug
            : PALETTE_ORDER[index % PALETTE_ORDER.length];
        const pal = PALETTES[key];
        const r = rng(hash(slide.slug || String(index)));
        const peakX = 1600 * (0.6 + r() * 0.2);

        const layers = [
            { base: 650, amp: 150, freq: 5 },
            { base: 730, amp: 190, freq: 4, peakX, peakH: 340, peakW: 430 },
            { base: 810, amp: 140, freq: 6 },
            { base: 890, amp: 110, freq: 7 },
            { base: 965, amp: 70, freq: 9 },
        ];
        const colors = [
            pal.ridge[0],
            pal.ridge[1],
            pal.ridge[2],
            pal.ridge[3],
            pal.ridge[3],
        ];

        let svg =
            '<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="1000" viewBox="0 0 1600 1000" preserveAspectRatio="xMidYMid slice">';
        svg +=
            "<defs>" +
            '<linearGradient id="sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' +
            pal.sky[0] +
            '"/><stop offset=".55" stop-color="' +
            pal.sky[1] +
            '"/><stop offset="1" stop-color="' +
            pal.sky[2] +
            '"/></linearGradient>' +
            '<radialGradient id="glow"><stop offset="0" stop-color="' +
            (pal.sun || pal.moon) +
            '" stop-opacity=".85"/><stop offset="1" stop-color="' +
            (pal.sun || pal.moon) +
            '" stop-opacity="0"/></radialGradient>' +
            '<linearGradient id="fog" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="' +
            pal.fog +
            '" stop-opacity="0"/><stop offset=".5" stop-color="' +
            pal.fog +
            '" stop-opacity=".5"/><stop offset="1" stop-color="' +
            pal.fog +
            '" stop-opacity="0"/></linearGradient>' +
            '<filter id="b" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="12"/></filter>' +
            "</defs>";
        svg += '<rect width="1600" height="1000" fill="url(#sky)"/>';

        if (pal.stars) {
            for (let i = 0; i < 130; i++) {
                svg +=
                    '<circle cx="' +
                    (r() * 1600).toFixed(0) +
                    '" cy="' +
                    (r() * 520).toFixed(0) +
                    '" r="' +
                    (0.6 + r() * 1.1).toFixed(2) +
                    '" fill="#fff" opacity="' +
                    (0.25 + r() * 0.65).toFixed(2) +
                    '"/>';
            }
        }
        const sx = pal.sunPos[0] * 1600,
            sy = pal.sunPos[1] * 1000;
        svg +=
            '<circle cx="' +
            sx +
            '" cy="' +
            sy +
            '" r="440" fill="url(#glow)" opacity="' +
            (pal.moon ? 0.35 : 0.7) +
            '"/>';
        svg +=
            '<circle cx="' +
            sx +
            '" cy="' +
            sy +
            '" r="' +
            (pal.moon ? 46 : 84) +
            '" fill="' +
            (pal.sun || pal.moon) +
            '"/>';

        let apex = [peakX, 400];
        layers.forEach((layer, i) => {
            const rd = ridge(r, layer);
            if (layer.peakH) apex = rd.apex;
            svg += '<path d="' + rd.d + '" fill="' + colors[i] + '"/>';
            if (i < 3)
                svg +=
                    '<rect x="0" y="' +
                    (layer.base - 90) +
                    '" width="1600" height="260" fill="url(#fog)" opacity=".7"/>';
        });
        svg +=
            '<rect x="0" y="880" width="1600" height="120" fill="#000" opacity=".18"/>';

        if (pal.plume) {
            for (let i = 0; i < 7; i++) {
                svg +=
                    '<circle cx="' +
                    (apex[0] + i * 26 + r() * 10).toFixed(0) +
                    '" cy="' +
                    (apex[1] - 8 - i * 42).toFixed(0) +
                    '" r="' +
                    (24 + i * 15) +
                    '" fill="' +
                    pal.plume +
                    '" opacity="' +
                    (0.55 - i * 0.06).toFixed(2) +
                    '" filter="url(#b)"/>';
            }
        }
        svg += "</svg>";

        return {
            uri: "data:image/svg+xml;charset=utf-8," + encodeURIComponent(svg),
            peakPct: Math.round(peakX / 16),
        };
    }

    /* Gambar disiapkan sekali di awal sebagai bitmap (JPEG). SVG yang dipakai langsung sebagai background
     digambar ulang di setiap frame saat ukurannya berubah, itu yang membuat animasi berat. */
    const IMG_W = 1280,
        IMG_H = 800;

    function rasterize(svgUri) {
        return new Promise((resolve) => {
            const fallback = { src: svgUri, w: 1600, h: 1000 };
            const img = new Image();
            img.onload = () => {
                const c = document.createElement("canvas");
                c.width = IMG_W;
                c.height = IMG_H;
                c.getContext("2d").drawImage(img, 0, 0, IMG_W, IMG_H);
                c.toBlob(
                    (blob) =>
                        resolve(
                            blob
                                ? {
                                      src: URL.createObjectURL(blob),
                                      w: IMG_W,
                                      h: IMG_H,
                                  }
                                : fallback,
                        ),
                    "image/jpeg",
                    0.92,
                );
            };
            img.onerror = () => resolve(fallback);
            img.src = svgUri;
        });
    }

    function loadPhoto(url) {
        return new Promise((resolve) => {
            const img = new Image();
            img.onload = () =>
                resolve({
                    src: url,
                    w: img.naturalWidth,
                    h: img.naturalHeight,
                });
            img.onerror = () => resolve(null);
            img.src = url;
        });
    }

    async function prepare(slide, i) {
        slide._scene = buildScene(slide, i);
        const photo = slide.image ? await loadPhoto(slide.image) : null;
        slide._img = photo || (await rasterize(slide._scene.uri));
        slide._px = photo ? 50 : slide._scene.peakPct;
        slide._py = 40;
        // pastikan sudah ter-decode sebelum dipakai animasi
        await new Promise((done) => {
            const d = new Image();
            d.onload = d.onerror = done;
            d.src = slide._img.src;
        });
    }

    function setBg(el, slide) {
        el.style.backgroundImage = 'url("' + slide._img.src + '")';
        el.style.backgroundPosition = slide._px + "% " + slide._py + "%";
    }

    /* latar utama berisi satu lapisan gambar (.hero__ximg), sama seperti lapisan pengembang */
    function showBg(slide) {
        let xi = els.bg.firstElementChild;
        if (!xi) {
            xi = document.createElement("div");
            xi.className = "hero__ximg";
            els.bg.appendChild(xi);
        }
        setBg(xi, slide);
    }

    /* ------------------------------------------------------------------
     Util
     ------------------------------------------------------------------ */
    const esc = (s) =>
        String(s == null ? "" : s).replace(
            /[&<>"']/g,
            (c) =>
                ({
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': "&quot;",
                    "'": "&#39;",
                })[c],
        );
    const pad2 = (i) => String(i + 1).padStart(2, "0");
    const fmt = (v) => Number(v).toLocaleString("id-ID");
    const STATUS = { buka: "Dibuka", tutup: "Ditutup", dibatasi: "Dibatasi" };

    function lines(name) {
        const parts = String(name).trim().split(/\s+/);
        if (parts.length === 1) return [parts[0]];
        if (/^gunung$/i.test(parts[0]))
            return [parts[0], parts.slice(1).join(" ")];
        const mid = Math.ceil(parts.length / 2);
        return [parts.slice(0, mid).join(" "), parts.slice(mid).join(" ")];
    }

    const play = (el, keyframes, opts) => {
        const a = el.animate(
            keyframes,
            Object.assign({ duration: 700, easing: EASE, fill: "both" }, opts),
        );
        return a.finished.catch(() => {});
    };
    const cancelAll = (el, subtree) =>
        el
            .getAnimations(subtree ? { subtree: true } : undefined)
            .forEach((a) => a.cancel());

    /* ------------------------------------------------------------------
     Render konten
     ------------------------------------------------------------------ */
    function setContent(i) {
        const s = slides[i];
        els.place.textContent = s.place || "";
        els.title.innerHTML = lines(s.name)
            .map((t) => '<span class="ln"><span>' + esc(t) + "</span></span>")
            .join("");

        let meta = "";
        if (s.elevation) meta += "<li>" + fmt(s.elevation) + " mdpl</li>";
        if (s.trails != null) meta += "<li>" + s.trails + " jalur</li>";
        meta +=
            '<li data-status="' +
            esc(s.status || "") +
            '"><span class="dot"></span>' +
            esc(STATUS[s.status] || "Status belum ada") +
            "</li>";
        els.meta.innerHTML = meta;

        els.desc.textContent = s.description || "";
        els.detail.setAttribute("href", s.url || "#");
        els.count.textContent = pad2(i);
        hero.setAttribute("aria-label", "Gunung pilihan: " + s.name);
    }

    function makeCard(i) {
        const s = slides[i];
        const b = document.createElement("button");
        b.type = "button";
        b.className = "card";
        b.setAttribute("aria-label", "Lihat " + s.name);
        setBg(b, s);
        b.innerHTML =
            '<span class="card__text"><span class="card__place">' +
            esc(s.place) +
            '</span><span class="card__name">' +
            lines(s.name).map(esc).join("<br>") +
            "</span></span>";
        return b;
    }

    function renderTrack() {
        els.track.replaceChildren();
        for (let k = 1; k < n; k++)
            els.track.appendChild(makeCard((current + k) % n));
    }

    function contentParts() {
        return [...els.content.children].filter((c) => c !== els.title);
    }

    function contentOut() {
        const jobs = [];
        contentParts().forEach((el, i) =>
            jobs.push(
                play(
                    el,
                    [
                        { opacity: 1, transform: "none" },
                        { opacity: 0, transform: "translateY(-12px)" },
                    ],
                    { duration: 350, delay: i * 30, easing: "ease-in" },
                ),
            ),
        );
        els.title.querySelectorAll(".ln > span").forEach((el, i) =>
            jobs.push(
                play(
                    el,
                    [
                        { transform: "translateY(0)" },
                        { transform: "translateY(-105%)" },
                    ],
                    {
                        duration: 480,
                        delay: i * 50,
                        easing: "cubic-bezier(.7,0,.84,0)",
                    },
                ),
            ),
        );
        return Promise.all(jobs);
    }

    function contentIn(delay) {
        const base = delay || 0;
        cancelAll(els.content, true);
        els.title
            .querySelectorAll(".ln > span")
            .forEach((el, i) =>
                play(
                    el,
                    [
                        { transform: "translateY(105%)" },
                        { transform: "translateY(0)" },
                    ],
                    { duration: 900, delay: base + i * 110, fill: "backwards" },
                ),
            );
        contentParts().forEach((el, i) =>
            play(
                el,
                [
                    { opacity: 0, transform: "translateY(18px)" },
                    { opacity: 1, transform: "none" },
                ],
                {
                    duration: 750,
                    delay: base + 220 + i * 80,
                    fill: "backwards",
                },
            ),
        );
    }

    async function rollCount(i, dir) {
        const el = els.count;
        await play(
            el,
            [
                { transform: "translateY(0)", opacity: 1 },
                {
                    transform: "translateY(" + (dir > 0 ? -45 : 45) + "%)",
                    opacity: 0,
                },
            ],
            { duration: 260, easing: "ease-in" },
        );
        el.textContent = pad2(i);
        await play(
            el,
            [
                {
                    transform: "translateY(" + (dir > 0 ? 45 : -45) + "%)",
                    opacity: 0,
                },
                { transform: "translateY(0)", opacity: 1 },
            ],
            { duration: 480 },
        );
        cancelAll(el);
    }

    /* ------------------------------------------------------------------
     Autoplay
     ------------------------------------------------------------------ */
    function stopAuto() {
        clearTimeout(autoTimer);
        autoTimer = null;
    }
    function startAuto() {
        stopAuto();
        if (reduceMotion.matches || n < 2) return;
        autoTimer = setTimeout(() => go(1), AUTOPLAY_MS);
    }
    document.addEventListener("visibilitychange", () => {
        if (document.hidden) stopAuto();
        else if (!busy && ready) startAuto();
    });

    /* ------------------------------------------------------------------
     Pindah slide
     dir  1 = maju (kartu ke-p membesar), -1 = mundur (wipe dari kiri)
     ------------------------------------------------------------------ */
    /* Animasi kartu membesar memakai clip-path + transform (tanpa mengubah layout):
     latar penuh ditampilkan di balik "jendela" yang membesar dari ukuran kartu ke layar penuh. */
    function coverFit(slide, r) {
        const s = Math.max(r.w / slide._img.w, r.h / slide._img.h);
        return {
            s,
            x: r.x + ((r.w - slide._img.w * s) * slide._px) / 100,
            y: r.y + ((r.h - slide._img.h * s) * slide._py) / 100,
        };
    }
    function imageTransform(slide, r, hr) {
        const F = coverFit(slide, { x: 0, y: 0, w: hr.w, h: hr.h });
        const C = coverFit(slide, r);
        const k = C.s / F.s;
        return (
            "translate(" +
            (C.x - k * F.x) +
            "px," +
            (C.y - k * F.y) +
            "px) scale(" +
            k +
            ")"
        );
    }
    const clipFor = (r, hr, radius) =>
        "inset(" +
        r.y +
        "px " +
        (hr.w - r.x - r.w) +
        "px " +
        (hr.h - r.y - r.h) +
        "px " +
        r.x +
        "px round " +
        radius +
        "px)";

    function stepSize() {
        const c = els.track.children;
        if (c.length > 1) return c[1].offsetLeft - c[0].offsetLeft;
        const cs = getComputedStyle(els.track);
        return c[0].offsetWidth + (parseFloat(cs.columnGap) || 0);
    }

    async function go(dir, p) {
        p = p || 0;
        if (!ready || busy || n < 2) return;
        busy = true;
        stopAuto();

        const target = dir > 0 ? (current + 1 + p) % n : (current - 1 + n) % n;

        if (reduceMotion.matches) {
            current = target;
            showBg(slides[current]);
            renderTrack();
            setContent(current);
            busy = false;
            return;
        }

        hero.classList.add("is-busy"); // matikan efek hover kartu selama animasi
        const hrect = hero.getBoundingClientRect();
        const hr = { w: hrect.width, h: hrect.height };
        const step = stepSize();
        const old = current;
        const next = slides[target];

        const ex = document.createElement("div");
        ex.className = "hero__expander";
        const xi = document.createElement("div");
        xi.className = "hero__ximg";
        setBg(xi, next);
        ex.appendChild(xi);
        hero.insertBefore(ex, els.shade);

        const cards = [...els.track.children];
        const jobs = [];
        let removeCount = 0;

        if (dir > 0) {
            /* kartu terangkat lalu membesar menutup layar */
            const card = cards[p];
            const cr = card.getBoundingClientRect();
            const r = {
                x: cr.left - hrect.left,
                y: cr.top - hrect.top,
                w: cr.width,
                h: cr.height,
            };
            const lift = { x: r.x - 12, y: r.y - 28, w: r.w + 24, h: r.h + 24 };
            const full = { x: 0, y: 0, w: hr.w, h: hr.h };
            const e1 = "cubic-bezier(.2,.7,.3,1)";
            const e2 = "cubic-bezier(.76,0,.24,1)";

            ex.style.clipPath = clipFor(r, hr, 16);
            xi.style.transform = imageTransform(next, r, hr);
            card.style.visibility = "hidden";
            cards.slice(0, p).forEach((c) =>
                jobs.push(
                    play(c, [{ opacity: 1 }, { opacity: 0 }], {
                        duration: 300,
                    }),
                ),
            );

            jobs.push(
                ex
                    .animate(
                        [
                            { clipPath: clipFor(r, hr, 16), easing: e1 },
                            {
                                clipPath: clipFor(lift, hr, 20),
                                offset: 0.26,
                                easing: e2,
                            },
                            { clipPath: clipFor(full, hr, 0) },
                        ],
                        { duration: DUR, fill: "forwards" },
                    )
                    .finished.catch(() => {}),
            );

            jobs.push(
                xi
                    .animate(
                        [
                            {
                                transform: imageTransform(next, r, hr),
                                easing: e1,
                            },
                            {
                                transform: imageTransform(next, lift, hr),
                                offset: 0.26,
                                easing: e2,
                            },
                            { transform: "translate(0px,0px) scale(1)" },
                        ],
                        { duration: DUR, fill: "forwards" },
                    )
                    .finished.catch(() => {}),
            );

            jobs.push(
                play(els.bg, [{ opacity: 1 }, { opacity: 0.5 }], {
                    duration: DUR * 0.7,
                    easing: "ease-out",
                }),
            );

            /* kartu baru di ujung kanan, geser deretan ke kiri */
            removeCount = p + 1;
            for (let k = 0; k <= p; k++) {
                const nc = makeCard((old + k) % n);
                nc.style.opacity = "0";
                els.track.appendChild(nc);
                jobs.push(
                    play(
                        nc,
                        [
                            { opacity: 0, transform: "translateX(40px)" },
                            { opacity: 1, transform: "none" },
                        ],
                        {
                            duration: 700,
                            delay: DUR * 0.5 + k * 80,
                            easing: "cubic-bezier(.2,.7,.3,1)",
                        },
                    ),
                );
            }
            jobs.push(
                play(
                    els.track,
                    [
                        { transform: "translateX(0)" },
                        { transform: "translateX(" + -(p + 1) * step + "px)" },
                    ],
                    { duration: DUR * 0.9, delay: 120 },
                ),
            );
        } else {
            /* mundur: gambar menyapu dari kiri */
            ex.style.clipPath = "inset(0 100% 0 0)";
            jobs.push(
                ex
                    .animate(
                        [
                            { clipPath: "inset(0 100% 0 0)" },
                            { clipPath: "inset(0 0% 0 0)" },
                        ],
                        { duration: DUR * 0.9, easing: EASE, fill: "forwards" },
                    )
                    .finished.catch(() => {}),
            );
            jobs.push(
                play(els.bg, [{ opacity: 1 }, { opacity: 0.6 }], {
                    duration: DUR * 0.7,
                    easing: "ease-out",
                }),
            );

            const lastCard = cards[cards.length - 1];
            const front = makeCard(old);
            els.track.prepend(front);
            jobs.push(
                play(front, [{ opacity: 0 }, { opacity: 1 }], {
                    duration: 700,
                    delay: DUR * 0.3,
                }),
            );
            jobs.push(
                play(lastCard, [{ opacity: 1 }, { opacity: 0 }], {
                    duration: 400,
                }),
            );
            jobs.push(
                play(
                    els.track,
                    [
                        { transform: "translateX(" + -step + "px)" },
                        { transform: "translateX(0)" },
                    ],
                    { duration: DUR * 0.9, delay: 120 },
                ),
            );
        }

        contentOut();
        rollCount(target, dir);

        await Promise.all(jobs);

        /* rapikan: lapisan pengembang (gambar sudah ter-render) dijadikan latar baru, tanpa menggambar ulang */
        current = target;
        const oldBg = els.bg;
        ex.className = "hero__bg";
        ex.style.clipPath = "";
        xi.style.transform = "";
        cancelAll(ex);
        cancelAll(xi);
        els.bg = ex;
        oldBg.remove();
        cancelAll(els.track);
        if (dir > 0) {
            for (let k = 0; k < removeCount; k++)
                els.track.firstElementChild.remove();
        } else {
            els.track.lastElementChild.remove();
        }
        [...els.track.children].forEach((c) => {
            cancelAll(c);
            c.style.opacity = "";
            c.style.visibility = "";
        });

        setContent(current);
        contentIn(0);
        hero.classList.remove("is-busy");
        busy = false;
        startAuto();
    }

    /* ------------------------------------------------------------------
     Event
     ------------------------------------------------------------------ */
    els.next && els.next.addEventListener("click", () => go(1));
    els.prev && els.prev.addEventListener("click", () => go(-1));
    els.track.addEventListener("click", (e) => {
        const card = e.target.closest(".card");
        if (!card) return;
        go(1, [...els.track.children].indexOf(card));
    });
    document.addEventListener("keydown", (e) => {
        if (document.querySelector(".nav.is-open")) return; // menu layar kecil sedang terbuka
        if (e.key === "ArrowRight") go(1);
        else if (e.key === "ArrowLeft") go(-1);
    });

    let startX = null;
    hero.addEventListener("pointerdown", (e) => {
        startX = e.clientX;
    });
    hero.addEventListener("pointerup", (e) => {
        if (startX == null) return;
        const dx = e.clientX - startX;
        startX = null;
        if (Math.abs(dx) > 70 && !e.target.closest(".card, a, button"))
            go(dx < 0 ? 1 : -1);
    });

    /* ------------------------------------------------------------------
     Mulai (setelah semua gambar siap)
     ------------------------------------------------------------------ */
    function init() {
        const full = Math.min(4, Math.max(1, n - 2)); // jumlah kartu penuh
        hero.style.setProperty("--cards", full + 0.4); // + sisa yang terpotong
        hero.style.setProperty("--gaps", full);
        ready = true;
        showBg(slides[0]);
        renderTrack();
        setContent(0);
        if (!reduceMotion.matches) {
            contentIn(150);
            [...els.track.children].forEach((c, i) =>
                play(
                    c,
                    [
                        { opacity: 0, transform: "translateY(40px)" },
                        { opacity: 1, transform: "none" },
                    ],
                    {
                        duration: 800,
                        delay: 300 + i * 90,
                        fill: "backwards",
                        easing: "cubic-bezier(.2,.7,.3,1)",
                    },
                ),
            );
        }
        startAuto();
    }

    Promise.all(slides.map(prepare)).then(init);

    if (n < 2) {
        [els.prev, els.next].forEach((b) => b && (b.hidden = true));
    }
})();
