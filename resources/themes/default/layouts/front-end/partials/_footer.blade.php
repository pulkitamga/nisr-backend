<style>
    .nisr-footer {
        --nisr-ft-bg: #182228;
        --nisr-ft-card: rgba(255, 255, 255, .08);
        --nisr-ft-text: rgba(255, 255, 255, .96);
        --nisr-ft-muted: rgba(243, 249, 248, .84);
        --nisr-ft-dim: rgba(230, 240, 238, .72);
        --nisr-ft-accent: #57d5c7;
        --nisr-ft-border: rgba(255, 255, 255, .16);
        --nisr-ft-radius: 14px;
        background: var(--nisr-ft-bg);
        color: var(--nisr-ft-text);
        border-top: 2px solid var(--nisr-ft-accent);
    }

    .nisr-footer *,
    .nisr-footer *::before,
    .nisr-footer *::after {
        box-sizing: border-box;
    }

    .nisr-footer a {
        color: inherit;
        text-decoration: none;
    }

    .nisr-footer a:focus-visible,
    .nisr-ft-sub input:focus-visible,
    .nisr-ft-sub button:focus-visible {
        outline: 2px solid rgba(87, 213, 199, .88);
        outline-offset: 3px;
    }

    .nisr-footer img {
        display: block;
        max-width: 100%;
    }

    .nisr-ft-shell {
        width: min(100% - 2.5rem, 1200px);
        margin-inline: auto;
    }

    .nisr-ft-quick {
        border-bottom: 1px solid var(--nisr-ft-border);
    }

    .nisr-ft-quick-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
    }

    .nisr-ft-quick-item {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .7rem;
        padding: 1.2rem 1rem;
        border-inline-end: 1px solid var(--nisr-ft-border);
        color: var(--nisr-ft-muted);
        font-size: .86rem;
        font-weight: 600;
        overflow: hidden;
        transition: color .25s ease, background .25s ease;
    }

    .nisr-ft-quick-item:last-child {
        border-inline-end: none;
    }

    .nisr-ft-quick-item:hover {
        color: var(--nisr-ft-accent);
        background: var(--nisr-ft-card);
    }

    .nisr-ft-quick-item::after {
        content: '';
        position: absolute;
        inset-inline-start: 50%;
        inset-inline-end: 50%;
        bottom: 0;
        height: 2px;
        background: var(--nisr-ft-accent);
        transition: inset-inline-start .3s cubic-bezier(.22, 1, .36, 1), inset-inline-end .3s cubic-bezier(.22, 1, .36, 1);
    }

    .nisr-ft-quick-item:hover::after {
        inset-inline-start: 0;
        inset-inline-end: 0;
    }

    .nisr-ft-quick-item svg,
    .nisr-ft-quick-item i {
        width: 1.1rem;
        height: 1.1rem;
        opacity: .88;
        flex-shrink: 0;
    }

    .nisr-ft-main {
        padding: clamp(2.4rem, 5vw, 3.4rem) 0;
    }

    .nisr-ft-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) minmax(0, 1fr) minmax(0, 1.1fr);
        gap: clamp(1.5rem, 3vw, 2.4rem);
    }

    .nisr-ft-hdr {
        margin: 0 0 1rem;
        color: var(--nisr-ft-text);
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
    }

    .nisr-ft-brand {
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
    }

    .nisr-ft-brand > a img {
        width: auto;
        max-height: 2.45rem;
        object-fit: contain;
        filter: brightness(0) invert(1);
        opacity: .85;
    }

    .nisr-ft-tagline {
        max-width: 22rem;
        margin: 0;
        color: var(--nisr-ft-dim);
        font-size: .86rem;
        line-height: 1.7;
    }

    .nisr-ft-apps {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
    }

    .nisr-ft-apps a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .35rem .55rem;
        background: rgba(255, 255, 255, .96);
        border: 1px solid rgba(255, 255, 255, .18);
        border-radius: 10px;
        box-shadow: 0 10px 24px rgba(0, 0, 0, .18);
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .nisr-ft-apps img {
        height: 2rem;
        width: auto;
        border-radius: 6px;
        opacity: 1;
        transition: transform .2s ease;
    }

    .nisr-ft-apps a:hover {
        background: #ffffff;
        box-shadow: 0 14px 28px rgba(0, 0, 0, .22);
        transform: translateY(-2px);
    }

    .nisr-ft-apps a:hover img {
        transform: translateY(-1px);
    }

    .nisr-ft-links {
        display: grid;
        gap: .55rem;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .nisr-ft-links a {
        position: relative;
        display: inline-flex;
        align-items: center;
        padding-inline-start: 0;
        color: var(--nisr-ft-muted);
        font-size: .88rem;
        transition: color .2s ease, padding-inline-start .2s ease;
    }

    .nisr-ft-links a::before {
        content: '';
        width: 0;
        height: 1.5px;
        margin-inline-end: .45rem;
        background: var(--nisr-ft-accent);
        transition: width .25s cubic-bezier(.22, 1, .36, 1);
        flex-shrink: 0;
    }

    .nisr-ft-links a:hover {
        color: var(--nisr-ft-accent);
        padding-inline-start: .25rem;
    }

    .nisr-ft-links a:hover::before {
        width: .65rem;
    }

    .nisr-ft-newsletter p {
        margin: 0 0 .9rem;
        color: var(--nisr-ft-dim);
        font-size: .84rem;
        line-height: 1.6;
    }

    .nisr-ft-sub {
        display: flex;
        gap: 0;
        border: 1px solid var(--nisr-ft-border);
        border-radius: var(--nisr-ft-radius);
        overflow: hidden;
        transition: border-color .2s ease;
    }

    .nisr-ft-sub:focus-within {
        border-color: var(--nisr-ft-accent);
    }

    .nisr-ft-sub input {
        flex: 1;
        min-width: 0;
        padding: .72rem 1rem;
        background: rgba(255, 255, 255, .04);
        border: none;
        color: var(--nisr-ft-text);
        font-size: .84rem;
        outline: none;
    }

    .nisr-ft-sub input::placeholder {
        color: var(--nisr-ft-muted);
    }

    .nisr-ft-sub button {
        padding: .72rem 1.3rem;
        background: var(--nisr-ft-accent);
        border: none;
        color: #0d2930;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
        white-space: nowrap;
        transition: background .2s ease;
    }

    .nisr-ft-sub button:hover {
        background: #7ae3d8;
    }

    .nisr-ft-divider {
        height: 1px;
        margin: 1.2rem 0;
        border: none;
        background: var(--nisr-ft-border);
    }

    .nisr-ft-contact {
        display: flex;
        flex-wrap: nowrap;
        align-items: center;
        gap: .85rem 1.5rem;
        justify-content: space-between;
    }

    .nisr-ft-contact-item {
        display: inline-grid;
        grid-auto-flow: column;
        grid-auto-columns: max-content;
        align-items: center;
        gap: .6rem;
        color: var(--nisr-ft-muted);
        font-size: .84rem;
        line-height: 1.5;
        transition: color .2s ease;
        width: fit-content;
        justify-content: start;
    }

    .nisr-ft-contact-item--email a {
        display: inline-block;
        white-space: nowrap;
        word-break: normal;
        overflow-wrap: normal;
        direction: ltr;
        unicode-bidi: isolate;
        text-align: start;
    }

    .nisr-ft-contact-item--phone {
        min-inline-size: 13ch;
    }

    .nisr-ft-contact-item--phone a {
        display: inline-block;
        white-space: nowrap;
        word-break: normal;
        overflow-wrap: normal;
        direction: ltr;
        unicode-bidi: isolate;
        text-align: start;
    }

    .nisr-ft-contact-item--email {
        min-inline-size: 26ch;
    }

    .nisr-ft-contact-item:hover {
        color: var(--nisr-ft-accent);
    }

    .nisr-ft-contact-item i,
    .nisr-ft-contact-item svg {
        width: .95rem;
        height: .95rem;
        opacity: .82;
        flex-shrink: 0;
    }

    .nisr-ft-bottom {
        padding: 1.35rem 0;
    }

    .nisr-ft-bottom-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .nisr-ft-copy {
        margin: 0;
        color: var(--nisr-ft-dim);
        font-size: .8rem;
    }

    .nisr-ft-bottom-end {
        display: flex;
        align-items: center;
        gap: 1.4rem;
    }

    .nisr-ft-legal {
        display: flex;
        gap: 1.2rem;
    }

    .nisr-ft-legal a {
        position: relative;
        color: var(--nisr-ft-dim);
        font-size: .8rem;
        transition: color .2s ease;
    }

    .nisr-ft-legal a::after {
        content: '';
        position: absolute;
        inset-inline: 0;
        bottom: -2px;
        height: 1px;
        background: var(--nisr-ft-accent);
        transform: scaleX(0);
        transition: transform .25s ease;
    }

    .nisr-ft-legal a:hover {
        color: var(--nisr-ft-accent);
    }

    .nisr-ft-legal a:hover::after {
        transform: scaleX(1);
    }

    .nisr-ft-social {
        display: flex;
        gap: .5rem;
    }

    .nisr-ft-social a {
        display: grid;
        place-items: center;
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, .1);
        color: var(--nisr-ft-muted);
        font-size: .78rem;
        transition: background .2s ease, color .2s ease, transform .2s ease;
    }

    .nisr-ft-social a:hover {
        background: var(--nisr-ft-accent);
        color: #0d2930;
        transform: translateY(-2px);
    }

    .nisr-ft-social svg {
        width: .85rem;
        height: .85rem;
    }

    @media (max-width: 1023px) {
        .nisr-ft-grid {
            grid-template-columns: 1fr 1fr;
        }

        .nisr-ft-contact {
            flex-wrap: wrap;
            justify-content: flex-start;
            align-items: flex-start;
        }
    }

    @media (max-width: 767px) {
        .nisr-ft-shell {
            width: min(100% - 1.25rem, 1200px);
        }

        .nisr-ft-quick-grid {
            grid-template-columns: repeat(4, 1fr);
        }

        .nisr-ft-quick-item {
            flex-direction: column;
            gap: .4rem;
            padding: 1rem .5rem;
            font-size: .76rem;
        }

        .nisr-ft-grid {
            grid-template-columns: 1fr;
        }

        .nisr-ft-bottom-inner,
        .nisr-ft-bottom-end {
            flex-direction: column;
            text-align: center;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .nisr-ft-quick-item,
        .nisr-ft-quick-item::after,
        .nisr-ft-links a,
        .nisr-ft-links a::before,
        .nisr-ft-apps img,
        .nisr-ft-sub,
        .nisr-ft-sub button,
        .nisr-ft-social a,
        .nisr-ft-legal a,
        .nisr-ft-legal a::after,
        .nisr-ft-contact-item {
            transition: none !important;
        }
    }
</style>

<div class="nisr-footer">
    <div class="nisr-ft-quick">
        <div class="nisr-ft-shell">
            <div class="nisr-ft-quick-grid">
                @if($web_config['business_pages']?->firstWhere('slug', 'about-us'))
                    <a href="{{ route('about-us') }}" class="nisr-ft-quick-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4m0-4h.01M8 12h.01"/></svg>
                        <span>{{ translate('about_us') }}</span>
                    </a>
                @endif

                <a href="{{ route('contacts') }}" class="nisr-ft-quick-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.705 2.81a13.94 13.94 0 0 0 2.81.705c.907.344 1.85.578 2.81.705A2 2 0 0 1 20 7.11v3z"/></svg>
                    <span>{{ translate('contact_Us') }}</span>
                </a>

                <a href="{{ route('helpTopic') }}" class="nisr-ft-quick-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3m.01-6v1"/></svg>
                    <span>{{ translate('FAQ') }}</span>
                </a>

                <a href="{{ route('frontend.blog.index') }}" class="nisr-ft-quick-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    <span>{{ translate('Blog') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="nisr-ft-main">
        <div class="nisr-ft-shell">
            <div class="nisr-ft-grid">
                <div class="nisr-ft-brand">
                    <a href="{{ route('home') }}">
                        <img src="{{ getStorageImages(path: $web_config['footer_logo'], type: 'logo') }}" alt="{{ $web_config['company_name'] }}">
                    </a>
                    @php($footer_description = trim((string) ($web_config['footer_description'] ?? '')))
                    @php($about_text = trim(strip_tags(str_replace(['&nbsp;','&amp;'], [' ','&'], $web_config['about']->value ?? ''))))
                    @php($brand_text = $footer_description !== '' ? $footer_description : $about_text)
                    @if(strlen($brand_text) > 20)
                        <p class="nisr-ft-tagline">{{ str($brand_text)->limit(130) }}</p>
                    @endif
                    @if($web_config['ios']['status'] || $web_config['android']['status'])
                        <div class="nisr-ft-apps">
                            @if($web_config['ios']['status'])
                                <a href="{{ $web_config['ios']['link'] }}" aria-label="{{ translate('download_our_app') }} iOS">
                                    <img src="{{ dynamicAsset(path: 'public/assets/front-end/png/apple_app.png') }}" alt="{{ translate('App_Store') }}">
                                </a>
                            @endif
                            @if($web_config['android']['status'])
                                <a href="{{ $web_config['android']['link'] }}" aria-label="{{ translate('download_our_app') }} Android">
                                    <img src="{{ dynamicAsset(path: 'public/assets/front-end/png/google_app.png') }}" alt="{{ translate('Google_Play') }}">
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <div>
                    <h6 class="nisr-ft-hdr">{{ translate('special') }}</h6>
                    <ul class="nisr-ft-links">
                        @php($flash_deals = \App\Models\FlashDeal::where(['status' => 1, 'deal_type' => 'flash_deal'])->whereDate('start_date', '<=', date('Y-m-d'))->whereDate('end_date', '>=', date('Y-m-d'))->first())
                        @if(isset($flash_deals))
                            <li><a href="{{ route('flash-deals', [$flash_deals['id']]) }}">{{ translate('flash_deal') }}</a></li>
                        @endif
                        <li><a href="{{ route('products', ['data_from' => 'featured', 'page' => 1]) }}">{{ translate('featured_products') }}</a></li>
                        <li><a href="{{ route('products', ['data_from' => 'latest', 'page' => 1]) }}">{{ translate('latest_products') }}</a></li>
                        <li><a href="{{ route('products', ['data_from' => 'best-selling', 'page' => 1]) }}">{{ translate('best_selling_product') }}</a></li>
                        <li><a href="{{ route('products', ['data_from' => 'top-rated', 'page' => 1]) }}">{{ translate('top_rated_product') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h6 class="nisr-ft-hdr">{{ translate('account_&_shipping_info') }}</h6>
                    @if(auth('customer')->check())
                        <ul class="nisr-ft-links">
                            <li><a href="{{ route('user-account') }}">{{ translate('profile_info') }}</a></li>
                            <li><a href="{{ route('track-order.index') }}">{{ translate('track_order') }}</a></li>
                            <li><a href="{{ route('warranty.track.page') }}">{{ translate('warranty') }}</a></li>
                            <li><a href="{{ route('our-policies') }}">{{ translate('our_policies') }}</a></li>
                        </ul>
                    @else
                        <ul class="nisr-ft-links">
                            <li><a href="{{ route('customer.auth.login') }}">{{ translate('profile_info') }}</a></li>
                            <li><a href="{{ route('customer.auth.login') }}">{{ translate('wish_list') }}</a></li>
                            <li><a href="{{ route('track-order.index') }}">{{ translate('track_order') }}</a></li>
                            <li><a href="{{ route('warranty.track.page') }}">{{ translate('warranty') }}</a></li>
                            <li><a href="{{ route('our-policies') }}">{{ translate('our_policies') }}</a></li>
                        </ul>
                    @endif
                </div>

                <div>
                    <h6 class="nisr-ft-hdr">{{ translate('newsletter') }}</h6>
                    <div class="nisr-ft-newsletter">
                        <p>{{ translate('subscribe_to_our_new_channel_to_get_latest_updates') }}</p>
                        <form action="{{ route('subscription') }}" method="post" class="nisr-ft-sub">
                            @csrf
                            <input type="email" name="subscription_email" placeholder="{{ translate('your_Email_Address') }}" required>
                            <button type="submit">{{ translate('subscribe') }}</button>
                        </form>
                    </div>
                    <hr class="nisr-ft-divider">
                    <div class="nisr-ft-contact">
                        <div class="nisr-ft-contact-item nisr-ft-contact-item--phone">
                            <i class="fa fa-phone"></i>
                            <a href="{{ 'tel:' . $web_config['phone'] }}">{{ getWebConfig(name: 'company_phone') }}</a>
                        </div>
                        <div class="nisr-ft-contact-item nisr-ft-contact-item--email">
                            <i class="fa fa-envelope"></i>
                            <a href="{{ 'mailto:' . getWebConfig(name: 'company_email') }}">{{ getWebConfig(name: 'company_email') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="nisr-ft-bottom">
        <div class="nisr-ft-shell">
            <div class="nisr-ft-bottom-inner">
                <p class="nisr-ft-copy">{{ getWebConfig(name: 'company_copyright_text') ?? $web_config['copyright_text'] }}</p>
                <div class="nisr-ft-bottom-end">
                    @if($web_config['social_media'])
                        <div class="nisr-ft-social">
                            @foreach ($web_config['social_media'] as $item)
                                @if ($item->name == "twitter")
                                    <a href="{{ $item->link }}" target="_blank" aria-label="Twitter">
                                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    </a>
                                @else
                                    <a href="{{ $item->link }}" target="_blank" aria-label="{{ $item->name }}">
                                        <i class="{{ $item->icon }}"></i>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <div class="nisr-ft-legal">
                        <a href="{{ route('terms') }}">{{ translate('terms_&_conditions') }}</a>
                        <a href="{{ route('privacy-policy') }}">{{ translate('privacy_policy') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php($cookie = $web_config['cookie_setting'] ? json_decode($web_config['cookie_setting']['value'], true) : null)
    @if($cookie && $cookie['status'] == 1)
        <section id="cookie-section"></section>
    @endif
</div>
