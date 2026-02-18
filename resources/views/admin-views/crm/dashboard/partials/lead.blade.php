<div class="col-sm-6 col-lg-3">
    <a class="business-analytics card py-3">
        <h5 class="business-analytics__subtitle">{{ translate('Total Leads') }}</h5>
        <h2 class="business-analytics__title" id="totalLeads">{{ $totalLeads }}</h2>
        <img src="{{ asset('assets/back-end/img/complaints/total-lead.png') }}" width="30" class="business-analytics__img" height="30">
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="business-analytics card py-3">
        <h5 class="business-analytics__subtitle">{{ translate('Working Leads') }}</h5>
        <h2 class="business-analytics__title" id="workingLeads">{{ $workingLeads }}</h2>
        <img src="{{ asset('assets/back-end/img/complaints/working.png') }}" width="30" class="business-analytics__img" height="30">
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="business-analytics card py-3">
        <h5 class="business-analytics__subtitle">{{ translate('Qualified Leads') }}</h5>
        <h2 class="business-analytics__title" id="qualifiedLeads">{{ $qualifiedLeads }}</h2>
        <img src="{{ asset('assets/back-end/img/complaints/qualified.png') }}" width="30" class="business-analytics__img" height="30">
    </a>
</div>
<div class="col-sm-6 col-lg-3">
    <a class="business-analytics card py-3">
        <h5 class="business-analytics__subtitle">{{ translate('Converted Leads') }}</h5>
        <h2 class="business-analytics__title" id="convertedLeads">{{ $convertedLeads }}</h2>
        <img src="{{ asset('assets/back-end/img/complaints/converted.png') }}" width="30" class="business-analytics__img" height="30">
    </a>
</div>