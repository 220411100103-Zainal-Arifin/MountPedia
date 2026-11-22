
import { sceneFor } from "./scene.js";

(() => {
    "use strict";

    document.querySelectorAll(".mx-card").forEach((card) => {
        const media = card.querySelector(".mx-card__media");
        if (!media) return;

        const photo = card.dataset.image;
        if (photo) {
            media.style.backgroundImage =
                'url("' + photo.replace(/"/g, "%22") + '")';
            return;
        }

        const scene = sceneFor(card.dataset.slug);
        media.style.backgroundImage = 'url("' + scene.uri + '")';
        media.style.backgroundPosition = scene.peakPct + "% 40%";
    });

    const grid = document.querySelector(".masonry");
    if (grid) {
        const RATIOS = [
            ["4 / 5", 4 / 5],
            ["3 / 4", 3 / 4],
            ["1 / 1", 1],
            ["4 / 3", 4 / 3],
        ];
        const items = Array.from(grid.children);
        const initial = items.map((li) => {
            const card = li.querySelector(".mx-card");
            const v = card ? card.style.getPropertyValue("--ratio").trim() : "";
            return Math.max(
                0,
                RATIOS.findIndex((r) => r[0] === v),
            );
        });
        const colCount = () =>
            window.matchMedia("(max-width: 520px)").matches
                ? 1
                : window.matchMedia("(max-width: 980px)").matches
                  ? 2
                  : 3;

        const place = (idx, cols, colW, gap) => {
            const tot = Array(cols).fill(0);
            const cnt = Array(cols).fill(0);
            const at = idx.map((k) => {
                let c = 0;
                for (let j = 1; j < cols; j++) if (tot[j] < tot[c]) c = j;
                tot[c] += colW / RATIOS[k][1];
                cnt[c]++;
                return c;
            });
            const h = tot.map((t, c) => t + gap * Math.max(0, cnt[c] - 1));
            let score = Math.max(...h) - Math.min(...h);
            const last = Array(cols).fill(-1);
            idx.forEach((k, i) => {
                if (last[at[i]] === k) score += 40;
                last[at[i]] = k;
            });
            const limit = Math.ceil(idx.length / 3) + 1;
            RATIOS.forEach((_, k) => {
                const used = idx.filter((v) => v === k).length;
                if (used > limit) score += 30 * (used - limit);
            });
            return { at, score };
        };

        const solve = (cols, colW, gap) => {
            let idx = initial.slice();
            let best = place(idx, cols, colW, gap);
            for (let pass = 0; pass < 4; pass++) {
                let changed = false;
                for (let i = 0; i < idx.length; i++) {
                    for (let k = 0; k < RATIOS.length; k++) {
                        if (k === idx[i]) continue;
                        const t = idx.slice();
                        t[i] = k;
                        const p = place(t, cols, colW, gap);
                        if (p.score < best.score - 0.5) {
                            idx = t;
                            best = p;
                            changed = true;
                        }
                    }
                }
                if (!changed) break;
            }
            return { idx, at: best.at };
        };

        const build = () => {
            const n = items.length;
            const cols = Math.max(1, Math.min(colCount(), n));
            grid.classList.add("masonry--js");
            grid.replaceChildren();
            const gap = parseFloat(getComputedStyle(grid).columnGap) || 0;
            const colW = (grid.clientWidth - gap * (cols - 1)) / cols;

            const { idx, at } =
                n === 1 ? { idx: [0], at: [0] } : solve(cols, colW, gap);
            const lists = Array.from({ length: cols }, () => {
                const col = document.createElement("li");
                col.className = "mx-col";
                col.setAttribute("role", "presentation");
                const list = document.createElement("ul");
                list.className = "mx-col__list";
                list.setAttribute("role", "list");
                col.append(list);
                grid.append(col);
                return list;
            });
            items.forEach((li, i) => {
                li.style.setProperty(
                    "--ratio",
                    n === 1 ? "16 / 9" : RATIOS[idx[i]][0],
                );
                lists[at[i]].append(li);
            });
        };

        let current = colCount();
        build();
        window.addEventListener("resize", () => {
            const c = colCount();
            if (c !== current) {
                current = c;
                build();
            }
        });
    }

    const root = document.querySelector(".explorer");
    const backdrop = document.querySelector(".explorer__backdrop");
    if (root && backdrop) {
        const first = document.querySelector(".mx-card");
        const photo = first && first.dataset.image;
        const setSolid = (color) => root.style.setProperty("--bg-solid", color);

        if (photo) {
            backdrop.style.backgroundImage =
                'url("' + photo.replace(/"/g, "%22") + '")';
            backdrop.style.backgroundPosition = "50% 100%";
            
            const img = new Image();
            img.crossOrigin = "anonymous";
            img.onload = () => {
                try {
                    const c = document.createElement("canvas");
                    c.width = c.height = 32;
                    const ctx = c.getContext("2d");
                    ctx.drawImage(img, 0, 0, 32, 32);
                    const d = ctx.getImageData(0, 29, 32, 3).data;
                    let r = 0,
                        g = 0,
                        b = 0;
                    const n = d.length / 4;
                    for (let i = 0; i < d.length; i += 4) {
                        r += d[i];
                        g += d[i + 1];
                        b += d[i + 2];
                    }
                    setSolid(
                        "rgb(" +
                            [r, g, b]
                                .map((v) => Math.round((v / n) * 0.8))
                                .join(",") +
                            ")",
                    );
                } catch (e) {
                    
                }
            };
            img.src = photo;
        } else {
            const scene = sceneFor(first ? first.dataset.slug : "semeru");
            backdrop.style.backgroundImage = 'url("' + scene.uri + '")';
            backdrop.style.backgroundPosition = scene.peakPct + "% 100%";
            setSolid(scene.bottom);
        }
    }

    const form = document.querySelector("[data-filter-form]");
    if (!form) return;

    const submit = () =>
        form.requestSubmit ? form.requestSubmit() : form.submit();
    form.querySelectorAll("[data-autosubmit]").forEach((el) =>
        el.addEventListener("change", submit),
    );

    
    form.addEventListener("submit", () => {
        form.querySelectorAll("input[name], select[name]").forEach((el) => {
            const isRadio = el.type === "radio";
            if (el.value === "" && (!isRadio || el.checked)) el.disabled = true;
        });
    });
    
    window.addEventListener("pageshow", () =>
        form
            .querySelectorAll("[disabled]")
            .forEach((el) => (el.disabled = false)),
    );
})();
