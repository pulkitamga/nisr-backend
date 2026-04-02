"use strict";

function buildCrmListQueryParams(form) {
    const params = new URLSearchParams();

    if (!form) {
        return params;
    }

    new FormData(form).forEach((value, key) => {
        if (value === undefined || value === null || String(value).trim() === "") {
            return;
        }

        params.set(key, String(value));
    });

    params.delete("page");

    return params;
}

document.addEventListener("DOMContentLoaded", function () {
    if (
        typeof window.$ === "function"
        && typeof window.changeInputTypeForDateRangePicker === "function"
    ) {
        window.changeInputTypeForDateRangePicker($("input.js-daterangepicker-with-range"));
    }
});

document.addEventListener("click", function (event) {
    const exportButton = event.target.closest("[data-crm-export-button]");
    if (!exportButton) {
        return;
    }

    event.preventDefault();

    const formSelector = exportButton.dataset.form;
    const form = formSelector ? document.querySelector(formSelector) : null;
    const baseUrl = exportButton.dataset.baseUrl || exportButton.getAttribute("href");

    if (!baseUrl) {
        return;
    }

    const exportUrl = new URL(baseUrl, window.location.origin);
    const params = buildCrmListQueryParams(form);

    params.forEach((value, key) => {
        exportUrl.searchParams.set(key, value);
    });

    window.location.href = exportUrl.toString();
});
