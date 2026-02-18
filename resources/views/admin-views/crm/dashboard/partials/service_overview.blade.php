<div class="card remove-card-shadow h-100">
    <div class="card-header">
        <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
            {{translate(' Service Overview')}}
        </h4>
    </div>
    <div class="card-body justify-content-center d-flex flex-column">
        <div>
            <div class="position-relative">
                <div id="serviceChart1" class="apex-pie-chart d-flex justify-content-center"></div>
                <div class="total--orders">
                    <h3 id="totalInvoiceHeader"> {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $totalInvoice), currencyCode: getCurrencyCode()) }}</h3>
                    <span class="text-capitalize">Total Invoice Amount</span>
                </div>
            </div>
            <div class="apex-legends flex-column">
                <div class="before-bg-0">
                    <span class="text-capitalize">{{translate('Total Services Completed ')}}(<span id="totalServicesLegend">{{ $totalServices }}</span>)</span>
                </div>
                <div class="before-bg-1">
                    <span class="text-capitalize">{{translate('Total Invoice Amount ')}}( <span id="totalInvoiceLegend">{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $totalInvoice), currencyCode: getCurrencyCode()) }}</span>)</span>
                </div>
            </div>
        </div>
    </div>
</div>