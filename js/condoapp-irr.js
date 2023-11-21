$.get(condoapp_irr.ajax_url, {
    action: 'fetch_cashflows',
    nonce: condoapp_irr.nonce
}, function(cashflows) {
    // Process the cashflows data here
    console.log("Received cashflows: ", cashflows);
    // ... rest of your code ...
}).fail(function(jqXHR, textStatus, errorThrown) {
    console.log("AJAX request failed: ", textStatus, errorThrown);
});
