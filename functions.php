<?php
// Your PHP opening tag

// Theme setup function
function condoapptheme_setup() {
    // Add support for various WordPress features here if needed
}
add_action('after_setup_theme', 'condoapptheme_setup' );

// Enqueue styles and scripts
function condoapptheme_enqueue_styles_scripts() {
    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    
    // Enqueue your theme stylesheet
    wp_enqueue_style('condoapptheme-style', get_stylesheet_uri());
    
    // Enqueue Bootstrap JS and Popper.js
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'condoapptheme_enqueue_styles_scripts');

// Enqueue the JavaScript file
function condoapp_enqueue_scripts() {
    // Enqueue the existing AJAX script
    wp_register_script('condoapp-ajax', get_template_directory_uri() . '/condoapp-ajax.js', array('jquery'), null, true);
    wp_localize_script('condoapp-ajax', 'condoapp_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('condoapp_load_more_nonce')
    ));
    wp_enqueue_script('condoapp-ajax');

    // Enqueue Ion.RangeSlider CSS
    wp_enqueue_style('ion-rangeslider', get_template_directory_uri() . '/js/ion.rangeSlider-master/css/ion.rangeSlider.min.css');

    // Enqueue Ion.RangeSlider JS
    wp_enqueue_script('ion-rangeslider', get_template_directory_uri() . '/js/ion.rangeSlider-master/js/ion.rangeSlider.min.js', array('jquery'), null, true);

    // Enqueue your custom filters script that initializes Ion.RangeSlider
    wp_enqueue_script('condoapp-filters', get_template_directory_uri() . '/js/condoapp-filters.js', array('jquery', 'ion-rangeslider'), null, true);

    // Get the price range
    $price_range = get_price_range();

    // Localize the filters script with the price range
    wp_localize_script('condoapp-filters', 'condoapp_price_range', $price_range);    
}
add_action('wp_enqueue_scripts', 'condoapp_enqueue_scripts');

// The AJAX handler function to load more unit cards
function condoapp_load_more_units() {
    // Verify the nonce for security
    check_ajax_referer('condoapp_load_more_nonce', 'nonce');

    // Retrieve the offset from the AJAX request
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $limit = 10; // The number of units to load with each request

    // Query to get the next set of units
    global $wpdb;
    $units = $wpdb->get_results($wpdb->prepare("
        SELECT
            *
        FROM condo_app.pre_con_unit_database_20230827_v4 u
            LEFT JOIN condo_app.pre_con_pdf_jpg_database_20230827 j on j.pdf_link = u.floor_plan_link
        LIMIT %d OFFSET %d
    ", $limit, $offset));

    // Loop through the units and output the HTML for each unit card
    foreach ($units as $unit) {
        echo condoapp_get_unit_card_html($unit); // Use the function to get the HTML
    }

    wp_die(); // End the AJAX request
}
add_action('wp_ajax_nopriv_load_more_units', 'condoapp_load_more_units');
add_action('wp_ajax_load_more_units', 'condoapp_load_more_units');

function condoapp_get_unit_card_html($unit) {
    // Default image if floor_plan_link is empty
    $default_image = get_template_directory_uri() . '/images/default-floorplan.jpg';
    $image = !empty($unit->jpg_link) ? esc_url($unit->jpg_link) : $default_image;

    // Placeholder data for investment summary
    $investment_data = [
        'annualized_return'       => 'TBD', // To be replaced with actual data
        'pre_occupancy_deposit'   => 'TBD', // To be replaced with actual data
        'cash_on_cash_return'     => 'TBD', // To be replaced with actual data
        'projected_rent'          => 'TBD', // To be replaced with actual data
        'holding_period'          => 'TBD', // To be replaced with actual data
        'projected_appreciation'  => 'TBD', // To be replaced with actual data
    ];
    
    ob_start(); // Start output buffering
    ?>
    <div class="card mb-3">
        <div class="row no-gutters">
            <!-- Floor Plan Image Section -->
            <div class="col-md-4">
                <div class="image-header">
                    <!-- Placeholder for the image header -->
                    <span><?php echo esc_html($unit->bedrooms); ?> beds | <?php echo esc_html($unit->bathrooms); ?> baths | <?php echo esc_html($unit->interior_size); ?> sqft interior</span>
                </div>
                <img src="<?php echo $image; ?>" class="card-img" alt="Floor Plan">
            </div>

            <!-- Unit Details Section -->
            <div class="col-md-5">
                <div class="card-body">
                    <h5 class="card-title"><?php echo esc_html($unit->project); ?> | Model <?php echo esc_html($unit->model); ?> | Unit #<?php echo esc_html($unit->unit_number); ?></h5>
                    <p class="card-text">Starting from $<?php echo esc_html(number_format($unit->price)); ?></p>
                    <p class="card-text">Occupancy: <?php echo esc_html($unit->occupancy_date); ?></p>
                    <p class="card-text">Developer: <?php echo esc_html($unit->developer); ?></p>
                    <p class="card-text"><?php echo esc_html($unit->address); ?></p>
                    <!-- Add more unit-specific details here -->
                </div>
            </div>

            <!-- Investment Summary Section -->
            <div class="col-md-3">
                <div class="card-body">
                    <h5 class="card-title">Investment Summary</h5>
                    <p class="card-text">+<?php echo esc_html($investment_data['annualized_return']); ?>% annualized return</p>
                    <p class="card-text"><?php echo esc_html($investment_data['pre_occupancy_deposit']); ?>% pre-occupancy deposit</p>
                    <p class="card-text">$<?php echo esc_html($investment_data['cash_on_cash_return']); ?> Cash-on-cash return</p>
                    <p class="card-text">$<?php echo esc_html($investment_data['projected_rent']); ?> projected rent</p>
                    <p class="card-text"><?php echo esc_html($investment_data['holding_period']); ?> year holding period</p>
                    <p class="card-text"><?php echo esc_html($investment_data['projected_appreciation']); ?>% Projected appreciation</p>
                    <!-- More investment details here -->
                    <button class="btn btn-primary">Speak to an Agent</button>
                    <!-- Icons for interaction -->
                </div>
            </div>
        </div>
    </div>
    <?php
    $html = ob_get_clean(); // Store the contents of the output buffer and clear it
    return $html;
}

function get_price_range() {
    global $wpdb;

    // Query the highest and lowest prices
    $price_query = $wpdb->get_row("
        SELECT
            MIN(CAST(price AS UNSIGNED)) as min_price,
            MAX(CAST(price AS UNSIGNED)) as max_price
        FROM pre_con_unit_database_20230827_v4
    ");

    // Check if the query was successful and we have non-null prices
    if (is_null($price_query->min_price) || is_null($price_query->max_price)) {
        return array('min' => 0, 'max' => 0); // Default values if no prices are found
    }

    // Round the values to the nearest 100K
    $min_price = floor($price_query->min_price / 100000) * 100000;
    $max_price = ceil($price_query->max_price / 100000) * 100000;

    return array('min' => $min_price, 'max' => $max_price);
}
