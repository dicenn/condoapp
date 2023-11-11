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
            // AJAX call to filter units by price range
            $.ajax({
                url: condoapp_ajax.ajax_url, // This should be already localized in your main AJAX script
                type: 'POST',
                data: {
                    action: 'filter_units_by_price', // This is the WordPress hook you'll use
                    nonce: condoapp_ajax.nonce, // Nonce for security
                    price_range: {
                        min: data.from,
                        max: data.to
                    }
                },
                success: function(response) {
                    // Handle the response
                    console.log(response);
                },
                error: function(error) {
                    console.error('Error:', error);
                }
            });
            console.log("Selected range: " + data.from + " to " + data.to);
        }
    });
});
