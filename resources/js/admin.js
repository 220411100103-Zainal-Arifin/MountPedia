/* Perilaku shell admin: sidebar (ciut/buka, off-canvas di layar kecil) dan dropdown profil */
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
        /* penyimpanan tidak tersedia */
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
                /* abaikan */
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

    // Form filter di halaman daftar: pilihan langsung memicu pencarian; kolom kosong tidak ikut ke URL
    document.querySelectorAll("[data-filter-form]").forEach((form) => {
        const submit = () =>
            form.requestSubmit ? form.requestSubmit() : form.submit();
        form.querySelectorAll("[data-autosubmit]").forEach((el) =>
            el.addEventListener("change", submit),
        );
        form.addEventListener("submit", () =>
            form.querySelectorAll("input[name], select[name]").forEach((el) => {
                if (el.value === "") el.disabled = true;
            }),
        );
    });
    // tombol Kembali (bfcache) bisa memulihkan halaman dengan kolom yang masih nonaktif
    window.addEventListener("pageshow", () =>
        document
            .querySelectorAll("[data-filter-form] [disabled]")
            .forEach((el) => (el.disabled = false)),
    );

    // Slug otomatis dari nama selama kolom slug belum diubah manual
    const slugSource = document.querySelector("[data-slug-source]");
    const slugTarget = document.querySelector("[data-slug-target]");
    if (slugSource && slugTarget) {
        let touched = slugTarget.value !== "";
        const slugify = (text) =>
            text
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/[^a-z0-9]+/g, "-")
                .replace(/^-+|-+$/g, "");
        slugTarget.addEventListener("input", () => (touched = true));
        slugSource.addEventListener("input", () => {
            if (!touched) slugTarget.value = slugify(slugSource.value);
        });
    }

    // Dialog konfirmasi hapus: tombol [data-delete] mengisi form lalu membuka dialog
    const dialog = document.getElementById("confirm-delete");
    if (dialog && typeof dialog.showModal === "function") {
        const form = dialog.querySelector("[data-delete-form]");
        const nameEl = dialog.querySelector("[data-delete-name]");
        const noteEl = dialog.querySelector("[data-delete-note]");

        document.addEventListener("click", (e) => {
            const trigger = e.target.closest("[data-delete]");
            if (trigger) {
                form.action = trigger.dataset.action;
                nameEl.textContent = trigger.dataset.name || "data ini";
                noteEl.textContent =
                    trigger.dataset.note ||
                    "Tindakan ini tidak bisa dibatalkan.";
                dialog.showModal();
                return;
            }
            if (e.target.closest("[data-delete-cancel]")) dialog.close();
        });
        // klik di luar kotak dialog menutupnya
        dialog.addEventListener("click", (e) => {
            if (e.target === dialog) dialog.close();
        });
    }

    // Notifikasi sukses hilang sendiri
    document
        .querySelectorAll("[data-flash]")
        .forEach((el) => setTimeout(() => el.remove(), 5000));
})();
