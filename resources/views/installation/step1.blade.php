@extends('layouts.blank')

@section('content')
    <div class="text-center text-white mb-4">
        <h2>{{ __('') }}</h2>
        <h6 class="fw-normal">
            {{ __('') }}
        </h6>
    </div>

    <div class="pb-2">
        <div class="progress cursor-pointer" role="progressbar" aria-label="6valley Software Installation"
             aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" data-bs-toggle="tooltip"
             data-bs-placement="top" data-bs-custom-class="custom-progress-tooltip" data-bs-title="First Step!"
             data-bs-delay='{"hide":1000}'>
            <div class="progress-bar width-20"></div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="p-4 mb-md-3 mx-xl-4 px-md-5">
            <div class="d-flex justify-content-end mb-2">
                <a href="https://docs.dynamiclogic.com/docs-six-valley/intro/" class="d-flex align-items-center gap-1"
                   target="_blank">
                    {{ __('') }}
                    <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                          data-bs-title="Follow our documentation">
                            <img src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/info.svg') }}"
                                 class="svg" alt="">
                    </span>
                </a>
            </div>

            <div class="d-flex align-items-center column-gap-3 flex-wrap mb-4">
                <h5 class="fw-bold fs text-uppercase">{{ __('') }}</h5>
                <h5 class="fw-normal">{{ __('') }}</h5>
            </div>

            <div class="bg-light p-4 rounded mb-4">
                <h6 class="fw-bold text-uppercase fs m-0 letter-spacing  mb-4 pb-sm-3 --fs-14px">
                    {{ __('') }}
                </h6>

                <div class="px-xl-2 pb-sm-3">
                    <div class="row g-4 g-md-5">
                        <div class="col-md-6">
                            <div class="d-flex gap-3 align-items-center">
                                <img alt=""
                                     src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/php-version.svg') }}">
                                <div class="d-flex align-items-center gap-2 justify-content-between flex-grow-1">
                                    {{ __('') }}
                                    @php($phpVersion = number_format((float)phpversion(), 2, '.', ''))
                                    @if ($phpVersion >= 8.1)
                                        <img width="20" alt=""
                                             src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/check.png') }}">
                                    @else
                                        <span class="cursor-pointer" data-bs-toggle="tooltip"
                                              data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                              data-bs-html="true" data-bs-delay='{"hide":1000}'
                                              data-bs-title="Your php version in server is lower than 8.1 version
                                                   <a href='https://support.cpanel.net/hc/en-us/articles/360052624713-How-to-change-the-PHP-version-for-a-domain-in-cPanel-or-WHM'
                                                   class='d-block' target='_blank'>{{ __('See how to update') }}</a> ">
                                                <img class="svg text-danger" alt=""
                                                     src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/info.svg') }}">
                                            </span>
                                    @endif

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3 align-items-center">
                                <img alt=""
                                     src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/curl-enabled.svg') }}">
                                <div class="d-flex align-items-center gap-2 justify-content-between flex-grow-1">
                                    {{ __('') }}
                                    @if ($permission['curl_enabled'])
                                        <img width="20" alt=""
                                             src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/check.png') }}">
                                    @else
                                        <span class="cursor-pointer" data-bs-toggle="tooltip"
                                              data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                              data-bs-html="true" data-bs-delay='{"hide":1000}'
                                              data-bs-title="Curl extension is not enabled in your server. To enable go to PHP version > extensions and select curl.">
                                                <img class="svg text-danger" alt=""
                                                     src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/info.svg') }}">
                                            </span>
                                    @endif

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3 align-items-center">
                                <img alt=""
                                     src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/route-service.svg') }}">
                                <div class="d-flex align-items-center gap-2 justify-content-between flex-grow-1">
                                    {{ __('') }}
                                    @if ($permission['db_file_write_perm'])
                                        <img width="20" alt=""
                                             src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/check.png') }}">
                                    @else
                                        <span class="cursor-pointer" data-bs-toggle="tooltip"
                                              data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                              data-bs-html="true" data-bs-delay='{"hide":1000}'
                                              data-bs-title="...">
                                                <img class="svg text-danger" alt=""
                                                     src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/info.svg') }}">
                                            </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-3 align-items-center">
                                <img alt=""
                                     src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/route-service.svg') }}">
                                <div class="d-flex align-items-center gap-2 justify-content-between flex-grow-1">
                                    {{ __('') }}
                                    @if ($permission['routes_file_write_perm'])
                                        <img width="20" alt=""
                                             src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/check.png') }}">
                                    @else
                                        <span class="cursor-pointer" data-bs-toggle="tooltip"
                                              data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                              data-bs-html="true" data-bs-delay='{"hide":1000}'
                                              data-bs-title="...">
                                                <img class="svg text-danger" alt=""
                                                     src="{{ dynamicAsset(path: 'public/assets/installation/assets/img/svg-icons/info.svg') }}">
                                            </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <p>{{ __('') }}</p>
                @if ($permission['curl_enabled'] == 1 && $permission['db_file_write_perm'] == 1 && $permission['routes_file_write_perm'] == 1 && $phpVersion >= 8.1)
                    <a href="{{ route('step2') }}" class="btn btn-dark px-sm-5">
                        {{ __('') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
