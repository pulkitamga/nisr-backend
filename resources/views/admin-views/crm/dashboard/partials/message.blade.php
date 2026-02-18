<div class="col-sm-6 col-lg-3">
    <a class="business-analytics card py-3" href="#">
        <h5 class="business-analytics__subtitle">{{ translate('Inbound Messages') }}</h5>
        <h2 class="business-analytics__title" id="inboundMessages">{{ $inboundMessages }}</h2>
        <img src="{{ asset('assets/back-end/img/complaints/inbound.png') }}" width="30" height="30" class="business-analytics__img" alt="Inbound Messages">
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="business-analytics card py-3">
        <h5 class="business-analytics__subtitle">{{ translate('New Messages') }}</h5>
        <h2 class="business-analytics__title" id="newMessages">{{ $newMessages }}</h2>
        <img src="{{ asset('assets/back-end/img/complaints/new.png') }}" width="30" height="30" class="business-analytics__img" alt="New Messages">
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="business-analytics card py-3">
        <h5 class="business-analytics__subtitle">{{ translate('Converted Messages') }}</h5>
        <h2 class="business-analytics__title" id="convertedMessages">{{ $convertedMessages }}</h2>
        <img src="{{ asset('assets/back-end/img/complaints/converted.png') }}" width="30" height="30" class="business-analytics__img" alt="Converted Messages">
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="business-analytics card py-3" href="#">
        <h5 class="business-analytics__subtitle">{{ translate('Ignored Messages') }}</h5>
        <h2 class="business-analytics__title" id="ignoredMessages">{{ $ignoredMessages }}</h2>
        <img src="{{ asset('assets/back-end/img/complaints/ignored.png') }}" width="30" height="30" class="business-analytics__img" alt="Ignored Messages">
    </a>
</div>