"use strict";

function getCrmDealText(id, fallback = "") {
    const node = document.getElementById(id);
    return node?.dataset?.text?.trim() || fallback;
}

const crmDealUiText = {
    areYouSure: getCrmDealText("crm-deal-are-you-sure", "Are you sure?"),
    yes: getCrmDealText("crm-deal-yes", "Yes"),
    cancel: getCrmDealText("crm-deal-cancel", "Cancel"),
    success: getCrmDealText("crm-deal-success", "Success"),
    error: getCrmDealText("crm-deal-error", "Error"),
    updatedSuccessfully: getCrmDealText("crm-deal-updated-successfully", "Updated successfully"),
    somethingWentWrong: getCrmDealText("crm-deal-something-went-wrong", "Something went wrong"),
    disqualifyTitle: getCrmDealText("crm-deal-disqualify-title", "Disqualify Deal?"),
    disqualifyBody: getCrmDealText("crm-deal-disqualify-body", "This should be used before sending quotation."),
    markLostTitle: getCrmDealText("crm-deal-mark-lost-title", "Mark Deal Lost?"),
    markLostBody: getCrmDealText("crm-deal-mark-lost-body", "Use this after quotation is sent."),
    closeTitle: getCrmDealText("crm-deal-close-title", "Close Deal?"),
    closeBody: getCrmDealText("crm-deal-close-body", "Review logic must be completed before close."),
    escalateWarning: getCrmDealText("crm-deal-escalate-warning", "This will notify the department and owner."),
    yesEscalate: getCrmDealText("crm-deal-yes-escalate", "Yes, Escalate"),
    noOrdersFound: getCrmDealText("crm-deal-no-orders-found", "No orders found for this customer."),
    action: getCrmDealText("crm-deal-action-label", "Action"),
    orderId: getCrmDealText("crm-deal-order-id-label", "Order ID"),
    date: getCrmDealText("crm-deal-date-label", "Date"),
    amount: getCrmDealText("crm-deal-amount-label", "Amount"),
    status: getCrmDealText("crm-deal-status-label", "Status"),
    link: getCrmDealText("crm-deal-link-label", "Link"),
    failedOrders: getCrmDealText("crm-deal-failed-orders", "Failed to load orders. Please try again."),
    linkOrderTitle: getCrmDealText("crm-deal-link-order-title", "Link Order?"),
    linkOrderBody: getCrmDealText("crm-deal-link-order-body", "Order will be linked to the selected deal."),
    yesLinkIt: getCrmDealText("crm-deal-yes-link-it", "Yes, Link it!"),
    linked: getCrmDealText("crm-deal-linked-title", "Linked!"),
    orderLinked: getCrmDealText("crm-deal-order-linked", "Order linked successfully!"),
    failed: getCrmDealText("crm-deal-failed-title", "Failed"),
    serverError: getCrmDealText("crm-deal-server-error", "Server error. Please try again."),
};

function submitDealStatusAction(routeUrl, dealId, titleText, confirmText) {
    if (!routeUrl || !dealId) {
        return;
    }

    Swal.fire({
        title: titleText,
        text: confirmText,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: crmDealUiText.yes,
        cancelButtonText: crmDealUiText.cancel,
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: routeUrl,
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                deal_id: dealId,
            },
            success: function (response) {
                Swal.fire(
                    crmDealUiText.success,
                    response.message || crmDealUiText.updatedSuccessfully,
                    "success",
                );

                setTimeout(function () {
                    window.location.reload();
                }, 500);
            },
            error: function (xhr) {
                Swal.fire(
                    crmDealUiText.error,
                    xhr.responseJSON?.message || crmDealUiText.somethingWentWrong,
                    "error",
                );
            },
        });
    });
}

function formatPanelCurrency(value) {
    const symbol = document.getElementById("crm-deal-currency")?.dataset?.symbol || "";
    const position = document.getElementById("crm-deal-currency")?.dataset?.position || "left";
    const spaced = document.getElementById("crm-deal-currency")?.dataset?.space === "1";
    const decimals = Number(document.getElementById("crm-deal-currency")?.dataset?.decimals || 2);
    const amount = Number.parseFloat(value);
    const safeAmount = Number.isFinite(amount) ? amount : 0;
    const formattedNumber = safeAmount.toLocaleString(undefined, {
        minimumFractionDigits: Number.isFinite(decimals) ? decimals : 2,
        maximumFractionDigits: Number.isFinite(decimals) ? decimals : 2,
    });
    const spacing = spaced ? " " : "";

    if (position === "right") {
        return `${formattedNumber}${spacing}${symbol}`;
    }

    return `${symbol}${spacing}${formattedNumber}`;
}

$(document).on("click", ".deal-disqualify-btn", function () {
    submitDealStatusAction(
        $("#dealDisqualifyRoute").data("url"),
        $(this).data("deal-id"),
        crmDealUiText.disqualifyTitle,
        crmDealUiText.disqualifyBody,
    );
});

$(document).on("click", ".deal-mark-lost-btn", function () {
    submitDealStatusAction(
        $("#dealMarkLostRoute").data("url"),
        $(this).data("deal-id"),
        crmDealUiText.markLostTitle,
        crmDealUiText.markLostBody,
    );
});

$(document).on("click", ".deal-close-btn", function () {
    submitDealStatusAction(
        $("#dealCloseRoute").data("url"),
        $(this).data("deal-id"),
        crmDealUiText.closeTitle,
        crmDealUiText.closeBody,
    );
});

$(document).on("click", ".create-quotation-btn", function (event) {
    event.preventDefault();

    const dealId = $(this).data("id");
    const currentUrl = $(this).attr("href");

    if (!currentUrl || !dealId) {
        return;
    }

    const targetUrl = new URL(currentUrl, window.location.origin);
    targetUrl.searchParams.set("deal_id", dealId);
    window.location.href = targetUrl.toString();
});

$(document).on("click", ".request-quotation-btn", function (event) {
    event.preventDefault();

    const requestUrl = $(this).data("request-url");
    if (!requestUrl) {
        return;
    }

    $.ajax({
        url: requestUrl,
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.status) {
                toastr.success(response.message);
                return;
            }

            toastr.error(crmDealUiText.somethingWentWrong);
        },
        error: function (xhr) {
            toastr.error(xhr.responseJSON?.message || crmDealUiText.somethingWentWrong);
        },
    });
});

$(document).on("click", ".escalate-btn, .escalate-wholesale-btn", function () {
    const dealId = $(this).data("deal-id");
    if (!dealId) {
        return;
    }

    if ($(this).hasClass("escalate-wholesale-btn")) {
        $("#escalateWholesaleDealId").val(dealId);
        $("#escalateWholesaleDealModal").modal("show");
        return;
    }

    $("#escalateRetailDealId").val(dealId);
    $("#escalateRetailDealModal").modal("show");
});

$("#escalateRetailDealForm, #escalateWholesaleDealForm")
    .off("submit.crmDeals")
    .on("submit.crmDeals", function (event) {
        event.preventDefault();
        const form = this;

        Swal.fire({
            title: crmDealUiText.areYouSure,
            text: crmDealUiText.escalateWarning,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: crmDealUiText.yesEscalate,
            cancelButtonText: crmDealUiText.cancel,
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

$(document).on("click", ".link-order-btn", function () {
    const getUserOrdersRoute = $("#getUserOrdersRoute").data("url");
    const dealId = $(this).data("deal-id");
    const userId = $(this).data("user-id");
    const userName = $(this).data("user-name");

    if (!getUserOrdersRoute || !dealId || !userId) {
        return;
    }

    $("#modal-deal-id").text(dealId);
    $("#modal-user-name").text(userName || "");
    $("#orders-list").html('<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>');

    $.get(getUserOrdersRoute, { user_id: userId, deal_id: dealId }, function (response) {
        if (!response.orders || response.orders.length === 0) {
            $("#orders-list").html(`<p class="text-muted text-center">${crmDealUiText.noOrdersFound}</p>`);
            return;
        }

        let html = `
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="100">${crmDealUiText.action}</th>
                            <th>${crmDealUiText.orderId}</th>
                            <th>${crmDealUiText.date}</th>
                            <th>${crmDealUiText.amount}</th>
                            <th>${crmDealUiText.status}</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        response.orders.forEach((order) => {
            const date = new Date(order.created_at).toLocaleString("en-IN", {
                day: "2-digit",
                month: "short",
                year: "numeric",
                hour: "2-digit",
                minute: "2-digit",
            });

            const statusBadge = {
                delivered: "success",
                confirmed: "primary",
                processing: "info",
                pending: "warning",
                canceled: "danger",
                failed: "dark",
                returned: "secondary",
            }[order.order_status] || "dark";

            html += `
                <tr>
                    <td>
                        <button class="btn btn-sm btn-primary link-this-order"
                            data-deal-id="${dealId}"
                            data-order-id="${order.id}">
                            <i class="tio-link"></i> ${crmDealUiText.link}
                        </button>
                    </td>
                    <td><strong>#${order.id}</strong></td>
                    <td>${date}</td>
                    <td>${formatPanelCurrency(order.order_amount)}</td>
                    <td>
                        <span class="badge badge-soft-${statusBadge}">
                            ${String(order.order_status || "").replace(/_/g, " ")}
                        </span>
                    </td>
                </tr>
            `;
        });

        html += "</tbody></table></div>";
        $("#orders-list").html(html);
    }).fail(function () {
        $("#orders-list").html(`<p class="text-danger text-center">${crmDealUiText.failedOrders}</p>`);
    });

    $("#linkOrderModal").modal("show");
});

$(document).on("click", ".link-this-order", function () {
    const linkOrderRoute = $("#linkOrderRoute").data("url");
    const dealId = $(this).data("deal-id");
    const orderId = $(this).data("order-id");

    if (!linkOrderRoute || !dealId || !orderId) {
        return;
    }

    Swal.fire({
        title: crmDealUiText.linkOrderTitle,
        text: crmDealUiText.linkOrderBody,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: crmDealUiText.yesLinkIt,
        cancelButtonText: crmDealUiText.cancel,
        confirmButtonColor: "#1e88e5",
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        $.post(linkOrderRoute, {
            _token: $('meta[name="csrf-token"]').attr("content"),
            deal_id: dealId,
            order_id: orderId,
        }, function (response) {
            if (response.success) {
                Swal.fire({
                    icon: "success",
                    title: crmDealUiText.linked,
                    text: response.message || crmDealUiText.orderLinked,
                    timer: 2000,
                    showConfirmButton: false,
                });
                return;
            }

            Swal.fire(
                crmDealUiText.failed,
                response.message || crmDealUiText.somethingWentWrong,
                "error",
            );
        }).fail(function (xhr) {
            console.error(xhr.responseText);
            Swal.fire(
                crmDealUiText.error,
                xhr.responseJSON?.message || crmDealUiText.serverError,
                "error",
            );
        });
    });
});
