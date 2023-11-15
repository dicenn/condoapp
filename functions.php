<?php
// Your PHP opening tag

// kill the wordpress admin bar at the top of the page in the browser
add_filter('show_admin_bar', '__return_false');

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
        'nonce'    => wp_create_nonce('condoapp_nonce') // General nonce for all AJAX actions
    ));
    wp_enqueue_script('condoapp-ajax');

    // Enqueue Ion.RangeSlider CSS and JS
    wp_enqueue_style('ion-rangeslider', get_template_directory_uri() . '/js/ion.rangeSlider-master/css/ion.rangeSlider.min.css');
    wp_enqueue_script('ion-rangeslider', get_template_directory_uri() . '/js/ion.rangeSlider-master/js/ion.rangeSlider.min.js', array('jquery'), null, true);

    // Get all filter ranges
    $filter_ranges = get_filter_ranges();

    // Enqueue your custom filters script that initializes Ion.RangeSlider
    wp_register_script('condoapp-filters', get_template_directory_uri() . '/js/condoapp-filters.js', array('jquery', 'ion-rangeslider', 'condoapp-ajax'), null, true);
    wp_localize_script('condoapp-filters', 'condoapp_filter_data', $filter_ranges);
    wp_enqueue_script('condoapp-filters');
}
add_action('wp_enqueue_scripts', 'condoapp_enqueue_scripts');

// The AJAX handler function to load more unit cards
function condoapp_load_more_units() {
    // Verify the nonce for security
    check_ajax_referer('condoapp_nonce', 'nonce');

    // Retrieve offset and filters from AJAX request
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $filters = isset($_POST['filters']) ? $_POST['filters'] : array(); // Capture all filters
    $limit = 10;

    // Get units and SQL query for debugging
    $query_data = get_filtered_units_sql($filters, $offset, $limit);
    $units = $query_data['results'];
    $debug_sql = $query_data['sql'];

    // Generate HTML for unit cards
    $html = '';
    foreach ($units as $unit) {
        $html .= condoapp_get_unit_card_html($unit);
    }

    // Echo HTML and SQL query for debugging (remove SQL echo in production)
    echo $html . "<!-- SQL Debug: " . htmlspecialchars($debug_sql) . " -->";
    echo "SQL Debug: " . htmlspecialchars($debug_sql);
    wp_die();
}
add_action('wp_ajax_nopriv_load_more_units', 'condoapp_load_more_units');
add_action('wp_ajax_load_more_units', 'condoapp_load_more_units');

// function condoapp_filter_units_by_price() {
//     // Verify the nonce for security
//     check_ajax_referer('condoapp_nonce', 'nonce');

//     // Debug: Check if the function is firing
//     // echo 'Function condoapp_filter_units_by_price is firing.<br>';

//     // Retrieve filters from AJAX request
//     $filters = isset($_POST['filters']['price_range']) ? $_POST['filters']['price_range'] : array();

//     // Debug: Output the received filters
//     // echo 'Received filters: ' . json_encode($filters) . '<br>';
//     // error_log('Received filters: ' . print_r($_POST['filters'], true));

//     $limit = 10;
//     $offset = 0; // Always start from the first set of units when filtering

//     // Get units
//     $units = get_filtered_units_sql($filters, $offset, $limit);

//     // Debug: Output the number of units retrieved
//     // echo 'Number of units retrieved: ' . count($units) . '<br>';
//     // error_log('Number of units retrieved: ' . count($units));

//     // Generate HTML for unit cards
//     $html = '';
//     foreach ($units as $unit) {
//         $html .= condoapp_get_unit_card_html($unit);
//     }

//     // Debug: Output the generated HTML
//     // echo 'Generated HTML: ' . htmlspecialchars($html) . '<br>';

//     // Echo HTML and end AJAX request
//     echo $html;
//     wp_die();
// }
// add_action('wp_ajax_nopriv_filter_units_by_price', 'condoapp_filter_units_by_price');
// add_action('wp_ajax_filter_units_by_price', 'condoapp_filter_units_by_price');

// generates the html unit card
function condoapp_get_unit_card_html_robust($unit) {
    // Default image if floor_plan_link is empty
    // $default_image = get_template_directory_uri() . '/images/default-floorplan.jpg';
    // $image = !empty($unit->jpg_link) ? esc_url($unit->jpg_link) : $default_image;
    $image = esc_url($unit->jpg_link);

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

// obtains the min and max price from database and uses this to inform what price range to make the slider
// function get_price_range() {
//     global $wpdb;

//     // Query the highest and lowest prices
//     $price_query = $wpdb->get_row("
//         SELECT
//             MIN(CAST(price AS UNSIGNED)) as min_price,
//             MAX(CAST(price AS UNSIGNED)) as max_price
//         FROM pre_con_unit_database_20230827_v4
//     ");

//     // Check if the query was successful and we have non-null prices
//     if (is_null($price_query->min_price) || is_null($price_query->max_price)) {
//         return array('min' => 0, 'max' => 0); // Default values if no prices are found
//     }

//     // Round the values to the nearest 100K
//     $min_price = floor($price_query->min_price / 100000) * 100000;
//     $max_price = ceil($price_query->max_price / 100000) * 100000;

//     return array('min' => $min_price, 'max' => $max_price);
// }

// gets units from the database both initially on page load and based on filters set by user
function get_filtered_units_sql($filters = array(), $offset = 0, $limit = 10) {
    global $wpdb;

    // Base SQL query
    $sql = "SELECT *
            FROM condo_app.pre_con_unit_database_20230827_v4 u
            LEFT JOIN condo_app.pre_con_pdf_jpg_database_20230827 j ON j.pdf_link = u.floor_plan_link
            LEFT JOIN (select project as project_dd, deposit_date from condo_app.deposit_structure where deposit_occupancy = 'TRUE') d ON d.project_dd = u.project";

    // WHERE clauses
    $where_clauses = array();

    // Price range filter
    if (isset($filters['price_range']['min']) && isset($filters['price_range']['max'])) {
        $where_clauses[] = $wpdb->prepare("u.price BETWEEN %d AND %d", $filters['price_range']['min'], $filters['price_range']['max']);
    }

    // Square footage range filter
    if (isset($filters['square_footage_range']['min']) && isset($filters['square_footage_range']['max'])) {
        $where_clauses[] = $wpdb->prepare("u.interior_size BETWEEN %d AND %d", $filters['square_footage_range']['min'], $filters['square_footage_range']['max']);
    }

    // Occupancy date range filter
    // if (isset($filters['occupancy_date_range']['min']) && isset($filters['occupancy_date_range']['max'])) {
    //     $where_clauses[] = $wpdb->prepare("u.occupancy_date BETWEEN %s AND %s", $filters['occupancy_date_range']['min'], $filters['occupancy_date_range']['max']);
    // }

    // Dropdown filters
    $dropdown_filters = ['bedrooms', 'bathrooms', 'unit_type', 'developer', 'project', 'den'];
    foreach ($dropdown_filters as $filter) {
        if (!empty($filters[$filter])) {
            $where_clauses[] = "u.$filter IN ('" . implode("', '", array_map('esc_sql', $filters[$filter])) . "')";
        }
    }

    // Pre-occupancy deposit filter
    // Assuming this filter requires a special handling
    if (!empty($filters['pre_occupancy_deposit'])) {
        // Add your specific condition for pre_occupancy_deposit here
        // Example: $where_clauses[] = "d.pre_occupancy_deposit IN ('" . implode("', '", array_map('esc_sql', $filters['pre_occupancy_deposit'])) . "')";
    }

    // Append WHERE clauses if any
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }

    // Add LIMIT and OFFSET
    $sql .= $wpdb->prepare(" LIMIT %d OFFSET %d", $limit, $offset);

    // Execute the query
    $results = $wpdb->get_results($sql);

    // Return both the SQL query and the results
    return array('results' => $results, 'sql' => $sql);
}

// gets the values for dropdown filters
function get_distinct_values($column) {
    global $wpdb;
    $query = $wpdb->get_col("
        SELECT DISTINCT $column 
        FROM condo_app.pre_con_unit_database_20230827_v4 u
        WHERE $column IS NOT NULL
    ");
    return $query ?: []; // Return an empty array if the query fails
}

// gets the values for slider filters
function get_min_max_values($column) {
    global $wpdb;
    $query = $wpdb->get_row("
        SELECT MIN($column) as min_value, MAX($column) as max_value 
        FROM pre_con_unit_database_20230827_v4 u
            LEFT JOIN condo_app.pre_con_pdf_jpg_database_20230827 j ON j.pdf_link = u.floor_plan_link
            LEFT JOIN condo_app.deposit_structure d ON d.project = u.project AND deposit_occupancy = 'TRUE'
    ");
    return $query ?: ['min_value' => null, 'max_value' => null];
}

// gets the values for the occupancy date slider filter specifically
function get_pre_occupancy_deposits() {
    global $wpdb;
    $query = "
        SELECT
            project,
            ROUND(SUM(deposit_percent), 2) as pre_occupancy_deposit
        FROM condo_app.deposit_structure
        WHERE deposit_occupancy = ''
        GROUP BY project
    ";
    $results = $wpdb->get_results($query, ARRAY_A);
    return $results ?: []; // Return an empty array if the query fails or returns no results
}

// calls the filter functions (get_distinct_values, get_min_max_values, get_pre_occupancy_deposits) and gets their values for use in the filters themselves
function get_filter_ranges() {
    // Get ranges for slider filters
    $price_range = get_min_max_values('price');
    $square_footage_range = get_min_max_values('interior_size');
    $occupancy_date_range = get_min_max_values('deposit_date');

    // Get distinct values for dropdown filters
    $bedrooms = get_distinct_values('bedrooms');
    $bathrooms = get_distinct_values('bathrooms');
    $unit_type = get_distinct_values('unit_type');
    $pre_occupancy_deposit = get_pre_occupancy_deposits();
    $developer = get_distinct_values('developer');
    $project = get_distinct_values('project');
    $den = get_distinct_values('den');

    return [
        'price_range' => $price_range,
        'square_footage_range' => $square_footage_range,
        'occupancy_date_range' => $occupancy_date_range,
        'bedrooms' => $bedrooms,
        'bathrooms' => $bathrooms,
        'unit_type' => $unit_type,
        'pre_occupancy_deposit' => $pre_occupancy_deposit,
        'developer' => $developer,
        'project' => $project,
        'den' => $den
    ];
}

function condoapp_filter_units() {
    // Verify the nonce for security
    check_ajax_referer('condoapp_nonce', 'nonce');

    // Retrieve filters from AJAX request
    $filters = isset($_POST['filters']) ? $_POST['filters'] : array();

    // Set default values for filters if not provided
    $default_filters = array(
        'price_range' => array('min' => 0, 'max' => PHP_INT_MAX),
        'square_footage_range' => array('min' => 0, 'max' => PHP_INT_MAX),
        // Add default values for other filters
        'occupancy_date_range' => array('min' => '1900-01-01', 'max' => '2100-01-01'),
        'bedrooms' => array(),
        'bathrooms' => array(),
        'unit_type' => array(),
        'pre_occupancy_deposit' => array(),
        'developer' => array(),
        'project' => array(),
        'den' => array(),
        // ... Add more filters as needed
    );

    // Merge received filters with default values
    $filters = array_merge($default_filters, $filters);

    $limit = 10;
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

    // Get units based on filters
    $query_data = get_filtered_units_sql($filters, $offset, $limit);

    // Generate HTML for unit cards
    $html = '';
    foreach ($query_data['results'] as $unit) {
        $html .= condoapp_get_unit_card_html($unit);
    }

    // Echo HTML and end AJAX request
    echo $html;
    wp_die();
}
add_action('wp_ajax_nopriv_filter_units', 'condoapp_filter_units');
add_action('wp_ajax_filter_units', 'condoapp_filter_units');

// temporary get unit card function for testing purposes
function condoapp_get_unit_card_html($unit) {
    // Simplified output for testing
    $output = '<div style="border: 1px solid #ddd; margin-bottom: 10px;">'; // Container with border and margin
    $output .= esc_html($unit->project) . ' | ' .
               'Model ' . esc_html($unit->model) . ' | ' .
               'Unit #' . esc_html($unit->unit_number) . ' | ' .
               'Price: $' . esc_html(number_format($unit->price)) . ' | ' .
               'Beds: ' . esc_html($unit->bedrooms) . ' | ' .
               'Baths: ' . esc_html($unit->bathrooms) . ' | ' .
               'Sqft: ' . esc_html($unit->interior_size) . ' | ' .
               'Occupancy: ' . esc_html($unit->occupancy_date) . ' | ' .
               'Developer: ' . esc_html($unit->developer) . ' | ' .
            //    'Deposit Date: ' . (isset($unit->deposit_date) ? esc_html($unit->deposit_date) : 'N/A');
    $output .= '</div>'; // Close container

    return $output;
}