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

    log_cash_flows($unitProcessedData);

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

    // Function to ensure data is treated as an array
    $ensureArray = function($data) {
        if (is_string($data)) {
            // Attempt to convert string to array, assuming JSON format
            $decoded = json_decode($data, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($data) ? $data : [];
    };

    // Ensure 'mortgage_interest' is treated as an array
    $mortgageInterestArray = $ensureArray($unitProcessedData['mortgage_interest']);

    // Function to convert date string into an array
    $parseDateArray = function($dateString) {
        $trimmedString = trim($dateString, "[]");
        return explode(',', $trimmedString);
    };

    // Convert the 'corresponding_date' string into an array
    $correspondingDateArray = $parseDateArray($unitProcessedData['corresponding_date']);

    // Check if file is empty to write headers
    if (filesize($logFile) == 0) {
        $headers = ['Timestamp', 'Project', 'Model', 'Unit', 'Price', 'Cashflow index', 'Deposit', 'Closing Cost', 'Mortgage Payment', 'Mortgage Principal', 'Mortgage Interest', 'Rent', 'Rent Expense', 'Rental Net Income', 'Net Cash Flow', 'Corresponding Date'];
        fputcsv($fileHandle, $headers);
    }

    // Loop through the arrays up to maxIndex and log each entry
    for ($i = 0; $i <= $maxIndex; $i++) {
        $row = [
            $timestamp,
            $unitProcessedData['project'] ?? 'N/A',
            $unitProcessedData['model'] ?? 'N/A',
            $unitProcessedData['unit'] ?? 'N/A',
            $unitProcessedData['price'] ?? 'N/A',
            $i ?? 'N/A',
            $unitProcessedData['deposits'][$i] ?? 0,
            $unitProcessedData['closing_costs'][$i] ?? 0,
            $unitProcessedData['mortgage_payment'][$i] ?? 0,
            $unitProcessedData['mortgage_principal'][$i] ?? 0,
            $mortgageInterestArray[$i] ?? 0,
            $unitProcessedData['rent'][$i] ?? 0,
            $unitProcessedData['rent_expenses'][$i] ?? 0,
            $unitProcessedData['rental_net_income'][$i] ?? 0,
            $unitProcessedData['net_cash_flows'][$i] ?? 0,
            $correspondingDateArray[$i] ?? 'N/A'
        ];
        fputcsv($fileHandle, $row);
    }

    fclose($fileHandle);
}
