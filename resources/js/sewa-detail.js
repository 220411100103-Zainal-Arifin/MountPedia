/* ==========================================================================
   Detail Tempat Sewa: navigasi sticky (scrollspy), filter katalog,
   lightbox foto, "tampilkan semua ulasan", dan peta Leaflet.
   Semua bagian bersifat tambahan: halaman tetap terbaca tanpa JS.
   Perlu:  npm i leaflet
   ========================================================================== */
import L from "leaflet";
import "leaflet/dist/leaflet.css";

/* ---------- Scrollspy untuk navigasi sticky ---------- */
(() => {
    const links = Array.from(document.querySelectorAll("[data-subnav-link]"));
    if (!links.length || !("IntersectionObserver" in window)) return;

    const byId = new Map(links.map((a) => [a.dataset.subnavLink, a]));
    const setActive = (id) => {
        links.forEach((a) => {
            if (a === byId.get(id)) {
                a.setAttribute("aria-current", "true");
                // jaga tab aktif tetap terlihat pada layar sempit
                a.scrollIntoView({ block: "nearest", inline: "center" });
            } else {
                a.removeAttribute("aria-current");
            }
        });
    };

    const visible = new Set();
    const order = links.map((a) => a.dataset.subnavLink);
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((e) =>
                e.isIntersecting
                    ? visible.add(e.target.id)
                    : visible.delete(e.target.id),
            );
            const current = order.find((id) => visible.has(id));
            if (current) setActive(current);
        },
        { rootMargin: "-20% 0px -65% 0px" },
    );
    order.forEach((id) => {
        const el = document.getElementById(id);
        if (el) observer.observe(el);
    });
})();

/* ---------- Filter katalog ---------- */
(() => {
    const catalog = document.querySelector("[data-catalog]");
    if (!catalog) return;

    const cards = Array.from(catalog.querySelectorAll("[data-item]"));
    const search = document.querySelector("[data-catalog-search]");
    const buttons = Array.from(document.querySelectorAll("[data-cat-btn]"));
    const counter = document.querySelector("[data-catalog-count]");
    const none = document.querySelector("[data-catalog-none]");
    let category = "";

    const apply = () => {
        const q = (search ? search.value : "").trim().toLowerCase();
        let shown = 0;
        cards.forEach((card) => {
            const ok =
                (category === "" || card.dataset.cat === category) &&
                (q === "" || card.dataset.name.includes(q));
            card.hidden = !ok;
            if (ok) shown++;
        });
        if (counter) counter.textContent = String(shown);
        if (none) none.hidden = shown !== 0;
        catalog.hidden = shown === 0;
    };

    buttons.forEach((btn) =>
        btn.addEventListener("click", () => {
            category = btn.dataset.catBtn;
            buttons.forEach((b) => {
                const on = b === btn;
                b.classList.toggle("is-active", on);
                b.setAttribute("aria-pressed", on ? "true" : "false");
            });
            apply();
        }),
    );
    if (search) search.addEventListener("input", apply);
})();

/* ---------- Lightbox foto ---------- */
(() => {
    const box = document.querySelector("[data-lightbox]");
    if (!box || typeof box.showModal !== "function") return;

    let photos = [];
    try {
        photos = JSON.parse(box.dataset.photos || "[]");
    } catch (e) {
        photos = [];
    }
    if (!photos.length) return;

    const img = box.querySelector("[data-lightbox-img]");
    const caption = box.querySelector("[data-lightbox-caption]");
    let index = 0;

    const show = (i) => {
        index = (i + photos.length) % photos.length;
        img.src = photos[index].url;
        img.alt = photos[index].caption || "";
        caption.textContent =
            photos[index].caption || `${index + 1} / ${photos.length}`;
    };

    document.querySelectorAll("[data-lightbox-open]").forEach((btn) =>
        btn.addEventListener("click", () => {
            show(parseInt(btn.dataset.lightboxOpen, 10) || 0);
            box.showModal();
        }),
    );
    box.querySelector("[data-lightbox-close]").addEventListener("click", () =>
        box.close(),
    );
    box.querySelector("[data-lightbox-prev]").addEventListener("click", () =>
        show(index - 1),
    );
    box.querySelector("[data-lightbox-next]").addEventListener("click", () =>
        show(index + 1),
    );
    box.addEventListener("keydown", (e) => {
        if (e.key === "ArrowLeft") show(index - 1);
        if (e.key === "ArrowRight") show(index + 1);
    });
    // klik di area gelap menutup
    box.addEventListener("click", (e) => {
        if (e.target === box || e.target.classList.contains("lightbox__figure"))
            box.close();
    });
})();

/* ---------- Ulasan: tampilkan semua ---------- */
(() => {
    const more = document.querySelector("[data-reviews-more]");
    if (!more) return;
    more.addEventListener("click", () => {
        document
            .querySelectorAll("[data-reviews] [data-extra]")
            .forEach((li) => (li.hidden = false));
        more.remove();
    });
})();

/* ---------- Peta Leaflet ---------- */
(() => {
    const el = document.querySelector("[data-map]");
    if (!el) return;

    const lat = parseFloat(el.dataset.lat);
    const lng = parseFloat(el.dataset.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

    const init = () => {
        const map = L.map(el, {
            scrollWheelZoom: false,
            zoomControl: true,
        }).setView([lat, lng], 14);
        L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
            maxZoom: 19,
            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        }).addTo(map);

        const icon = L.divIcon({
            className: "",
            html: '<div class="map-pin"></div>',
            iconSize: [34, 34],
            iconAnchor: [17, 34],
        });
        L.marker([lat, lng], { icon, title: el.dataset.name || "" }).addTo(map);

        // scroll-zoom baru aktif setelah peta diklik, supaya tidak menjebak scroll halaman
        map.on("click", () => map.scrollWheelZoom.enable());
        map.on("mouseout", () => map.scrollWheelZoom.disable());
    };

    // inisialisasi saat peta mendekati layar
    if ("IntersectionObserver" in window) {
        const io = new IntersectionObserver(
            (entries) => {
                if (entries.some((e) => e.isIntersecting)) {
                    io.disconnect();
                    init();
                }
            },
            { rootMargin: "300px 0px" },
        );
        io.observe(el);
    } else {
        init();
    }
})();
