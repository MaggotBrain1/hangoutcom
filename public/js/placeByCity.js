$('#hangout_form_city').change(function () {
    var citySelector = $(this);

    // Request the neighborhoods of the selected city.
    $.ajax({
        url: "{{ path('app_places_by_city') }}",
        type: "GET",
        dataType: "JSON",
        data: {
            cityid: citySelector.val()
        },
        success: function (places) {
            var placesSelected = $("#hangout_form_place");

            // Remove current options
            placesSelected.html('');

            // Empty value ...
            placesSelected.append('<option value> Select a neighborhood of ' + citySelector.find("option:selected").text() + ' ...</option>');


            $.each(places, function (key, place) {
                placesSelected.append('<option value="' + place.id + '">' + place.name + '</option>');
            });
        },
        error: function (err) {
            alert("An error ocurred while loading data ...");
        }
    });
});