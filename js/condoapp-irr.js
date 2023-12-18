jQuery(document).ready(function($) {
    // Event delegation for dynamically loaded content
    $('#unit-cards').on('click', '.calculate-xirr', function() {
        var unitId = parseInt($(this).data('unit-id'));
        var holdingPeriod = parseInt($(this).closest('.unit-card').find('.holding-period').val());
        var rent = parseFloat($(this).closest('.unit-card').find('.rent').val());
        var appreciationRate = parseFloat($(this).closest('.unit-card').find('.appreciation-rate').val());
    
        console.log('Recalculating XIRR for unit:', unitId);
    
        console.log('Sending data:', { unitId, holdingPeriod, rent, appreciationRate });    
    
        $.ajax({
            type: 'POST',
            url: condoapp_irr_object.ajax_url,
            data: {
                action: 'recalculate_xirr',
                nonce: condoapp_irr_object.nonce,
                unit_id: unitId,
                holding_period: holdingPeriod,
                rent: rent,
                appreciation_rate: appreciationRate
            },
            success: function(response) {
                console.log('Success function triggered', response);
                if (response.success) {
                    // Find the .xirr-result element in the same unit card as the clicked button and update its text
                    $('[data-unit-id="' + unitId + '"]').closest('.unit-card').find('.xirr-result').text(response.data.new_xirr);
                } else {
                    alert('Error recalculating XIRR: ' + (response.data || 'Unknown error'));
                }
            },            
        });
    });
});
