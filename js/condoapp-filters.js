// console.log('condoapp-filters.js is loaded');

jQuery(document).ready(function($) {
    // Check if condoapp_filter_data is defined and has price_range data
    if (condoapp_filter_data && condoapp_filter_data.price_range) {
        $("#price-range").ionRangeSlider({
            type: "double",
            grid: true,
            min: Math.floor(condoapp_filter_data.price_range.min_value / 100000) * 100000,
            max: Math.ceil(condoapp_filter_data.price_range.max_value / 100000) * 100000,
            from: Math.floor(condoapp_filter_data.price_range.min_value / 100000) * 100000,
            to: Math.ceil(condoapp_filter_data.price_range.max_value / 100000) * 100000,
            step: 50000,
            prettify_enabled: true,
            prettify_separator: ",",
            onFinish: function(data) {
                // Update global filter state
                window.currentFilters.price_range.min = data.from;
                window.currentFilters.price_range.max = data.to;
                window.offset = 0; // Reset offset for new filtered data

                // AJAX call to filter units by price range
                $.ajax({
                    url: condoapp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'filter_units_by_price',
                        nonce: condoapp_ajax.nonce,
                        filters: window.currentFilters,
                        offset: window.offset
                    },
                    success: function(response) {
                        $('#unit-cards').html(response);
                        window.offset = 10; // Set offset for next load
                    },
                    error: function(error) {
                        console.error('Error:', error);
                    }
                });
            }
        });
    } else {
        console.error('condoapp_filter_data or price_range data is not available.');
    }
});