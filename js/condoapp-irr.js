jQuery(document).ready(function($) {
    $('.calculate-xirr').click(function() {
        var unitId = parseInt($(this).data('unit-id'));
        var holdingPeriod = parseInt($(this).closest('div').find('.holding-period').val());
        var rent = parseFloat($(this).closest('div').find('.rent').val());
        var appreciationRate = parseFloat($(this).closest('div').find('.appreciation-rate').val());
    
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
            // success: function(response) {
            //     if (response.success) {
            //         $('[data-unit-id="' + unitId + '"]').closest('.unit-card').find('.xirr-result').text(response.data.new_xirr);
            //     } else {
            //         alert('Error recalculating XIRR: ' + response.data);
            //     }
            // },            
            // error: function(xhr, status, error) {
            //     console.error('AJAX Error:', xhr.status, xhr.responseText);
            // }
        });
    });
});
