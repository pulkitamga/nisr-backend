    @extends('layouts.front-end.app')

    @section('title', $product['name'])

    @push('css_or_js')
    @include(VIEW_FILE_NAMES['product_seo_meta_content_partials'], ['metaContentData' => $product?->seoInfo, 'productDetails' => $product])
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/product-details.css') }}" />
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/css/service-request.css') }}" />
    <!-- Before closing body tag -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet CSS -->
    <!-- In your layout (if not already included) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    @endpush

    @section('content')
    @include('layouts.front-end.partials._store-header')
    <div class="__inline-23">
        <div class="container mt-4 rtl text-align-direction">
            <div class="row {{Session::get('direction') === "rtl" ? '__dir-rtl' : ''}}">
                <div class="col-lg-9">
                    <div class="row">
                        <div class="col-lg-5 col-md-4">
                            <div class="cz-product-gallery">
                                <div class="cz-preview">
                                    <div id="sync1" class="owl-carousel owl-theme product-thumbnail-slider">
                                        @if($product->images!=null && json_decode($product->images)>0)
                                        @if(json_decode($product->colors) && count($product->color_images_full_url)>0)
                                        @foreach ($product->color_images_full_url as $key => $photo)
                                        @if($photo['color'] != null)
                                        <div
                                            class="product-preview-item d-flex align-items-center justify-content-center {{$key==0?'active':''}}"
                                            id="image{{$photo['color']}}">
                                            <img class="cz-image-zoom img-responsive w-100"
                                                src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}"
                                                data-zoom="{{ getStorageImages(path: $photo['image_name'], type: 'product')  }}"
                                                alt="{{ translate('product') }}" width="">
                                            <div class="cz-image-zoom-pane"></div>
                                        </div>
                                        @else
                                        <div
                                            class="product-preview-item d-flex align-items-center justify-content-center {{$key==0?'active':''}}"
                                            id="image{{$key}}">
                                            <img class="cz-image-zoom img-responsive w-100"
                                                src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}"
                                                data-zoom="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}"
                                                alt="{{ translate('product') }}" width="">
                                            <div class="cz-image-zoom-pane"></div>
                                        </div>
                                        @endif
                                        @endforeach
                                        @else
                                        @foreach ($product->images_full_url as $key => $photo)
                                        <div
                                            class="product-preview-item d-flex align-items-center justify-content-center {{$key==0?'active':''}}"
                                            id="image{{$key}}">
                                            <img class="cz-image-zoom img-responsive w-100"
                                                src="{{ getStorageImages($photo, type: 'product') }}"
                                                data-zoom="{{ getStorageImages(path: $photo, type: 'product') }}"
                                                alt="{{ translate('product') }}" width="">
                                            <div class="cz-image-zoom-pane"></div>
                                        </div>
                                        @endforeach
                                        @endif
                                        @endif
                                    </div>

                                    @if($product?->preview_file_full_url['path'])
                                    <div>
                                        <div class="product-preview-modal-text"
                                            data-toggle="modal"
                                            data-target="#product-preview-modal">
                                            <span class="text-primary fw-bold py-2 user-select-none fs-14">
                                                {{ translate('See_Preview') }}
                                            </span>
                                        </div>
                                    </div>
                                    @endif
                                </div>


                                <div class="cz">
                                    <div class="table-responsive __max-h-515px" data-simplebar>
                                        <div class="d-flex">
                                            <div id="sync2" class="owl-carousel owl-theme product-thumb-slider">
                                                @if($product->images!=null && json_decode($product->images)>0)
                                                @if(json_decode($product->colors) && count($product->color_images_full_url)>0)
                                                @foreach ($product->color_images_full_url as $key => $photo)
                                                @if($photo['color'] != null)
                                                <div class="">
                                                    <a class="product-preview-thumb color-variants-preview-box-{{ $photo['color'] }} {{$key==0?'active':''}} d-flex align-items-center justify-content-center"
                                                        id="preview-img{{$photo['color']}}"
                                                        href="#image{{$photo['color']}}">
                                                        <img alt="{{ translate('product') }}"
                                                            src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}">
                                                    </a>
                                                </div>
                                                @else
                                                <div class="">
                                                    <a class="product-preview-thumb {{$key==0?'active':''}} d-flex align-items-center justify-content-center"
                                                        id="preview-img{{$key}}" href="#image{{$key}}">
                                                        <img alt="{{ translate('product') }}"
                                                            src="{{ getStorageImages(path: $photo['image_name'], type: 'product') }}">
                                                    </a>
                                                </div>
                                                @endif
                                                @endforeach
                                                @else
                                                @foreach ($product->images_full_url as $key => $photo)
                                                <div class="">
                                                    <a class="product-preview-thumb {{$key==0?'active':''}} d-flex align-items-center justify-content-center"
                                                        id="preview-img{{$key}}" href="#image{{$key}}">
                                                        <img alt="{{ translate('product') }}"
                                                            src="{{ getStorageImages(path: $photo, type: 'product') }}">
                                                    </a>
                                                </div>
                                                @endforeach
                                                @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                     @php
                            $locale = app()->getLocale();
                            $serviceTranslations = $product->translations
                                ->where('locale', $locale)
                                ->filter(fn($t) => in_array($t->key, ['service_tittle', 'parts_included']));

                            $serviceTitle = optional($serviceTranslations->firstWhere('key', 'service_tittle'))->value ?? $product->service->title;

                            $partsIncluded = optional($serviceTranslations->firstWhere('key', 'parts_included'))->value;

                            if (empty($partsIncluded)) {
                                $filters = is_array($product->service->parts_included)
                                    ? $product->service->parts_included
                                    : json_decode($product->service->parts_included ?? '[]', true);

                                $partsIncluded = implode(', ', $filters ?? []);
                            }
                        @endphp 
                        <div class="col-lg-7 col-md-8 mt-md-0 mt-sm-3 web-direction">
                            <div class=" __h-100 product-cart-option-container">
                                <div class="card p-3 mb-2">
                                    <h1 class="mb-2 __inline-24">{{ $serviceTitle }}</h1>
                                            <div class="d-flex flex-wrap align-items-center mb-2 pro">
                                        <div class="star-rating me-2">
                                            @for($inc=1;$inc<=5;$inc++)
                                                @if ($inc <=(int)$overallRating[0])
                                                <i class="tio-star text-warning"></i>
                                                @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0]> ((int)$overallRating[0]))
                                                    <i class="tio-star-half text-warning"></i>
                                                    @else
                                                    <i class="tio-star-outlined text-warning"></i>
                                                    @endif
                                                    @endfor
                                        </div>
                                        <span
                                            class="d-inline-block  align-middle mt-1 me-md-2 me-sm-0 fs-14 text-muted">({{$overallRating[0]}})</span>
                                        <span
                                            class="font-regular font-for-tab d-inline-block font-size-sm text-body align-middle mt-1 ms-1 me-md-2 me-1 ps-md-2 ps-sm-1 pe-md-2 pe-sm-1"><span class="web-text-primary">{{$overallRating[1]}}</span> {{translate('reviews')}}</span>
                                    </div>


                                    <div class="mb-2 d-flex align-items-sm-center gap-3">
                                        <div class="product-description-label text-dark font-bold mt-0 text-muted">
                                            {{translate('InShop_price')}} :
                                        </div>
                                        <span class="font-weight-normal text-accent d-flex align-items-end gap-2">
                                            <span class="fs-24 font-bold">
                                                {{ webCurrencyConverter(amount: $product->service->base_price_inshop) }}
                                            </span>
                                            @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                            <del class=" align-middle text-muted fs-18 font-semibold">
                                                {{ webCurrencyConverter(amount: $product->service->base_price_inshop) }}
                                            </del>
                                            @endif
                                        </span>
                                        <!-- <span class="font-weight-normal text-accent d-flex align-items-end gap-2">
                                                <span class="discounted-unit-price fs-24 font-bold">
                                                    {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                                                </span>
                                                @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                                    <del class="product-total-unit-price align-middle text-muted fs-18 font-semibold">
                                                        {{ webCurrencyConverter(amount: $product->unit_price) }}
                                                    </del>
                                                @endif
                                            </span> -->
                                    </div>

                                    <div class="mb-3 d-flex align-items-sm-center gap-3">
                                        <div class="product-description-label text-dark font-bold mt-0 text-muted">
                                            {{translate('InMobile_price')}} :
                                        </div>
                                        <span class="font-weight-normal text-accent d-flex align-items-end gap-2">
                                            <span class="fs-24 font-bold ">
                                                {{ webCurrencyConverter(amount: $product->service->base_price_mobile) }}
                                            </span>
                                            @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                            <del class=" align-middle text-muted fs-18 font-semibold">
                                                {{ webCurrencyConverter(amount: $product->service->base_price_mobile) }}
                                            </del>
                                            @endif
                                        </span>
                                        <!-- <span class="font-weight-normal text-accent d-flex align-items-end gap-2">
                                                <span class="discounted-unit-price fs-24 font-bold">
                                                    {{ getProductPriceByType(product: $product, type: 'discounted_unit_price', result: 'string') }}
                                                </span>
                                                @if(getProductPriceByType(product: $product, type: 'discount', result: 'value') > 0)
                                                    <del class="product-total-unit-price align-middle text-muted fs-18 font-semibold">
                                                        {{ webCurrencyConverter(amount: $product->unit_price) }}
                                                    </del>
                                                @endif
                                            </span> -->
                                    </div>
                                </div>

                              @if(!empty($partsIncluded))
                                            <div class="card p-3 mt-lg-3 mb-2">
                                                <div class="row">
                                                    <div class="col-lg-3">
                                                        <h6 class="font-bold mb-1">{{ translate('parts_include') }}:</h6>
                                                    </div>
                                                    <div class="col-lg-9">
                                                        <p class="mb-0 text-muted">
                                                            {{ $partsIncluded }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                 <div class="card p-3 mt-lg-3">
                                    <div class="row">
                                        <div class="col-lg-3">
                                            <h6 class="font-bold mb-1">{{ translate('charges') }}:</h6>
                                        </div>
                                        <div class="col-lg-9">

                                            <p class="mb-0 text-muted">
                                                {{ translate('free_up_to') }} {{ $product->service->included_km_mobile }} {{ translate('km_then') }} {{ webCurrencyConverter(amount: $product->service->travel_fee_per_km) }} {{ translate('per_extra_km') }}

                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @include('web-views.partials._service-request-experience')

                        </div>
                    </div>
                </div>


                <div class="mt-4 rtl text-align-direction">
                    <div class="px-4 pb-3 mb-3 me-0 me-md-2 bg-white __review-overview __rounded-10 pt-3">
                        <ul class="nav nav-tabs nav--tabs d-flex justify-content-center mt-3"
                            role="tablist">
                            <li class="nav-item">
                                <a class="nav-link __inline-27 active " href="#overview"
                                    data-toggle="tab" role="tab">
                                    {{translate('overview')}}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link __inline-27" href="#reviews" data-toggle="tab"
                                    role="tab">
                                    {{translate('reviews')}}
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content px-lg-3">
                            <div class="tab-pane fade show active text-justify" id="overview"
                                role="tabpanel">
                                <div class="row pt-2 specification">

                                    @if($product->video_url != null && (str_contains($product->video_url, "youtube.com/embed/")))
                                    <div class="col-12 mb-4">
                                        <iframe width="420" height="315"
                                            src="{{$product->video_url}}">
                                        </iframe>
                                    </div>
                                    @endif
                                    @if ($product['details'])
                                    <div class="text-body col-lg-12 col-md-12 overflow-scroll fs-13 text-justify details-text-justify rich-editor-html-content">
                                        {!! $product['details'] !!}
                                    </div>
                                    @endif

                                </div>
                                @if (!$product['details'] && ($product->video_url == null || !(str_contains($product->video_url, "youtube.com/embed/"))))
                                <div>
                                    <div class="text-center text-capitalize py-5">
                                        <img class="mw-90"
                                            src="{{theme_asset(path: 'public/assets/front-end/img/icons/nodata.svg')}}"
                                            alt="">
                                        <p class="text-capitalize mt-2">
                                            <small>{{translate('product_details_not_found')}}
                                                !</small>
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="tab-pane fade" id="reviews" role="tabpanel">
                                @if(count($product->reviews)==0 && $productReviews->total() == 0)
                                <div>
                                    <div class="text-center text-capitalize">
                                        <img class="mw-100"
                                            src="{{theme_asset(path: 'public/assets/front-end/img/icons/empty-review.svg')}}"
                                            alt="">
                                        <p class="text-capitalize">
                                            <small>{{translate('No_review_given_yet')}}!</small>
                                        </p>
                                    </div>
                                </div>
                                @else
                                <div class="row pt-2 pb-3">
                                    <div class="col-lg-4 col-md-5 ">
                                        <div
                                            class=" row d-flex justify-content-center align-items-center">
                                            <div
                                                class="col-12 d-flex justify-content-center align-items-center">
                                                <h2 class="overall_review mb-2 __inline-28">
                                                    {{$overallRating[0]}}
                                                </h2>
                                            </div>
                                            <div class="d-flex justify-content-center align-items-center star-rating ">
                                                @for($inc=1;$inc<=5;$inc++)
                                                    @if ($inc <=(int)$overallRating[0])
                                                    <i class="tio-star text-warning"></i>
                                                    @elseif ($overallRating[0] != 0 && $inc <= (int)$overallRating[0] + 1.1 && $overallRating[0]> ((int)$overallRating[0]))
                                                        <i class="tio-star-half text-warning"></i>
                                                        @else
                                                        <i class="tio-star-outlined text-warning"></i>
                                                        @endif
                                                        @endfor
                                            </div>
                                            <div
                                                class="col-12 d-flex justify-content-center align-items-center mt-2">
                                                <span class="text-center">
                                                    {{$productReviews->total()}} {{translate('ratings')}}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-8 col-md-7 pt-sm-3 pt-md-0">
                                        <div
                                            class="d-flex align-items-center mb-2 font-size-sm">
                                            <div
                                                class="__rev-txt"><span
                                                    class="d-inline-block align-middle text-body">{{translate('excellent')}}</span>
                                            </div>
                                            <div class="w-0 flex-grow">
                                                <div class="progress text-body __h-5px">
                                                    <div class="progress-bar web--bg-primary"
                                                        role="progressbar"
                                                        style="width: <?php echo $widthRating = ($rating[0] != 0) ? ($rating[0] / $overallRating[1]) * 100 : (0); ?>%;"
                                                        aria-valuenow="60" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="col-1 text-body">
                                                <span
                                                    class=" ms-3 float-end ">
                                                    {{$rating[0]}}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="d-flex align-items-center mb-2 text-body font-size-sm">
                                            <div
                                                class="__rev-txt"><span
                                                    class="d-inline-block align-middle ">{{translate('good')}}</span>
                                            </div>
                                            <div class="w-0 flex-grow">
                                                <div class="progress __h-5px">
                                                    <div class="progress-bar web--bg-primary" role="progressbar"
                                                        style="width: <?php echo $widthRating = ($rating[1] != 0) ? ($rating[1] / $overallRating[1]) * 100 : (0); ?>%; background-color: #a7e453;"
                                                        aria-valuenow="27" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="col-1">
                                                <span
                                                    class="ms-3 float-end">
                                                    {{$rating[1]}}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="d-flex align-items-center mb-2 text-body font-size-sm">
                                            <div
                                                class="__rev-txt"><span
                                                    class="d-inline-block align-middle ">{{translate('average')}}</span>
                                            </div>
                                            <div class="w-0 flex-grow">
                                                <div class="progress __h-5px">
                                                    <div class="progress-bar web--bg-primary" role="progressbar"
                                                        style="width: <?php echo $widthRating = ($rating[2] != 0) ? ($rating[2] / $overallRating[1]) * 100 : (0); ?>%; background-color: #ffda75;"
                                                        aria-valuenow="17" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="col-1">
                                                <span
                                                    class="ms-3 float-end">
                                                    {{$rating[2]}}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="d-flex align-items-center mb-2 text-body font-size-sm">
                                            <div
                                                class="__rev-txt "><span
                                                    class="d-inline-block align-middle">{{translate('below_Average')}}</span>
                                            </div>
                                            {{-- Map View --}}
                                            <div class="mt-3">
                                                <label class="form-label">{{ translate('Live') }} {{ translate('Location') }}</label>
                                                <div id="liveLocationMap" style="max-height: 200px; width: 100%; border: 1px solid #ddd; border-radius: 6px;"></div>
                                                <input type="hidden" name="latitude" id="latitude">
                                                <input type="hidden" name="longitude" id="longitude">
                                            </div>

                                            <div class="col-1">
                                                <span
                                                    class="ms-3 float-end">
                                                    {{$rating[3]}}
                                                </span>
                                            </div>
                                        </div>

                                        <div
                                            class="d-flex align-items-center text-body font-size-sm">
                                            <div
                                                class="__rev-txt"><span
                                                    class="d-inline-block align-middle ">{{translate('poor')}}</span>
                                            </div>
                                            <div class="w-0 flex-grow">
                                                <div class="progress __h-5px">
                                                    <div class="progress-bar web--bg-primary" role="progressbar"
                                                        style="width: <?php echo $widthRating = ($rating[4] != 0) ? ($rating[4] / $overallRating[1]) * 100 : (0); ?>%;"
                                                        aria-valuenow="4" aria-valuemin="0"
                                                        aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="col-1">
                                                <span
                                                    class="ms-3 float-end">
                                                    {{$rating[4]}}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row pb-4 mb-3">
                                    <div class="__inline-30">
                                        <span
                                            class="text-capitalize">{{ translate('Product_review') }}</span>
                                    </div>
                                </div>
                                @endif

                                <div class="row pb-4">
                                    <div class="col-12" id="product-review-list">
                                        @include('web-views.partials._product-reviews')
                                    </div>

                                    @if(count($product->reviews) > 2)
                                    <div class="col-12">
                                        <div
                                            class="card-footer d-flex justify-content-center align-items-center">
                                            <button class="btn text-white view_more_button web--bg-primary">
                                                {{ translate('view_more') }}
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                @php($companyReliability = getWebConfig('company_reliability'))
                @if($companyReliability != null)
                <div class="product-details-shipping-details">
                    @foreach ($companyReliability as $key=>$value)
                    @if ($value['status'] == 1 && !empty($value['title']))
                    <div class="shipping-details-bottom-border">
                        <div class="px-3 py-3">
                            <img class="float-end me-2 __img-20"
                                src="{{ getStorageImages(path: imagePathProcessing(imageData: $value['image'],path: 'company-reliability'), type: 'source', source: 'public/assets/front-end/img'.'/'.$value['item'].'.png') }}"
                                alt="">
                            <span>{{translate($value['title'])}}</span>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif

                @if(getWebConfig(name: 'business_mode')=='multi')
                <div class="__inline-31">

                    @if($product->added_by=='seller')
                    @if(isset($product->seller->shop))
                    <div class="row position-relative">
                        <div class="col-12 position-relative">
                            <a href="{{route('shopView',['id'=> $product?->seller?->shop->id])}}" class="d-block">
                                <div class="d-flex __seller-author align-items-center">
                                    <div>
                                        <img class="__img-60 img-circle" alt=""
                                            src="{{ getStorageImages(path: $product?->seller?->shop->image_full_url, type: 'shop') }}">
                                    </div>
                                    <div
                                        class="ms-2 w-0 flex-grow">
                                        <h6>
                                            {{$product->seller->shop->name}}
                                        </h6>
                                        <span class="text-capitalize">{{translate('vendor_info')}}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">

                                    @if($sellerTemporaryClose || ($product->seller->shop->vacation_status && $currentDate >= $sellerVacationStartDate && $currentDate <= $sellerVacationEndDate))
                                        <span class="chat-seller-info product-details-seller-info"
                                        data-toggle="tooltip"
                                        title="{{ translate('this_shop_is_temporary_closed_or_on_vacation').' '.translate('You_cannot_add_product_to_cart_from_this_shop_for_now') }}">
                                        <img src="{{theme_asset(path: 'public/assets/front-end/img/info.png')}}" alt="i">
                                        </span>
                                        @endif
                                </div>
                            </a>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="row d-flex justify-content-between">
                                <div class="col-6 ">
                                    <div
                                        class="d-flex justify-content-center align-items-center rounded __h-79px hr-right-before">
                                        <div class="text-center">
                                            <img src="{{theme_asset(path: 'public/assets/front-end/img/rating.svg')}}"
                                                class="mb-2" alt="">
                                            <div class="__text-12px text-base">
                                                <strong>{{$totalReviews}}</strong> {{translate('reviews')}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div
                                        class="d-flex justify-content-center align-items-center rounded __h-79px">
                                        <div class="text-center">
                                            <img
                                                src="{{theme_asset(path: 'public/assets/front-end/img/products.svg')}}"
                                                class="mb-2" alt="">
                                            <div class="__text-12px text-base">
                                                <strong>{{$productsForReview->total()}}</strong> {{translate('products')}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 position-static mt-3">
                            <div class="chat_with_seller-buttons">
                                @if (auth('customer')->id())
                                <button class="btn w-100 d-block text-center web--bg-primary text-white"
                                    data-toggle="modal"
                                    data-target="#chatting_modal" {{ ($product->seller->shop->temporary_close || ($product->seller->shop->vacation_status && date('Y-m-d') >= date('Y-m-d', strtotime($product->seller->shop->vacation_start_date)) && date('Y-m-d') <= date('Y-m-d', strtotime($product->seller->shop->vacation_end_date)))) ? 'disabled' : '' }}>
                                    <img class="mb-1" alt=""
                                        src="{{theme_asset(path: 'public/assets/front-end/img/chat-16-filled-icon.png')}}">
                                    <span class="d-none d-sm-inline-block text-capitalize">
                                        {{translate('chat_with_vendor')}}
                                    </span>
                                </button>
                                @else
                                <a href="{{route('customer.auth.login')}}"
                                    class="btn w-100 d-block text-center web--bg-primary text-white">
                                    <img src="{{theme_asset(path: 'public/assets/front-end/img/chat-16-filled-icon.png')}}"
                                        class="mb-1" alt="">
                                    <span class="d-none d-sm-inline-block text-capitalize">{{translate('chat_with_vendor')}}</span>
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="row position-relative d-flex justify-content-between">
                        <div class="col-9">
                            <a href="{{route('shopView',[0])}}" class="row d-flex ">
                                <div>
                                    <img class="__inline-32" alt=""
                                        src="{{ getStorageImages(path:$web_config['fav_icon'], type: 'logo') }}">
                                </div>
                                <div class="{{Session::get('direction') === "rtl" ? 'right' : 'mt-3 ms-2'}} get-view-by-onclick"
                                    data-link="{{ route('shopView',[0]) }}">
                                    <span class="font-bold __text-16px">
                                        {{$web_config['company_name']}}
                                    </span><br>
                                </div>

                                @if($product->added_by == 'admin' && ($inHouseTemporaryClose || ($inHouseVacationStatus && $currentDate >= $inHouseVacationStartDate && $currentDate <= $inHouseVacationEndDate)))
                                    <div class="{{Session::get('direction') === "rtl" ? 'right' : 'ms-3'}}">
                                    <span class="chat-seller-info" data-toggle="tooltip"
                                        title="{{translate('this_shop_is_temporary_closed_or_on_vacation._You_cannot_add_product_to_cart_from_this_shop_for_now')}}">
                                        <img src="{{theme_asset(path: 'public/assets/front-end/img/info.png')}}"
                                            alt="i">
                                    </span>
                        </div>
                        @endif
                        </a>
                    </div>

                    <div class="col-12 mt-2">
                        <div class="row d-flex justify-content-between">
                            <div class="col-6 ">
                                <div
                                    class="d-flex justify-content-center align-items-center rounded __h-79px hr-right-before">
                                    <div class="text-center">
                                        <img src="{{theme_asset(path: 'public/assets/front-end/img/rating.svg')}}"
                                            class="mb-2" alt="">
                                        <div class="__text-12px text-base">
                                            <strong>{{$totalReviews}}</strong> {{translate('reviews')}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div
                                    class="d-flex justify-content-center align-items-center rounded __h-79px">
                                    <div class="text-center">
                                        <img src="{{theme_asset(path: 'public/assets/front-end/img/products.svg')}}"
                                            class="mb-2" alt="">
                                        <div class="__text-12px text-base">
                                            <strong>{{$productsForReview->total()}}</strong> {{translate('products')}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 position-static mt-3">
                        <div class="chat_with_seller-buttons">
                            @if (auth('customer')->id())
                            <button class="btn w-100 d-block text-center web--bg-primary text-white"
                                data-toggle="modal"
                                data-target="#chatting_modal" {{ ($inHouseTemporaryClose || ($inHouseVacationStatus && $currentDate >= $inHouseVacationStartDate && $currentDate <= $inHouseVacationEndDate)) ? 'disabled' : '' }}>
                                <img class="mb-1" alt=""
                                    src="{{ theme_asset(path: 'public/assets/front-end/img/chat-16-filled-icon.png')}}">
                                <span class="d-none d-sm-inline-block text-capitalize">
                                    {{translate('chat_with_vendor')}}
                                </span>
                            </button>
                            @else
                            <a href="{{ route('shopView',[0]) }}" class="btn w-100 d-block text-center web--bg-primary text-white">
                                <img class="mb-1" alt=""
                                    src="{{ theme_asset(path: 'public/assets/front-end/img/chat-16-filled-icon.png')}}">
                                <span class="d-none d-sm-inline-block text-capitalize">
                                    {{translate('chat_with_vendor')}}
                                </span>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            <div class="pt-4 pb-3">
                <span class=" __text-16px font-bold text-capitalize">
                    @if(getWebConfig(name: 'business_mode')=='multi')
                    {{ translate('more_from_the_store')}}
                    @else
                    {{ translate('you_may_also_like')}}
                    @endif
                </span>
            </div>
            <div>
                @foreach($moreProductFromSeller as $item)
                @include('web-views.partials._service-products-product-details',['product'=>$item,'decimal_point_settings'=>$decimalPointSettings])
                @endforeach
            </div>
        </div>
    </div>
    </div>

    <div class="modal fade rtl text-align-direction" id="show-modal-view" tabindex="-1" role="dialog" aria-labelledby="show-modal-image"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body flex justify-content-center">
                    <button class="btn btn-default __inline-33 dir-end-minus-7px"
                        data-dismiss="modal">
                        <i class="fa fa-close"></i>
                    </button>
                    <img class="element-center" id="attachment-view" src="" alt="">
                </div>
            </div>
        </div>
    </div>

    </div>
    @php($defaultLocation = getWebConfig(name: 'default_location'))

    @include("web-views.products._product-details-sticky", ['productDetails' => $product])

    @if($product?->preview_file_full_url['path'])
    @include('web-views.partials._product-preview-modal', ['previewFileInfo' => $previewFileInfo])
    @endif

    @include('layouts.front-end.partials.modal._chatting',['seller'=>$product->seller, 'user_type'=>$product->added_by])

    <span id="route-review-list-product" data-url="{{ route('review-list-product') }}"></span>
    <span id="products-details-page-data" data-id="{{ $product['id'] }}"></span>
    <span id="default-latitude-address" data-value="{{ $defaultLocation ? $defaultLocation['lat']:'-33.8688' }}"></span>
    <span id="default-longitude-address" data-value="{{ $defaultLocation ? $defaultLocation['lng']:'151.2195' }}"></span>
    @endsection

    @push('script')

    <script src="{{ theme_asset(path: 'public/assets/front-end/js/product-details.js') }}"></script>
    <script type="text/javascript" async="async"
        src="https://platform-api.sharethis.com/js/sharethis.js#property=5f55f75bde227f0012147049&product=sticky-share-buttons"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('web-views.partials._service-request-js-config')
    <script src="{{ theme_asset(path: 'public/assets/front-end/js/service-request.js') }}"></script>


    @if(getWebConfig('map_api_status') ==1 )
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{getWebConfig('map_api_key')}}&callback=mapsShopping&loading=async&libraries=places"
        defer>
    </script>
    @endif
    @endpush
