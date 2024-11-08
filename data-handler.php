<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Function to handle data received via REST API
 * (Note: Since we're using the webhook for data handling, this function may not be necessary.)
 */
function sharering_link_handle_data( $request ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sharering_link_data';

    $data = $request->get_body();
    $json_data = json_decode( $data, true );

    if ( json_last_error() !== JSON_ERROR_NONE ) {
        return new WP_Error( 'invalid_json', 'Invalid JSON data.', array( 'status' => 400 ) );
    }

    // Insert data into database
    $inserted = $wpdb->insert(
        $table_name,
        array(
            'session_id' => isset( $json_data['session_id'] ) ? sanitize_text_field( $json_data['session_id'] ) : '',
            'user_data'  => maybe_serialize( $json_data ),
            'time'       => current_time( 'mysql' ),
        ),
        array(
            '%s',
            '%s',
            '%s',
        )
    );

    if ( $inserted === false ) {
        return new WP_Error( 'db_insert_error', 'Failed to insert data into the database.', array( 'status' => 500 ) );
    }

    return rest_ensure_response( array( 'message' => 'Data saved successfully.' ) );
}
