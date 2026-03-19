'use strict';
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


$('#city').on('change',function (){
    let iCityId = $(this).val();
    fFetchCitiesArea(iCityId);
});

function fFetchCitiesArea(iCityId = 0){
    const selectedAreaValues = ($('#shipping_methods_area').val() || []).map(String);

    $.ajax({
        url: $("#route-get-cities-area").data("url"), // Update with your route name
        method: "GET",
        data: { iCityId },
        beforeSend: function () {
            $("#loading").fadeIn();
        },
        success: function (response) {
            $('#shipping_methods_area').empty();
            $('#shipping_methods_area').append(`<option value="0" disabled="">---Select---</option>`);
            // Populate modal table with new data
            response.data.forEach((area, index) => {
                const isSelected = selectedAreaValues.includes(String(area.id));
                $('#shipping_methods_area').append(`
                    <option value='${area.id}' ${isSelected ? 'selected' : ''}>${area.name}</option>
                `);
            });
            $('#shipping_methods_area').trigger('change');
        },
        complete: function () {
            $("#loading").fadeOut();
        },
        error: function () {
            $("#loading").fadeOut();
            toastr.error("Failed to fetch area. Please try again.");
        }
    });
}
