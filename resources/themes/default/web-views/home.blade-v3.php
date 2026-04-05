@extends('layouts.front-end.app')


@push('css_or_js')
<meta name="robots" content="index, follow">
<meta property="og:image" content="{{$web_config['web_logo']['path']}}" />
<meta property="og:title" content="{{ translate('welcome_to') }} {{$web_config['company_name']}} {{ translate('home') }}" />
<meta property="og:url" content="{{env('APP_URL')}}">
<meta name="description" content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">
<meta property="og:description" content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">
<meta property="twitter:card" content="{{$web_config['web_logo']['path']}}" />
<meta property="twitter:title" content="{{ translate('welcome_to') }} {{$web_config['company_name']}} {{ translate('home') }}" />
<meta property="twitter:url" content="{{env('APP_URL')}}">
<meta property="twitter:description" content="{{ substr(strip_tags(str_replace('&nbsp;', ' ', $web_config['about']->value)), 0, 160) }}">
<link rel="stylesheet" href="{{theme_asset(path: 'public/assets/front-end/css/home.css')}}" />
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/owl.carousel.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/owl.theme.default.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/swiper-bundle.min.css') }}">
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/select2/css/select2.min.css') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════════════════
   NISR HOME v3 — Luxury Automotive Editorial
   ═══════════════════════════════════════════════════════ */
:root {
    --v-teal: #0d9488;
    --v-teal-deep: #0f766e;
    --v-teal-10: rgba(13,148,136,0.10);
    --v-teal-05: rgba(13,148,136,0.05);
    --v-navy: #0a0f1a;
    --v-navy-90: #111827;
    --v-ink: #181f2a;
    --v-ink-70: #374151;
    --v-warm: #6b7280;
    --v-muted: #9ca3af;
    --v-silver: #d1d5db;
    --v-alto: #e5e7eb;
    --v-surface: #fafaf9;
    --v-cream: #fefefe;
    --v-white: #ffffff;
    --v-gold: #b8860b;
    --v-red: #dc2626;
    --v-star: #eab308;
    --v-r: 12px;
    --v-r-lg: 20px;
    --v-r-full: 9999px;
    --v-ease: cubic-bezier(0.22, 1, 0.36, 1);
    --v-fast: 0.25s var(--v-ease);
    --v-mid: 0.45s var(--v-ease);
    --v-slow: 0.7s var(--v-ease);
}

/* --- Reset & Base --- */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
.v3-section { font-family: 'Inter', -apple-system, sans-serif; }
.v3-section img { display: block; max-width: 100%; }
.v3-section a { text-decoration: none; color: inherit; }

/* --- Scroll Reveal --- */
.v3-reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.8s var(--v-ease), transform 0.8s var(--v-ease);
}
.v3-reveal.v3-visible {
    opacity: 1;
    transform: translateY(0);
}

/* --- Shared Typography --- */
.v3-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    color: var(--v-teal);
    line-height: 1;
}
.v3-eyebrow::before {
    content: '';
    width: 32px;
    height: 1px;
    background: currentColor;
}
.v3-heading {
    font-family: 'Playfair Display', Georgia, serif;
    font-weight: 500;
    color: var(--v-ink);
    line-height: 1.15;
    letter-spacing: -0.02em;
}
.v3-sub {
    font-size: clamp(0.9375rem, 1.1vw, 1.0625rem);
    color: var(--v-warm);
    line-height: 1.75;
    font-weight: 400;
}

/* ═══════════════════════════════════════════════════════
   HERO — Full-bleed cinematic
   ═══════════════════════════════════════════════════════ */
.v3-hero {
    position: relative;
    height: clamp(480px, 72vh, 780px);
    overflow: hidden;
    background: var(--v-navy);
}
.v3-hero-track {
    position: absolute;
    inset: 0;
}
.v3-hero-slide {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center 40%;
    opacity: 0;
    transition: opacity 1.2s ease;
}
.v3-hero-slide.v3-active { opacity: 1; }

/* Cinematic gradient — dark left-to-right with teal whisper */
.v3-hero-slide::after {
    content: '';
    position: absolute;
    inset: 0;
    background:
        linear-gradient(to right, rgba(10,15,26,0.92) 0%, rgba(10,15,26,0.72) 45%, rgba(10,15,26,0.25) 100%),
        linear-gradient(to top, rgba(10,15,26,0.6) 0%, transparent 40%);
    z-index: 1;
}

.v3-hero-content {
    position: absolute;
    inset: 0;
    z-index: 2;
    display: flex;
    align-items: center;
    padding: 0 clamp(1.5rem, 5vw, 6rem);
    max-width: 1440px;
    margin: 0 auto;
}
.v3-hero-body {
    max-width: 580px;
}
.v3-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: var(--v-r-full);
    padding: 0.375rem 1rem 0.375rem 0.5rem;
    margin-bottom: 1.75rem;
    font-size: 0.6875rem;
    font-weight: 500;
    color: rgba(255,255,255,0.75);
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.v3-hero-badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--v-teal);
    animation: v3-pulse 2s ease infinite;
}
@keyframes v3-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
.v3-hero h1 {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(2rem, 4.5vw, 3.75rem);
    font-weight: 500;
    color: #fff;
    line-height: 1.1;
    letter-spacing: -0.025em;
    margin-bottom: 1rem;
}
.v3-hero-desc {
    font-size: clamp(0.875rem, 1.1vw, 1rem);
    color: rgba(255,255,255,0.55);
    line-height: 1.7;
    font-weight: 400;
    max-width: 420px;
    margin-bottom: 2rem;
}
.v3-hero-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}
.v3-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'Inter', sans-serif;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #fff;
    padding: 0.8rem 2rem;
    border-radius: var(--v-r-full);
    background: var(--v-teal);
    border: none;
    cursor: pointer;
    transition: all var(--v-mid);
    box-shadow: 0 4px 24px rgba(13,148,136,0.25);
}
.v3-btn-primary:hover {
    background: var(--v-teal-deep);
    box-shadow: 0 6px 32px rgba(13,148,136,0.35);
    transform: translateY(-2px);
    color: #fff;
    text-decoration: none;
}
.v3-btn-primary svg { transition: transform var(--v-mid); }
.v3-btn-primary:hover svg { transform: translateX(3px); }
html[dir="rtl"] .v3-btn-primary:hover svg { transform: translateX(-3px); }

.v3-btn-ghost {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: 'Inter', sans-serif;
    font-size: 0.8125rem;
    font-weight: 500;
    color: rgba(255,255,255,0.7);
    padding: 0.8rem 1.5rem;
    border-radius: var(--v-r-full);
    border: 1px solid rgba(255,255,255,0.15);
    background: transparent;
    cursor: pointer;
    transition: all var(--v-mid);
}
.v3-btn-ghost:hover {
    border-color: rgba(255,255,255,0.35);
    color: #fff;
    background: rgba(255,255,255,0.05);
    text-decoration: none;
}

/* Hero nav dots */
.v3-hero-dots {
    position: absolute;
    bottom: 2.5rem;
    right: clamp(1.5rem, 5vw, 6rem);
    z-index: 10;
    display: flex;
    flex-direction: column;
    gap: 10px;
    list-style: none;
}
.v3-hero-dots li {
    width: 3px;
    height: 24px;
    border-radius: 3px;
    background: rgba(255,255,255,0.2);
    border: none;
    padding: 0;
    cursor: pointer;
    transition: var(--v-mid);
}
.v3-hero-dots li.v3-active {
    background: var(--v-teal);
    height: 36px;
}
html[dir="rtl"] .v3-hero-dots {
    right: auto;
    left: clamp(1.5rem, 5vw, 6rem);
}

/* Hero bottom fade into content */
.v3-hero::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 120px;
    background: linear-gradient(to bottom, transparent, var(--v-cream));
    z-index: 3;
    pointer-events: none;
}

/* ═══════════════════════════════════════════════════════
   TRUSTED — Slim prestige bar
   ═══════════════════════════════════════════════════════ */
.v3-trusted {
    padding: 3.5rem 0;
    text-align: center;
    position: relative;
}
.v3-trusted h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.25rem, 2.5vw, 2rem);
    font-weight: 400;
    color: var(--v-ink);
    margin-bottom: 0.5rem;
}
.v3-trusted h2 em {
    font-style: italic;
    color: var(--v-teal);
}
.v3-trusted p {
    font-size: 0.9375rem;
    color: var(--v-warm);
    max-width: 480px;
    margin: 0 auto;
    line-height: 1.6;
}
.v3-trusted-line {
    width: 48px;
    height: 2px;
    background: var(--v-teal);
    margin: 1.5rem auto 0;
    border-radius: 2px;
}

/* ═══════════════════════════════════════════════════════
   PRODUCTS — Minimal showcase
   ═══════════════════════════════════════════════════════ */
.v3-products {
    padding: clamp(3rem, 6vw, 5.5rem) 0;
    background: var(--v-cream);
}
.v3-products-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 2.5rem;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.v3-products-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.5rem, 2.8vw, 2.25rem);
    font-weight: 500;
    color: var(--v-ink);
    letter-spacing: -0.02em;
}
.v3-products-link {
    font-size: 0.8125rem;
    font-weight: 500;
    color: var(--v-teal);
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: color var(--v-fast);
    white-space: nowrap;
}
.v3-products-link:hover { color: var(--v-teal-deep); }
.v3-products-link svg { transition: transform var(--v-fast); }
.v3-products-link:hover svg { transform: translateX(4px); }
html[dir="rtl"] .v3-products-link:hover svg { transform: translateX(-4px); }

/* Product cards — borderless, image-forward */
.v3-prod-card {
    background: var(--v-white);
    border-radius: var(--v-r-lg);
    overflow: hidden;
    transition: transform var(--v-mid), box-shadow var(--v-mid);
    display: flex;
    flex-direction: column;
}
.v3-prod-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 60px rgba(0,0,0,0.08);
}
.v3-prod-img {
    position: relative;
    background: var(--v-surface);
    padding: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    overflow: hidden;
}
.v3-prod-img img {
    max-height: 170px;
    width: auto;
    object-fit: contain;
    transition: transform 0.6s var(--v-ease);
}
.v3-prod-card:hover .v3-prod-img img { transform: scale(1.06); }
.v3-prod-badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    font-size: 0.625rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 6px;
    z-index: 2;
    letter-spacing: 0.03em;
}
.v3-prod-badge--sale { background: var(--v-red); color: #fff; }
.v3-prod-badge--out { background: var(--v-warm); color: #fff; }
.v3-prod-qv {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(4px);
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    opacity: 0;
    transform: scale(0.85);
    transition: var(--v-fast);
    color: var(--v-ink);
    z-index: 2;
    padding: 0;
}
.v3-prod-card:hover .v3-prod-qv { opacity: 1; transform: scale(1); }
.v3-prod-qv:hover { background: var(--v-teal); color: #fff; }
html[dir="rtl"] .v3-prod-badge { left: auto; right: 0.75rem; }
html[dir="rtl"] .v3-prod-qv { right: auto; left: 0.75rem; }

.v3-prod-body {
    padding: 1rem 1.25rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    flex: 1;
}
.v3-prod-stars {
    display: flex;
    align-items: center;
    gap: 1px;
    color: var(--v-star);
    font-size: 0.6875rem;
}
.v3-prod-stars span {
    color: var(--v-muted);
    font-size: 0.6875rem;
    margin-left: 4px;
    font-weight: 500;
}
.v3-prod-name {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--v-ink);
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color var(--v-fast);
}
.v3-prod-name:hover { color: var(--v-teal); text-decoration: none; }
.v3-prod-price {
    font-size: 0.9375rem;
    font-weight: 700;
    color: var(--v-ink);
}
.v3-prod-old {
    font-size: 0.75rem;
    color: var(--v-muted);
    font-weight: 400;
    text-decoration: line-through;
    margin-right: 6px;
}

/* ═══════════════════════════════════════════════════════
   CATEGORIES — Visual masonry grid
   ═══════════════════════════════════════════════════════ */
.v3-cats {
    padding: clamp(3rem, 6vw, 5.5rem) 0;
}
.v3-cat-card {
    background: var(--v-white);
    border-radius: var(--v-r-lg);
    padding: 2rem 1.5rem;
    text-align: center;
    transition: all var(--v-mid);
    border: 1px solid transparent;
}
.v3-cat-card:hover {
    border-color: rgba(13,148,136,0.15);
    box-shadow: 0 12px 40px rgba(13,148,136,0.06);
    transform: translateY(-4px);
}
.v3-cat-card img {
    height: 100px;
    width: auto;
    max-width: 100%;
    object-fit: contain;
    margin: 0 auto 1rem;
    transition: transform 0.6s var(--v-ease);
}
.v3-cat-card:hover img { transform: scale(1.08); }
.v3-cat-tag {
    display: inline-block;
    font-size: 0.5625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: var(--v-red);
    background: rgba(220,38,38,0.06);
    padding: 0.2rem 0.75rem;
    border-radius: var(--v-r-full);
    margin-bottom: 0.5rem;
}
.v3-cat-name {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--v-ink);
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 0.625rem;
}
.v3-cat-go {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--v-teal);
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: gap var(--v-fast), color var(--v-fast);
}
.v3-cat-card:hover .v3-cat-go { gap: 8px; }
.v3-cat-go:hover { color: var(--v-teal-deep); }

/* ═══════════════════════════════════════════════════════
   WHY US — Dark cinematic
   ═══════════════════════════════════════════════════════ */
.v3-why {
    padding: clamp(4rem, 8vw, 7rem) 0;
    background: var(--v-navy);
    position: relative;
    overflow: hidden;
}
.v3-why::before {
    content: '';
    position: absolute;
    top: -200px;
    right: -200px;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(13,148,136,0.08), transparent 70%);
    pointer-events: none;
}
.v3-why-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2rem;
    position: relative;
    z-index: 1;
}
@media (max-width: 991px) { .v3-why-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 575px) { .v3-why-grid { grid-template-columns: 1fr; } }
.v3-why-card {
    padding: 2.25rem 1.5rem;
    border-radius: var(--v-r-lg);
    border: 1px solid rgba(255,255,255,0.06);
    background: rgba(255,255,255,0.02);
    transition: all var(--v-mid);
}
.v3-why-card:hover {
    border-color: rgba(13,148,136,0.2);
    background: rgba(255,255,255,0.04);
}
.v3-why-num {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    font-weight: 500;
    color: var(--v-teal);
    line-height: 1;
    margin-bottom: 1rem;
    opacity: 0.5;
}
.v3-why-card h3 {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #fff;
    margin-bottom: 0.5rem;
}
.v3-why-card p {
    font-size: 0.8125rem;
    color: rgba(255,255,255,0.45);
    line-height: 1.65;
}

/* ═══════════════════════════════════════════════════════
   FILTER — Floating glass panel
   ═══════════════════════════════════════════════════════ */
.v3-filter {
    padding: clamp(3rem, 6vw, 5rem) 0;
    background: var(--v-cream);
}
.v3-filter-wrap {
    background: var(--v-white);
    border-radius: var(--v-r-lg);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 20px 60px rgba(0,0,0,0.06);
    overflow: hidden;
}
.v3-filter-inner {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 0;
}
@media (max-width: 767px) { .v3-filter-inner { grid-template-columns: 1fr; } }
.v3-filter-hero {
    background: linear-gradient(160deg, var(--v-navy) 0%, #0c3c38 100%);
    padding: clamp(2.5rem, 4vw, 3.5rem);
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.v3-filter-hero h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.5rem, 2.5vw, 2.25rem);
    font-weight: 500;
    color: #fff;
    line-height: 1.2;
    margin-bottom: 0.75rem;
}
.v3-filter-hero p {
    font-size: 0.875rem;
    color: rgba(255,255,255,0.5);
    line-height: 1.7;
}
.v3-filter-form-area {
    padding: clamp(2rem, 3vw, 3rem);
}
.v3-filter-form-area h3 {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--v-ink);
    margin-bottom: 1.5rem;
}
.v3-filter-fields {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.v3-filter-row {
    display: flex;
    gap: 0.875rem;
}
@media (max-width: 480px) { .v3-filter-row { flex-direction: column; } }
.v3-filter-row .v3-filter-field { flex: 1; }
.v3-filter-field { display: flex; flex-direction: column; gap: 0.375rem; }
.v3-filter-label {
    font-size: 0.6875rem;
    font-weight: 600;
    color: var(--v-ink-70);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}
.v3-filter-sel {
    width: 100%;
    padding: 0.65rem 0.875rem;
    border: 1.5px solid var(--v-alto);
    border-radius: var(--v-r);
    font-family: 'Inter', sans-serif;
    font-size: 0.8125rem;
    color: var(--v-ink);
    background: var(--v-cream);
    transition: border-color var(--v-fast);
    appearance: none;
}
.v3-filter-sel:focus {
    outline: none;
    border-color: var(--v-teal);
}
.v3-filter-btn {
    font-family: 'Inter', sans-serif;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #fff;
    padding: 0.7rem 2rem;
    border: none;
    border-radius: var(--v-r);
    background: var(--v-ink);
    cursor: pointer;
    transition: all var(--v-fast);
    margin-top: 0.25rem;
}
.v3-filter-btn:hover { background: var(--v-navy-90); }

/* ═══════════════════════════════════════════════════════
   DEALERS — Magazine layout
   ═══════════════════════════════════════════════════════ */
.v3-dealers {
    padding: clamp(3rem, 6vw, 5.5rem) 0;
}
.v3-dealer-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-bottom: 1.25rem;
}
@media (max-width: 767px) { .v3-dealer-grid { grid-template-columns: 1fr; } }
.v3-dealer-card {
    border-radius: var(--v-r-lg);
    overflow: hidden;
    position: relative;
    min-height: 280px;
    cursor: pointer;
}
.v3-dealer-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    inset: 0;
    transition: transform 0.8s var(--v-ease);
}
.v3-dealer-card:hover img { transform: scale(1.05); }
.v3-dealer-card::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(10,15,26,0.85) 0%, rgba(10,15,26,0.2) 60%);
    z-index: 1;
    transition: background var(--v-mid);
}
.v3-dealer-card:hover::after {
    background: linear-gradient(to top, rgba(10,15,26,0.9) 0%, rgba(10,15,26,0.3) 70%);
}
.v3-dealer-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 2;
    padding: 1.5rem;
}
.v3-dealer-info h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    font-weight: 500;
    color: #fff;
    margin-bottom: 0.375rem;
}
.v3-dealer-info p {
    font-size: 0.8125rem;
    color: rgba(255,255,255,0.6);
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Wide dealer banner */
.v3-dealer-wide {
    border-radius: var(--v-r-lg);
    overflow: hidden;
    position: relative;
    min-height: 220px;
    display: flex;
    align-items: stretch;
}
.v3-dealer-wide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    inset: 0;
    transition: transform 0.8s var(--v-ease);
}
.v3-dealer-wide:hover img { transform: scale(1.03); }
.v3-dealer-wide::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to right, rgba(10,15,26,0.85) 0%, rgba(10,15,26,0.3) 60%);
    z-index: 1;
}
.v3-dealer-wide-info {
    position: relative;
    z-index: 2;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
    max-width: 50%;
}
@media (max-width: 767px) { .v3-dealer-wide-info { max-width: 100%; } }
.v3-dealer-wide-info h3 {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    font-weight: 500;
    color: #fff;
    margin-bottom: 0.375rem;
}
.v3-dealer-wide-info p {
    font-size: 0.8125rem;
    color: rgba(255,255,255,0.6);
    line-height: 1.6;
}

/* ═══════════════════════════════════════════════════════
   WHOLESALER — Bold CTA
   ═══════════════════════════════════════════════════════ */
.v3-wholesale {
    padding: clamp(3rem, 6vw, 5rem) 0;
}
.v3-wholesale-card {
    background: var(--v-navy);
    border-radius: var(--v-r-lg);
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center;
}
.v3-wholesale-card::before {
    content: '';
    position: absolute;
    bottom: -100px;
    right: 10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(13,148,136,0.1), transparent 70%);
    pointer-events: none;
}
@media (max-width: 767px) { .v3-wholesale-card { flex-direction: column; } }
.v3-wholesale-body {
    flex: 1;
    padding: clamp(2.5rem, 5vw, 4rem);
    position: relative;
    z-index: 1;
}
.v3-wholesale-body h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.5rem, 2.8vw, 2.5rem);
    font-weight: 500;
    color: #fff;
    line-height: 1.15;
    margin-bottom: 0.75rem;
}
.v3-wholesale-body p {
    font-size: 0.9375rem;
    color: rgba(255,255,255,0.5);
    line-height: 1.7;
    max-width: 440px;
    margin-bottom: 1.75rem;
}
.v3-wholesale-img {
    flex: 0 0 40%;
    max-width: 400px;
    position: relative;
    z-index: 1;
    padding: 2rem;
}
@media (max-width: 991px) { .v3-wholesale-img { display: none; } }
.v3-wholesale-img img {
    width: 100%;
    object-fit: contain;
    filter: drop-shadow(0 8px 32px rgba(0,0,0,0.3));
}

/* ═══════════════════════════════════════════════════════
   BLOG — Editorial
   ═══════════════════════════════════════════════════════ */
.v3-blog {
    padding: clamp(3rem, 6vw, 5.5rem) 0;
    background: var(--v-cream);
}
.v3-blog-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 1.25rem;
}
@media (max-width: 767px) { .v3-blog-grid { grid-template-columns: 1fr; } }
.v3-blog-feat {
    border-radius: var(--v-r-lg);
    overflow: hidden;
    background: var(--v-white);
    height: 100%;
    transition: box-shadow var(--v-mid);
}
.v3-blog-feat:hover { box-shadow: 0 16px 48px rgba(0,0,0,0.06); }
.v3-blog-feat-img {
    height: 240px;
    overflow: hidden;
}
.v3-blog-feat-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s var(--v-ease);
}
.v3-blog-feat:hover .v3-blog-feat-img img { transform: scale(1.04); }
.v3-blog-feat-body { padding: 1.5rem; }
.v3-blog-feat-title {
    font-family: 'Inter', sans-serif;
    font-size: 1rem;
    font-weight: 600;
    color: var(--v-ink);
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 0.5rem;
    transition: color var(--v-fast);
}
.v3-blog-feat-title:hover { color: var(--v-teal); text-decoration: none; }
.v3-blog-feat-excerpt {
    font-size: 0.8125rem;
    color: var(--v-warm);
    line-height: 1.65;
}
.v3-blog-list { display: flex; flex-direction: column; gap: 1rem; }
.v3-blog-item {
    display: flex;
    background: var(--v-white);
    border-radius: var(--v-r);
    overflow: hidden;
    transition: box-shadow var(--v-mid);
}
.v3-blog-item:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
.v3-blog-item-img {
    flex: 0 0 110px;
    overflow: hidden;
}
.v3-blog-item-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    min-height: 100px;
}
.v3-blog-item-body {
    flex: 1;
    padding: 0.875rem 1rem;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.v3-blog-item-title {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--v-ink);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 0.25rem;
    transition: color var(--v-fast);
}
.v3-blog-item-title:hover { color: var(--v-teal); text-decoration: none; }
.v3-blog-item-excerpt {
    font-size: 0.6875rem;
    color: var(--v-muted);
    line-height: 1.5;
}
.v3-blog-read {
    font-size: 0.6875rem;
    font-weight: 600;
    color: var(--v-teal);
}
.v3-blog-read:hover { color: var(--v-teal-deep); text-decoration: none; }
.v3-blog-more {
    text-align: center;
    margin-top: 2.5rem;
}
.v3-blog-more a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8125rem;
    font-weight: 500;
    color: var(--v-ink);
    padding: 0.65rem 1.75rem;
    border-radius: var(--v-r-full);
    border: 1.5px solid var(--v-alto);
    transition: all var(--v-fast);
}
.v3-blog-more a:hover {
    border-color: var(--v-teal);
    color: var(--v-teal);
    text-decoration: none;
}

/* ═══════════════════════════════════════════════════════
   REVIEWS
   ═══════════════════════════════════════════════════════ */
.v3-reviews {
    padding: clamp(3rem, 6vw, 5.5rem) 0;
}
.v3-review-card {
    background: var(--v-white);
    border-radius: var(--v-r-lg);
    padding: 2rem;
    border: 1px solid var(--v-alto);
    transition: all var(--v-mid);
    height: 100%;
    display: flex;
    flex-direction: column;
}
.v3-review-card:hover {
    border-color: rgba(13,148,136,0.2);
    box-shadow: 0 12px 40px rgba(0,0,0,0.05);
}
.v3-review-top {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.25rem;
}
.v3-review-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
}
.v3-review-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.v3-review-meta h4 {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--v-ink);
    margin-bottom: 2px;
}
.v3-review-stars {
    color: var(--v-star);
    font-size: 0.6875rem;
}
.v3-review-text {
    font-size: 0.8125rem;
    color: var(--v-warm);
    line-height: 1.7;
    flex: 1;
    font-style: italic;
}

/* ═══════════════════════════════════════════════════════
   APP DOWNLOAD
   ═══════════════════════════════════════════════════════ */
.v3-app {
    padding: clamp(3rem, 6vw, 5rem) 0;
}
.v3-app-card {
    background: var(--v-navy);
    border-radius: var(--v-r-lg);
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center;
}
.v3-app-card::before {
    content: '';
    position: absolute;
    top: -150px;
    left: 30%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(13,148,136,0.07), transparent 70%);
    pointer-events: none;
}
@media (max-width: 767px) { .v3-app-card { flex-direction: column; } }
.v3-app-body {
    flex: 1;
    padding: clamp(2.5rem, 5vw, 4rem);
    position: relative;
    z-index: 1;
}
.v3-app-body h2 {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.5rem, 2.8vw, 2.25rem);
    font-weight: 500;
    color: #fff;
    line-height: 1.15;
    margin-bottom: 1.5rem;
}
.v3-app-stores {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}
@media (max-width: 767px) { .v3-app-stores { justify-content: center; } }
.v3-app-store {
    display: inline-block;
    transition: transform var(--v-fast);
}
.v3-app-store:hover { transform: translateY(-2px); }
.v3-app-store img { height: 38px; width: auto; }
.v3-app-mockup {
    flex: 0 0 auto;
    position: relative;
    z-index: 1;
    padding: 2rem;
}
@media (max-width: 991px) { .v3-app-mockup { display: none; } }
.v3-app-mockup img {
    max-height: 16rem;
    object-fit: contain;
    filter: drop-shadow(0 12px 32px rgba(0,0,0,0.3));
}

/* ═══════════════════════════════════════════════════════
   SWIPER OVERRIDES
   ═══════════════════════════════════════════════════════ */
.mySwiperThree .swiper-slide,
.mySwiperOne .swiper-slide { height: auto; }
.mySwiperTwo { padding-bottom: 2rem; }
</style>
@endpush

@section('content')

{{-- ═══════════════════════════════════════════════════════
     HERO — Full-bleed cinematic banner
     ═══════════════════════════════════════════════════════ --}}
@if(isset($sectionData['main_banner']) && $sectionData['main_banner']['is_active'] == 1)
@php
$banners = array_filter($sectionData['main_banner']['data'], fn($banner) => $banner['is_active'] ?? false);
@endphp
@if(count($banners) > 0)
<section class="v3-hero v3-section">
    <div class="v3-hero-track">
        @foreach($banners as $index => $banner)
        <div class="v3-hero-slide {{ $index === 0 ? 'v3-active' : '' }}" style="background-image: url('{{ asset($banner['image']) }}');"></div>
        @endforeach
    </div>
    <div class="v3-hero-content">
        <div class="v3-hero-body">
            <div class="v3-hero-badge">
                <span class="v3-hero-badge-dot"></span>
                Est. 1946
            </div>
            <h1>{{ $banners[0]['heading'] }}</h1>
            <p class="v3-hero-desc">{{ $banners[0]['paragraph'] }}</p>
            <div class="v3-hero-actions">
                <a href="{{ \App\Support\CmsContentSanitizer::sanitizeLink($banners[0]['buttonLink'] ?? '') ?: '#' }}" class="v3-btn-primary">
                    {{ $banners[0]['buttonText'] }}
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('products') }}" class="v3-btn-ghost">{{ translate('Browse Products') }}</a>
            </div>
        </div>
    </div>
    <ol class="v3-hero-dots">
        @foreach($banners as $index => $banner)
        <li data-slide="{{ $index }}" class="{{ $index === 0 ? 'v3-active' : '' }}"></li>
        @endforeach
    </ol>
</section>
@endif
@endif


{{-- ═══════════════════════════════════════════════════════
     TRUSTED — Heritage badge
     ═══════════════════════════════════════════════════════ --}}
@if(
isset($sectionData['trusted_by']) &&
$sectionData['trusted_by']['is_active'] &&
!empty($sectionData['trusted_by']['data']) &&
$sectionData['trusted_by']['data'][0]['is_active']
)
@php $trusted = $sectionData['trusted_by']['data'][0]; @endphp
<section class="v3-trusted v3-section v3-reveal">
    <div class="container">
        <h2>{{ $trusted['heading'] }} <em>{{ $trusted['year'] }}</em></h2>
        <p>{{ $trusted['paragraph'] }}</p>
        <div class="v3-trusted-line"></div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════════════
     PRODUCTS — Clean showcase
     ═══════════════════════════════════════════════════════ --}}
@if(isset($sectionData['products']) && $sectionData['products']['is_active'] == 1 && $products->count() != 0)
@php $trusted = $sectionData['products']['data'][0]; @endphp
@php
$slides = $products;
if($products->count() < 8) { $slides=$products->concat($products); }
@endphp
<section class="v3-products v3-section v3-reveal">
    <div class="container">
        <div class="v3-products-head">
            <h2 class="v3-products-title">{{ $trusted['section_title'] }}</h2>
            <a href="{{ route('products', ['data_from' => 'featured', 'page' => 1]) }}" class="v3-products-link">
                {{ translate('View All') }}
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="swiper mySwiperThree">
            <div class="swiper-wrapper">
                @foreach($slides as $product)
                @php $overallRating = getOverallRating($product->reviews); @endphp
                <div class="swiper-slide">
                    <div class="v3-prod-card">
                        <div class="v3-prod-img">
                            @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                            <span class="v3-prod-badge v3-prod-badge--sale">-{{ getProductPriceByType(product: $product, type: 'discount', result: 'string') }}</span>
                            @endif
                            <a href="{{ route('product', $product->slug) }}">
                                <img src="{{ getStorageImages(path: $product->thumbnail_full_url, type: 'product') }}" alt="{{ $product->name }}">
                            </a>
                            <button class="v3-prod-qv action-product-quick-view" data-product-id="{{ $product->id }}" aria-label="Quick view">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            @if($product->product_type === 'physical' && $product->current_stock <= 0)
                            <span class="v3-prod-badge v3-prod-badge--out">{{ translate('out_of_stock') }}</span>
                            @endif
                        </div>
                        <div class="v3-prod-body">
                            @if($overallRating[0] != 0)
                            <div class="v3-prod-stars">
                                @for($i = 1; $i <= 5; $i++)
                                    @if ($i <= (int)$overallRating[0])<i class="tio-star"></i>
                                    @elseif ($i <= (int)$overallRating[0] + 1 && $overallRating[0] > (int)$overallRating[0])<i class="tio-star-half"></i>
                                    @else<i class="tio-star-outlined"></i>
                                    @endif
                                @endfor
                                <span>({{ count($product->reviews) }})</span>
                            </div>
                            @endif
                            <a href="{{ route('product', $product->slug) }}" class="v3-prod-name">{{ $product->name }}</a>
                            <div>
                                @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                <del class="v3-prod-old">{{ webCurrencyConverter(amount: $product->unit_price) }}</del>
                                @endif
                                <span class="v3-prod-price">{{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════════════
     CATEGORIES
     ═══════════════════════════════════════════════════════ --}}
@if(isset($sectionData['categories']) && $sectionData['categories']['is_active'] == 1)
@php $trustedCategory = $sectionData['categories']['data']; @endphp
@php
$categoryCount = count($categories);
$repeatCount = $categoryCount > 0 ? ($categoryCount < 4 ? ceil(8 / $categoryCount) : 1) : 0;
$repeatedCategories=[];
for ($i=0; $i < $repeatCount; $i++) { foreach ($categories as $category) { $repeatedCategories[]=$category; } }
$finalCategories=array_slice($repeatedCategories, 0, 8);
@endphp
<section class="v3-cats v3-section v3-reveal">
    <div class="container">
        <div style="margin-bottom: 2.5rem;">
            <span class="v3-eyebrow">{{ translate('Categories') }}</span>
            <h2 class="v3-heading" style="font-size: clamp(1.5rem, 2.8vw, 2.25rem); margin-top: 0.75rem;">{{ $trustedCategory['heading'] }}</h2>
        </div>
        <div class="swiper mySwiperOne">
            <div class="swiper-wrapper">
                @foreach($finalCategories as $category)
                <div class="swiper-slide">
                    <div class="v3-cat-card">
                        <a href="{{ route('products', ['category_id' => $category->id, 'data_from' => 'category', 'page' => 1]) }}">
                            <img src="{{ getStorageImages(path:$category->icon_full_url, type:'category') }}" alt="{{ $category->name }}">
                        </a>
                        <span class="v3-cat-tag">{{ translate('Deals') }}</span>
                        <p class="v3-cat-name">{{ strtoupper($category->name) }}</p>
                        <a href="{{ route('products', ['category_id' => $category->id, 'data_from' => 'category', 'page' => 1]) }}" class="v3-cat-go">
                            {{ translate('Shop now') }}
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════════════
     WHY CHOOSE US — Dark cinematic
     ═══════════════════════════════════════════════════════ --}}
@if(isset($sectionData['why_choose_us']) && $sectionData['why_choose_us']['is_active'] == 1)
@php $section = $sectionData['why_choose_us']['data']['section']; @endphp
<section class="v3-why v3-section v3-reveal">
    <div class="container">
        <div style="text-align: center; margin-bottom: 3rem;">
            <span class="v3-eyebrow" style="color: var(--v-teal-muted); justify-content: center;">{{ translate('Why Us') }}</span>
            <h2 class="v3-heading" style="color: #fff; font-size: clamp(1.5rem, 2.8vw, 2.25rem); margin-top: 0.75rem;">{{ $section['title'] }}</h2>
            <p class="v3-sub" style="color: rgba(255,255,255,0.4); max-width: 480px; margin: 0.75rem auto 0;">{!! nl2br(e($section['subtitle'])) !!}</p>
        </div>
        <div class="v3-why-grid">
            @foreach($section['cards'] as $index => $card)
            <div class="v3-why-card">
                <div class="v3-why-num">0{{ $index + 1 }}</div>
                <h3>{{ $card['title'] }}</h3>
                <p>{{ $card['description'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════════════
     FIND YOUR BATTERY
     ═══════════════════════════════════════════════════════ --}}
@if(isset($sectionData['find_perfect_match']) && $sectionData['find_perfect_match']['is_active'] == 1)
@php
    $findPerfectMatchRaw = $sectionData['find_perfect_match']['data'] ?? [];
    $findPerfectMatchFallback = [
        'section_heading' => translate('find_perfect_match'),
        'hero_heading' => translate('find_perfect_match'),
        'hero_description' => translate('shop_by_vehicle_year_make_model'),
        'filter_title' => translate('filter_options'),
        'make_label' => translate('make'),
        'model_label' => translate('model'),
        'year_label' => translate('model_year'),
        'make_placeholder' => translate('select_make'),
        'model_placeholder' => translate('select_model'),
        'year_placeholder' => translate('select_year'),
        'apply_button_text' => translate('apply_filters'),
    ];
    if (is_array($findPerfectMatchRaw) && array_key_exists(0, $findPerfectMatchRaw) && is_array($findPerfectMatchRaw[0])) {
        $legacyHeading = $findPerfectMatchRaw[0]['heading'] ?? $findPerfectMatchFallback['section_heading'];
        $legacyParagraph = $findPerfectMatchRaw[0]['paragraph'] ?? $findPerfectMatchFallback['hero_description'];
        $findPerfectMatchRaw = ['section_heading' => $legacyHeading, 'hero_heading' => $legacyHeading, 'hero_description' => $legacyParagraph];
    }
    $findPerfectMatch = array_merge($findPerfectMatchFallback, is_array($findPerfectMatchRaw) ? $findPerfectMatchRaw : []);
@endphp
<section class="v3-filter v3-section v3-reveal" aria-label="{{ $findPerfectMatch['section_heading'] }}">
    <div class="container">
        <div class="v3-filter-wrap">
            <div class="v3-filter-inner">
                <div class="v3-filter-hero">
                    <h2>{{ $findPerfectMatch['hero_heading'] }}</h2>
                    <p>{{ $findPerfectMatch['hero_description'] }}</p>
                </div>
                <div class="v3-filter-form-area" role="region" aria-labelledby="heading-filters">
                    <h3 id="heading-filters">{{ $findPerfectMatch['filter_title'] }}</h3>
                    <form class="v3-filter-fields" aria-label="{{ translate('vehicle_filter_options') }}" action="{{ route('products') }}" method="GET">
                        <div class="v3-filter-row">
                            <div class="v3-filter-field">
                                <label for="make" class="v3-filter-label">{{ $findPerfectMatch['make_label'] }}</label>
                                <select id="make" name="make" class="v3-filter-sel vehicle-select2">
                                    <option value="">{{ $findPerfectMatch['make_placeholder'] }}</option>
                                    @foreach($makes as $make)
                                    <option value="{{ $make->getRawOriginal('name') }}" data-id="{{ $make->id }}" {{ ($selectedVehicleFilters['make'] ?? null) === $make->getRawOriginal('name') ? 'selected' : '' }}>{{ $make->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="v3-filter-field">
                                <label for="model" class="v3-filter-label">{{ $findPerfectMatch['model_label'] }}</label>
                                <select id="model" name="model" class="v3-filter-sel vehicle-select2" disabled>
                                    <option value="">{{ $findPerfectMatch['model_placeholder'] }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="v3-filter-field">
                            <label for="year" class="v3-filter-label">{{ $findPerfectMatch['year_label'] }}</label>
                            <select id="year" name="year" class="v3-filter-sel vehicle-select2" {{ !empty($selectedVehicleFilters['year']) ? '' : 'disabled' }}>
                                <option value="">{{ $findPerfectMatch['year_placeholder'] }}</option>
                                @foreach($years as $year)
                                <option value="{{ $year->getRawOriginal('year') }}" {{ (string)($selectedVehicleFilters['year'] ?? $currentYear) === (string)$year->getRawOriginal('year') ? 'selected' : '' }}>{{ $year->year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="v3-filter-btn">{{ $findPerfectMatch['apply_button_text'] }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════════════
     WHY JOIN US — Magazine layout
     ═══════════════════════════════════════════════════════ --}}
@if(isset($sectionData['why_join_us']) && $sectionData['why_join_us']['is_active'] == 1)
@php $data = $sectionData['why_join_us']['data']['section']; @endphp
<section class="v3-dealers v3-section v3-reveal">
    <div class="container">
        <div style="margin-bottom: 2.5rem;">
            <span class="v3-eyebrow">{{ translate('Dealers') }}</span>
            <h2 class="v3-heading" style="font-size: clamp(1.5rem, 2.8vw, 2.25rem); margin-top: 0.75rem;">{{ $data['title'] }}</h2>
        </div>
        <div class="v3-dealer-grid">
            @if(isset($data['cards'][0]))
            <div class="v3-dealer-card">
                <img src="{{ asset($data['cards'][0]['image']) }}" alt="{{ $data['cards'][0]['image_alt'] }}">
                <div class="v3-dealer-info">
                    <h3>{{ $data['cards'][0]['title'] }}</h3>
                    <p>{{ $data['cards'][0]['description'] }}</p>
                </div>
            </div>
            @endif
            @if(isset($data['cards'][1]))
            <div class="v3-dealer-card">
                <img src="{{ asset($data['cards'][1]['image']) }}" alt="{{ $data['cards'][1]['image_alt'] }}">
                <div class="v3-dealer-info">
                    <h3>{{ $data['cards'][1]['title'] }}</h3>
                    <p>{{ $data['cards'][1]['description'] }}</p>
                </div>
            </div>
            @endif
        </div>
        @if(isset($data['cards'][2]))
        <div class="v3-dealer-wide">
            <img src="{{ asset($data['cards'][2]['image']) }}" alt="{{ $data['cards'][2]['image_alt'] }}">
            <div class="v3-dealer-wide-info">
                <h3>{{ $data['cards'][2]['title'] }}</h3>
                <p>{{ $data['cards'][2]['description'] }}</p>
            </div>
        </div>
        @endif
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════════════
     WHOLESALER
     ═══════════════════════════════════════════════════════ --}}
@php $wholesalerSection = $sectionData['wholesaler_section'] ?? null; @endphp
@if($wholesalerSection && $wholesalerSection['is_active'] == 1)
@php $data = $wholesalerSection['data']; $buttonText = $data['button_text'] ?? ($data['button']['text'] ?? ''); @endphp
<section class="v3-wholesale v3-section v3-reveal">
    <div class="container">
        <div class="v3-wholesale-card">
            <div class="v3-wholesale-body">
                <h2>{{ $data['title'] }}</h2>
                <p>{{ $data['description'] }}</p>
                <a href="{{ \App\Support\CmsContentSanitizer::sanitizeLink($data['button']['link'] ?? '') ?: '#' }}" class="v3-btn-primary">{{ $buttonText }}</a>
            </div>
            <div class="v3-wholesale-img">
                <img src="{{ asset($data['image']) }}" alt="Wholeseller Image">
            </div>
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════════════
     BLOG
     ═══════════════════════════════════════════════════════ --}}
@if(getWebConfig('blog_feature_active_status') == 1)
@if(isset($sectionData['blog']) && $sectionData['blog']['is_active'] == 1)
@php $trusted = $sectionData['blog']['data']; @endphp
<section class="v3-blog v3-section v3-reveal">
    <div class="container">
        <div style="margin-bottom: 2.5rem;">
            <span class="v3-eyebrow">{{ translate('Blog') }}</span>
            <h2 class="v3-heading" style="font-size: clamp(1.5rem, 2.8vw, 2.25rem); margin-top: 0.75rem;">{{ $trusted['heading'] }}</h2>
        </div>
        <div class="v3-blog-grid">
            @if($latestPosts->count() > 0)
            @php $featured = $latestPosts->first(); @endphp
            <div class="v3-blog-feat">
                <div class="v3-blog-feat-img"><img src="{{ asset('storage/blog/image/' . $featured->image) }}" alt="{{ $featured->heading }}"></div>
                <div class="v3-blog-feat-body">
                    <a href="{{ route('frontend.blog.details', ['slug' => $featured?->slug]) }}" class="v3-blog-feat-title">{{ \Illuminate\Support\Str::limit(strip_tags($featured->title), 80) }}</a>
                    <p class="v3-blog-feat-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($featured->description), 200) }}</p>
                </div>
            </div>
            @endif
            <div class="v3-blog-list">
                @foreach($latestPosts->skip(1)->take(3) as $post)
                <div class="v3-blog-item">
                    <div class="v3-blog-item-img"><img src="{{ asset('storage/blog/image/' . $post->image) }}" alt="{{ $post->heading }}"></div>
                    <div class="v3-blog-item-body">
                        <div>
                            <a href="{{ route('frontend.blog.details', ['slug' => $post?->slug]) }}" class="v3-blog-item-title">{{ \Illuminate\Support\Str::limit(strip_tags($post->title), 50) }}</a>
                            <p class="v3-blog-item-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($post->description), 50) }}</p>
                        </div>
                        <a href="{{ route('frontend.blog.details', ['slug' => $post?->slug]) }}" class="v3-blog-read">{{ translate('Read More') }}</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        <div class="v3-blog-more"><a href="{{ route('frontend.blog.index') }}">{{ translate('Read More') }}</a></div>
    </div>
</section>
@endif
@endif


{{-- ═══════════════════════════════════════════════════════
     REVIEWS
     ═══════════════════════════════════════════════════════ --}}
@if(isset($sectionData['client_review']) && $sectionData['client_review']['is_active'] == 1)
<section class="v3-reviews v3-section v3-reveal">
    <div class="container">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <span class="v3-eyebrow">{{ translate('Reviews') }}</span>
            <h2 class="v3-heading" style="font-size: clamp(1.5rem, 2.8vw, 2.25rem); margin-top: 0.75rem;">{{ translate('What Our Clients Say') }}</h2>
        </div>
        <div class="swiper mySwiperTwo">
            <div class="swiper-wrapper">
                @foreach($sectionData['client_review']['data']['clients'] as $client)
                @php
                $clientImage = $client['image'] ?? '';
                if (\Illuminate\Support\Str::startsWith($clientImage, ['http://', 'https://'])) { $clientImageSrc = $clientImage; }
                else { $clientImageSrc = asset(ltrim($clientImage, '/')); }
                @endphp
                <div class="swiper-slide">
                    <div class="v3-review-card">
                        <div class="v3-review-top">
                            <div class="v3-review-avatar"><img src="{{ $clientImageSrc }}" alt="client-img"></div>
                            <div class="v3-review-meta">
                                <h4>{{ $client['name'] }}</h4>
                                <div class="v3-review-stars">{{ $client['rating'] }}</div>
                            </div>
                        </div>
                        <p class="v3-review-text">"{{ $client['review'] }}"</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif


{{-- ═══════════════════════════════════════════════════════
     DOWNLOAD APP
     ═══════════════════════════════════════════════════════ --}}
@php
$downloadApp = $sectionData['download_app'] ?? null;
$content = $downloadApp['data']['content'] ?? [];
$resolveDownloadImage = function (?string $image) {
    if (empty($image)) return '';
    if (\Illuminate\Support\Str::startsWith($image, ['http://', 'https://'])) return $image;
    $normalized = ltrim($image, '/');
    if (\Illuminate\Support\Str::startsWith($normalized, ['storage/', 'uploads/'])) return asset($normalized);
    return asset('uploads/' . $normalized);
};
@endphp
@if ($downloadApp && $downloadApp['is_active'] == 1)
<section class="v3-app v3-section v3-reveal">
    <div class="container">
        <div class="v3-app-card">
            <div class="v3-app-body">
                <h2>{{ $content['heading']}}</h2>
                <div class="v3-app-stores">
                    @if (!empty($content['android_button']['image']))
                    <a href="{{ $web_config['android']['link'] }}" class="v3-app-store"><img src="{{ $resolveDownloadImage($content['android_button']['image'] ?? '') }}" alt="{{ $content['android_button']['alt'] ?? '' }}"></a>
                    @endif
                    @if (!empty($content['ios_button']['image']))
                    <a href="{{ $web_config['ios']['link'] }}" class="v3-app-store"><img src="{{ $resolveDownloadImage($content['ios_button']['image'] ?? '') }}" alt="{{ $content['ios_button']['alt'] ?? '' }}"></a>
                    @endif
                </div>
            </div>
            @if (!empty($content['mockup_image']['image']))
            <div class="v3-app-mockup"><img src="{{ $resolveDownloadImage($content['mockup_image']['image'] ?? '') }}" alt="{{ $content['mockup_image']['alt'] ?? '' }}"></div>
            @endif
        </div>
    </div>
</section>
@endif


<span id="direction-from-session" data-value="{{ session()->get('direction') }}"></span>

@endsection

@push('script')
<script src="{{theme_asset(path: 'public/assets/front-end/js/owl.carousel.min.js')}}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/home.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/custom-slider.js') }}"></script>
<script src="{{ theme_asset(path: 'public/assets/front-end/js/swiper-bundle.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/select2/js/select2.min.js') }}"></script>

@php($serializedModels = $models->map(function ($model) {
    return ['id' => $model->id, 'make_id' => $model->make_id, 'value' => $model->getRawOriginal('name'), 'label' => $model->name];
})->values())

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Scroll Reveal ──
    const revealObs = new IntersectionObserver((entries) => {
        entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('v3-visible'); revealObs.unobserve(e.target); } });
    }, { threshold: 0.1 });
    document.querySelectorAll('.v3-reveal').forEach(el => revealObs.observe(el));

    // ── Hero Slider ──
    const heroSlides = document.querySelectorAll('.v3-hero-slide');
    const heroDots = document.querySelectorAll('.v3-hero-dots li');
    const heroHeadings = document.querySelectorAll('.v3-hero h1');
    const heroDescs = document.querySelectorAll('.v3-hero-desc');
    const heroCTAs = document.querySelectorAll('.v3-hero-actions a');
    const bannerData = @json($banners);
    let heroIdx = 0, heroTimer;

    function setHeroSlide(idx) {
        heroSlides.forEach((s, i) => s.classList.toggle('v3-active', i === idx));
        heroDots.forEach((d, i) => d.classList.toggle('v3-active', i === idx));
        // Update text content
        if (bannerData[idx]) {
            heroHeadings.forEach(h => h.textContent = bannerData[idx].heading);
            heroDescs.forEach(d => d.textContent = bannerData[idx].paragraph);
            const cta = heroCTAs[0];
            if (cta) {
                cta.textContent = bannerData[idx].buttonText;
                cta.href = bannerData[idx].buttonLink || '#';
            }
        }
        heroIdx = idx;
    }

    function heroAuto() { heroTimer = setInterval(() => setHeroSlide((heroIdx + 1) % heroSlides.length), 6000); }
    heroDots.forEach((d, i) => d.addEventListener('click', () => { clearInterval(heroTimer); setHeroSlide(i); heroAuto(); }));
    if (heroSlides.length) { setHeroSlide(0); heroAuto(); }

    // ── Swipers ──
    new Swiper(".mySwiperOne", { slidesPerView: 4, spaceBetween: 16, loop: true, autoplay: { delay: 2500, disableOnInteraction: false }, breakpoints: { 0: { slidesPerView: 1 }, 640: { slidesPerView: 2 }, 1024: { slidesPerView: 5 } } });
    new Swiper(".mySwiperTwo", { slidesPerView: 1, spaceBetween: 24, breakpoints: { 768: { slidesPerView: 1 }, 992: { slidesPerView: 2 } }, loop: true, autoplay: { delay: 3000 } });
    new Swiper(".mySwiperThree", { slidesPerView: 4, spaceBetween: 16, breakpoints: { 0: { slidesPerView: 1 }, 640: { slidesPerView: 2 }, 1024: { slidesPerView: 4 } }, loop: true, autoplay: { delay: 3000 } });

    // ── Vehicle Filter ──
    const models = @json($serializedModels);
    const modelPlaceholder = @json($findPerfectMatch['model_placeholder'] ?? 'Select Model');
    const selectedMake = @json($selectedVehicleFilters['make'] ?? null);
    const selectedModel = @json($selectedVehicleFilters['model'] ?? null);
    const selectedYear = @json($selectedVehicleFilters['year'] ?? $currentYear);
    function populateHomeModels(makeName, preferredModel = null) {
        const makeId = $('#make option').filter(function() { return $(this).val() === makeName; }).data('id');
        const filtered = models.filter(m => m.make_id == makeId);
        $('#model').empty().prop('disabled', false).append('<option value="">' + modelPlaceholder + '</option>');
        filtered.forEach(m => { const sel = m.value === preferredModel ? 'selected' : ''; $('#model').append(`<option value="${m.value}" ${sel}>${m.label}</option>`); });
        $('#model').trigger('change.select2');
    }
    $('.vehicle-select2').select2({ width: '100%', dir: @json(session('direction') ?? 'ltr') });
    if (selectedMake) populateHomeModels(selectedMake, selectedModel);
    if (selectedModel || selectedYear) $('#year').prop('disabled', false);
    $('#make').on('change', function() {
        const makeId = $(this).find('option:selected').data('id');
        const filtered = models.filter(m => m.make_id == makeId);
        $('#model').empty().prop('disabled', false).append('<option value="">' + modelPlaceholder + '</option>');
        filtered.forEach(m => { $('#model').append(`<option value="${m.value}">${m.label}</option>`); });
        $('#model').val(null).trigger('change');
        $('#year').prop('disabled', true).val(null).trigger('change');
    });
    $('#model').on('change', function() { $('#year').prop('disabled', !$(this).val()); });
});
</script>
@endpush
