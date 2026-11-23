/* Geser horizontal dengan drag mouse (sentuhan sudah native).
   Dipakai untuk tab sticky dan rail "Perlengkapan Serupa". */
export function dragScroll(el) {
    if (!el) return;
    let down = false,
        moved = false,
        startX = 0,
        startLeft = 0;

    el.addEventListener("pointerdown", (e) => {
        if (e.pointerType !== "mouse" || e.button !== 0) return;
        down = true;
        moved = false;
        startX = e.clientX;
        startLeft = el.scrollLeft;
    });
    window.addEventListener("pointermove", (e) => {
        if (!down) return;
        const dx = e.clientX - startX;
        if (!moved && Math.abs(dx) > 5) {
            moved = true;
            el.classList.add("is-dragging");
        }
        if (moved) el.scrollLeft = startLeft - dx;
    });
    const end = () => {
        if (!down) return;
        down = false;
        el.classList.remove("is-dragging");
    };
    window.addEventListener("pointerup", end);
    window.addEventListener("pointercancel", end);
    // drag tidak boleh dianggap klik pada link/kartu
    el.addEventListener(
        "click",
        (e) => {
            if (moved) {
                e.preventDefault();
                e.stopPropagation();
                moved = false;
            }
        },
        true,
    );
    el.addEventListener("dragstart", (e) => e.preventDefault());
}
