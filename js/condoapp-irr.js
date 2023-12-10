jQuery(document).ready(function($) {
    $('.calculate-xirr').click(function() {
        var unitId = $(this).data('unit-id');
        var holdingPeriod = $(this).closest('div').find('.holding-period').val();
        var rent = $(this).closest('div').find('.rent').val();
        var appreciationRate = $(this).closest('div').find('.appreciation-rate').val();

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
                if (response.success) {
                    // Update the XIRR display on the unit card
                    // Assuming the XIRR result is displayed in a span with class 'xirr-result'
                    $('[data-unit-id="' + unitId + '"]').closest('.unit-card').find('.xirr-result').text(response.data.new_xirr);
                } else {
                    alert('Error recalculating XIRR: ' + response.data);
                }
            },
            error: function(error) {
                console.error('AJAX Error:', error);
            }
        });
    });
});
