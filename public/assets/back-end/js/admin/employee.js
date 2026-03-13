"use strict";

let messageAreYouSure = $("#message-are-you-sure").data("text");
let messageYesWord = $("#message-yes-word").data("text");
let messageNoWord = $("#message-no-word").data("text");
let messageWantAddOrUpdateThisProduct = $(
    "#message-want-to-add-or-update-this-request"
).data("text");

$(document).on("ready", function () {
    $('#showDepartmentsModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget); // Button that triggered the modal
        const employeeId = button.data('employee-id'); // Extract ticket ID
        const departmentIdSelect = document.getElementById('department-id');
        $('#department-employee-id').val(employeeId);
        $(departmentIdSelect).val(0).trigger('change');
    });

    $('#showBranchModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget); // Button that triggered the modal
        const employeeId = button.data('employee-id'); // Extract ticket ID
        const branchIdSelect = document.getElementById('branch-id');
        $('#branch-employee-id').val(employeeId);
        $(branchIdSelect).val(0).trigger('change');
        
    });
})