@props(['rating' => 0, 'size' => 16])
<span
    class="stars"
    style="--pct: {{ max(0, min(5, (float) $rating)) / 5 * 100 }}%; --s: {{ (int) $size }}px"
    role="img"
    aria-label="Rating {{ number_format((float) $rating, 1, ',', '.') }} dari 5"
></span>
