<style>
    .nisr-page-shell {
        --nisr-ink: #102f3a;
        --nisr-muted: #58717a;
        --nisr-border: rgba(16, 47, 58, 0.1);
        --nisr-panel: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(244, 249, 248, 0.96) 100%);
        --nisr-panel-soft: linear-gradient(180deg, rgba(245, 251, 250, 0.94) 0%, rgba(255, 255, 255, 0.98) 100%);
        --nisr-shadow: 0 1.5rem 3.8rem rgba(16, 56, 62, 0.12);
        --nisr-radius-xl: 2rem;
        --nisr-radius-lg: 1.5rem;
        --nisr-radius-md: 1.1rem;
        --nisr-accent: var(--web-primary, #147a74);
        --nisr-accent-soft: rgba(var(--bs-base-rgb), 0.12);
        --nisr-accent-line: rgba(var(--bs-base-rgb), 0.2);
        padding-block: 1.5rem 4.5rem;
        background:
            radial-gradient(circle at 15% 0%, rgba(var(--bs-base-rgb), 0.08), transparent 32%),
            radial-gradient(circle at 85% 20%, rgba(var(--bs-base-rgb), 0.12), transparent 28%),
            linear-gradient(180deg, #f7fbfa 0%, #eef5f3 100%);
    }

    .nisr-page-shell .container {
        position: relative;
        z-index: 1;
    }

    .nisr-page-hero,
    .nisr-surface,
    .nisr-blog-card {
        border: 1px solid var(--nisr-border);
        box-shadow: var(--nisr-shadow);
    }

    .nisr-page-hero {
        position: relative;
        overflow: hidden;
        padding: clamp(1.8rem, 3vw, 3.3rem);
        border-radius: var(--nisr-radius-xl);
        background: var(--nisr-panel-soft);
        margin-block-end: 1.75rem;
    }

    .nisr-page-hero::before,
    .nisr-page-hero::after {
        content: "";
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
    }

    .nisr-page-hero::before {
        inline-size: clamp(12rem, 22vw, 20rem);
        block-size: clamp(12rem, 22vw, 20rem);
        inset-block-start: -4rem;
        inset-inline-end: -3rem;
        background: radial-gradient(circle, rgba(var(--bs-base-rgb), 0.18) 0%, rgba(var(--bs-base-rgb), 0) 70%);
    }

    .nisr-page-hero::after {
        inline-size: 14rem;
        block-size: 14rem;
        inset-block-end: -7rem;
        inset-inline-start: -4rem;
        background: radial-gradient(circle, rgba(var(--bs-base-rgb), 0.1) 0%, rgba(var(--bs-base-rgb), 0) 72%);
    }

    .nisr-page-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .75rem;
        margin-block-end: 1.1rem;
        color: var(--nisr-accent);
        font-size: .82rem;
        font-weight: 800;
        letter-spacing: .22em;
        text-transform: uppercase;
    }

    .nisr-page-eyebrow::before {
        content: "";
        inline-size: 2.75rem;
        block-size: 1px;
        background: currentColor;
        opacity: .7;
    }

    .nisr-page-title {
        max-inline-size: 12ch;
        margin: 0;
        color: var(--nisr-ink);
        font-size: clamp(2rem, 4vw, 4rem);
        line-height: 1.04;
        font-weight: 700;
        letter-spacing: -.04em;
    }

    .nisr-page-lead {
        max-inline-size: 62ch;
        margin: 1rem 0 0;
        color: var(--nisr-muted);
        font-size: clamp(1rem, 1.6vw, 1.18rem);
        line-height: 1.8;
    }

    .nisr-hero-actions,
    .nisr-inline-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .9rem;
        margin-top: 1.6rem;
    }

    .nisr-stat-pill,
    .nisr-link-pill,
    .nisr-chip {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        min-block-size: 2.8rem;
        padding-inline: 1rem;
        border-radius: 999px;
        border: 1px solid rgba(var(--bs-base-rgb), 0.16);
        background: rgba(255, 255, 255, 0.82);
        color: var(--nisr-ink);
        font-size: .94rem;
        font-weight: 600;
        text-decoration: none;
    }

    .nisr-link-pill:hover,
    .nisr-link-pill:focus-visible,
    .nisr-chip:hover,
    .nisr-chip:focus-visible {
        color: var(--nisr-accent);
        border-color: rgba(var(--bs-base-rgb), 0.28);
        text-decoration: none;
    }

    .nisr-page-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(18rem, .95fr);
        gap: clamp(1rem, 2vw, 1.6rem);
        align-items: start;
    }

    .nisr-page-grid--narrow {
        max-inline-size: 44rem;
        margin-inline: auto;
    }

    .nisr-surface {
        border-radius: var(--nisr-radius-xl);
        background: var(--nisr-panel);
        padding: clamp(1.2rem, 2.4vw, 2rem);
    }

    .nisr-surface--soft {
        background: var(--nisr-panel-soft);
    }

    .nisr-surface-head {
        display: flex;
        flex-direction: column;
        gap: .5rem;
        margin-block-end: 1.5rem;
    }

    .nisr-section-kicker {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        inline-size: 2rem;
        block-size: 2rem;
        border-radius: .75rem;
        background: rgba(var(--bs-base-rgb), 0.12);
        color: var(--nisr-accent);
        font-weight: 700;
    }

    .nisr-section-title {
        margin: 0;
        color: var(--nisr-ink);
        font-size: clamp(1.2rem, 2vw, 1.6rem);
        font-weight: 700;
        line-height: 1.25;
    }

    .nisr-section-copy,
    .nisr-field-note,
    .nisr-mini-card p,
    .nisr-faq-answer,
    .nisr-richtext {
        color: var(--nisr-muted);
        line-height: 1.8;
    }

    .nisr-form-section + .nisr-form-section {
        margin-block-start: 1.5rem;
        padding-block-start: 1.5rem;
        border-top: 1px solid rgba(16, 47, 58, 0.08);
    }

    .nisr-form-section-head {
        display: flex;
        align-items: start;
        gap: .9rem;
        margin-block-end: 1rem;
    }

    .nisr-page-shell label,
    .nisr-page-shell .form-label {
        margin-block-end: .45rem;
        color: var(--nisr-ink);
        font-size: .95rem;
        font-weight: 700;
    }

    .nisr-page-shell .form-control,
    .nisr-page-shell .form-select,
    .nisr-page-shell .custom-select {
        min-block-size: 3.15rem;
        border: 1px solid rgba(16, 47, 58, 0.14);
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.96);
        color: var(--nisr-ink);
        box-shadow: none;
    }

    .nisr-page-shell textarea.form-control {
        min-block-size: 9rem;
    }

    .nisr-page-shell .form-control:focus,
    .nisr-page-shell .form-select:focus,
    .nisr-page-shell .custom-select:focus {
        border-color: rgba(var(--bs-base-rgb), 0.45);
        box-shadow: 0 0 0 .24rem rgba(var(--bs-base-rgb), 0.12);
    }

    .nisr-page-shell .input-group .form-control {
        border-start-end-radius: 0;
        border-end-end-radius: 0;
    }

    .nisr-page-shell .input-group-append .btn,
    .nisr-page-shell .input-group-append .input-group-text {
        min-block-size: 3.15rem;
        border-radius: 0 1rem 1rem 0;
    }

    [dir="rtl"] .nisr-page-shell .input-group-append .btn,
    [dir="rtl"] .nisr-page-shell .input-group-append .input-group-text {
        border-radius: 1rem 0 0 1rem;
    }

    .nisr-page-shell .btn--primary,
    .nisr-page-shell .btn.btn-primary,
    .nisr-page-shell .btn.btn--primary {
        min-block-size: 3.25rem;
        border-radius: 999px;
        padding-inline: 1.45rem;
        box-shadow: 0 1rem 2.2rem rgba(var(--bs-base-rgb), 0.18);
    }

    .nisr-page-shell .btn.btn-outline-primary {
        border-radius: 999px;
    }

    .nisr-submit {
        inline-size: 100%;
    }

    .nisr-alert {
        border: 1px solid rgba(190, 60, 52, 0.15);
        border-radius: 1rem;
        background: rgba(255, 245, 244, 0.95);
        padding: .95rem 1rem;
    }

    .nisr-mini-card {
        padding: 1rem 1.05rem;
        border-radius: var(--nisr-radius-lg);
        border: 1px solid rgba(var(--bs-base-rgb), 0.12);
        background: rgba(255, 255, 255, 0.8);
    }

    .nisr-mini-card strong,
    .nisr-blog-meta {
        color: var(--nisr-ink);
    }

    .nisr-mini-card p {
        margin: .35rem 0 0;
        font-size: .95rem;
    }

    .nisr-checklist {
        display: grid;
        gap: .85rem;
        padding: 0;
        margin: 0;
        list-style: none;
    }

    .nisr-checklist li {
        position: relative;
        padding-inline-start: 1.7rem;
        color: var(--nisr-ink);
        line-height: 1.65;
    }

    .nisr-checklist li::before {
        content: "";
        position: absolute;
        inset-inline-start: 0;
        inset-block-start: .55rem;
        inline-size: .7rem;
        block-size: .7rem;
        border-radius: 999px;
        background: var(--nisr-accent);
        box-shadow: 0 0 0 .35rem rgba(var(--bs-base-rgb), 0.12);
    }

    .nisr-option-grid {
        display: grid;
        gap: .85rem;
    }

    .nisr-option-card {
        display: flex;
        align-items: start;
        gap: .8rem;
        padding: 1rem;
        border: 1px solid rgba(16, 47, 58, 0.1);
        border-radius: 1.15rem;
        background: rgba(255, 255, 255, 0.82);
        cursor: pointer;
    }

    .nisr-option-card input {
        margin-top: .3rem;
    }

    .nisr-option-card__title {
        display: block;
        color: var(--nisr-ink);
        font-weight: 700;
    }

    .nisr-option-card__copy {
        display: block;
        margin-top: .2rem;
        color: var(--nisr-muted);
        font-size: .92rem;
        line-height: 1.6;
    }

    .nisr-faq-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .nisr-faq-item {
        border-radius: var(--nisr-radius-lg);
        border: 1px solid var(--nisr-border);
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 1rem 2.4rem rgba(16, 56, 62, 0.08);
        overflow: hidden;
    }

    .nisr-faq-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        inline-size: 100%;
        padding: 1.15rem 1.2rem;
        border: 0;
        background: transparent;
        color: var(--nisr-ink);
        text-align: start;
        font-size: 1rem;
        font-weight: 700;
    }

    .nisr-faq-icon {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        inline-size: 2rem;
        block-size: 2rem;
        border-radius: 999px;
        border: 1px solid rgba(var(--bs-base-rgb), 0.18);
        color: var(--nisr-accent);
        font-size: 1.15rem;
        transition: transform .24s ease;
    }

    .nisr-faq-trigger:not(.collapsed) .nisr-faq-icon {
        transform: rotate(45deg);
    }

    .nisr-faq-answer {
        padding: 0 1.2rem 1.2rem;
        white-space: pre-line;
    }

    .nisr-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: .85rem;
        min-block-size: 20rem;
        text-align: center;
    }

    .nisr-otp-input {
        text-align: center;
        letter-spacing: .5em;
        font-size: clamp(1.4rem, 3vw, 2rem);
        font-weight: 700;
    }

    .nisr-blog-shell .blog-root-container {
        padding-block: 0 1rem;
    }

    .nisr-blog-toolbar {
        margin-block-end: 1.5rem;
    }

    .nisr-blog-shell .blog-top-nav {
        flex-wrap: nowrap;
        overflow-x: auto;
        padding: 0 .25rem .3rem 0;
        margin: 0;
        list-style: none;
        scrollbar-width: none;
    }

    .nisr-blog-shell .blog-top-nav::-webkit-scrollbar {
        display: none;
    }

    .nisr-blog-shell .blog-top-nav li a {
        display: inline-flex;
        align-items: center;
        min-block-size: 2.8rem;
        border-color: rgba(16, 47, 58, 0.1) !important;
        border-radius: 999px !important;
        background: rgba(255, 255, 255, 0.84);
        color: var(--nisr-ink);
        text-decoration: none;
        white-space: nowrap;
    }

    .nisr-blog-shell .blog-top-nav li.active a {
        border-color: rgba(var(--bs-base-rgb), 0.28) !important;
        background: rgba(var(--bs-base-rgb), 0.12);
        color: var(--nisr-accent);
    }

    .nisr-blog-shell .blog-top-nav_prev-btn,
    .nisr-blog-shell .blog-top-nav_next-btn {
        z-index: 2;
    }

    .nisr-blog-card {
        position: relative;
        overflow: hidden;
        border-radius: var(--nisr-radius-xl);
        background: var(--nisr-panel);
        transition: transform .22s ease, box-shadow .22s ease;
    }

    .nisr-blog-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 1.75rem 3.2rem rgba(16, 56, 62, 0.14);
    }

    .nisr-blog-card__media img {
        inline-size: 100%;
        block-size: auto;
        object-fit: cover;
        aspect-ratio: 16 / 9;
    }

    .nisr-blog-card--feature .nisr-blog-card__media img {
        aspect-ratio: 16 / 8.3;
    }

    .nisr-blog-card__body {
        display: flex;
        flex-direction: column;
        gap: .9rem;
        padding: 1.25rem;
    }

    .nisr-blog-card__title,
    .nisr-blog-card__title a {
        color: var(--nisr-ink);
        text-decoration: none;
    }

    .nisr-blog-card__title {
        margin: 0;
        font-size: clamp(1.15rem, 1.8vw, 1.5rem);
        line-height: 1.3;
        font-weight: 700;
    }

    .nisr-blog-card__excerpt {
        margin: 0;
        color: var(--nisr-muted);
        line-height: 1.75;
    }

    .nisr-blog-card__footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: auto;
        padding-top: .9rem;
        border-top: 1px solid rgba(16, 47, 58, 0.08);
    }

    .nisr-inline-link {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        color: var(--nisr-accent);
        font-weight: 700;
        text-decoration: none;
    }

    .nisr-recent-post-list {
        display: grid;
        gap: .9rem;
    }

    .nisr-recent-post {
        display: grid;
        grid-template-columns: 5rem minmax(0, 1fr);
        gap: .9rem;
        align-items: center;
        text-decoration: none;
    }

    .nisr-recent-post img {
        inline-size: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        border-radius: 1rem;
    }

    .nisr-recent-post__title {
        color: var(--nisr-ink);
        font-weight: 700;
        line-height: 1.45;
    }

    .nisr-article-card img {
        border-radius: 1.45rem;
    }

    .nisr-blog-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .85rem 1.2rem;
        color: var(--nisr-muted);
        font-size: .96rem;
    }

    .nisr-blog-meta a {
        color: var(--nisr-ink);
        font-weight: 700;
        text-decoration: none;
    }

    .nisr-share-list {
        display: flex;
        flex-wrap: wrap;
        gap: .85rem;
        justify-content: center;
    }

    .nisr-share-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        inline-size: 3rem;
        block-size: 3rem;
        border-radius: 999px;
        border: 1px solid rgba(16, 47, 58, 0.1);
        background: rgba(255, 255, 255, 0.86);
    }

    .nisr-share-button img {
        inline-size: 1.35rem;
        block-size: 1.35rem;
    }

    .nisr-richtext {
        font-size: 1rem;
    }

    .nisr-richtext > *:first-child {
        margin-top: 0;
    }

    .nisr-richtext h2,
    .nisr-richtext h3,
    .nisr-richtext h4 {
        color: var(--nisr-ink);
        margin-top: 2rem;
        margin-bottom: .75rem;
        line-height: 1.3;
    }

    .nisr-richtext ul,
    .nisr-richtext ol {
        padding-inline-start: 1.2rem;
    }

    .nisr-richtext a {
        color: var(--nisr-accent);
    }

    @media (max-width: 991.98px) {
        .nisr-page-grid,
        .nisr-faq-grid {
            grid-template-columns: 1fr;
        }

        .nisr-page-hero,
        .nisr-surface {
            border-radius: 1.5rem;
        }
    }

    @media (max-width: 767.98px) {
        .nisr-page-shell {
            padding-block: 1rem 3rem;
        }

        .nisr-page-title {
            max-inline-size: 100%;
        }

        .nisr-page-eyebrow {
            letter-spacing: .16em;
        }

        .nisr-hero-actions,
        .nisr-inline-actions {
            flex-direction: column;
            align-items: stretch;
        }

        .nisr-stat-pill,
        .nisr-link-pill,
        .nisr-chip {
            justify-content: center;
        }

        .nisr-blog-card__body {
            padding: 1rem;
        }

        .nisr-blog-card__footer,
        .nisr-blog-meta {
            flex-direction: column;
            align-items: start;
        }

        .nisr-otp-input {
            letter-spacing: .35em;
        }
    }
</style>
