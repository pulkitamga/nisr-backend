"use strict";

function getCareerTicketText(id, fallback = "") {
    const node = document.getElementById(id);
    return node?.dataset?.text?.trim() || fallback;
}

const careerTicketUiText = {
    areYouSure: getCareerTicketText("career-ticket-are-you-sure", "Are you sure?"),
    yes: getCareerTicketText("career-ticket-yes", "Yes"),
    cancel: getCareerTicketText("career-ticket-cancel", "Cancel"),
    escalateWarning: getCareerTicketText("career-ticket-escalate-warning", "This will notify the department and owner."),
    yesEscalate: getCareerTicketText("career-ticket-yes-escalate", "Yes, Escalate"),
};

$(document).ready(function () {
    if ($.fn.select2) {
        $(".select2").select2();
    }

    $(document).on("click", ".action-btn", function () {
        const action = $(this).data("action");
        const route = $(this).data("route");
        const ticketId = $(this).data("ticket-id");
        const interviewId = $(this).data("interview-id");

        switch (action) {
            case "assign-recruiter":
                $("#assignTicketId").val(ticketId);
                $("#assignRecruiterForm").attr("action", route);
                $("#assignRecruiterModal").modal("show");
                break;
            case "screen":
                $("#screenTicketId").val(ticketId);
                $("#screenForm").attr("action", route);
                $("#screenModal").modal("show");
                $("input[name='qualified']").off("change.careerScreen").on("change.careerScreen", function () {
                    $("#reasonCodeDiv").toggle($("#qualifiedNo").is(":checked"));
                });
                break;
            case "schedule-interview":
                $("#scheduleTicketId").val(ticketId);
                $("#scheduleInterviewForm").attr("action", route);
                $("#scheduleInterviewModal").modal("show");
                break;
            case "conduct-interview":
                $("#conductTicketId").val(ticketId);
                $("#conductInterviewId").val(interviewId);
                $("#conductInterviewForm").attr("action", route);
                $("#conductInterviewModal").modal("show");
                break;
            case "attach-offer":
                $("#attachTicketId").val(ticketId);
                $("#attachOfferForm").attr("action", route);
                $("#attachOfferModal").modal("show");
                break;
            case "decline-offer":
                $("#declineTicketId").val(ticketId);
                $("#declineOfferForm").attr("action", route);
                $("#declineOfferModal").modal("show");
                break;
            case "reject":
                $("#rejectTicketId").val(ticketId);
                $("#rejectForm").attr("action", route);
                $("#rejectModal").modal("show");
                break;
            case "talent-pool":
                $("#talentPoolTicketId").val(ticketId);
                $("#talentPoolForm").attr("action", route);
                $("#talentPoolModal").modal("show");
                break;
            default:
                break;
        }
    });

    $(document).on("click", ".escalate-btn", function () {
        const ticketId = $(this).data("ticket-id");
        $("#escalateTicketId").val(ticketId);
        $("#escalateTicketModal").modal("show");
    });

    $(".confirm-submit-form").off("submit.careerTicket").on("submit.careerTicket", function (event) {
        event.preventDefault();
        const form = this;

        Swal.fire({
            title: careerTicketUiText.areYouSure,
            showCancelButton: true,
            confirmButtonText: careerTicketUiText.yes,
            cancelButtonText: careerTicketUiText.cancel,
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $("#escalateTicketForm").off("submit.careerTicket").on("submit.careerTicket", function (event) {
        event.preventDefault();
        const form = this;

        Swal.fire({
            title: careerTicketUiText.areYouSure,
            text: careerTicketUiText.escalateWarning,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: careerTicketUiText.yesEscalate,
            cancelButtonText: careerTicketUiText.cancel,
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
