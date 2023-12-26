// event listener for the 'speak to an agent' form
document.addEventListener('DOMContentLoaded', function() {
    // Modal handling code
    var modal = document.getElementById("agentModal");
    var btn = document.getElementById("speakToAgentButton");
    var span = document.getElementById("closeModal");

    if (btn && modal) {
        btn.onclick = function() {
            modal.style.display = "block";
        };
    }

    if (span) {
        span.onclick = function() {
            modal.style.display = "none";
        };
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    };

    // Form submission handling with AJAX
    var form = document.getElementById('agentContactForm');
    var messageElement = document.getElementById('formSubmissionMessage');

    if (form && messageElement) {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            var formData = new FormData(this);
            formData.append('_wpnonce', condoapp_ajax.nonce); // Add nonce to the form data

            // AJAX request to admin-post.php
            fetch(condoapp_ajax.post_url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // console.log(data); // You can see the response here
                messageElement.innerText = 'Thanks for submitting. We will contact you soon.';
            })
            .catch(error => console.error('Error:', error));
        });
    }
});

// hamburger menu event listener
document.querySelector('.hamburger-menu-button').addEventListener('click', function() {
    document.querySelector('.hamburger-menu-content').classList.toggle('open');
});

// toggle that expands and contracts the side filter panel
function toggleNav() {
    var sidepanel = document.getElementById("mySidepanel");
    var toggleButton = document.getElementById("toggleButton");

    if (sidepanel.style.transform === "translateX(0px)") {
        sidepanel.style.transform = "translateX(-100%)"; // Move panel out of view
        toggleButton.style.left = '0'; // Move button to its original position
    } else {
        sidepanel.style.transform = "translateX(0px)"; // Move panel into view
        toggleButton.style.left = '250px'; // Move button to the right by the width of the panel
    }
}

// console.log('condoapp-ajax.js is loaded');

// Define currentFilters in the global scope
window.currentFilters = {
    price_range: {
        min: 0, // Default values, will be updated when condoapp-filters.js runs
        max: 0
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
        max: 0
    },
    occupancy_date_range: {
        min: '', // Use appropriate default value
        max: ''  // Use appropriate default value
    }
};

window.offset = 10; // Initialize with 10 as the first set of units is already loaded

jQuery(document).ready(function($) {
    // Update currentFilters with actual values from condoapp_filter_data
    if (condoapp_filter_data) {
        // Price Range
        if (condoapp_filter_data.price_range) {
            window.currentFilters.price_range.min = condoapp_filter_data.price_range.min_value;
            window.currentFilters.price_range.max = condoapp_filter_data.price_range.max_value;
        }
    
        // Square Footage Range
        if (condoapp_filter_data.square_footage_range) {
            window.currentFilters.square_footage_range.min = condoapp_filter_data.square_footage_range.min_value;
            window.currentFilters.square_footage_range.max = condoapp_filter_data.square_footage_range.max_value;
        }
    
        // Occupancy Date Range
        if (condoapp_filter_data.occupancy_date_range) {
            window.currentFilters.occupancy_date_range.min = condoapp_filter_data.occupancy_date_range.min_value;
            window.currentFilters.occupancy_date_range.max = condoapp_filter_data.occupancy_date_range.max_value;
        }
    
        // Bedrooms
        if (condoapp_filter_data.bedrooms) {
            window.currentFilters.bedrooms = condoapp_filter_data.bedrooms;
        }
    
        // Bathrooms
        if (condoapp_filter_data.bathrooms) {
            window.currentFilters.bathrooms = condoapp_filter_data.bathrooms;
        }
    
        // Unit Type
        if (condoapp_filter_data.unit_type) {
            window.currentFilters.unit_type = condoapp_filter_data.unit_type;
        }
    
        // Pre-Occupancy Deposit
        if (condoapp_filter_data.pre_occupancy_deposit) {
            window.currentFilters.pre_occupancy_deposit = condoapp_filter_data.pre_occupancy_deposit;
        }
    
        // Developer
        if (condoapp_filter_data.developer) {
            window.currentFilters.developer = condoapp_filter_data.developer;
        }
    
        // Project
        if (condoapp_filter_data.project) {
            window.currentFilters.project = condoapp_filter_data.project;
        }
    
        // Den
        if (condoapp_filter_data.den) {
            window.currentFilters.den = condoapp_filter_data.den;
        }
    }    

    let loading = false; // To prevent multiple simultaneous loads

    // console.log('currentFilters variable initialized with:', window.currentFilters);

    $(window).scroll(function() {
        console.log('Scrolled');
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 100 && !loading && !window.allUnitsLoaded) {
            loading = true;

            $('#spinner-container').show();
            console.log('Sending AJAX request...');

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
                    
                },
                success: function(response) {
                    // Append new unit cards
                    $('#unit-cards').append(response.replace(/<!--.*?-->/g, '')); // Remove debug comments and append new content
                
                    // Move spinner to the end of the content
                    $('#unit-cards').append($('#spinner-container'));
                    $('#unit-cards').append($('#no-more-units-message'));
                
                    // Hide loading indicator
                    $('#spinner-container').hide();
                    $('#no-more-units-message').hide();
                
                    // console.log("AJAX Success Response:", response); // Print the response for debugging

                    // Check if more content was loaded
                    if (response.trim() === '') {
                        console.log("No more units to load.");
                        $('#no-more-units-message').show();
                        window.allUnitsLoaded = true; // Update the flag as all units are loaded
                    } else {
                        window.offset += 10; // Increment offset for next load
                    }
                
                    // Reset the loading flag
                    loading = false;
                    console.log("New offset:", window.offset);
                },                            
                error: function(jqXHR, textStatus, errorThrown) {
                    // Hide loading indicator
                    $('#spinner-container').hide();
                    console.log('AJAX request failed:', textStatus, errorThrown);
                    loading = false;
                },
                complete: function() {
                    // Ensure the loading indicator is hidden after the request completes
                    $('#spinner-container').hide();
                }
            });
        }
    });
});