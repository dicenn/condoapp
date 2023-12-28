<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Calculation\Financial;

// kill the wordpress admin bar at the top of the page in the browser
add_filter('show_admin_bar', '__return_false');

// Theme setup function
function condoapptheme_setup() {
    // Add support for various WordPress features here if needed
}
add_action('after_setup_theme', 'condoapptheme_setup' );

// Enqueue styles and scripts
function condoapptheme_enqueue_styles_scripts() {
    wp_enqueue_style( 'condoapp-style', get_stylesheet_uri() ); // This is your main style.css
    wp_enqueue_style( 'condoapp-header', get_template_directory_uri() . '/css/header.css' );
    wp_enqueue_style( 'condoapp-filters', get_template_directory_uri() . '/css/filters.css' );
    wp_enqueue_style( 'condoapp-unit-cards', get_template_directory_uri() . '/css/unit-cards.css' );
    wp_enqueue_style( 'condoapp-speak-agent', get_template_directory_uri() . '/css/speak-agent.css' );
    wp_enqueue_style( 'condoapp-sliders', get_template_directory_uri() . '/css/sliders.css' );
    wp_enqueue_style( 'condoapp-buttons', get_template_directory_uri() . '/css/buttons.css' );

    // Enqueue Bootstrap CSS
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css');
    
    // Enqueue Bootstrap Multiselect CSS
    wp_enqueue_style('bootstrap-multiselect-css', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/1.1.2/css/bootstrap-multiselect.min.css');

    // Enqueue your theme stylesheet
    wp_enqueue_style('condoapptheme-style', get_stylesheet_uri());
    
    // Enqueue Bootstrap JS and Popper.js
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'condoapptheme_enqueue_styles_scripts');

function condoapp_enqueue_scripts() {
    // Enqueue the existing AJAX script
    wp_register_script('condoapp-ajax', get_template_directory_uri() . '/condoapp-ajax.js', array('jquery'), null, true);
    wp_localize_script('condoapp-ajax', 'condoapp_ajax', array(
        'ajax_url'    => admin_url('admin-ajax.php'), // URL for AJAX requests
        'post_url'    => admin_url('admin-post.php'), // URL for form submissions via admin-post.php
        'nonce'       => wp_create_nonce('condoapp_nonce') // General nonce for all AJAX actions
    ));
    wp_enqueue_script('condoapp-ajax');

    // Enqueue Ion.RangeSlider CSS and JS
    wp_enqueue_style('ion-rangeslider', get_template_directory_uri() . '/js/ion.rangeSlider-master/css/ion.rangeSlider.min.css');
    wp_enqueue_script('ion-rangeslider', get_template_directory_uri() . '/js/ion.rangeSlider-master/js/ion.rangeSlider.min.js', array('jquery'), null, true);

    // Enqueue Bootstrap Multiselect JS
    wp_enqueue_script('bootstrap-multiselect-js', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-multiselect/1.1.2/js/bootstrap-multiselect.min.js', array('jquery', 'bootstrap-js'), null, true);

    // Get all filter ranges
    $filter_ranges = get_filter_ranges();

    // Enqueue your custom filters script that initializes Ion.RangeSlider and Bootstrap Multiselect
    wp_register_script('condoapp-filters', get_template_directory_uri() . '/js/condoapp-filters.js', array('jquery', 'ion-rangeslider', 'bootstrap-multiselect-js', 'condoapp-ajax'), null, true);
    wp_localize_script('condoapp-filters', 'condoapp_filter_data', $filter_ranges);
    wp_enqueue_script('condoapp-filters');

    // Enqueue the IRR calculator script
    wp_enqueue_script('condoapp-irr-calculator', get_template_directory_uri() . '/js/condoapp-irr.js', array('jquery'), null, true);
    wp_localize_script('condoapp-irr-calculator', 'condoapp_irr_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('condoapp_irr_nonce') // Specific nonce for IRR AJAX actions
    ));
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
    // echo $html . "<!-- SQL Debug: " . htmlspecialchars($debug_sql) . " -->";
    echo $html;
    // echo "SQL Debug: " . htmlspecialchars($debug_sql);
    wp_die();
}
add_action('wp_ajax_nopriv_load_more_units', 'condoapp_load_more_units');
add_action('wp_ajax_load_more_units', 'condoapp_load_more_units');

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

// gets units from the database both initially on page load and based on filters set by user
function get_filtered_units_sql($filters = array(), $offset = 0, $limit = 10) {
    global $wpdb;

    // Base SQL query
    $sql = "SELECT
                *
            FROM wp_condoappdev.pre_con_unit_database_20230827_v4 u
                LEFT JOIN wp_condoappdev.pre_con_pdf_jpg_database_20230827 j ON j.pdf_link = u.floor_plan_link
                LEFT JOIN (
                    select
                        project as project_dd
                        ,CASE WHEN deposit_date LIKE '%/%/%' THEN DATE_FORMAT(STR_TO_DATE(deposit_date, '%m/%d/%Y'), '%Y-%m-%d') ELSE deposit_date END AS deposit_date
                    from wp_condoappdev.deposit_structure
                        where deposit_occupancy = 'TRUE') d ON d.project_dd = u.project
                left join (
                    select
                        project as project_pd
                        ,ROUND(SUM(deposit_percent), 2) as pre_occupancy_deposit
                    FROM wp_condoappdev.deposit_structure
                        WHERE deposit_occupancy = ''
                    GROUP BY project) pd on pd.project_pd = u.project";
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
    if (isset($filters['occupancy_date_range']['min']) && isset($filters['occupancy_date_range']['max'])) {
        $where_clauses[] = $wpdb->prepare("d.deposit_date BETWEEN %s AND %s", $filters['occupancy_date_range']['min'], $filters['occupancy_date_range']['max']);
    }

    // Dropdown filters
    $dropdown_filters = ['bedrooms', 'bathrooms', 'unit_type', 'developer', 'project', 'den', 'pre_occupancy_deposit'];
    foreach ($dropdown_filters as $filter) {
        if (!empty($filters[$filter])) {
            // Use a different schema prefix for the pre_occupancy_deposit filter
            if ($filter === 'pre_occupancy_deposit') {
                $where_clauses[] = "pd.$filter IN ('" . implode("', '", array_map('esc_sql', $filters[$filter])) . "')";
            } else {
                $where_clauses[] = "u.$filter IN ('" . implode("', '", array_map('esc_sql', $filters[$filter])) . "')";
            }
        }
    }    
    
    // Append WHERE clauses if any
    if (!empty($where_clauses)) {
        $sql .= " WHERE " . implode(' AND ', $where_clauses);
    }

    // Add LIMIT and OFFSET
    $sql .= $wpdb->prepare(" ORDER BY u.id LIMIT %d OFFSET %d", $limit, $offset);

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
        FROM wp_condoappdev.pre_con_unit_database_20230827_v4 u
        WHERE $column IS NOT NULL
    ");
    return $query ?: []; // Return an empty array if the query fails
}

// gets the values for slider filters
function get_min_max_values($column) {
    global $wpdb;
    $query = $wpdb->get_row("
        SELECT
            MIN($column) as min_value
            ,MAX($column) as max_value 
        FROM wp_condoappdev.pre_con_unit_database_20230827_v4 u
            LEFT JOIN wp_condoappdev.pre_con_pdf_jpg_database_20230827 j ON j.pdf_link = u.floor_plan_link
            LEFT JOIN (
                select
                    project as project_dd
                    ,CASE WHEN deposit_date LIKE '%/%/%' THEN DATE_FORMAT(STR_TO_DATE(deposit_date, '%m/%d/%Y'), '%Y-%m-%d') ELSE deposit_date END AS deposit_date
                from wp_condoappdev.deposit_structure
                where deposit_occupancy = 'TRUE') d ON d.project_dd = u.project"
    );
    return $query ?: ['min_value' => null, 'max_value' => null];
}

// gets the values for the occupancy date slider filter specifically
function get_pre_occupancy_deposits() {
    global $wpdb;
    $query = "
        SELECT DISTINCT
            ROUND(SUM(deposit_percent), 2) as pre_occupancy_deposit
        FROM wp_condoappdev.deposit_structure
        WHERE deposit_occupancy = ''
        GROUP BY project
    ";
    $results = $wpdb->get_col($query);
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
    // Fetch all cash flow data
    $cashflowData = fetch_cashflow_data();

    // Set a default appreciation rate
    $appreciationRate = 0.06;

    // Process and calculate cash flows for the specific unit
    $processedData = process_and_calculate_cashflows($cashflowData, null, $appreciationRate, null);

    // Find the processed data for the current unit
    $unitProcessedData = null;
    foreach ($processedData as $data) {
        if ($data['id'] == $unit->id) {
            $unitProcessedData = $data;
            break;
        }
    }

    // Check if processed data was found
    if ($unitProcessedData === null) {
        return 'No cash flow data available for this unit.';
    }

    // Determine the default values for holding period and rent
    $holdingPeriod = $unitProcessedData['occupancy_index'];
    $rent = 0;
    foreach ($unitProcessedData['rent'] as $rentValue) {
        if ($rentValue > 0) {
            $rent = $rentValue;
            break;
        }
    }

    // Calculate XIRR
    $xirrResult = calculateXIRR($unitProcessedData['net_cash_flows'], $unitProcessedData['corresponding_date']);

    // echo esc_html($unit->project) . " | " . 
    // esc_html($unit->model) . " | " . 
    // esc_html($unit->unit_number) . "<br>";
     
    if (is_numeric($xirrResult)) {
        // It's a number, so format it as a percentage
        $xirrFormatted = esc_html(number_format($xirrResult * 100, 2)) . '%';
    } else {
        // It's an error message, so handle it accordingly
        $xirrFormatted = $xirrResult; // Display the error message
    }

    // Generate the unit card HTML
    $output = '<div class="unit-card" style="border: 1px solid #ddd; margin-bottom: 10px;" data-unit-id="' . esc_attr($unit->id) . '">';
        $output .= esc_html($unit->project) . ' | ' .
            // ... rest of the unit card details ...
            'Model ' . esc_html($unit->model) . ' | ' .
            'Unit #' . esc_html($unit->unit_number) . ' | ' .
            'Price: $' . esc_html(number_format($unit->price)) . ' | ' .
            'Beds: ' . esc_html($unit->bedrooms) . ' | ' .
            'Baths: ' . esc_html($unit->bathrooms) . ' | ' .
            'Sqft: ' . esc_html($unit->interior_size) . ' | ' .
            'Developer: ' . esc_html($unit->developer) . ' | ' .
            'Deposit Date: ' . (isset($unit->deposit_date) ? esc_html($unit->deposit_date) : 'N/A') . ' | ' .
            'XIRR: <span class="xirr-result">' . $xirrFormatted . '</span>';

    // Add interactive elements in a container
    $output .= '<div class="unit-card-controls">';
    $output .= '<label>Holding Period (Years): <input type="number" class="holding-period" value="' . esc_attr($holdingPeriod) . '"></label><br>';
    $output .= '<label>Rent ($): <input type="number" class="rent" value="' . esc_attr($rent) . '"></label><br>';
    $output .= '<label>Appreciation Rate (%): <input type="number" class="appreciation-rate" value="' . esc_attr($appreciationRate) . '"></label>';
    $output .= '<button class="calculate-xirr" data-unit-id="' . esc_attr($unit->id) . '">Calculate XIRR</button>';
    $output .= '</div>'; // Close interactive elements container

    $output .= '</div>'; // Close unit card container
    
    return $output;
}

// Define the process_cashflow_data, calculate_net_cash_flows, and calculate_xirr functions as needed
function fetch_cashflow_data() {
    global $wpdb;

    $query = "
        SELECT 
            *
        FROM wp_condoappdev.cashflows_20231120_v4 c
            left join (select id as unit_id, price from wp_condoappdev.pre_con_unit_database_20230827_v4) p on p.unit_id = c.id
        order by id
    ";

    $results = $wpdb->get_results($query, ARRAY_A);

    // Process and return the results
    return $results;
}

function process_and_calculate_cashflows($data, $holdingPeriod, $appreciationRate = 0.06, $rent = null, $useUserInput = false) {

    // echo "<pre>Initial values: Holding Period: $holdingPeriod, Rent: $rent, Appreciation Rate: $appreciationRate, User input: $useUserInput</pre>";

    foreach ($data as $key => $row) {
        // Decode JSON fields
        $data[$key]['deposits'] = json_decode($row['deposits'], true);
        $data[$key]['closing_costs'] = json_decode($row['closing_costs'], true);
        $data[$key]['mortgage_payment'] = json_decode($row['mortgage_payment'], true);
        $data[$key]['mortgage_principal'] = json_decode($row['mortgage_principal'], true);
        $data[$key]['rent'] = json_decode($row['rent'], true);
        $data[$key]['rent_expenses'] = json_decode($row['rent_expenses'], true);
        $data[$key]['rental_net_income'] = json_decode($row['rental_net_income'], true);
    
        if ($useUserInput && $rent !== null) {
            // Overwrite rent array based on user input
            $monthlyAppreciationFactor = pow(1 + 0.03, 1/12) - 1;
            $occupancyIndex = $row['occupancy_index'];
            for ($i = 0; $i < count($data[$key]['rent']); $i++) {
                if ($i < $occupancyIndex) {
                    $data[$key]['rent'][$i] = 0;
                } elseif ($i == $occupancyIndex) {
                    $data[$key]['rent'][$i] = $rent;
                } else {
                    $data[$key]['rent'][$i] = $data[$key]['rent'][$i - 1] * (1 + $monthlyAppreciationFactor);
                }
            }

            // Recalculate rental net income
            for ($i = 0; $i < count($data[$key]['rent']); $i++) {
                $data[$key]['rental_net_income'][$i] = $data[$key]['rent'][$i] - $data[$key]['rent_expenses'][$i];
            }
        }
        
        // Local variables to hold values for each unit
        $localHoldingPeriod = $useUserInput ? $holdingPeriod + 1 : ($row['occupancy_index'] + 1);
        $localAppreciationRate = $appreciationRate;
    
        // Calculate net cash flows
        $netCashFlows = [];
        $price = $row['price']; // Assuming 'price' is a field in your data
        $selling_costs = $row['selling_costs']; // Assuming 'selling_costs' is a field in your data
        $mortgage_payments_year = $row['mortgage_payments_year']; // Assuming this is a field in your data

        for ($i = 0; $i < $localHoldingPeriod; $i++) {
            $netCashFlow = -($data[$key]['deposits'][$i] ?? 0)
                          - ($data[$key]['closing_costs'][$i] ?? 0)
                          - ($data[$key]['mortgage_payment'][$i] ?? 0)
                          + ($data[$key]['rental_net_income'][$i] ?? 0);
            array_push($netCashFlows, $netCashFlow);
        }

        // Calculate the sale price of the property at the end of the holding period
        $salePrice = $price * pow(pow(1 + $appreciationRate,(1 / $mortgage_payments_year) ), $localHoldingPeriod - 1);

        // Calculate the selling costs
        $sellingCosts = $salePrice * $selling_costs;

        // Calculate the amount remaining on the mortgage
        $depositsSum = array_sum(array_slice($data[$key]['deposits'], 0, $localHoldingPeriod));
        $principalSum = array_sum(array_slice($data[$key]['mortgage_principal'], 0, $localHoldingPeriod));
        $mortgageRemaining = ($price - $depositsSum) - $principalSum;

        // Adjust the last cash flow
        $last_cashflow_addition = $salePrice - $sellingCosts - $mortgageRemaining;
        $netCashFlows[$localHoldingPeriod - 1] += $last_cashflow_addition;

        // echo "<pre>Net cashflows: "; print_r($netCashFlows); echo "</pre>";

        $data[$key]['net_cash_flows'] = $netCashFlows;

    }

    return $data;
}

function calculateXIRR($netCashFlows, $correspondingDateStrings) {

    // Preprocess the date strings if they are not in proper JSON format
    $jsonFormattedDateStrings = preg_replace('/(\d{4}-\d{2}-\d{2})/', '"$1"', $correspondingDateStrings);
    $correspondingDatesArray = json_decode($jsonFormattedDateStrings, true);

    // Check if the decoding was successful and the result is an array
    if (!is_array($correspondingDatesArray)) {
        return 'Error: Invalid date format';
    }

    // Ensure the dates array is the same length as the cash flows array
    $correspondingDatesArray = array_slice($correspondingDatesArray, 0, count($netCashFlows));

    // Convert the date strings to DateTime objects
    $dates = array_map(function($date) {
        return new DateTime($date);
    }, $correspondingDatesArray);

    // Convert DateTime objects to Excel date serial numbers
    $excelDates = array_map(function($date) {
        return PhpOffice\PhpSpreadsheet\Shared\Date::dateTimeToExcel($date);
    }, $dates);

    // Debugging: Output the cash flows and dates
    // echo "<pre>Cash Flows: ";
    // print_r($netCashFlows);
    // echo "Dates: ";
    // print_r($excelDates);
    // echo "</pre>";

    try {
        // Calculate XIRR using PhpSpreadsheet's Financial class
        $xirr = Financial::XIRR($netCashFlows, $excelDates);
        return $xirr;
    } catch (Exception $e) {
        // Handle exceptions, such as non-converging calculations
        return 'Error: ' . $e->getMessage();
    }
}

function recalculate_xirr_ajax_handler() {
    // echo 'AJAX handler triggered.';
    // Check for nonce for security
    check_ajax_referer('condoapp_irr_nonce', 'nonce');

    // Echo received data for debugging
    // echo '<pre>Received Data: ';
    // print_r($_POST);
    // echo '</pre>';

    $unit_id = isset($_POST['unit_id']) ? intval($_POST['unit_id']) : 0;
    $holding_period = isset($_POST['holding_period']) ? intval($_POST['holding_period']) : null;
    $rent = isset($_POST['rent']) ? floatval($_POST['rent']) : null;
    $appreciation_rate = isset($_POST['appreciation_rate']) ? floatval($_POST['appreciation_rate']) : 0.06;

    // Echo processed values for debugging
    // echo "<pre>Processed Data: Unit ID: $unit_id, Holding Period: $holding_period, Rent: $rent, Appreciation Rate: $appreciation_rate</pre>";

    // Fetch all cash flow data
    $cashflowData = fetch_cashflow_data();
    // Process and calculate cash flows for the specific unit

    // echo "<pre>Passing to function: Holding Period: $holding_period, Rent: $rent, Appreciation Rate: $appreciation_rate, User Input: true</pre>";

    $processedData = process_and_calculate_cashflows($cashflowData, $holding_period, $appreciation_rate, $rent, true);
    
    // Echo processed cash flow data for debugging
    // echo '<pre>Processed Cash Flows: ';
    // print_r($processedData);
    // echo '</pre>';

    // Find the processed data for the current unit
    $unitProcessedData = null;
    foreach ($processedData as $data) {
        if ($data['id'] == $unit_id) {
            $unitProcessedData = $data;
            break;
        }
    }

    if ($unitProcessedData === null) {
        echo 'No cash flow data available for this unit.';
        wp_die();
    }

    // Debug: Print keys (headers) of unitProcessedData array
    // if (is_array($unitProcessedData) && !empty($unitProcessedData)) {
    //     echo "Available keys in unitProcessedData:\n";
    //     echo "<pre>" . implode(", ", array_keys($unitProcessedData)) . "</pre>";
    // } else {
    //     echo "unitProcessedData is empty or not an array.";
    // }

    // log_cash_flows($unitProcessedData);

    // echo "<pre>Cash Flows for Unit ID {$unit_id}: ";
    // print_r($unitProcessedData['net_cash_flows']);
    // echo "</pre>";

    // Calculate XIRR
    $xirrResult = calculateXIRR($unitProcessedData['net_cash_flows'], $unitProcessedData['corresponding_date']);

    // Echo XIRR calculation result for debugging
    // echo '<pre>XIRR Calculation Result: ' . $xirrResult . '</pre>';
    
    if (is_numeric($xirrResult)) {
        $xirrFormatted = number_format($xirrResult * 100, 2) . '%';
        wp_send_json_success(array('new_xirr' => $xirrFormatted));
    } else {
        wp_send_json_error('Error: ' . $xirrResult);
    }
    wp_die(); // Terminate to ensure no further output is sent
}
add_action('wp_ajax_recalculate_xirr', 'recalculate_xirr_ajax_handler');
add_action('wp_ajax_nopriv_recalculate_xirr', 'recalculate_xirr_ajax_handler');

function log_cash_flows($unitProcessedData) {
    // $themeDirectory = get_template_directory(); // Gets the directory of the current theme
    // $logDirectory = $themeDirectory . '/logs'; // Path to the logs directory
    // $logFile = $logDirectory . '/cashflow_log.csv'; // Path to your log file
    
    // echo '<pre>unitProcessedData: ';
    // print_r($unitProcessedData);
    // echo '</pre>';

    // Hardcoded file path
    $logFile = '/Applications/XAMPP/xamppfiles/htdocs/condoapp/wordpress/wp-content/themes/condoapptheme/logs/cashflow_log.csv';

    // Open the file for writing
    $fileHandle = fopen($logFile, 'a');
    if (!$fileHandle) {
        echo "Failed to open log file for writing";
        return;
    }

    // Determine the maximum index in the 'net_cash_flows' array
    $maxIndex = count($unitProcessedData['net_cash_flows']) - 1;

    // Current timestamp for logging
    $timestamp = date('Y-m-d H:i:s');

    // Loop through the arrays up to maxIndex and log each entry
    for ($i = 0; $i <= $maxIndex; $i++) {
        $dataToLog = [
            'Timestamp' => $timestamp,
            'Project' => $unitProcessedData['project'] ?? 'N/A',
            'Model' => $unitProcessedData['model'] ?? 'N/A',
            'Unit' => $unitProcessedData['unit'] ?? 'N/A',
            'Deposit' => $unitProcessedData['deposits'][$i] ?? 'N/A',
            'Closing Cost' => $unitProcessedData['closing_costs'][$i] ?? 'N/A',
            'Mortgage Payment' => $unitProcessedData['mortgage_payment'][$i] ?? 'N/A',
            'Mortgage Principal' => $unitProcessedData['mortgage_principal'][$i] ?? 'N/A',
            'Mortgage Interest' => $unitProcessedData['mortgage_interest'][$i] ?? 'N/A',
            'Rent' => $unitProcessedData['rent'][$i] ?? 'N/A',
            'Rent Expense' => $unitProcessedData['rent_expenses'][$i] ?? 'N/A',
            'Rental Net Income' => $unitProcessedData['rental_net_income'][$i] ?? 'N/A',
            'Net Cash Flow' => $unitProcessedData['net_cash_flows'][$i] ?? 'N/A',
            'Corresponding Date' => trim($unitProcessedData['corresponding_date'][$i], "[]") ?? 'N/A'
        ];

        // Check if file is empty to write headers
        if (filesize($logFile) == 0) {
            fputcsv($fileHandle, array_keys($dataToLog));
        }

        // Write data to CSV
        fputcsv($fileHandle, $dataToLog);
    }

    // Close the file
    fclose($fileHandle);
}

function handle_agent_contact_form() {
    global $wpdb;

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'submit_agent_form') {
        // Verify the nonce for security
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'condoapp_nonce')) {
            echo "Nonce verification failed!";
            return;
        }

        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);

        $table_name = 'sign_ups';

        $inserted = $wpdb->insert(
            $table_name,
            array('name' => $name, 'email' => $email, 'phone' => $phone),
            array('%s', '%s', '%s')
        );

        if ($inserted === false) {
            echo json_encode(array('success' => false, 'message' => $wpdb->last_error));
        } else {
            echo json_encode(array('success' => true, 'message' => 'Data inserted successfully.'));
        }        
        exit;
    }
}
add_action('init', 'handle_agent_contact_form');