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
        // resetDropdown('#pre-occupancy-deposit-filter');

        // Reset slider filters
        resetSlider('#price-range');
        resetSlider('#square-footage-range');
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
                min: '', // Use appropriate default value
                max: ''  // Use appropriate default value
            }
        };

        window.offset = 0; // Initialize with 10 as the first set of units is already loaded

        // Trigger filter update
        filterUnits();
    });

    function resetDropdown(dropdownId) {
        $(dropdownId).multiselect('deselectAll', false);
        $(dropdownId).multiselect('updateButtonText');
    }

    function resetSlider(sliderId) {
        var slider = $(sliderId).data("ionRangeSlider");
        slider.reset();
    }

    function initializeSlider(sliderId, filterData, filterKey, step, prettifySeparator) {
        if (filterData && filterData[filterKey]) {
            $(sliderId).ionRangeSlider({
                type: "double",
                grid: true,
                min: Math.floor(filterData[filterKey].min_value / (step * 2)) * (step * 2),
                max: Math.ceil(filterData[filterKey].max_value / (step * 2)) * (step * 2),
                from: Math.floor(filterData[filterKey].min_value / (step * 2)) * (step * 2),
                to: Math.ceil(filterData[filterKey].max_value / (step * 2)) * (step * 2),
                step: step,
                prettify_enabled: true,
                prettify_separator: prettifySeparator,
                onFinish: function(data) {
                    window.currentFilters[filterKey].min = data.from;
                    window.currentFilters[filterKey].max = data.to;
                    window.offset = 0;
                    filterUnits();
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
            allSelectedText: 'All Selected'
        }).change(function() {
            window.currentFilters[filterDataKey] = $(this).val();
            window.offset = 0;
            filterUnits();
        });
    }
    
    function filterUnits() {
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
                window.offset = 10;
            },
            error: function(error) {
                console.error('Error:', error);
            }
        });
    }

    // Initialize sliders
    initializeSlider("#price-range", condoapp_filter_data, 'price_range', 50000, ",");
    initializeSlider("#square-footage-range", condoapp_filter_data, 'square_footage_range', 50, ",");
    // initializeSlider("#pre-occupancy-deposit-filter", condoapp_filter_data, 'pre_occupancy_deposit', 1, ",");

    // Initialize dropdowns
    initializeDropdown('#bedrooms-filter', 'bedrooms');
    initializeDropdown('#bathrooms-filter', 'bathrooms');
    initializeDropdown('#unit-type-filter', 'unit_type');
    // initializeDropdown('#pre-occupancy-deposit-filter', 'pre_occupancy_deposit');
    initializeDropdown('#developer-filter', 'developer');
    initializeDropdown('#project-filter', 'project');
    initializeDropdown('#den-filter', 'den');

    // Add more filter initializations as needed...
});