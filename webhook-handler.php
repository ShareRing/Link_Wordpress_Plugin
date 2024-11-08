<?php
// Include WordPress functionality
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php'); // Adjust the path as necessary

// Get the raw POST data
$rawPostData = file_get_contents('php://input');

// Decode the JSON data
$data = json_decode($rawPostData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('Invalid JSON data received.');
    http_response_code(400);
    echo 'Invalid JSON data.';
    exit;
}

// Check if 'values' and 'sessionId' are present
if (isset($data['values']) && isset($data['sessionId'])) {
    $values = $data['values'];
    $session_id = sanitize_text_field($data['sessionId']);

    // Store values in the database associated with the session ID
    global $wpdb;
    $table_name = $wpdb->prefix . 'sharering_link_data';

    // Insert the values into the database
    $json_values = wp_json_encode($values);

    $inserted = $wpdb->insert(
        $table_name,
        array(
            'session_id' => $session_id,
            'user_data'  => $json_values,
            'time'       => current_time('mysql'),
        ),
        array(
            '%s',
            '%s',
            '%s',
        )
    );

    if ($inserted === false) {
        error_log('Failed to insert data into the database.');
        http_response_code(500);
        echo 'Failed to save data.';
        exit;
    } else {
        error_log('Data saved successfully for session ID: ' . $session_id);
        http_response_code(200);
        echo 'Data saved successfully.';
        exit;
    }
} else {
    error_log('No values or session ID found in the webhook data.');
    http_response_code(400);
    echo 'No values or session ID found in the data.';
    exit;
}
