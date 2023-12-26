jQuery(document).ready(function($) {
    // ... other code ...

    $('#clear-filters-btn').click(function() {
        // Reset dropdown filters
        resetDropdown('#bedrooms-filter');
        resetDropdown('#bathrooms-filter');
        resetDropdown('#unit-type-filter');
        resetDropdown('#developer-filter');
        resetDropdown('#project-filter');
        resetDropdown('#den-filter');
        resetDropdown('#pre-occupancy-deposit-filter');

        // Reset slider filters
        resetSlider('#price-range');
        resetSlider('#square-footage-range');
        resetSlider('#occupancy-date-range');
        // resetSlider('#pre-occupancy-deposit-filter');
        // ... reset other slider filters ...

        // Update global filter state
        window.currentFilters = {
            price_range: {
                min: 0, // Default values, will be updated when condoapp-filters.js runs
                max: 100000000
            },
            // Initialize other filters with default values
            bedrooms: [],
            bathrooms: [],
            unit_type: [],
            pre_occupancy_deposit: [],
            developer: [],
            project: [],
            den: [],
            square_footage_range: {
                min: 0,
                max: 100000000
            },
            occupancy_date_range: {
                min: '1900-01-01', // Align with PHP default
                max: '2100-01-01'  // Align with PHP default
            }
        };

        // Scroll to the top of the page
        $('html, body').animate({ scrollTop: 0 }, 'slow');

        window.offset = 0; // Initialize with 10 as the first set of units is already loaded
        window.allUnitsLoaded = false; // reset
        // Trigger filter update
        filterUnits();
    });

    $('#apply-filters-btn').click(function() {
        window.offset = 0; // Reset offset for filtered results
        window.allUnitsLoaded = false; // reset
        $('html, body').animate({ scrollTop: 0 }, 'slow');
        filterUnits(); // Now call filterUnits
    });
    
    function resetDropdown(dropdownId) {
        $(dropdownId).multiselect('deselectAll', false);
        $(dropdownId).multiselect('updateButtonText');
    }

    function resetSlider(sliderId) {
        var slider = $(sliderId).data("ionRangeSlider");
        slider.reset();
    }

    function initializeNumericSlider(sliderId, filterData, filterKey, step, prettifySeparator) {
        if (filterData && filterData[filterKey]) {
            var minVal = Math.floor(filterData[filterKey].min_value / (step * 2)) * (step * 2);
            var maxVal = Math.ceil(filterData[filterKey].max_value / (step * 2)) * (step * 2);
    
            $(sliderId).ionRangeSlider({
                type: "double",
                grid: true,
                min: minVal,
                max: maxVal,
                from: minVal,
                to: maxVal,
                step: step,
                prettify_enabled: true,
                prettify_separator: prettifySeparator,
                prettify: function(num) {
                    return num.toLocaleString();
                },
                onFinish: function(data) {
                    window.currentFilters[filterKey].min = data.from;
                    window.currentFilters[filterKey].max = data.to;
                    // window.offset = 0; //needed previously when filters were triggered on any modification, commented out when moved 'filter' button
                    // filterUnits(); //needed previously when filters were triggered on any modification, commented out when moved 'filter' button
                }
            });
        } else {
            console.error(`${filterKey} data is not available.`);
        }
    }

    function initializeDateSlider(sliderId, filterData, filterKey) {
        if (filterData && filterData[filterKey]) {
            var minDate = new Date(filterData[filterKey].min_value);
            var maxDate = new Date(filterData[filterKey].max_value);
    
            // Add one month to the maximum date
            maxDate.setMonth(maxDate.getMonth() + 1);
    
            $(sliderId).ionRangeSlider({
                type: "double",
                grid: true,
                min: minDate.getTime(),
                max: maxDate.getTime(),
                from: minDate.getTime(),
                to: maxDate.getTime(),
                step: 30 * 24 * 60 * 60 * 1000, // Approximate 1 month in milliseconds
                prettify_enabled: true,
                prettify_separator: ",",
                prettify: function(num) {
                    return new Date(num).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                },
                onFinish: function(data) {
                    // Set the selected dates to the first of their respective months and adjust time to midday
                    var fromDate = new Date(data.from);
                    fromDate.setDate(1);
                    fromDate.setHours(12);
    
                    var toDate = new Date(data.to);
                    toDate.setDate(1);
                    toDate.setHours(12);
    
                    window.currentFilters[filterKey].min = fromDate.toISOString().split('T')[0];
                    window.currentFilters[filterKey].max = toDate.toISOString().split('T')[0];
                    // window.offset = 0; //needed previously when filters were triggered on any modification, commented out when moved 'filter' button
                    // filterUnits(); //needed previously when filters were triggered on any modification, commented out when moved 'filter' button
                }
            });
        } else {
            console.error(`${filterKey} data is not available.`);
        }
    }    
    
    function initializeDropdown(dropdownId, filterDataKey) {
        var $dropdown = $(dropdownId);
    
        // Clear existing options
        $dropdown.empty();
    
        // Dynamically add new options based on sorted filter data
        if (condoapp_filter_data && condoapp_filter_data[filterDataKey]) {
            // Sort the array of values
            var sortedValues = condoapp_filter_data[filterDataKey].sort(function(a, b) {
                return a - b; // For numerical sorting
            });
    
            // Append sorted values as options
            $.each(sortedValues, function(index, value) {
                var displayValue = value.toString().replace(/_/g, ' ');
                if (filterDataKey === 'pre_occupancy_deposit') {
                    // Convert decimal to percentage for display
                    displayValue = (value * 100).toFixed(0) + '%';
                } else {
                    displayValue = value.toString().replace(/_/g, ' ');
                    // Additional formatting can be added here for other filters if needed
                }
                $dropdown.append($('<option>', {
                    value: value,
                    text: displayValue //+ ' Bedroom' + (value > 1 ? 's' : '') // Adjust this line as needed for other filters
                }));
                
            });
        }
    
        // Initialize the multiselect plugin
        $dropdown.multiselect({
            includeSelectAllOption: true,
            enableFiltering: true,
            buttonWidth: '100%',
            nonSelectedText: '', // Changed text here
            nSelectedText: 'Selected',
            allSelectedText: 'All Selected',
            templates: {
                li: '<li style="max-width: 240px;"><a href="javascript:void(0);"><label class="pl-2" style="white-space: normal; overflow: hidden; text-overflow: ellipsis; max-width: 100%;"></label></a></li>'
            }
        }).change(function() {
            window.currentFilters[filterDataKey] = $(this).val();
            // window.offset = 0; //needed previously when filters were triggered on any modification, commented out when moved 'filter' button
            // filterUnits(); //needed previously when filters were triggered on any modification, commented out when moved 'filter' button
        });
    }
    
    function filterUnits() {
        var spinnerContainer = $('#spinner-container').detach(); // Detach the spinner container
        var noMoreUnits = $('#no-more-units-message').detach(); // Detach the "No more units" container
        spinnerContainer.show(); // Show spinner
    
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
                $('#unit-cards').html(response); // Replace the content in unit-cards
    
                // Count the number of unit cards loaded in the response
                var loadedUnitsCount = $(response).filter('.unit-card').length;
    
                if (loadedUnitsCount < 10) {
                    noMoreUnits.show(); // Show "No more units" message
                } else {
                    noMoreUnits.hide(); // Hide "No more units" message
                    window.offset = 10; // Update offset for next load
                }
    
                $('#unit-cards').append(spinnerContainer); // Reattach the spinner container
                $('#unit-cards').append(noMoreUnits); // Reattach the "No more units" container
                spinnerContainer.hide(); // Hide spinner
            },
            error: function(error) {
                console.error('Error:', error);
                spinnerContainer.hide(); // Hide spinner on error
            },
            complete: function() {
                // Optionally, hide spinner here if needed
            }
        });
    }    

    // Initialize sliders
    initializeNumericSlider("#price-range", condoapp_filter_data, 'price_range', 50000, ",");
    initializeNumericSlider("#square-footage-range", condoapp_filter_data, 'square_footage_range', 50, ",");
    initializeDateSlider("#occupancy-date-range", condoapp_filter_data, 'occupancy_date_range', 1, ",");

    // Initialize dropdowns
    initializeDropdown('#bedrooms-filter', 'bedrooms');
    initializeDropdown('#bathrooms-filter', 'bathrooms');
    initializeDropdown('#unit-type-filter', 'unit_type');
    initializeDropdown('#pre-occupancy-deposit-filter', 'pre_occupancy_deposit');
    initializeDropdown('#developer-filter', 'developer');
    initializeDropdown('#project-filter', 'project');
    initializeDropdown('#den-filter', 'den');

    // Add more filter initializations as needed...
});