
(() => {
    "use strict";

    const root = document.querySelector("[data-admin]");
    if (!root) return;

    const KEY = "mp-admin-sidebar";
    const mobile = window.matchMedia("(max-width: 1024px)");

    try {
        if (!mobile.matches && localStorage.getItem(KEY) === "collapsed") {
            root.classList.add("is-collapsed");
        }
    } catch (e) {
        
    }

    const toggle = document.querySelector("[data-sidebar-toggle]");
    toggle &&
        toggle.addEventListener("click", () => {
            if (mobile.matches) {
                root.classList.toggle("is-open");
                return;
            }
            const collapsed = root.classList.toggle("is-collapsed");
            try {
                localStorage.setItem(KEY, collapsed ? "collapsed" : "open");
            } catch (e) {
                
            }
        });

    document
        .querySelectorAll("[data-sidebar-close]")
        .forEach((el) =>
            el.addEventListener("click", () =>
                root.classList.remove("is-open"),
            ),
        );
    mobile.addEventListener("change", () => root.classList.remove("is-open"));

    document.querySelectorAll("[data-dropdown]").forEach((dd) => {
        const btn = dd.querySelector("[data-dropdown-toggle]");
        const menu = dd.querySelector(".dropdown__menu");
        const set = (open) => {
            menu.hidden = !open;
            btn.setAttribute("aria-expanded", String(open));
        };
        btn.addEventListener("click", () => set(menu.hidden));
        document.addEventListener("click", (e) => {
            if (!dd.contains(e.target)) set(false);
        });
        document.addEventListener("keydown", (e) => {
            if (e.key === "Escape") {
                set(false);
                root.classList.remove("is-open");
            }
        });
    });
})();
