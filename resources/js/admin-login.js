
import { sceneFor } from "./scene.js";

(() => {
    "use strict";

    const root = document.body; 

    
    const rgbToHsl = (r, g, b) => {
        r /= 255;
        g /= 255;
        b /= 255;
        const max = Math.max(r, g, b);
        const min = Math.min(r, g, b);
        const l = (max + min) / 2;
        let h = 0;
        let s = 0;
        if (max !== min) {
            const d = max - min;
            s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
            if (max === r) h = (g - b) / d + (g < b ? 6 : 0);
            else if (max === g) h = (b - r) / d + 2;
            else h = (r - g) / d + 4;
            h /= 6;
        }
        return [h, s, l];
    };
    const hslToRgb = (h, s, l) => {
        if (s === 0) {
            const v = Math.round(l * 255);
            return [v, v, v];
        }
        const q = l < 0.5 ? l * (1 + s) : l + s - l * s;
        const p = 2 * l - q;
        const f = (t) => {
            if (t < 0) t += 1;
            if (t > 1) t -= 1;
            if (t < 1 / 6) return p + (q - p) * 6 * t;
            if (t < 1 / 2) return q;
            if (t < 2 / 3) return p + (q - p) * (2 / 3 - t) * 6;
            return p;
        };
        return [f(h + 1 / 3), f(h), f(h - 1 / 3)].map((v) =>
            Math.round(v * 255),
        );
    };

    
    const applyTone = (src) => {
        const img = new Image();
        img.crossOrigin = "anonymous";
        img.onload = () => {
            try {
                const c = document.createElement("canvas");
                c.width = c.height = 32;
                const ctx = c.getContext("2d");
                ctx.drawImage(img, 0, 0, 32, 32);
                const d = ctx.getImageData(8, 8, 16, 16).data;
                let r = 0;
                let g = 0;
                let b = 0;
                const n = d.length / 4;
                for (let i = 0; i < d.length; i += 4) {
                    r += d[i];
                    g += d[i + 1];
                    b += d[i + 2];
                }
                const [h, s] = rgbToHsl(r / n, g / n, b / n);
                const sat = Math.min(0.55, Math.max(0.22, s));
                root.style.setProperty(
                    "--tone-light",
                    hslToRgb(h, sat, 0.6).join(", "),
                );
                root.style.setProperty(
                    "--tone-dark",
                    hslToRgb(h, sat, 0.28).join(", "),
                );
            } catch (e) {
                
            }
        };
        img.src = src;
    };

    
    const bg = document.querySelector("[data-login-bg]");
    if (bg) {
        const photo = bg.dataset.image;
        const show = () =>
            requestAnimationFrame(() => bg.classList.add("is-ready"));

        if (photo) {
            bg.style.backgroundImage =
                'url("' + photo.replace(/"/g, "%22") + '")';
            const img = new Image();
            img.onload = img.onerror = show;
            img.src = photo;
            applyTone(photo);
        } else {
            const scene = sceneFor(bg.dataset.slug);
            bg.style.backgroundImage = 'url("' + scene.uri + '")';
            bg.style.backgroundPosition = scene.peakPct + "% 50%";
            show();
            applyTone(scene.uri);
        }
    }

    const input = document.querySelector("[data-pw]");
    const toggle = document.querySelector("[data-pw-toggle]");
    if (input && toggle) {
        toggle.addEventListener("click", () => {
            const reveal = input.type === "password";
            input.type = reveal ? "text" : "password";
            toggle.classList.toggle("is-on", reveal);
            toggle.setAttribute("aria-pressed", String(reveal));
            toggle.setAttribute(
                "aria-label",
                reveal ? "Sembunyikan password" : "Tampilkan password",
            );
        });
    }
})();
