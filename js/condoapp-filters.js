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
                        action: 'filter_units',
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

    if (condoapp_filter_data && condoapp_filter_data.square_footage_range) {
        $("#square-footage-range").ionRangeSlider({
            type: "double",
            grid: true,
            min: Math.floor(condoapp_filter_data.square_footage_range.min_value / 100) * 100,
            max: Math.ceil(condoapp_filter_data.square_footage_range.max_value / 100) * 100,
            from: Math.floor(condoapp_filter_data.square_footage_range.min_value / 100) * 100,
            to: Math.ceil(condoapp_filter_data.square_footage_range.max_value / 100) * 100,
            step: 50,
            prettify_enabled: true,
            prettify_separator: ",",
            onFinish: function(data) {
                // Update global filter state for square footage
                window.currentFilters.square_footage_range.min = data.from;
                window.currentFilters.square_footage_range.max = data.to;
                window.offset = 0; // Reset offset for new filtered data

                $.ajax({
                    url: condoapp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'filter_units',
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
    }

    $('#bedrooms-filter input[type="checkbox"]').change(function() {
        // Update the bedrooms filter in currentFilters
        window.currentFilters.bedrooms = $('#bedrooms-filter input[type="checkbox"]:checked').map(function() {
            return this.value;
        }).get();

        window.offset = 0; // Reset offset for new filtered data

        // AJAX call to filter units by updated filters
        $.ajax({
            url: condoapp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_units',
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
    });
    
});