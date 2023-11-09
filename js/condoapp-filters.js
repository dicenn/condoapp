jQuery(document).ready(function($) {
    $("#price-range").ionRangeSlider({
        type: "double",
        grid: true,
        min: condoapp_price_range.min,
        max: condoapp_price_range.max,
        from: condoapp_price_range.min,
        to: condoapp_price_range.max,
        step: 50000,
        prettify_enabled: true,
        prettify_separator: ",",
        onFinish: function(data) {
            // Here you can add the AJAX call to filter the units based on the selected price range
            // For now, you can just log the selected values to confirm it's working
            console.log("Selected range: " + data.from + " to " + data.to);
        }
    });
});
