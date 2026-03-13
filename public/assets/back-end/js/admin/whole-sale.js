'use strict';

    $(".action-get-onchange").on("change", function () {
        let getUrlPrefix = $(this).data("url-prefix") + $(this).val();
        let id = $(this).data("element-id");
        let getElementType = $(this).data("element-type");
        getProductListbyCategory(getUrlPrefix, id, getElementType);
    });


let row_count = parseInt($('tbody tr[data-row-id]').last().data('row-id')) || 0;


$('#add-price-range').on('click', function () {
    if (!window.remainingTiers || window.remainingTiers.length === 0) {
        Swal.fire({
        title: 'No tier available!',
        text: 'You have already added all available tiers.',
        icon: 'info',
        confirmButtonText: 'OK'
    });
    return;
    }

    row_count++;

    // Find last max_qty to set min qty of new row automatically
    let lastMaxQty = 0;
    const lastRow = $(".range-row").last();
    if (lastRow.length > 0) {
        lastMaxQty = parseInt(lastRow.find("input[name='max_qty[]']").val()) || 0;
    }
    const minQtyValue = lastMaxQty + 1;
const newRow = `
    <tr class="range-row" data-row-id="${row_count}">
        <td class="text-center row-number">${row_count}</td>
        <td>
            <div class="tier-select-wrapper position-relative">
                <input type="text" class="form-control tier-input" placeholder="Select Tier" data-row-id="${row_count}">
                <div class="tier-dropdown d-none border mt-1 bg-white" style="position: absolute; z-index: 999; max-height: 150px; overflow-y: auto; width: 100%;"></div>
            </div>
        </td>
        <td>
            <input type="number" class="form-control" name="min_qty[]" placeholder="Enter min. qty" value="${minQtyValue}">
        </td>
        <td>
            <input type="number" class="form-control" name="max_qty[]" placeholder="Enter max. qty">
        </td>
        <td>
            <input type="text" class="form-control unit-price" name="unit_price[]" placeholder="Unit Price" data-row="${row_count}">
        </td>
        <td>
            <input type="number" step="0.01" class="form-control discount-input" name="discount[]" placeholder="Discount (%)" data-row="${row_count}">
        </td>
        <td>
            <input type="text" class="form-control final-price" name="final_price[]" placeholder="Final Price" data-row="${row_count}">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger remove-row-btn" data-row-id="${row_count}">Remove</button>
        </td>
    </tr>
`;

    $('#range-rows').append(newRow);
});

// Open tier dropdown on input click
$(document).on('click', '.tier-input', function () {
    const input = $(this);
    const dropdown = input.siblings('.tier-dropdown');
    dropdown.empty().removeClass('d-none');

    if (!window.remainingTiers || window.remainingTiers.length === 0) {
        dropdown.html('<div class="px-2 py-1 text-muted">No Tier Available</div>');
        return;
    }

    window.remainingTiers.forEach(function (tier, index) {
        const item = $('<div class="px-2 py-1 tier-option" style="cursor: pointer;"></div>').text(tier.name);
        item.on('click', function () {
            input.val(tier.name);

            // Remove any existing hidden input to avoid duplicates
            input.closest('td').find('input[type="hidden"][name="tier[]"]').remove();

            // Add hidden input for form submit
            input.closest('td').append(`<input type="hidden" name="tier[]" value="${tier.name}">`);

            dropdown.addClass('d-none');

            // Remove selected tier from remainingTiers
            window.remainingTiers.splice(index, 1);
        });
        dropdown.append(item);
    });
});


$(document).on('click', '.remove-row-btn', function () {
    $(this).closest('tr').remove();
});


// Close tier dropdown if click outside
$(document).on('click', function (e) {
    if (!$(e.target).closest('.tier-select-wrapper').length) {
        $('.tier-dropdown').addClass('d-none');
    }
});


    function getProductListbyCategory(getUrlPrefix, id, getElementType) {
        let message = $("#message-select-word").data("text");
        $("#sub-sub-category-select")
            .empty()
            .append(
                `<option value="null" selected disabled>---` +
                message +
                `---</option>`
            );        
    
        $.get({
            url: getUrlPrefix,
            dataType: "json",
            beforeSend: function () {
                $("#loading").fadeIn();
            },
            success: function (data) {
                if (getElementType === "select") {
                    $("#sub-sub-category-select")
                        .empty()
                        .append(data.select_tag);
                }
            },
            complete: function () {
                $("#loading").fadeOut();
            },
        });
    }


    function updateRowNumbers() {
        let lastMaxQty = 1;
        row_count = 0;
        $(".range-row").each(function(index) {
            const currentRow = $(this);
            const minQtyField = currentRow.find("input[name='min_qty[]']");
            const maxQtyField = currentRow.find("input[name='max_qty[]']");
            currentRow.attr("data-row-id", row_count);
            currentRow.find(".row-number").text(row_count + 1 + ".");

            const prevRowMaxQty = lastMaxQty;
            const minQtyValue = prevRowMaxQty + 0;

            // Update the min_qty for the current row if needed
            // minQtyField.val(minQtyValue);

            // Update lastMaxQty for next iteration
            lastMaxQty = parseInt(maxQtyField.val()) || 0;

            row_count++;
        });
    }
    $(document).on("click", ".remove-product-btn", function () {
        $(this).closest("tr").remove();
        updateRowNumbers(); // Update row numbering after removing a row
    });

    // Event listener for adding rows
    $(document).on("click", "#add-price-range", function () {
        addProductRow();
    });

    function UpdateMinQty(){
        
    }
    $(document).on("input", "input[name='min_qty[]']", function() {
        const prevRowMaxQty = parseInt($(this).closest('tr').prev().find("input[name='max_qty[]']").val()) || 0;
        const currentMinQty = parseInt($(this).val()) || 0;

        if (currentMinQty <= prevRowMaxQty && currentMinQty !== 0) {
            $(this).css("border-color", "red"); // Highlight the input field
        } else {
            $(this).css("border-color", ""); // Remove the highlight
        }
    });

    // auto dropdown option select 
    /*let selectedProductId = "{{ $ProductData->product_id }}"; 
    setTimeout(function () {
        let category = $("#category_id").val();
        let sub_category = $("#sub-category-select").attr("data-id");
        getRequestFunctionality('{{ route('admin.products.get-categories') }}?parent_id=' + category + '&sub_category=' + sub_category, 'sub-category-select', 'select');
        getProductListbyCategory('{{ route('admin.products.get-products') }}?parent_id=' + sub_category, 0, 'select');
    }, 100)*/

    //default attribute and product select if ids are available 
    /*let selectedAttributeId = "{{ $ProductData->attribute_id ?? '' }}";
    function selectOption(selector, value) {
        if (value) {
            let observer = new MutationObserver((mutations, obs) => {
                let option = $(selector).find(`option[value="${value}"]`);
                if (option.length) {
                    option.prop('selected', true).trigger('change');
                    obs.disconnect();
                }
            });
            observer.observe(document.querySelector(selector), { childList: true });
        }
    }
    selectOption('#sub-sub-category-select', selectedProductId);
    selectOption('#get-attribute', selectedAttributeId);*/