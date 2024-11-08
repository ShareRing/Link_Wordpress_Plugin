<?php
// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

// Delete custom database table
$table_name = $wpdb->prefix . 'sharering_link_data';
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

// Delete plugin options
delete_option( 'sharering_link_options' );
