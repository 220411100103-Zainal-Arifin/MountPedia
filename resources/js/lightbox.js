/* Lightbox foto berbasis <dialog>, dipakai halaman detail tempat sewa dan detail barang.
   Markup: <dialog data-lightbox data-photos='[{"url":"..","caption":".."}]'> dengan
   [data-lightbox-img], [data-lightbox-caption], [data-lightbox-prev|next|close].
   Pemicu: elemen [data-lightbox-open="indeks"], atau lightbox.open(indeks) dari kode. */
export function initLightbox() {
    const box = document.querySelector("[data-lightbox]");
    if (!box || typeof box.showModal !== "function") return null;

    let photos = [];
    try {
        photos = JSON.parse(box.dataset.photos || "[]");
    } catch (e) {
        photos = [];
    }
    if (!photos.length) return null;

    const img = box.querySelector("[data-lightbox-img]");
    const caption = box.querySelector("[data-lightbox-caption]");
    let index = 0;

    const show = (i) => {
        index = (i + photos.length) % photos.length;
        img.src = photos[index].url;
        img.alt = photos[index].caption || "";
        caption.textContent = photos[index].caption || `${index + 1} / ${photos.length}`;
    };
    const open = (i = 0) => {
        show(i);
        box.showModal();
    };

    document.querySelectorAll("[data-lightbox-open]").forEach((btn) =>
        btn.addEventListener("click", () => open(parseInt(btn.dataset.lightboxOpen, 10) || 0)),
    );
    box.querySelector("[data-lightbox-close]").addEventListener("click", () => box.close());
    box.querySelector("[data-lightbox-prev]").addEventListener("click", () => show(index - 1));
    box.querySelector("[data-lightbox-next]").addEventListener("click", () => show(index + 1));
    box.addEventListener("keydown", (e) => {
        if (e.key === "ArrowLeft") show(index - 1);
        if (e.key === "ArrowRight") show(index + 1);
    });
    // klik di area gelap menutup
    box.addEventListener("click", (e) => {
        if (e.target === box || e.target.classList.contains("lightbox__figure")) box.close();
    });

    return { open };
}
