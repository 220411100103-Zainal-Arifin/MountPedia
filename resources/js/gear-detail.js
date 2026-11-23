/* ==========================================================================
   Detail Barang: galeri (ganti foto lewat thumbnail + lightbox) dan
   panah geser untuk "Perlengkapan Serupa". Halaman tetap terbaca tanpa JS.
   ========================================================================== */
import { initLightbox } from "./lightbox.js";
import { dragScroll } from "./drag-scroll.js";

const lightbox = initLightbox();

/* ---------- Galeri: thumbnail mengganti foto utama ---------- */
(() => {
    const stage = document.querySelector("[data-stage]");
    if (!stage) return;

    const img = stage.querySelector("img");
    const thumbs = Array.from(document.querySelectorAll("[data-thumb]"));
    let current = 0;

    const select = (i) => {
        const t = thumbs[i];
        if (!t) return;
        current = i;
        img.src = t.dataset.src;
        img.alt = t.dataset.alt || "";
        thumbs.forEach((b, k) => {
            b.classList.toggle("is-active", k === i);
            b.setAttribute("aria-current", k === i ? "true" : "false");
        });
    };

    thumbs.forEach((t, i) => t.addEventListener("click", () => select(i)));
    // klik foto utama: buka lightbox pada foto yang sedang tampil
    stage.addEventListener("click", () => lightbox && lightbox.open(current));
})();

/* ---------- Perlengkapan serupa: geser horizontal ---------- */
(() => {
    const rail = document.querySelector("[data-rail]");
    if (!rail) return;

    dragScroll(rail);
    const prev = document.querySelector("[data-rail-prev]");
    const next = document.querySelector("[data-rail-next]");
    const step = () => Math.max(240, Math.round(rail.clientWidth * 0.8));

    const sync = () => {
        const max = rail.scrollWidth - rail.clientWidth - 1;
        if (prev) prev.disabled = rail.scrollLeft <= 0;
        if (next) next.disabled = rail.scrollLeft >= max;
    };

    if (prev)
        prev.addEventListener("click", () =>
            rail.scrollBy({ left: -step(), behavior: "smooth" }),
        );
    if (next)
        next.addEventListener("click", () =>
            rail.scrollBy({ left: step(), behavior: "smooth" }),
        );
    rail.addEventListener("scroll", sync, { passive: true });
    window.addEventListener("resize", sync);
    sync();
})();
