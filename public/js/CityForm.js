let $city = $('#hangout_form_city');
$city.change(function() {
    //si on change de ville on réinitialise les valeurs associés au Lieu (Place)
    $('#hangout_form_street').val("");
    $('#hangout_form_latitude').val("");
    $('#hangout_form_longitude').val("");

    let $form = $(this).closest('form');
    let data = {};
    data[$city.attr('name')] = $city.val();
    $.ajax({
        url : $form.attr('action'),
        type: $form.attr('method'),
        data : data,
        complete: function(html) {
            $('#hangout_form_zipCode').replaceWith(
                $(html.responseText).find('#hangout_form_zipCode')
            );
            //pas de replace car cela retire l add event listener sur createPlace
            $('#hangout_form_place').html($(html.responseText).find('#hangout_form_place').html());
        }
    });
});

let $place = $('#hangout_form_place');
$place.change(function() {
    let $form = $(this).closest('form');
    let data = {};
    data[$place.attr('name')] = $place.val();
    $.ajax({
        url : $form.attr('action'),
        type: $form.attr('method'),
        data : data,
        complete: function(html) {
            $('#hangout_form_street').replaceWith(
                $(html.responseText).find('#hangout_form_street')
            );
            $('#hangout_form_latitude').replaceWith(
                $(html.responseText).find('#hangout_form_latitude')
            );
            $('#hangout_form_longitude').replaceWith(
                $(html.responseText).find('#hangout_form_longitude')
            );
        }
    });
});

