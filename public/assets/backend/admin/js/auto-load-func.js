"use strict";

document.addEventListener('DOMContentLoaded', function () {
    const offcanvasEl = document.getElementById('offcanvasSetupGuide');

    if (offcanvasEl && offcanvasEl.getAttribute('data-status') === 'show') {
        const bsOffcanvas = new bootstrap.Offcanvas(offcanvasEl);
        setTimeout(() => {
            bsOffcanvas.show();
        }, 500)
    }
});

var audio = document.getElementById("myAudio");
function playAudio() {
    audio.play();
}


function getInitialDataForPanel() {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content")
        }
    });
    $.ajax({
        url: $("#route-for-real-time-activities").data("route"),
        type: "GET",
        dataType: "json",
        success: function (response) {
            if (response?.new_order_count > 0) {
                playAudio();
                $("#popup-modal").appendTo("body").modal("show");
            }
            if (document.cookie.indexOf("6valley_restock_request_status=accepted") !== -1 || document.cookie.indexOf("6valley_restock_request_status=reject") !== -1) {
                $(".product-restock-stock-alert").hide();
            } else {
                if (response?.restockProductCount > 0 && response?.restockProduct) {
                    productRestockStockLimitStatus(response?.restockProduct);
                }
            }
            if (response?.lead_notifications?.length > 0) {
                let list = "";
                response.lead_notifications.forEach(note => {
                    list += `<li class="list-group-item">
                                <strong>${note.title}</strong><br>
                                <span>${note.message}</span>
                             </li>`;
                });
                $("#leadNotificationList").html(list);
                $("#leadNotificationModal").appendTo("body").modal("show");
            }
        }
    });
}




function getInitialDataForPanelEmployee() {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content")
        }
    });
    $.ajax({
        url: $("#route-for-real-time-activities").data("route"),
        type: "GET",
        dataType: "json",
        success: function (response) {
            if (response?.new_order_count > 0) {
                playAudio();
                $("#popup-modal").appendTo("body").modal("show");
            }
            if (document.cookie.indexOf("6valley_restock_request_status=accepted") !== -1 || document.cookie.indexOf("6valley_restock_request_status=reject") !== -1) {
                $(".product-restock-stock-alert").hide();
            } else {
                if (response?.restockProductCount > 0 && response?.restockProduct) {
                    productRestockStockLimitStatus(response?.restockProduct);
                }
            }
            if (response?.lead_notifications?.length > 0) {
                let list = "";
                response.lead_notifications.forEach(note => {
                    list += `<li class="list-group-item">
                                <strong>${note.title}</strong><br>
                                <span>${note.message}</span>
                             </li>`;
                });
                $("#leadNotificationList").html(list);
                $("#leadNotificationModal").appendTo("body").modal("show");
            }
        }
    });
}
