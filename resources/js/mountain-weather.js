/* ==========================================================================
   Prakiraan cuaca di halaman detail gunung.
   Mengambil JSON dari route `mountains.weather` (server yang memanggil
   Open-Meteo dan men-cache hasilnya), lalu mengisi <template> di Blade.
   Semua class Tailwind ada di Blade, jadi tidak perlu diatur ulang di sini.
   ========================================================================== */
(() => {
    "use strict";

    const root = document.querySelector("[data-weather]");
    if (!root) return;

    const q = (sel) => root.querySelector(sel);
    const clone = (name) =>
        q(`template[data-tpl="${name}"]`).content.firstElementChild.cloneNode(
            true,
        );
    const set = (el, field, text) => {
        const tasrget = el.matches(`[data-f="${field}"]`)
            ? el
            : el.querySelector(`[data-f="${field}"]`);
        if (target) target.textContent = text;
        return target;
    };
    const val = (v, suffix = "") =>
        v === null || v === undefined ? "–" : `${v}${suffix}`;

    const loading = q("[data-weather-loading]");
    const error = q("[data-weather-error]");
    const ready = q("[data-weather-ready]");
    const tabs = q("[data-weather-tabs]");
    const observed = q("[data-weather-observed]");

    let data = null;
    let active = 0;

    const show = (state) => {
        loading.hidden = state !== "loading";
        error.hidden = state !== "error";
        ready.hidden = state !== "ready";
    };

    const renderPoint = (p) => {
        const el = clone("point");
        const c = p.current;

        set(el, "label", `${p.label} · ${p.detail}`);
        set(el, "condition", c.condition);
        set(
            el,
            "gust",
            c.gust !== null ? `Hembusan hingga ${c.gust} km/jam` : "",
        );
        set(el, "temp", val(c.temp));
        set(el, "feels", val(c.feels, "°"));
        set(el, "humidity", val(c.humidity, "%"));
        set(el, "wind", val(c.wind, " km/jam"));

        const list = el.querySelector('[data-f="days"]');
        p.daily.forEach((d) => {
            const row = clone("day");
            set(row, "day", d.label);
            set(row, "condition", d.condition);
            set(row, "range", `${val(d.max)}°/${val(d.min)}°`);
            set(row, "rain", d.rain_pct === null ? "" : `${d.rain_pct}%`);
            list.append(row);
        });
        return el;
    };

    const select = (index, focus = false) => {
        active = index;
        ready.replaceChildren(renderPoint(data.points[index]));
        Array.from(tabs.children).forEach((b, i) => {
            b.setAttribute("aria-selected", i === index ? "true" : "false");
            b.tabIndex = i === index ? 0 : -1;
            if (focus && i === index) b.focus();
        });
    };

    const renderTabs = () => {
        tabs.replaceChildren();
        if (data.points.length < 2) return; // satu titik: tanpa tab
        data.points.forEach((p, i) => {
            const b = clone("tab");
            b.textContent = p.label;
            b.addEventListener("click", () => select(i));
            tabs.append(b);
        });
    };

    tabs.addEventListener("keydown", (e) => {
        if (!data || (e.key !== "ArrowRight" && e.key !== "ArrowLeft")) return;
        e.preventDefault();
        const n = data.points.length;
        select((active + (e.key === "ArrowRight" ? 1 : n - 1)) % n, true);
    });

    const load = async () => {
        show("loading");
        const ctrl = new AbortController();
        const timer = setTimeout(() => ctrl.abort(), 12000);
        try {
            const res = await fetch(root.dataset.url, {
                headers: { Accept: "application/json" },
                signal: ctrl.signal,
            });
            if (!res.ok) throw new Error(String(res.status));
            data = await res.json();
            if (!data.points || !data.points.length) throw new Error("kosong");

            renderTabs();
            select(0);
            observed.textContent = data.observed_at
                ? `Diperbarui ${data.observed_at}.`
                : "";
            show("ready");
        } catch (e) {
            show("error");
        } finally {
            clearTimeout(timer);
        }
    };

    q("[data-weather-retry]").addEventListener("click", load);
    load();
})();
