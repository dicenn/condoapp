console.log('condoapp-ajax.js is loaded');

jQuery(document).ready(function($) {
    let offset = 10; // Start with 10 since we already have the first 10 loaded
    let loading = false; // To prevent multiple simultaneous loads

    $(window).scroll(function() {
        console.log('Scrolled');
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 100 && !loading) {
            loading = true;
            
            $.ajax({
                type: 'POST',
                url: condoapp_ajax.ajax_url,
                data: {
                    action: 'load_more_units',
                    nonce: condoapp_ajax.nonce, // Send the nonce for security verification
                    offset: offset
                },
                beforeSend: function() {
                    console.log('Sending AJAX request...');
                },
                success: function(response) {
                    // console.log('AJAX request successful:', response);
                    $('#unit-cards').append(response);
                    offset += 10; // Prepare offset for next load
                    loading = false;
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log('AJAX request failed:', textStatus, errorThrown);
                    loading = false;
                }
            });
        }
    });
});
