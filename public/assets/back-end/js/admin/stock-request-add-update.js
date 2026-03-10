"use strict";

let messageAreYouSure = $("#message-are-you-sure").data("text");
let messageYesWord = $("#message-yes-word").data("text");
let messageNoWord = $("#message-no-word").data("text");
let messageWantAddOrUpdateThisProduct = $(
    "#message-want-to-add-or-update-this-request"
).data("text");

$(document).on("ready", function () {
    $(".product-add-stock-request-check").on("click", function () {
        getProductAddRequirementsCheck();
    });

    $('#showBranchesStockModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const productId = button.data('stock-product-id'); // actual product.id
        const stockRequestProductId = button.data('stock-request-product-id'); // stock_request_products.id
        const requestId = button.data('stock-request-id'); // stock_requests.id

        console.log('Modal open -> productId:', productId, 'stockRequestProductId:', stockRequestProductId, 'requestId:', requestId);

        $('#product_id').val(productId); // for display / UI
        $('#request_id').val(stockRequestProductId); // for backend transfer

        $('input[name="product_id"]').val(productId); // ensure form has correct product.id
        $('input[name="request_id"]').val(stockRequestProductId); // ensure form has correct stock_request_product.id

        // Store on modal element as fallback
        $(this).data('product-id', productId);
        $(this).data('stock-request-product-id', stockRequestProductId);
        $('#csv-error-container').empty();

        // Fetch branches stock
        $.ajax({
            url: $("#route-branches-products-stock").data("url"),
            method: "GET",
            data: {
                product_id: stockRequestProductId, // backend expects stock_request_products.id
                request_id: requestId // stock_requests.id
            },
            beforeSend: () => $("#loading").fadeIn(),
            success: function (response) {
                const { branchesStock, product } = response.data;
                const isTraceable = Number(product.is_traceable) === 1 || product.is_traceable === true;
                const requiredQty = Number(product.quantity) || 0;

                // Update UI
                $('#required-qty').text(requiredQty);
                $('#traceability-alert').toggleClass('d-none', !isTraceable);
                $('#csv-upload-section').toggle(isTraceable);
                $('input[name="serial_csv"]').prop('required', isTraceable).val('');
                $('#branchesStockForm').data('is-traceable', isTraceable);
                $('#branchesStockForm').data('required-qty', requiredQty);

                const tbody = $('#branches-tbody').empty();
                branchesStock.forEach((branch, index) => {
                    tbody.append(`
                    <tr>
                        <td class="text-center">${index + 1}</td>
                        <td class="text-center">
                            <input type="radio" name="selected_branches[]" value="${branch.branch_id}" 
                                   ${branch.available_stock < 1 ? 'disabled' : ''}>
                        </td>
                        <td>${branch.branch_name}</td>
                        <td class="text-center">${branch.available_stock}</td>
                        <td>${branch.branch_address}</td>
                    </tr>
                `);
                });
            },
            complete: () => $("#loading").fadeOut(),
            error: () => {
                $("#loading").fadeOut();
                toastr.error("Failed to load branches.");
            }
        });
    });

    $('#branchesStockForm').on('submit', function (e) {
        e.preventDefault();

        const selected = $('input[name="selected_branches[]"]:checked');
        if (selected.length === 0) {
            toastr.error('Select at least one branch.');
            return;
        }

        const isTraceable = $(this).data('is-traceable') === true;
        const serialCsvInput = $('input[name="serial_csv"]')[0];
        const hasCsvFile = !!(serialCsvInput && serialCsvInput.files && serialCsvInput.files.length > 0);
        if (isTraceable && !hasCsvFile) {
            toastr.error('CSV file is required for traceable product.');
            return;
        }

        const formData = new FormData(this);
        $.ajax({
            url: $(this).attr('action'),
            method: "POST",
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                if (res.success) {
                    toastr.success('Stock transferred successfully!');
                    $('#showBranchesStockModal').modal('hide');
                    location.reload();
                } else {
                    toastr.error(res.message || 'Transfer failed.');
                }
            },
            error: function (jqXHR) {
                let msg = 'Transfer failed.';
                let errorCsv = null;
                let errorCount = 'several';

                if (jqXHR.responseJSON) {
                    msg = jqXHR.responseJSON.message || msg;
                    errorCsv = jqXHR.responseJSON.error_csv || null;
                    errorCount = jqXHR.responseJSON.error_count || errorCount;
                }


                toastr.error(msg);

                if (errorCsv) {
                    const downloadUrl = $('#download-error-route').data('url').replace(':filename', errorCsv);

                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show csv-error-alert" role="alert">
                            <strong>Transfer failed!</strong>
                            ${errorCount} serial${errorCount == 1 ? '' : 's'} invalid.
                            <a href="${downloadUrl}" class="btn btn-sm btn-warning ms-2" download>
                                Download Error Report
                            </a>
                            <button type="button" class="btn-close" data-bs-dismiss="alert">&times;</button>
                        </div>
                    `;

                    $('#csv-error-container').html(alertHtml);
                }
            }

        });
    });
});

function getProductAddRequirementsCheck() {
    Swal.fire({
        title: messageAreYouSure,
        text: messageWantAddOrUpdateThisProduct,
        type: "warning",
        showCancelButton: true,
        cancelButtonColor: "default",
        confirmButtonColor: "#377dff",
        cancelButtonText: messageNoWord,
        confirmButtonText: messageYesWord,
        reverseButtons: true,
    }).then((result) => {
        if (result.value) {
            let formData = new FormData(document.getElementById("stock_request_form"));

            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            });

            $.post({
                url: $("#stock_request_form").attr("action"),
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $("#loading").fadeIn();
                },
                success: function (data, textStatus, jqXHR) {
                    if (data.success) {
                        toastr.success($("#message-stock-request-added-successfully").data("text"), {
                            CloseButton: true, ProgressBar: true
                        });
                        setTimeout(() => {
                            window.location = data.redirect ?? "{{ route('admin.stock-transfer.list') }}";
                        }, 1000);
                    } else {
                        toastr.error(data.message || "An error occurred.", { CloseButton: true, ProgressBar: true });
                    }
                },
                error: function (jqXHR) {
                    $("#loading").fadeOut();
                    let message = jqXHR.responseJSON?.message || "An unexpected error occurred.";
                    toastr.error(message, { CloseButton: true, ProgressBar: true });
                },
                complete: function () {
                    $("#loading").fadeOut();
                }
            });
        }
    });
}


function deleteDigitalVariationFileFunctionality() {
    $(".digital-variation-file-delete-button").on("click", function () {
        let variantKey = $(this).data("variant");
        let productId = $(this).data("product");

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });
        $.ajax({
            type: "POST",
            url: $("#route-admin-products-digital-variation-file-delete").data(
                "url"
            ),
            data: {
                product_id: productId,
                variant_key: variantKey,
            },
            success: function (response) {
                getUpdateDigitalVariationFunctionality();
                response.status === 1
                    ? toastr.success(response.message)
                    : toastr.error(response.message);
            },
        });
    });
}

function fetchProductsByCategory(categoryId, productId, rowId) {
    const url = $('#route-admin-products-search-category_wise').data('url');
    $.ajax({
        url: url,
        type: 'GET',
        data: { category_id: categoryId, productId: productId },
        success: function (response) {
            const productDropdown = $(`.product-select-${rowId}`);
            const attributeDropdown = $(`.attribute-select-${rowId}`);
            productDropdown.empty();
            attributeDropdown.empty();
            attributeDropdown.append(`<option value="" selected disabled>Select attributes</option>`);
            productDropdown.append(`<option value="" selected disabled>Select product</option>`);
            response.data.forEach(product => {
                productDropdown.append(`<option value="${product.id}">${product.name}</option>`);
            });
        },
        error: function () {
            toastr.error("failed to fetch products");
        }
    });
}

function fetchAttributesByProduct(categoryId, productId, rowId) {
    const url = $('#route-admin-products-search-category_wise').data('url');
    $.ajax({
        url: url,
        type: 'GET',
        data: { category_id: categoryId, productId: productId },
        success: function (response) {
            const attributeDropdown = $(`.attribute-select-${rowId}`);
            attributeDropdown.empty();
            attributeDropdown.append(`<option value="" selected disabled>Select attributes</option>`);
            var variationData = JSON.parse(response.data[0]['variation'], true);
            variationData.forEach(variation => {
                attributeDropdown.append(`<option value="${variation.type}">${variation.type}</option>`);
            });
        },
        error: function () {
            toastr.error("failed to fetch products");
        }
    });
}

$(document).on("change", ".category-select", function () {
    const categoryId = $(this).val();
    const rowId = $(this).data("row-id");
    const productId = $(`.product-select-${rowId}`).val();
    if (categoryId) {
        fetchProductsByCategory(categoryId, productId, rowId);
    }
});

$(document).on("change", ".product-select", function () {
    const productId = $(this).val();
    const rowId = $(this).data("row-id");
    const categoryId = $(`.category-select-${rowId}`).val();
    if (categoryId && productId) {
        fetchAttributesByProduct(categoryId, productId, rowId);
    }
});


$.fn.select2DynamicDisplay = function () {
    function updateDisplay($element) {
        var $rendered = $element
            .siblings(".select2-container")
            .find(".select2-selection--multiple")
            .find(".select2-selection__rendered");
        var $container = $rendered.parent();
        var containerWidth = $container.width();
        var totalWidth = 0;
        var itemsToShow = [];
        var remainingCount = 0;

        // Get all selected items
        var selectedItems = $element.select2("data");

        // Create a temporary container to measure item widths
        var $tempContainer = $("<div>")
            .css({
                display: "inline-block",
                padding: "0 15px",
                "white-space": "nowrap",
                visibility: "hidden",
            })
            .appendTo($container);

        // Calculate the width of items and determine how many fit
        selectedItems.forEach(function (item) {
            var $tempItem = $("<span>")
                .text(item.text)
                .css({
                    display: "inline-block",
                    padding: "0 12px",
                    "white-space": "nowrap",
                })
                .appendTo($tempContainer);

            var itemWidth = $tempItem.outerWidth(true);

            if (totalWidth + itemWidth <= containerWidth - 40) {
                totalWidth += itemWidth;
                itemsToShow.push(item);
            } else {
                remainingCount = selectedItems.length - itemsToShow.length;
                return false;
            }
        });

        $tempContainer.remove();

        const $searchForm = $rendered.find(".select2-search");

        var html = "";
        itemsToShow.forEach(function (item) {
            html += `<li class="name">
                                    <span>${item.text}</span>
                                    <span class="close-icon" data-id="${item.id}"><i class="tio-clear"></i></span>
                                    </li>`;
        });
        if (remainingCount > 0) {
            html += `<li class="ms-auto">
                                    <div class="more">+${remainingCount}</div>
                                    </li>`;
        }
        html += $searchForm.prop("outerHTML");

        $rendered.html(html);

        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // Attach event listener with debouncing
        $(".select2-search input").on(
            "input",
            debounce(function () {
                const inputValue = $(this).val().toLowerCase();

                const $listItems = $(".select2-results__options li");

                $listItems.each(function () {
                    const itemText = $(this).text().toLowerCase();
                    $(this).toggle(itemText.includes(inputValue));
                });
            }, 100)
        );

        $(".select2-search input").on("keydown", function (e) {
            if (e.which === 13) {
                e.preventDefault();

                const inputValue = $(this).val();
                if (
                    !inputValue ||
                    itemsToShow.find((item) => item.text === inputValue) ||
                    selectedItems.find((item) => item.text === inputValue)
                ) {
                    $(this).val("");
                    return null;
                }

                if (inputValue) {
                    $element.append(
                        new Option(inputValue, inputValue, true, true)
                    );
                    $element.val([...$element.val(), inputValue]);
                    $(this).val("");
                    $(".multiple-select2").select2DynamicDisplay();
                }
            }
        });
    }
    return this.each(function () {
        var $this = $(this);

        $this.select2({
            tags: true,
        });

        // Bind change event to update display
        $this.on("change", function () {
            updateDisplay($this);
        });

        // Initial display update
        updateDisplay($this);

        $(window).on("resize", function () {
            updateDisplay($this);
        });
        $(window).on("load", function () {
            updateDisplay($this);
        });

        // Handle the click event for the remove icon
        $(document).on(
            "click",
            ".select2-selection__rendered .close-icon",
            function (e) {
                e.stopPropagation();
                var $removeIcon = $(this);
                var itemId = $removeIcon.data("id");
                var $this2 = $removeIcon
                    .closest(".select2")
                    .siblings(".multiple-select2");
                $this2.val(
                    $this2.val().filter(function (id) {
                        return id != itemId;
                    })
                );
                $this2.trigger("change");
            }
        );
    });
};
$(".multiple-select2").select2DynamicDisplay();
