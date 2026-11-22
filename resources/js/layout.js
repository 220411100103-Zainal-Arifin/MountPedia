
(() => {
    "use strict";

    const nav = document.querySelector(".nav");
    const toggle = document.querySelector("[data-menu-toggle]");
    if (!nav || !toggle) return;

    const setMenu = (open) => {
        nav.classList.toggle("is-open", open);
        toggle.setAttribute("aria-expanded", String(open));
        toggle.setAttribute("aria-label", open ? "Tutup menu" : "Buka menu");
        document.documentElement.style.overflow = open ? "hidden" : "";
    };

    toggle.addEventListener("click", () =>
        setMenu(!nav.classList.contains("is-open")),
    );
    nav.querySelectorAll(".nav__links a").forEach((a) =>
        a.addEventListener("click", () => setMenu(false)),
    );
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") setMenu(false);
    });
    window.matchMedia("(min-width: 901px)").addEventListener("change", (e) => {
        if (e.matches) setMenu(false);
    });
})();
