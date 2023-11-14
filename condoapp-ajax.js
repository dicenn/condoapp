// console.log('condoapp-ajax.js is loaded');

// Define currentFilters in the global scope
window.currentFilters = {
    price_range: {
        min: 0, // Default values, will be updated when condoapp-filters.js runs
        max: 0
    },
    // Initialize other filters here as needed
};

window.offset = 10; // Initialize with 10 as the first set of units is already loaded

jQuery(document).ready(function($) {
    // Update currentFilters with actual values from condoapp_filter_data
    if (condoapp_filter_data && condoapp_filter_data.price_range) {
        window.currentFilters.price_range.min = condoapp_filter_data.price_range.min_value;
        window.currentFilters.price_range.max = condoapp_filter_data.price_range.max_value;
    }

    // Initialize other filters here as needed

    let loading = false; // To prevent multiple simultaneous loads

    // console.log('currentFilters variable initialized with:', window.currentFilters);

    $(window).scroll(function() {
        console.log('Scrolled');
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 100 && !loading) {
            loading = true;
            
            console.log("Load more units AJAX call triggered with offset:", window.offset);

            $.ajax({
                type: 'POST',
                url: condoapp_ajax.ajax_url,
                data: {
                    action: 'load_more_units',
                    nonce: condoapp_ajax.nonce,
                    filters: window.currentFilters, // Send current filters
                    offset: window.offset
                },
                beforeSend: function() {
                    // console.log('Sending AJAX request...');
                },
                success: function(response) {
                    // console.log('Response:', response); // Log the entire response for debugging
                
                    if (response.trim() !== '') {
                        $('#unit-cards').append(response);
                        window.offset += 10; // Increment offset for next load
                    } else {
                        console.log("No more units to load.");
                    }
                    loading = false;
                    console.log("New offset:", window.offset);
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('AJAX request failed:', textStatus, errorThrown);
                    loading = false;
                }
            });
        }
    });
});
