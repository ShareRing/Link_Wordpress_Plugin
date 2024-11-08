<?php
/**
 * Plugin Name: ShareRing Link Integration
 * Description: Integrate ShareRing Link to request and receive verifiable credentials.
 * Version: 1.6
 * Author: ShareRing Ltd
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define constants.
define( 'SHARERING_LINK_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SHARERING_LINK_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include necessary files.
include_once SHARERING_LINK_PLUGIN_DIR . 'admin-settings.php';
include_once SHARERING_LINK_PLUGIN_DIR . 'includes/encryption.php';
include_once SHARERING_LINK_PLUGIN_DIR . 'admin-settings.php';
include_once SHARERING_LINK_PLUGIN_DIR . 'data-handler.php';

// Activation hook to create database table.
register_activation_hook( __FILE__, 'sharering_link_create_table' );
function sharering_link_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sharering_link_data';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        session_id varchar(100) NOT NULL,
        user_data text NOT NULL,
        time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY session_id (session_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// Enqueue scripts and styles.
add_action( 'wp_enqueue_scripts', 'sharering_link_enqueue_scripts' );
function sharering_link_enqueue_scripts() {
    // Enqueue jQuery (already included in WordPress)
    wp_enqueue_script( 'jquery' );

    // Enqueue ShareRing's JavaScript Library
    wp_enqueue_script( 'sharering-link-lib', 'https://raw.githack.com/ShareRing/shareringlink-javascript-library/master/sharering.query.lib.prod.min.js', array('jquery'), '1.0', true );

    // Enqueue jQuery.qrcode for QR code generation
    wp_enqueue_script( 'jquery-qrcode', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js', array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'jquery.min.js", "https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.4/jquery.min.js",array( 'jquery' ), '1.0', true );
    // Enqueue custom ShareRing Link script
    wp_enqueue_script( 'sharering-link-js', SHARERING_LINK_PLUGIN_URL . 'public/js/sharering-link.js', array( 'jquery', 'jquery-qrcode', 'sharering-link-lib' ), '1.6', true );

    // Enqueue plugin's CSS
    wp_enqueue_style( 'sharering-link-css', SHARERING_LINK_PLUGIN_URL . 'public/css/sharering-link.css', array(), '1.0' );

    // Retrieve admin settings
    $options = get_option( 'sharering_link_options' );
    $client_id = isset( $options['client_id'] ) ? $options['client_id'] : 'basic';
    $query_id = isset( $options['query_id'] ) ? $options['query_id'] : '';
    $query_owner = isset( $options['query_owner'] ) ? $options['query_owner'] : '';
    $apn = isset( $options['qr_dynamic_link_apn'] ) ? $options['qr_dynamic_link_apn'] : 'network.sharering.application';
    $isi = isset( $options['qr_dynamic_link_isi'] ) ? $options['qr_dynamic_link_isi'] : '1557434411';
    $ibi = isset( $options['qr_dynamic_link_ibi'] ) ? $options['qr_dynamic_link_ibi'] : 'network.sharering.app';

    // $data_mapping = isset( $options['data_mapping'] ) ? $options['data_mapping'] : '';

    // Localize script to pass PHP data to JavaScript
    wp_localize_script( 'sharering-link-js', 'sharering_link_params', array(
        'redirect_url'               => isset( $options['redirect_url'] ) ? esc_url( $options['redirect_url'] ) : '',
        'polling_url'                => esc_url_raw( rest_url( 'sharering-link/v1/poll-data' ) ),
        'nonce'                      => wp_create_nonce( 'wp_rest' ),
        'qr_code_size'               => isset( $options['qr_code_size'] ) ? intval( $options['qr_code_size'] ) : 200, // Default size: 200px
        'qr_foreground_color'        => isset( $options['qr_foreground_color'] ) ? esc_attr( $options['qr_foreground_color'] ) : '#000000', // Default black
        'qr_background_color'        => isset( $options['qr_background_color'] ) ? esc_attr( $options['qr_background_color'] ) : '#FFFFFF', // Default white
        'qr_logo_url'                => isset( $options['qr_logo'] ) && $options['qr_logo'] ? wp_get_attachment_image_url( $options['qr_logo'], 'full' ) : '',
        'data_mapping'               => $data_mapping, // Pass as JSON string
    ) );
}

// Shortcode to display QR code.
function sharering_link_display_qrcode() {
    $options = get_option( 'sharering_link_options' );
    $client_id = isset( $options['client_id'] ) ? esc_attr( $options['client_id'] ) : 'basic';
    $query_id = isset( $options['query_id'] ) ? esc_attr( $options['query_id'] ) : '';
    $query_owner = isset( $options['query_owner'] ) ? esc_attr( $options['query_owner'] ) : '';

    ob_start();
    ?>
    <div class="sharering-query" 
         queryId="<?php echo urlencode($query_id . "," . $client_id); ?>" 
         qrcodeOwner="<?php echo $query_owner; ?>" 
         app="ShareRing Pro" 
         oninit="onInit" 
         onscan="onScan">
        <div class="qrcode-content">
            <!-- ShareRing's default QR code container (hidden to use custom QR code) -->
            <div id="sharering-qrcode" style="display: none;"></div>
            <!-- Custom QR code container -->
            <div id="custom-qrcode"></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'sharering_link_qrcode', 'sharering_link_display_qrcode' );

// Handle data pushed from ShareRing Link via REST API.
add_action( 'rest_api_init', function () {
    // Polling endpoint
    register_rest_route( 'sharering-link/v1', '/poll-data', array(
        'methods'             => 'GET',
        'callback'            => 'sharering_link_poll_data',
        'permission_callback' => '__return_true', // Adjust as needed for security
    ) );
});

/**
 * Function to handle polling requests
 */
function sharering_link_poll_data( $request ) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sharering_link_data';

    $session_id = sanitize_text_field( $request->get_param( 'session_id' ) );

    if ( empty( $session_id ) ) {
        return new WP_Error( 'no_session_id', 'No session ID provided.', array( 'status' => 400 ) );
    }

    // Check if data exists for the session ID
    $data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE session_id = %s", $session_id ) );

    if ( $data ) {
        $user_data = maybe_unserialize( $data->user_data );
        if ( is_string( $user_data ) ) {
            $user_data = json_decode( $user_data, true );
        }

        return rest_ensure_response( array(
            'data_received' => true,
            'data'          => $user_data,
        ) );
    } else {
        return rest_ensure_response( array(
            'data_received' => false,
        ) );
    }
}

// Enforce HTTPS on all non-admin pages.
add_action( 'template_redirect', 'sharering_link_force_https' );
function sharering_link_force_https() {
    if ( ! is_ssl() && ! is_admin() ) {
        if ( 0 === strpos( $_SERVER['REQUEST_URI'], 'http' ) ) {
            wp_redirect( preg_replace( '|^http://|', 'https://', $_SERVER['REQUEST_URI'] ), 301 );
            exit();
        } else {
            wp_redirect( 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 301 );
            exit();
        }
    }
}

add_filter( 'theme_page_templates', 'sharering_add_page_template' );
function sharering_add_page_template( $templates ) {
    $templates['sharering-thank-you.php'] = 'ShareRing Thank You';
    return $templates;
}

// Load the custom page template from the plugin directory.
add_filter( 'template_include', 'sharering_load_page_template' );
function sharering_load_page_template( $template ) {
    if ( is_page() ) {
        global $post;
        $page_template = get_post_meta( $post->ID, '_wp_page_template', true );
        if ( $page_template == 'sharering-thank-you.php' ) {
            $plugin_template = SHARERING_LINK_PLUGIN_DIR . 'templates/thank-you.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }
    }
    return $template;
}
// Shortcode to display ShareRing data on the redirect page
add_shortcode( 'sharering_link_display', 'sharering_link_display_shortcode' );
function sharering_link_display_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'field' => '', // Optional: specify a single field
    ), $atts, 'sharering_link_display' );

    // Get all query parameters
    $query_vars = $_GET;

    if ( empty( $query_vars ) ) {
        return '<p>No data to display.</p>';
    }

    // If a specific field is specified
    if ( ! empty( $atts['field'] ) ) {
        $field = sanitize_text_field( $atts['field'] );
        if ( isset( $query_vars[ $field ] ) ) {
            $value = sanitize_text_field( $query_vars[ $field ] );
            return '<p>' . esc_html( $value ) . '</p>';
        } else {
            return '<p>Field "' . esc_html( $field ) . '" not found.</p>';
        }
    }

    // Otherwise, display all fields
    $output = '<ul>';
    foreach ( $query_vars as $key => $value ) {
        $output .= '<li><strong>' . esc_html( $key ) . ':</strong> ' . esc_html( $value ) . '</li>';
    }
    $output .= '</ul>';

    return $output;
}
