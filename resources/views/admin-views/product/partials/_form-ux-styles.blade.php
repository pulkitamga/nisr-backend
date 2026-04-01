<style>
    .product-form-section-block {
        margin-top: 1.5rem;
        scroll-margin-top: 1.5rem;
    }

    .product-form-section-heading {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin-bottom: .75rem;
    }

    .product-form-section-index {
        width: 2rem;
        height: 2rem;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(55, 125, 255, .12);
        color: #377dff;
        font-weight: 700;
        flex-shrink: 0;
    }

    .product-form-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e325c;
        margin: 0;
    }

    .product-form-overview .card-body {
        padding: 1.25rem 1.5rem;
    }

    .product-type-switcher {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
    }

    .product-type-option {
        border: 1px solid #d5deef;
        border-radius: .85rem;
        background: #fff;
        color: #334257;
        padding: .75rem 1rem;
        min-width: 8.5rem;
        font-weight: 600;
        line-height: 1.2;
        transition: all .2s ease;
    }

    .product-type-option.is-active {
        border-color: #377dff;
        background: rgba(55, 125, 255, .08);
        color: #377dff;
        box-shadow: inset 0 0 0 1px rgba(55, 125, 255, .12);
    }

    .product-form-jump-links {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
    }

    .product-form-jump-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .5rem .85rem;
        border-radius: 999px;
        background: #f5f7fb;
        color: #52627c;
        font-size: .875rem;
        font-weight: 600;
        text-decoration: none;
        transition: all .2s ease;
    }

    .product-form-jump-link:hover {
        background: #eaf1ff;
        color: #377dff;
        text-decoration: none;
    }

    .optional-form-card summary {
        list-style: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        cursor: pointer;
    }

    .optional-form-card summary::-webkit-details-marker,
    .optional-form-card summary::marker {
        display: none;
    }

    .optional-form-card .card-body {
        border-top: 1px solid rgba(231, 234, 243, .7);
    }

    .optional-form-card__indicator {
        color: #718096;
        transition: transform .2s ease;
    }

    .optional-form-card[open] .optional-form-card__indicator {
        transform: rotate(90deg);
    }

    html[dir="rtl"] .product-form-section-block .select2-container,
    html[dir="rtl"] .product-form-overview .select2-container,
    body.rtl .product-form-section-block .select2-container,
    body.rtl .product-form-overview .select2-container {
        direction: rtl;
        text-align: right;
    }

    html[dir="rtl"] .product-form-section-block .select2-selection__rendered,
    html[dir="rtl"] .product-form-overview .select2-selection__rendered,
    body.rtl .product-form-section-block .select2-selection__rendered,
    body.rtl .product-form-overview .select2-selection__rendered {
        text-align: right;
    }

    html[dir="rtl"] .product-form-section-block .select2-selection--single .select2-selection__rendered,
    html[dir="rtl"] .product-form-overview .select2-selection--single .select2-selection__rendered,
    body.rtl .product-form-section-block .select2-selection--single .select2-selection__rendered,
    body.rtl .product-form-overview .select2-selection--single .select2-selection__rendered {
        padding-left: 2rem;
        padding-right: .75rem;
    }

    html[dir="rtl"] .product-form-section-block .select2-selection--single .select2-selection__arrow,
    html[dir="rtl"] .product-form-overview .select2-selection--single .select2-selection__arrow,
    body.rtl .product-form-section-block .select2-selection--single .select2-selection__arrow,
    body.rtl .product-form-overview .select2-selection--single .select2-selection__arrow {
        left: .5rem;
        right: auto;
    }

    html[dir="rtl"] .product-form-section-block .select2-search__field,
    html[dir="rtl"] .product-form-overview .select2-search__field,
    body.rtl .product-form-section-block .select2-search__field,
    body.rtl .product-form-overview .select2-search__field {
        text-align: right;
    }

    @media (max-width: 767.98px) {
        .product-form-overview .card-body {
            padding: 1rem;
        }

        .product-type-option {
            flex: 1 1 100%;
            min-width: 0;
        }

        .product-form-section-heading {
            align-items: flex-start;
        }
    }
</style>
