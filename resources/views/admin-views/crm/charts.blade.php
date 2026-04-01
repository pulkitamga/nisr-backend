@extends('layouts.back-end.app')

@section('title', translate('crm_analytics_dashboard'))

@push('css_or_js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"/>

<style>

.crm-card{
    border:1px solid #e5e7eb;
    border-radius:12px;
    background:#fff;
    padding:16px;
}

.crm-stat-card{
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:14px 16px;
    background:#fff;
}

.crm-stat-title{
    font-size:12px;
    color:#6b7280;
}

.crm-stat-value{
    font-size:22px;
    font-weight:700;
}

.chart-container{
    height:420px;
}

.legend-container{
    display:flex;
    flex-wrap:wrap;
    gap:10px;
    margin-top:12px;
}

.legend-item{
    display:flex;
    align-items:center;
    gap:8px;
    padding:6px 10px;
    border:1px solid #e5e7eb;
    border-radius:6px;
    cursor:pointer;
}

.legend-color{
    width:14px;
    height:14px;
    border-radius:3px;
}

</style>
@endpush

@section('content')

<div class="content container-fluid">

    <div class="mb-3">
        <h2 class="h1 mb-0 d-flex align-items-center gap-2">
            <img width="20" src="{{ dynamicAsset('public/assets/back-end/img/chart.png') }}">
            {{ translate('crm_analytics_dashboard') }}
        </h2>
    </div>


    {{-- FILTERS --}}
    <div class="card mb-3">
        <div class="card-body">

            <div class="row g-2">

                <div class="col-md-3">
                    <label>{{ translate('date_range') }}</label>
                    <input type="text" id="dateRange" class="form-control">
                </div>

                <div class="col-md-2">
                    <label>{{ translate('group_by') }}</label>
                    <select id="groupByFilter" class="form-control">
                        <option value="daily">{{ translate('daily') }}</option>
                        <option value="weekly">{{ translate('weekly') }}</option>
                        <option value="monthly">{{ translate('monthly') }}</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>{{ translate('channel') }}</label>
                    <select id="pipelineFilter" class="form-control">
                        <option value="">{{ translate('all') }}</option>
                        <option value="email">Email</option>
                        <option value="chat">Chat</option>
                        <option value="form">Form</option>
                        <option value="social">Social</option>
                        <option value="phone">Phone</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>{{ translate('message_type') }}</label>
                    <select id="messageType" class="form-control">
                        <option value="">{{ translate('all') }}</option>
                        <option value="complaint">Complaint</option>
                        <option value="support">Support</option>
                        <option value="career">Career</option>
                        <option value="service">Service</option>
                        <option value="retail">Retail</option>
                        <option value="wholesale">Wholesale</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>{{ translate('department') }}</label>
                    <select id="departmentFilter" class="form-control">
                        <option value="">{{ translate('all') }}</option>
                        @foreach($departments as $dept)
                            <option value="{{$dept->id}}">
                                {{$dept->getTranslatedField('name')}}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1">
                    <label>{{ translate('status') }}</label>
                    <select id="statusFilter" class="form-control">
                        <option value="">{{ translate('all') }}</option>
                        <option value="new">New</option>
                        <option value="processing">Processing</option>
                        <option value="converted">Converted</option>
                        <option value="spam">Spam</option>
                    </select>
                </div>

                <div class="col-12 mt-2 d-flex gap-2">

                    <button id="applyFilter" class="btn btn--primary">
                        {{ translate('filter') }}
                    </button>

                    <button id="resetFilter" class="btn btn-outline-secondary">
                        {{ translate('reset') }}
                    </button>

                    <button onclick="exportExcel()" class="btn btn-outline-success">
                        <i class="tio-download-to"></i> Excel
                    </button>

                    <button onclick="exportFullPDF()" class="btn btn-outline-danger">
                        <i class="tio-download-to"></i> PDF
                    </button>

                </div>

            </div>

        </div>
    </div>



    {{-- SUMMARY --}}
    <div class="row g-2 mb-3" id="summaryStats"></div>



    {{-- MAIN CHART --}}
    <div class="crm-card mb-3">

        <div class="d-flex justify-content-between mb-2">

            <h4>{{ translate('crm_analytics_overview') }}</h4>

            <select id="chartType" class="form-control w-auto">
                <option value="bar">Bar</option>
                <option value="line">Line</option>
                <option value="stackedBar">Stacked</option>
            </select>

        </div>

        <div class="chart-container">
            <canvas id="mainChart"></canvas>
        </div>

        <div class="legend-container" id="chartLegend"></div>

    </div>



    {{-- DATA TABLE --}}
    <div class="crm-card">

        <h4 class="mb-3">{{ translate('detailed_data') }}</h4>

        <div class="table-responsive">

            <table class="table table-borderless table-thead-bordered">

                <thead class="thead-light">
                <tr>
                    <th>{{translate('date')}}</th>
                    <th>{{translate('total')}}</th>
                    <th>{{translate('assigned')}}</th>
                    <th>{{translate('pending')}}</th>
                    <th>{{translate('converted')}}</th>
                    <th>{{translate('ignored')}}</th>
                    <th>{{translate('spam')}}</th>
                    <th>{{translate('assigned_%')}}</th>
                </tr>
                </thead>

                <tbody></tbody>

            </table>

        </div>

    </div>

</div>

@endsection



@push('script')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker"></script>

<script>

let mainChart = null;
let currentData = null;


$(document).ready(function(){

$('#dateRange').daterangepicker({
    locale:{format:'YYYY-MM-DD'},
    startDate:moment().subtract(6,'days'),
    endDate:moment()
});


$('#applyFilter').click(function(){
    loadChartData();
});

$('#resetFilter').click(function(){

    $('#departmentFilter').val('');
    $('#messageType').val('');
    $('#statusFilter').val('');
    $('#pipelineFilter').val('');

    loadChartData();
});


loadChartData();

});



function loadChartData(){

const dateRange = $('#dateRange').val().split(' - ');

$.get("{{route('admin.crm.chart.data')}}",{

start_date:dateRange[0],
end_date:dateRange[1],
department_id:$('#departmentFilter').val(),
message_type:$('#messageType').val(),
status:$('#statusFilter').val(),
pipeline:$('#pipelineFilter').val(),
group_by:$('#groupByFilter').val()

},function(res){

if(res.success){

currentData = res.data;

renderChart(res.data);

updateSummaryStats(res.data.summary);

updateDataTable(res.data.daily_stats);

updateLegend(res.data.legend);

}

});

}



function renderChart(data){

const ctx = document.getElementById('mainChart');

if(mainChart) mainChart.destroy();

mainChart = new Chart(ctx,{

type:$('#chartType').val(),

data:{
labels:data.labels,
datasets:data.datasets
},

options:{
responsive:true,
maintainAspectRatio:false,
plugins:{legend:{display:false}}
}

});

}



function updateSummaryStats(summary){

$('#summaryStats').html(`

<div class="col-md-2">
<div class="crm-stat-card">
<div class="crm-stat-title">Total</div>
<div class="crm-stat-value">${summary.total||0}</div>
</div>
</div>

<div class="col-md-2">
<div class="crm-stat-card">
<div class="crm-stat-title">Assigned</div>
<div class="crm-stat-value">${summary.assigned||0}</div>
</div>
</div>

<div class="col-md-2">
<div class="crm-stat-card">
<div class="crm-stat-title">Pending</div>
<div class="crm-stat-value">${summary.pending||0}</div>
</div>
</div>

<div class="col-md-2">
<div class="crm-stat-card">
<div class="crm-stat-title">Converted</div>
<div class="crm-stat-value">${summary.converted||0}</div>
</div>
</div>

<div class="col-md-2">
<div class="crm-stat-card">
<div class="crm-stat-title">Ignored</div>
<div class="crm-stat-value">${summary.ignored||0}</div>
</div>
</div>

<div class="col-md-2">
<div class="crm-stat-card">
<div class="crm-stat-title">Spam</div>
<div class="crm-stat-value">${summary.spam||0}</div>
</div>
</div>

`);

}



function updateDataTable(data){

let rows='';

data.forEach(row=>{

const percent=row.total?Math.round((row.assigned/row.total)*100):0;

rows+=`
<tr>
<td>${row.period}</td>
<td>${row.total}</td>
<td>${row.assigned}</td>
<td>${row.pending}</td>
<td>${row.converted}</td>
<td>${row.ignored}</td>
<td>${row.spam}</td>
<td>${percent}%</td>
</tr>
`;

});

$('tbody').html(rows);

}



function updateLegend(legend){

let html='';

legend.forEach(item=>{

html+=`
<div class="legend-item">
<div class="legend-color" style="background:${item.color}"></div>
${item.label}
</div>
`;

});

$('#chartLegend').html(html);

}



function exportExcel(){

const params = new URLSearchParams(getFilters()).toString();

window.open(`{{route('admin.crm.export.excel')}}?${params}`);

}



function exportFullPDF(){

const filters = getFilters();

const canvas=document.getElementById('mainChart');

filters.chart_image = canvas.toDataURL('image/png');

const form=document.createElement('form');

form.method='POST';

form.action='{{route('admin.crm.export.pdf')}}';

form.target='_blank';

filters._token='{{csrf_token()}}';

Object.keys(filters).forEach(key=>{

const input=document.createElement('input');

input.type='hidden';

input.name=key;

input.value=filters[key];

form.appendChild(input);

});

document.body.appendChild(form);

form.submit();

document.body.removeChild(form);

}



function getFilters(){

const dateRange = $('#dateRange').val().split(' - ');

return {

start_date:dateRange[0],
end_date:dateRange[1],
department_id:$('#departmentFilter').val(),
message_type:$('#messageType').val(),
status:$('#statusFilter').val(),
pipeline:$('#pipelineFilter').val(),
group_by:$('#groupByFilter').val()

};

}

</script>

@endpush