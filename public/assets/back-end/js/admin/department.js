'use strict';

let getYesWord = $('#message-yes-word').data('text');
let getCancelWord = $('#message-cancel-word').data('text');
let messageAreYouSureDeleteThis = $('#message-are-you-sure-delete-this').data('text');
let messageYouWillNotAbleRevertThis = $('#message-you-will-not-be-able-to-revert-this').data('text');

$('.reset-button').on('click',function (){
    let placeholderImg = $("#placeholderImg").data('img');
    $('#viewer').attr('src', placeholderImg);
    $('#viewerBanner').attr('src', placeholderImg);
    $('#viewerBottomBanner').attr('src', placeholderImg);
    $('#viewerLogo').attr('src', placeholderImg);
    $('.spartan_remove_row').click();
})

$('#exampleInputPassword ,#exampleRepeatPassword').on('keyup',function () {
    let pass = $("#exampleInputPassword").val();
    let passRepeat = $("#exampleRepeatPassword").val();
    if (pass === passRepeat){
        $('.pass').hide();
    }
    else{
        $('.pass').show();
    }
});
$('#apply').on('click',function () {
    let image = $("#image-set").val();
    if (image===null)
    {
        $('.image').show();
        return false;
    }
    let pass = $("#exampleInputPassword").val();
    let passRepeat = $("#exampleRepeatPassword").val();
    if (pass!==passRepeat){
        $('.pass').show();
        return false;
    }
});


$('.department-delete-button').on('click', function () {
    let departmentId = $(this).attr("id");
    Swal.fire({
        title: messageAreYouSureDeleteThis,
        text: messageYouWillNotAbleRevertThis,
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: getYesWord,
        cancelButtonText: getCancelWord,
        type: 'warning',
        reverseButtons: true
    }).then((result) => {
        if (result.value) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: $('#route-admin-department-delete').data('url'),
                method: 'POST',
                data: {id: departmentId},
                success: function (response) {
                    toastr.success(response.message);
                    location.reload();
                }
            });
        }
    })
})
