// console.log('condoapp-ajax.js is loaded');

// Define currentFilters in the global scope
window.currentFilters = {
    price_range: {
        min: 0, // Default values, will be updated when condoapp-filters.js runs
        max: 0
    }
};

window.offset = 10; // Initialize with 10 as the first set of units is already loaded

jQuery(document).ready(function($) {
    // Update currentFilters with actual values from condoapp_price_range
    window.currentFilters.price_range.min = condoapp_price_range.min;
    window.currentFilters.price_range.max = condoapp_price_range.max;

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
