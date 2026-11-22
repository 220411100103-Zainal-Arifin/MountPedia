/* Ilustrasi pemandangan gunung (SVG) yang dibuat di browser.
   Algoritma dan palet sama dengan hero beranda (home.js), sehingga gunung
   yang sama tampil identik di kedua halaman. Slug yang belum punya palet
   memakai salah satu palet bawaan secara stabil (berdasarkan hash slug). */

const PALETTES = {
    semeru: {
        sky: ["#1d2b4d", "#b4577f", "#ffb36b"],
        sun: "#ffe2b0",
        sunPos: [0.3, 0.6],
        ridge: ["#7a5278", "#5a3b66", "#3a2a4d", "#1d1730"],
        fog: "#ffc7a0",
        plume: "#e3cfc8",
    },
    arjuno: {
        sky: ["#41607a", "#8fb0c2", "#e8e4d4"],
        sun: "#fff6dc",
        sunPos: [0.26, 0.52],
        ridge: ["#94aab2", "#6f8c95", "#496a70", "#243d3d"],
        fog: "#f1f0e6",
    },
    panderman: {
        sky: ["#2f7ab8", "#86c1e6", "#e1f1f4"],
        sun: "#fffbe0",
        sunPos: [0.2, 0.3],
        ridge: ["#86b793", "#549468", "#336f49", "#173f2b"],
        fog: "#ffffff",
    },
    penanggungan: {
        sky: ["#241a4a", "#6a3d86", "#f28d6b"],
        sun: "#ffd1a0",
        sunPos: [0.34, 0.58],
        ridge: ["#7d4a85", "#58336e", "#3a2354", "#1c1233"],
        fog: "#f6a98d",
    },
    rinjani: {
        sky: ["#07122b", "#173a63", "#4d7fa3"],
        moon: "#f4f1e2",
        sunPos: [0.22, 0.2],
        stars: true,
        ridge: ["#35546e", "#223d57", "#142a3f", "#0a1826"],
        fog: "#8fb4cc",
    },
};
const PALETTE_ORDER = [
    "semeru",
    "arjuno",
    "panderman",
    "penanggungan",
    "rinjani",
];

const hash = (str) => {
    let h = 2166136261;
    for (let i = 0; i < str.length; i++) {
        h ^= str.charCodeAt(i);
        h = Math.imul(h, 16777619);
    }
    return h >>> 0;
};

const rng = (seed) => () => {
    seed |= 0;
    seed = (seed + 0x6d2b79f5) | 0;
    let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
    t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
    return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
};

const noise = (r, count) => {
    const v = Array.from({ length: count + 2 }, () => r());
    return (x) => {
        const i = Math.floor(x);
        const f = x - i;
        const s = f * f * (3 - 2 * f);
        return v[i] * (1 - s) + v[i + 1] * s;
    };
};

function ridge(r, o) {
    const n1 = noise(r, 60);
    const n2 = noise(r, 120);
    const n3 = noise(r, 240);
    const pts = [];
    let apex = [0, 1000];
    for (let x = 0; x <= 1600; x += 16) {
        const t = x / 1600;
        let h =
            o.amp *
            (0.55 * n1(t * o.freq) +
                0.3 * n2(t * o.freq * 2.1) +
                0.15 * n3(t * o.freq * 4.3));
        if (o.peakH) {
            const d = Math.abs(x - o.peakX) / o.peakW;
            h +=
                o.peakH *
                Math.pow(Math.max(0, 1 - d), 1.6) *
                (0.9 + 0.2 * n3(t * 30));
        }
        const y = o.base - h;
        if (y < apex[1]) apex = [x, y];
        pts.push(x + " " + y.toFixed(1));
    }
    return { d: "M0 1000 L" + pts.join(" L") + " L1600 1000 Z", apex };
}

// warna solid dasar ilustrasi: ridge terdepan setelah lapisan gelap 18% di dasar
const shade = (hex, k) => {
    const n = parseInt(hex.slice(1), 16);
    const c = [n >> 16, (n >> 8) & 255, n & 255].map((v) => Math.round(v * k));
    return "rgb(" + c.join(",") + ")";
};

/** @returns {{uri: string, peakPct: number, bottom: string}} data-URI SVG, posisi puncak (persen lebar), warna dasar */
export function sceneFor(slug) {
    slug = String(slug || "");
    const key = PALETTES[slug]
        ? slug
        : PALETTE_ORDER[hash(slug) % PALETTE_ORDER.length];
    const pal = PALETTES[key];
    const light = pal.sun || pal.moon;
    const r = rng(hash(slug));
    const peakX = 1600 * (0.6 + r() * 0.2);

    const layers = [
        { base: 650, amp: 150, freq: 5 },
        { base: 730, amp: 190, freq: 4, peakX, peakH: 340, peakW: 430 },
        { base: 810, amp: 140, freq: 6 },
        { base: 890, amp: 110, freq: 7 },
        { base: 965, amp: 70, freq: 9 },
    ];
    const colors = [
        pal.ridge[0],
        pal.ridge[1],
        pal.ridge[2],
        pal.ridge[3],
        pal.ridge[3],
    ];

    let svg =
        '<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="1000" viewBox="0 0 1600 1000" preserveAspectRatio="xMidYMid slice">' +
        "<defs>" +
        `<linearGradient id="sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${pal.sky[0]}"/><stop offset=".55" stop-color="${pal.sky[1]}"/><stop offset="1" stop-color="${pal.sky[2]}"/></linearGradient>` +
        `<radialGradient id="glow"><stop offset="0" stop-color="${light}" stop-opacity=".85"/><stop offset="1" stop-color="${light}" stop-opacity="0"/></radialGradient>` +
        `<linearGradient id="fog" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="${pal.fog}" stop-opacity="0"/><stop offset=".5" stop-color="${pal.fog}" stop-opacity=".5"/><stop offset="1" stop-color="${pal.fog}" stop-opacity="0"/></linearGradient>` +
        '<filter id="b" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="12"/></filter>' +
        "</defs>" +
        '<rect width="1600" height="1000" fill="url(#sky)"/>';

    if (pal.stars) {
        for (let i = 0; i < 130; i++) {
            svg += `<circle cx="${(r() * 1600).toFixed(0)}" cy="${(r() * 520).toFixed(0)}" r="${(0.6 + r() * 1.1).toFixed(2)}" fill="#fff" opacity="${(0.25 + r() * 0.65).toFixed(2)}"/>`;
        }
    }

    const sx = pal.sunPos[0] * 1600;
    const sy = pal.sunPos[1] * 1000;
    svg += `<circle cx="${sx}" cy="${sy}" r="440" fill="url(#glow)" opacity="${pal.moon ? 0.35 : 0.7}"/>`;
    svg += `<circle cx="${sx}" cy="${sy}" r="${pal.moon ? 46 : 84}" fill="${light}"/>`;

    let apex = [peakX, 400];
    layers.forEach((layer, i) => {
        const rd = ridge(r, layer);
        if (layer.peakH) apex = rd.apex;
        svg += `<path d="${rd.d}" fill="${colors[i]}"/>`;
        if (i < 3)
            svg += `<rect x="0" y="${layer.base - 90}" width="1600" height="260" fill="url(#fog)" opacity=".7"/>`;
    });
    svg +=
        '<rect x="0" y="880" width="1600" height="120" fill="#000" opacity=".18"/>';

    // kepulan hanya untuk gunung yang memang punya palet berkepulan (Semeru)
    if (pal.plume && key === slug) {
        for (let i = 0; i < 7; i++) {
            svg += `<circle cx="${(apex[0] + i * 26 + r() * 10).toFixed(0)}" cy="${(apex[1] - 8 - i * 42).toFixed(0)}" r="${24 + i * 15}" fill="${pal.plume}" opacity="${(0.55 - i * 0.06).toFixed(2)}" filter="url(#b)"/>`;
        }
    }
    svg += "</svg>";

    return {
        uri: "data:image/svg+xml;charset=utf-8," + encodeURIComponent(svg),
        peakPct: Math.round(peakX / 16),
        bottom: shade(pal.ridge[3], 0.82),
    };
}
