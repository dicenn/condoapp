// console.log('condoapp-filters.js is loaded');

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
            // Update global filter state
            window.currentFilters.price_range.min = data.from;
            window.currentFilters.price_range.max = data.to;
            window.offset = 0; // Reset to 10 if the first 10 units are already loaded
            // console.log("Sending filters:", window.currentFilters); // Add this line
            // console.log('Preparing AJAX request with filters:', window.currentFilters);
            // AJAX call to filter units by price range
            $.ajax({
                url: condoapp_ajax.ajax_url, // This should be already localized in your main AJAX script
                type: 'POST',
                data: {
                    action: 'filter_units_by_price',
                    nonce: condoapp_ajax.nonce,
                    filters: window.currentFilters, // Use global currentFilters
                    offset: window.offset
                },
                success: function(response) {
                    // Replace existing content with the new HTML
                    $('#unit-cards').html(response);
                    window.offset = 10; // Set offset to 10 after initial load
                    // console.log("Response received and content updated.");
                },                
                error: function(error) {
                    console.error('Error:', error);
                }
            });
            // console.log("Selected range: " + data.from + " to " + data.to);
            // console.log("AJAX request sent for filters:", window.currentFilters);
        }
    });
});