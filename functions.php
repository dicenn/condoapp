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
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('condoapp_nonce') // General nonce for all AJAX actions
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
    // wp_enqueue_script('condoapp-irr-calculator', get_template_directory_uri() . '/js/condoapp-irr.js', array('jquery'), null, true);
    // wp_localize_script('condoapp-irr-calculator', 'condoapp_irr', array(
    //     'ajax_url' => admin_url('admin-ajax.php'),
    //     'nonce'    => wp_create_nonce('condoapp_irr_nonce') // Specific nonce for IRR AJAX actions
    // ));
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
            FROM condo_app.pre_con_unit_database_20230827_v4 u
                LEFT JOIN condo_app.pre_con_pdf_jpg_database_20230827 j ON j.pdf_link = u.floor_plan_link
                LEFT JOIN (
                    select
                        project as project_dd
                        ,CASE WHEN deposit_date LIKE '%/%/%' THEN DATE_FORMAT(STR_TO_DATE(deposit_date, '%m/%d/%Y'), '%Y-%m-%d') ELSE deposit_date END AS deposit_date
                    from condo_app.deposit_structure
                        where deposit_occupancy = 'TRUE') d ON d.project_dd = u.project
                left join (
                    select
                        project as project_pd
                        ,ROUND(SUM(deposit_percent), 2) as pre_occupancy_deposit
                    FROM condo_app.deposit_structure
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
        SELECT
            MIN($column) as min_value
            ,MAX($column) as max_value 
        FROM condo_app.pre_con_unit_database_20230827_v4 u
            LEFT JOIN condo_app.pre_con_pdf_jpg_database_20230827 j ON j.pdf_link = u.floor_plan_link
            LEFT JOIN (
                select
                    project as project_dd
                    ,CASE WHEN deposit_date LIKE '%/%/%' THEN DATE_FORMAT(STR_TO_DATE(deposit_date, '%m/%d/%Y'), '%Y-%m-%d') ELSE deposit_date END AS deposit_date
                from condo_app.deposit_structure
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
        FROM condo_app.deposit_structure
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

    if (is_numeric($xirrResult)) {
        // It's a number, so format it as a percentage
        $xirrFormatted = esc_html(number_format($xirrResult * 100, 2)) . '%';
    } else {
        // It's an error message, so handle it accordingly
        $xirrFormatted = $xirrResult; // Display the error message
    }

    // Generate the unit card HTML
    $output = '<div style="border: 1px solid #ddd; margin-bottom: 10px;">';
    $output .= esc_html($unit->project) . ' | ' .
                'Model ' . esc_html($unit->model) . ' | ' .
                'Unit #' . esc_html($unit->unit_number) . ' | ' .
                'Price: $' . esc_html(number_format($unit->price)) . ' | ' .
                'Beds: ' . esc_html($unit->bedrooms) . ' | ' .
                'Baths: ' . esc_html($unit->bathrooms) . ' | ' .
                'Sqft: ' . esc_html($unit->interior_size) . ' | ' .
                // 'Project Pre-occupancy deposit: ' . esc_html($unit->pre_occupancy_deposit_pd) . '% | ' .
                'Developer: ' . esc_html($unit->developer) . ' | ' .
                'Deposit Date: ' . (isset($unit->deposit_date) ? esc_html($unit->deposit_date) : 'N/A') . ' | ' .
                'XIRR: ' . $xirrFormatted; // Corrected to use $xirrFormatted

    $output .= '<div>';
    $output .= 'Holding Period (Years): <input type="number" class="holding-period" value="' . esc_attr($holdingPeriod) . '"><br>';
    $output .= 'Rent ($): <input type="number" class="rent" value="' . esc_attr($rent) . '"><br>';
    $output .= 'Appreciation Rate (%): <input type="number" class="appreciation-rate" value="' . esc_attr($appreciationRate) . '">';
    $output .= '</div>';

    // Add a button to trigger the recalculation
    $output .= '<button class="calculate-xirr" data-unit-id="' . esc_attr($unit->id) . '">Calculate XIRR</button>';

    $output .= '</div>';

    return $output;
}

// Define the process_cashflow_data, calculate_net_cash_flows, and calculate_xirr functions as needed
function fetch_cashflow_data() {
    global $wpdb;

    $query = "
        SELECT 
            *
        FROM cashflows_20231120_v4 c
            left join (select id as unit_id, price from condo_app.pre_con_unit_database_20230827_v4) p on p.unit_id = c.id
    ";

    $results = $wpdb->get_results($query, ARRAY_A);

    // Process and return the results
    return $results;
}

function process_and_calculate_cashflows($data, $holdingPeriod, $appreciationRate = 0.06, $rent = null) {
    foreach ($data as $key => $row) {
        // Decode JSON fields
        $data[$key]['deposits'] = json_decode($row['deposits'], true);
        $data[$key]['closing_costs'] = json_decode($row['closing_costs'], true);
        $data[$key]['mortgage_payment'] = json_decode($row['mortgage_payment'], true);
        $data[$key]['mortgage_principal'] = json_decode($row['mortgage_principal'], true);
        $data[$key]['rent'] = json_decode($row['rent'], true);
        $data[$key]['rent_expenses'] = json_decode($row['rent_expenses'], true);
        $data[$key]['rental_net_income'] = json_decode($row['rental_net_income'], true);
        $holdingPeriod = $row['occupancy_index'] + 10;

        foreach ($data[$key]['rent'] as $rentValue) {
            if ($rentValue > 0) {
                $rent = $rentValue;
                break;
            }
        }

        // Debugging: Print out the holding period for this unit
        // echo "Project: {$row['project']}<br>";
        // echo "Model: {$row['model']}<br>";
        // echo "Unit: {$row['unit']}<br>";
        // echo "Unit Key: {$key}, Holding Period: {$holdingPeriod}<br>";

        // Calculate net cash flows
        $netCashFlows = [];
        $price = $row['price']; // Assuming 'price' is a field in your data
        $selling_costs = $row['selling_costs']; // Assuming 'selling_costs' is a field in your data
        $mortgage_payments_year = $row['mortgage_payments_year']; // Assuming this is a field in your data

        for ($i = 0; $i < $holdingPeriod; $i++) {
            $netCashFlow = -($data[$key]['deposits'][$i] ?? 0)
                          - ($data[$key]['closing_costs'][$i] ?? 0)
                          - ($data[$key]['mortgage_payment'][$i] ?? 0)
                          + ($data[$key]['rental_net_income'][$i] ?? 0);
            array_push($netCashFlows, $netCashFlow);

            // Debugging output: Print each cash flow
            // echo "Period {$i}: Net Cash Flow = {$netCashFlow}<br>";
        }

        // Calculate the sale price of the property at the end of the holding period
        // =(1+D13)^(1/12)-1
        $salePrice = $price * pow(pow(1 + $appreciationRate,(1 / $mortgage_payments_year) ), $holdingPeriod);

        // Calculate the selling costs
        $sellingCosts = $salePrice * $selling_costs;

        // Calculate the amount remaining on the mortgage
        $depositsSum = array_sum(array_slice($data[$key]['deposits'], 0, $holdingPeriod));
        $principalSum = array_sum(array_slice($data[$key]['mortgage_principal'], 0, $holdingPeriod));
        $mortgageRemaining = ($price - $depositsSum) - $principalSum;

        // Adjust the last cash flow
        $last_cashflow_addition = $salePrice - $sellingCosts - $mortgageRemaining;
        $netCashFlows[$holdingPeriod - 1] += $last_cashflow_addition;

        // Debugging: Start the table
        // echo "<table border='1'>";
        // echo "<tr><th>Period</th><th>Deposits</th><th>Closing Costs</th><th>Mortgage Payment</th><th>Mortgage Principal</th><th>Rent</th><th>Rent Expenses</th><th>Rental Net Income</th><th>Total Net Cash Flow</th></tr>";

        // for ($i = 0; $i < $holdingPeriod; $i++) {
        //     // Print each cash flow in a table row
        //     echo "<tr>";
        //     echo "<td>{$i}</td>";
        //     echo "<td>" . ($data[$key]['deposits'][$i] ?? 0) . "</td>";
        //     echo "<td>" . ($data[$key]['closing_costs'][$i] ?? 0) . "</td>";
        //     echo "<td>" . ($data[$key]['mortgage_payment'][$i] ?? 0) . "</td>";
        //     echo "<td>" . ($data[$key]['mortgage_principal'][$i] ?? 0) . "</td>";
        //     echo "<td>" . ($data[$key]['rent'][$i] ?? 0) . "</td>";
        //     echo "<td>" . ($data[$key]['rent_expenses'][$i] ?? 0) . "</td>";
        //     echo "<td>" . ($data[$key]['rental_net_income'][$i] ?? 0) . "</td>";
        //     echo "<td>{$netCashFlows[$i]}</td>";
        //     echo "</tr>";
        // }

        // // Debugging: Close the table and print additional data
        // echo "</table>";
        // echo "Original Price for Unit {$key}: {$price}<br>";
        // echo "Appreciation rate for Unit {$key}: {$appreciationRate}<br>";
        // echo "Mortgage payments per year for Unit {$key}: {$mortgage_payments_year}<br>";
        // echo "Holding period for Unit {$key}: {$holdingPeriod}<br>";
        // echo "Sale Price for Unit {$key}: {$salePrice}<br>";
        // echo "Selling Costs for Unit {$key}: {$sellingCosts}<br>";
        // echo "Mortgage Remaining for Unit {$key}: {$mortgageRemaining}<br>";
        // echo "Final Cash Flow for Unit {$key}: {$netCashFlows[$holdingPeriod - 1]}<br>";
        // echo "Total number of cash flows for Unit {$key}: " . count($netCashFlows) . "<br>";

        // Debugging: Print the final cash flow
        // echo "Final Cash Flow for Unit {$key}: {$netCashFlows[$holdingPeriod - 1]}<br>";

        // Debugging: Print the total number of cash flows for this unit
        // echo "Total number of cash flows for Unit {$key}: " . count($netCashFlows) . "<br>";

        // Store the calculated net cash flows back into the data array
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
    echo "<pre>Cash Flows: ";
    print_r($netCashFlows);
    echo "Dates: ";
    print_r($excelDates);
    echo "</pre>";

    try {
        // Calculate XIRR using PhpSpreadsheet's Financial class
        $xirr = Financial::XIRR($netCashFlows, $excelDates);
        return $xirr;
    } catch (Exception $e) {
        // Handle exceptions, such as non-converging calculations
        return 'Error: ' . $e->getMessage();
    }
}

// function calculateXIRR_old($netCashFlows, $correspondingDateStrings) {
//     // Debugging: Output the raw date strings and net cash flows
//     // echo "Raw date strings: " . $correspondingDateStrings . "<br>";
//     // echo "Net cash flows: ";
//     // print_r($netCashFlows);
//     // echo "<br>";

//     // Transform the date strings into a JSON format
//     $jsonFormattedDateStrings = preg_replace('/(\d{4}-\d{2}-\d{2})/', '"$1"', $correspondingDateStrings);

//     // Debugging: Output the JSON-formatted date strings
//     // echo "JSON-formatted date strings: " . $jsonFormattedDateStrings . "<br>";

//     // Decode the JSON-formatted string into an array
//     $correspondingDatesArray = json_decode($jsonFormattedDateStrings, true);

//     // Debugging: Output the decoded array
//     // echo "Decoded array: ";
//     // print_r($correspondingDatesArray);
//     // echo "<br>";

//     // Check if the decoding was successful and the result is an array
//     if (!is_array($correspondingDatesArray)) {
//         return 'Error: Invalid date format';
//     }

//     // Convert the date strings to DateTime objects
//     $dates = array_map(function($date) {
//         return new DateTime($date);
//     }, $correspondingDatesArray);

//     // Initialize variables
//     $xirr = 0.1; // Initial guess for the rate
//     $maxIterations = 100;
//     $tolerance = 0.0001;

//     for ($i = 0; $i < $maxIterations; $i++) {
//         $newtonRaphsonStep = calculateNewtonRaphsonStep($netCashFlows, $dates, $xirr);

//         // Debugging: Output the current iteration, XIRR value, and Newton-Raphson step
//         // echo "Iteration $i: XIRR = $xirr<br>";
//         // echo "Newton-Raphson Step: ";
//         // print_r($newtonRaphsonStep);
//         // echo "<br>";

//         // Check for division by zero
//         if ($newtonRaphsonStep['denominator'] == 0) {
//             return 'Error: Division by zero encountered';
//         }

//         // Update the XIRR value
//         $xirr -= $newtonRaphsonStep['numerator'] / $newtonRaphsonStep['denominator'];

//         // Check if the result is within the tolerance
//         if (abs($newtonRaphsonStep['numerator']) < $tolerance) {
//             return $xirr;
//         }
//     }

//     return 'Error: XIRR calculation did not converge';
// }

// function calculateNewtonRaphsonStep($cashFlows, $dates, $rate) {
//     $numerator = 0;
//     $denominator = 0;
//     $baseDate = $dates[0];

//     foreach ($cashFlows as $i => $cf) {
//         $days = $baseDate->diff($dates[$i])->days;
//         $denominatorTerm = pow(1 + $rate, $days / 365);

//         // Debugging: Output each cash flow, days, and denominator term
//         // echo "Cash Flow $i: $cf, Days: $days, Denominator Term: $denominatorTerm<br>";

//         // Avoid division by zero
//         if ($denominatorTerm == 0) {
//             return ['numerator' => 0, 'denominator' => 0];
//         }

//         $numerator += $cf / $denominatorTerm;
//         $denominator += (-$days / 365) * $cf / pow($denominatorTerm, 2);
//     }

//     return ['numerator' => $numerator, 'denominator' => $denominator];
// }