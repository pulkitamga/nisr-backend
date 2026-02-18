'use strict';

$('#submit-create-role').on('submit', function (e) {
    // Get all checked permissions
    let fields = $("input[name='permissions[]']:checked");

    if (fields.length === 0) {
        toastr.warning($('#select-minimum-one-box-message').data('warning'), {
            CloseButton: true,
            ProgressBar: true
        });
        e.preventDefault();
        return false;
    }

    // Get selected modules
    let selectedModules = new Set();
    let modulePermissions = {};

    fields.each(function () {
        let module = $(this).data('module');
        let perm = $(this).val();

        selectedModules.add(module);

        if (!modulePermissions[module]) {
            modulePermissions[module] = [];
        }
        modulePermissions[module].push(perm);
    });

    // Set values in hidden inputs
    $('#selected-modules').val(Array.from(selectedModules));
    $('#module-permissions-json').val(JSON.stringify(modulePermissions));

    // Allow form submit
    return true;
});


$("#select-all").on('change', function () {
    if ($(this).is(":checked") === true) {
        $(".module-permission").prop("checked", true);
    } else {
        $(".module-permission").removeAttr("checked");
    }
});

$(document).ready(function () {
    checkboxSelectionCheck();
})

function checkboxSelectionCheck() {
    let nonEmptyCount = 0;
    $(".module-permission").each(function () {
        if ($(this).is(":checked") !== true) {
            nonEmptyCount++;
        }
    });

    let selectAll = $("#select-all");
    if (nonEmptyCount === 0) {
        selectAll.prop("checked", true);
    } else {
        selectAll.removeAttr("checked");
    }
}

$('.module-permission').click(function () {
    checkboxSelectionCheck();
});


function toggleCrudOptions(module) {
    const crudOptions = document.getElementById('crud-options-' + module);
    const moduleCheckbox = document.getElementById(module);

    // Show CRUD options if module checkbox is checked
    if (moduleCheckbox.checked) {
        crudOptions.style.display = 'block';
    } else {
        crudOptions.style.display = 'none';
    }
}

// Optional: Select all modules and show/hide CRUD options accordingly
document.getElementById('select-all').addEventListener('click', function () {
    const checkboxes = document.querySelectorAll('.module-permission');
    checkboxes.forEach(function (checkbox) {
        checkbox.checked = this.checked;  // Check/uncheck all modules
        toggleCrudOptions(checkbox.id);  // Toggle CRUD options visibility
    }.bind(this));  // Use .bind(this) to pass correct context
});


document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('submit-create-role');

    document.querySelectorAll('.permission-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const moduleName = this.getAttribute('data-module');

            if (this.checked) {
                // Agar module name already add nahi hua, to add karo
                if (!form.querySelector(`input[name="modules[]"][value="${moduleName}"]`)) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'modules[]';
                    input.value = moduleName;
                    input.classList.add('dynamic-module');
                    form.appendChild(input);
                }
            } else {
                const stillChecked = document.querySelectorAll(`.permission-checkbox[data-module="${moduleName}"]:checked`);
                if (stillChecked.length === 0) {
                    const hiddenInput = form.querySelector(`input[name="modules[]"][value="${moduleName}"]`);
                    if (hiddenInput) hiddenInput.remove();
                }
            }
        });
    });


    
});




