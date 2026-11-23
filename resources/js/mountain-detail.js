/* Halaman detail gunung
   - ilustrasi bawaan (scene.js) untuk gunung tanpa foto
   - scrollspy + navigasi section yang menempel
   - peta Leaflet (dimuat saat mendekati layar)
   - lightbox galeri (<dialog>) dan carousel rekomendasi */
import { sceneFor } from "./scene.js";

const reduceMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
).matches;

/* ------------------------------------------------------------------
   Ilustrasi untuk elemen [data-scene="slug"]
   ------------------------------------------------------------------ */
function paintScenes() {
    document.querySelectorAll("[data-scene]").forEach((el) => {
        const scene = sceneFor(el.dataset.scene);
        el.style.backgroundImage = 'url("' + scene.uri + '")';
        el.style.backgroundPosition = scene.peakPct + "% 40%";
    });
}

/* ------------------------------------------------------------------
   Navigasi section: scrollspy + status "menempel"
   ------------------------------------------------------------------ */
function initSectionNav() {
    const nav = document.querySelector("[data-section-nav]");
    if (!nav) return;

    if (!reduceMotion) document.documentElement.classList.add("scroll-smooth");

    const links = [...nav.querySelectorAll('a[href^="#"]')];
    const sections = links
        .map((a) => document.getElementById(a.getAttribute("href").slice(1)))
        .filter(Boolean);
    const scroller = nav.querySelector("[data-section-nav-scroll]");

    const OFFSET = 140; // tinggi nav menempel + sedikit ruang
    let current = null;
    let ticking = false;

    const setActive = (id) => {
        if (id === current) return;
        current = id;
        links.forEach((a) => {
            const on = a.getAttribute("href") === "#" + id;
            a.setAttribute("aria-current", on ? "true" : "false");
            // geser tautan aktif ke tengah (untuk layar sempit, navigasinya bisa di-scroll)
            if (on && scroller && scroller.scrollWidth > scroller.clientWidth) {
                scroller.scrollTo({
                    left:
                        a.offsetLeft -
                        (scroller.clientWidth - a.offsetWidth) / 2,
                    behavior: reduceMotion ? "auto" : "smooth",
                });
            }
        });
    };

    const update = () => {
        ticking = false;
        let active = null;
        for (const s of sections) {
            if (s.getBoundingClientRect().top <= OFFSET) active = s.id;
        }
        // di dasar halaman, section terakhir dianggap aktif
        if (
            window.innerHeight + window.scrollY >=
                document.documentElement.scrollHeight - 4 &&
            sections.length
        ) {
            active = sections[sections.length - 1].id;
        }
        // sebelum section pertama tercapai: belum ada yang aktif
        if (active) setActive(active);
        else if (current) {
            current = null;
            links.forEach((a) => a.setAttribute("aria-current", "false"));
        }
    };

    window.addEventListener(
        "scroll",
        () => {
            if (!ticking) {
                ticking = true;
                requestAnimationFrame(update);
            }
        },
        { passive: true },
    );
    window.addEventListener("resize", update);
    update();

    // nama gunung muncul di bar setelah bar menempel
    const sentinel = document.querySelector("[data-nav-sentinel]");
    if (sentinel && "IntersectionObserver" in window) {
        new IntersectionObserver(([e]) => {
            nav.dataset.stuck = String(
                !e.isIntersecting && e.boundingClientRect.top < 0,
            );
        }).observe(sentinel);
    }
}

/* ------------------------------------------------------------------
   Peta Leaflet
   ------------------------------------------------------------------ */
const PIN = {
    summit: {
        cls: "bg-pine-900 text-white",
        icon: '<svg viewBox="0 0 24 24" class="size-4" fill="currentColor"><path d="M12 4 2 20h20L12 4zm0 5.2 5.4 9.3H6.6L12 9.2z"/></svg>',
    },
    basecamp: {
        cls: "bg-white text-pine-800",
        icon: null, // diisi nomor jalur
    },
    camp: {
        cls: "bg-gold text-pine-950",
        icon: '<svg viewBox="0 0 24 24" class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round"><path d="M3 20 12 5l9 15H3z"/><path d="M12 20v-7"/></svg>',
    },
};

function initMap() {
    const el = document.querySelector("[data-map]");
    const dataEl = document.getElementById("map-data");
    if (!el || !dataEl) return;

    let points = [];
    try {
        points = JSON.parse(dataEl.textContent || "[]");
    } catch (e) {
        /* data rusak */
    }
    if (!points.length) return;

    const load = async () => {
        const [{ default: L }] = await Promise.all([
            import("leaflet"),
            import("leaflet/dist/leaflet.css"),
        ]);
        const mobile = L.Browser.mobile;

        el.querySelector("[data-map-loading]")?.remove();

        const map = L.map(el, {
            scrollWheelZoom: false, // jangan "menjebak" scroll halaman
            dragging: !mobile, // di layar sentuh: geser dengan dua jari
            zoomSnap: 0.5,
        });

        const topo = L.tileLayer(
            "https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png",
            {
                maxZoom: 17,
                attribution:
                    'Kartografi &copy; <a href="https://opentopomap.org" target="_blank" rel="noopener">OpenTopoMap</a> (CC-BY-SA), data &copy; kontributor OpenStreetMap, SRTM',
            },
        ).addTo(map);
        const streets = L.tileLayer(
            "https://tile.openstreetmap.org/{z}/{x}/{y}.png",
            {
                maxZoom: 19,
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">kontributor OpenStreetMap</a>',
            },
        );
        L.control
            .layers({ Topografi: topo, "Peta jalan": streets }, null, {
                collapsed: true,
            })
            .addTo(map);

        map.on("focus", () => map.scrollWheelZoom.enable());
        map.on("blur", () => map.scrollWheelZoom.disable());

        const markers = new Map();
        const bounds = [];

        points.forEach((p) => {
            const style = PIN[p.type] || PIN.camp;
            const inner =
                style.icon ??
                '<span class="text-[13px] font-bold leading-none">' +
                    (p.order || "") +
                    "</span>";
            const icon = L.divIcon({
                className: "",
                html:
                    '<span class="grid size-9 place-items-center rounded-full shadow-lg ring-2 ring-white ' +
                    style.cls +
                    '">' +
                    inner +
                    "</span>",
                iconSize: [36, 36],
                iconAnchor: [18, 18],
                popupAnchor: [0, -20],
            });

            const body = document.createElement("div");
            const kind = document.createElement("p");
            kind.className =
                "text-[10px] font-medium uppercase tracking-[0.16em] text-pine-700";
            kind.textContent = p.label;
            const name = document.createElement("p");
            name.className = "mt-0.5 text-[15px] font-medium text-pine-900";
            name.textContent = p.name;
            body.append(kind, name);
            if (p.detail) {
                const d = document.createElement("p");
                d.className = "mt-0.5 text-[13px] text-ink-soft";
                d.textContent = p.detail;
                body.append(d);
            }

            const m = L.marker([p.lat, p.lng], {
                icon,
                title: p.name,
                keyboard: true,
            })
                .addTo(map)
                .bindPopup(body, { closeButton: false, offset: [0, 4] });
            markers.set(p.key, m);
            bounds.push([p.lat, p.lng]);
        });

        if (bounds.length === 1) map.setView(bounds[0], 12);
        else map.fitBounds(bounds, { padding: [56, 56], maxZoom: 14 });

        // daftar titik di bawah peta -> fokus ke penanda
        const items = document.querySelectorAll("[data-map-focus]");
        items.forEach((btn) =>
            btn.addEventListener("click", () => {
                const m = markers.get(btn.dataset.mapFocus);
                if (!m) return;
                map.flyTo(m.getLatLng(), Math.max(map.getZoom(), 13), {
                    duration: reduceMotion ? 0 : 0.8,
                });
                m.openPopup();
                el.scrollIntoView({
                    block: "nearest",
                    behavior: reduceMotion ? "auto" : "smooth",
                });
            }),
        );

        new ResizeObserver(() => map.invalidateSize()).observe(el);
    };

    // muat Leaflet dan ubin peta hanya ketika peta mendekati layar
    if (!("IntersectionObserver" in window)) {
        load();
        return;
    }
    const io = new IntersectionObserver(
        ([e]) => {
            if (e.isIntersecting) {
                io.disconnect();
                load();
            }
        },
        { rootMargin: "400px 0px" },
    );
    io.observe(el);
}

/* ------------------------------------------------------------------
   Lightbox galeri (<dialog>)
   ------------------------------------------------------------------ */
function initLightbox() {
    const dlg = document.querySelector("[data-lightbox]");
    const items = [...document.querySelectorAll("[data-lightbox-item]")];
    if (!dlg || !items.length || typeof dlg.showModal !== "function") return;

    const img = dlg.querySelector("[data-lb-img]");
    const cap = dlg.querySelector("[data-lb-caption]");
    const count = dlg.querySelector("[data-lb-count]");
    let index = 0;

    const show = (n) => {
        index = (n + items.length) % items.length;
        const it = items[index];
        img.src = it.dataset.full;
        img.alt = it.dataset.alt || "";
        cap.textContent = [
            it.dataset.caption,
            it.dataset.credit ? "Foto: " + it.dataset.credit : "",
        ]
            .filter(Boolean)
            .join(" · ");
        count.textContent = index + 1 + " / " + items.length;
    };

    items.forEach((it, n) =>
        it.addEventListener("click", () => {
            show(n);
            dlg.showModal();
        }),
    );
    dlg.querySelector("[data-lb-prev]").addEventListener("click", () =>
        show(index - 1),
    );
    dlg.querySelector("[data-lb-next]").addEventListener("click", () =>
        show(index + 1),
    );
    dlg.querySelector("[data-lb-close]").addEventListener("click", () =>
        dlg.close(),
    );
    dlg.addEventListener("click", (e) => {
        if (e.target === dlg) dlg.close();
    });
    dlg.addEventListener("keydown", (e) => {
        if (e.key === "ArrowLeft") show(index - 1);
        else if (e.key === "ArrowRight") show(index + 1);
    });
    dlg.addEventListener("close", () => {
        img.removeAttribute("src");
    });
}

/* ------------------------------------------------------------------
   Tab rekomendasi perlengkapan (tektok / camping 1-3+ malam)
   ------------------------------------------------------------------ */
function initGear() {
    const root = document.querySelector("[data-gear]");
    if (!root) return;

    const tabs = [...root.querySelectorAll("[data-gear-tab]")];
    const panels = [...root.querySelectorAll("[data-gear-panel]")];

    const select = (key, focus) => {
        tabs.forEach((t) => {
            const on = t.dataset.gearTab === key;
            t.setAttribute("aria-selected", String(on));
            t.tabIndex = on ? 0 : -1;
            if (on && focus) t.focus();
        });
        panels.forEach((p) => {
            p.hidden = p.dataset.gearPanel !== key;
        });
    };

    tabs.forEach((tab, i) => {
        tab.addEventListener("click", () => select(tab.dataset.gearTab));
        tab.addEventListener("keydown", (e) => {
            if (e.key !== "ArrowRight" && e.key !== "ArrowLeft") return;
            const next =
                (i + (e.key === "ArrowRight" ? 1 : -1) + tabs.length) %
                tabs.length;
            select(tabs[next].dataset.gearTab, true);
            e.preventDefault();
        });
    });
}

/* ------------------------------------------------------------------
   Carousel horizontal (rekomendasi gunung)
   ------------------------------------------------------------------ */
function initCarousel() {
    document.querySelectorAll("[data-carousel]").forEach((root) => {
        const track = root.querySelector("[data-carousel-track]");
        const prev = root.querySelector("[data-carousel-prev]");
        const next = root.querySelector("[data-carousel-next]");
        if (!track) return;

        const step = () => track.clientWidth * 0.8;
        const sync = () => {
            const max = track.scrollWidth - track.clientWidth - 2;
            if (prev) prev.disabled = track.scrollLeft <= 2;
            if (next) next.disabled = track.scrollLeft >= max;
        };

        prev?.addEventListener("click", () =>
            track.scrollBy({
                left: -step(),
                behavior: reduceMotion ? "auto" : "smooth",
            }),
        );
        next?.addEventListener("click", () =>
            track.scrollBy({
                left: step(),
                behavior: reduceMotion ? "auto" : "smooth",
            }),
        );
        track.addEventListener("scroll", sync, { passive: true });
        window.addEventListener("resize", sync);
        sync();
    });
}

paintScenes();
initSectionNav();
initMap();
initLightbox();
initGear();
initCarousel();
