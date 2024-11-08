<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Register the settings and add the settings page to the admin menu
function sharering_link_register_settings() {
    register_setting( 'sharering_link_settings', 'sharering_link_options' );

    add_settings_section(
        'sharering_link_section',
        'ShareRing Link Configuration',
        'sharering_link_section_callback',
        'sharering_link_settings'
    );

    // Add settings fields
    add_settings_field(
        'query_id',
        'Query ID',
        'sharering_link_query_id_render',
        'sharering_link_settings',
        'sharering_link_section'
    );

    add_settings_field(
        'query_owner',
        'Query Owner',
        'sharering_link_query_owner_render',
        'sharering_link_settings',
        'sharering_link_section'
    );

    add_settings_field(
        'client_id',
        __( 'Client ID', 'sharering-link' ),
        'sharering_link_client_id_render',
        'sharering_link',
        'sharering_link_section'
    );

    add_settings_field(
        'redirect_url',
        'Redirect URL',
        'sharering_link_redirect_url_render',
        'sharering_link_settings',
        'sharering_link_section'
    );

    add_settings_field(
        'data_mapping',
        'Data Mapping',
        'sharering_link_data_mapping_render',
        'sharering_link_settings',
        'sharering_link_section'
    );

    add_settings_field(
        'qr_code_size',
        'QR Code Size',
        'sharering_link_qr_code_size_render',
        'sharering_link_settings',
        'sharering_link_section'
    );

    add_settings_field(
        'qr_foreground_color',
        'QR Code Foreground Color',
        'sharering_link_qr_foreground_color_render',
        'sharering_link_settings',
        'sharering_link_section'
    );

    add_settings_field(
        'qr_background_color',
        'QR Code Background Color',
        'sharering_link_qr_background_color_render',
        'sharering_link_settings',
        'sharering_link_section'
    );

    add_settings_field(
        'qr_border_color',
        'QR Code Border Color',
        'sharering_link_qr_border_color_render',
        'sharering_link_settings',
        'sharering_link_section'
    );

    add_settings_field(
        'qr_logo',
        'QR Code Logo',
        'sharering_link_qr_logo_render',
        'sharering_link_settings',
        'sharering_link_section'
    );
}
add_action( 'admin_init', 'sharering_link_register_settings' );

// Add the settings page to the admin menu
function sharering_link_add_admin_menu() {
    add_options_page(
        'ShareRing Link Integration',
        'ShareRing Link',
        'manage_options',
        'sharering_link',
        'sharering_link_settings_page'
    );
}
add_action( 'admin_menu', 'sharering_link_add_admin_menu' );

// Callback function for the settings section
function sharering_link_section_callback() {
    echo '<p>Configure your ShareRing Link integration settings below.</p>';
	// Generate the API endpoint URL dynamically.
    $api_endpoint = rest_url( 'sharering-link/v1/data' );
    ?>
    <p>Please log in to ShareRing Link and set the API endpoint to:</p>
    <p><strong><?php echo esc_html( $api_endpoint ); ?></strong></p>
    <p>You can also adjust the size and appearance of the QR code below to fit your website's design.</p>
    <p><strong>Note:</strong> The <em>Data Mapping</em> field is optional. If left blank, the plugin will use the default data field names.</p>
    <?php
}

// Rendering functions for each settings field

function sharering_link_query_id_render() {
    $options = get_option( 'sharering_link_options' );
    ?>
    <input type="text" name="sharering_link_options[query_id]" value="<?php echo isset( $options['query_id'] ) ? esc_attr( $options['query_id'] ) : ''; ?>" />
    <p class="description">Enter your ShareRing Query ID.</p>
    <?php
}

function sharering_link_query_owner_render() {
    $options = get_option( 'sharering_link_options' );
    ?>
    <input type="text" name="sharering_link_options[query_owner]" value="<?php echo isset( $options['query_owner'] ) ? esc_attr( $options['query_owner'] ) : ''; ?>" />
    <p class="description">Enter your ShareRing Query Owner.</p>
    <?php
}

function sharering_link_client_id_render() {
    $options = get_option( 'sharering_link_options' );
    ?>
    <input type="text" name="sharering_link_options[client_id]" value="<?php echo isset( $options['client_id'] ) ? esc_attr( $options['client_id'] ) : ''; ?>" />
    <p class="description">Enter your ShareRing Client ID.</p>
    <?php
}

function sharering_link_redirect_url_render() {
    $options = get_option( 'sharering_link_options' );
    ?>
    <input type="url" name="sharering_link_options[redirect_url]" value="<?php echo isset( $options['redirect_url'] ) ? esc_url( $options['redirect_url'] ) : ''; ?>" />
    <p class="description">Enter the URL to redirect users after scanning the QR code.</p>
    <?php
}

function sharering_link_data_mapping_render() {
    $options = get_option( 'sharering_link_options' );
    ?>
    <textarea name="sharering_link_options[data_mapping]" id="data_mapping" rows="5" cols="50"><?php echo isset( $options['data_mapping'] ) ? esc_textarea( $options['data_mapping'] ) : ''; ?></textarea>
    <p class="description">Enter the data mapping in JSON format. Example:<br>
        <code>{"full_name":"name","email":"email_address","phone":"phone_number"}</code>
    </p>
    <?php
}

function sharering_link_qr_code_size_render() {
    $options = get_option( 'sharering_link_options' );
    ?>
    <input type="number" name="sharering_link_options[qr_code_size]" value="<?php echo isset( $options['qr_code_size'] ) ? intval( $options['qr_code_size'] ) : '200'; ?>" min="100" max="1000" />
    <p class="description">Set the size of the QR code in pixels (e.g., 200).</p>
    <?php
}

function sharering_link_qr_foreground_color_render() {
    $options = get_option( 'sharering_link_options' );
    $qr_foreground_color = isset( $options['qr_foreground_color'] ) ? $options['qr_foreground_color'] : '#000000';
    ?>
    <input type="text" name="sharering_link_options[qr_foreground_color]" class="color-field" value="<?php echo esc_attr( $qr_foreground_color ); ?>" />
    <p class="description">Choose the QR code foreground color.</p>
    <?php
}

function sharering_link_qr_background_color_render() {
    $options = get_option( 'sharering_link_options' );
    $qr_background_color = isset( $options['qr_background_color'] ) ? $options['qr_background_color'] : '#FFFFFF';
    ?>
    <input type="text" name="sharering_link_options[qr_background_color]" class="color-field" value="<?php echo esc_attr( $qr_background_color ); ?>" />
    <p class="description">Choose the QR code background color.</p>
    <?php
}

function sharering_link_qr_border_color_render() {
    $options = get_option( 'sharering_link_options' );
    $qr_border_color = isset( $options['qr_border_color'] ) ? $options['qr_border_color'] : '#000000';
    ?>
    <input type="text" name="sharering_link_options[qr_border_color]" class="color-field" value="<?php echo esc_attr( $qr_border_color ); ?>" />
    <p class="description">Choose the QR code border color.</p>
    <?php
}

function sharering_link_qr_logo_render() {
    $options = get_option( 'sharering_link_options' );
    $qr_logo = isset( $options['qr_logo'] ) ? $options['qr_logo'] : '';
    $qr_logo_url = $qr_logo ? wp_get_attachment_image_url( $qr_logo, 'thumbnail' ) : '';
    ?>
    <input type="hidden" name="sharering_link_options[qr_logo]" id="qr_logo" value="<?php echo esc_attr( $qr_logo ); ?>" />
    <img id="qr_logo_preview" src="<?php echo esc_url( $qr_logo_url ); ?>" style="max-width: 100px; display: <?php echo $qr_logo_url ? 'block' : 'none'; ?>;" />
    <br>
    <button type="button" class="button" id="upload_qr_logo_button">Upload Logo</button>
    <button type="button" class="button" id="remove_qr_logo_button">Remove Logo</button>
    <?php
}

// Render the settings page
function sharering_link_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    ?>
    <div class="wrap">
        <h1>ShareRing Link Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'sharering_link_settings' );
            do_settings_sections( 'sharering_link_settings' );
            submit_button();
            ?>
        </form>

        <?php sharering_link_show_scans_render(); ?>
    </div>
    <?php
}

// Function to display recent scans
function sharering_link_show_scans_render() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'sharering_link_data';
    $results = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY time DESC LIMIT 10" );

    if ( $results ) {
        echo '<h2>Recent Scans:</h2>';
        echo '<ul>';
        foreach ( $results as $row ) {
            $data = maybe_unserialize( $row->user_data );
            if ( is_string( $data ) ) {
                $data = json_decode( $data, true );
            }
            if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $data ) ) {
                echo '<li><strong>Time:</strong> ' . esc_html( $row->time ) . '<br>';
                echo '<em>Invalid data format.</em></li><hr>';
                continue;
            }

            echo '<li><strong>Time:</strong> ' . esc_html( $row->time ) . '<br>';
            echo '<ul>';
            foreach ( $data as $key => $value ) {
                if ( is_array( $value ) ) {
                    $value = implode( ', ', $value );
                }
                echo '<li><strong>' . esc_html( $key ) . ':</strong> ' . esc_html( $value ) . '</li>';
            }
            echo '</ul>';
            echo '</li><hr>';
        }
        echo '</ul>';
    } else {
        echo '<p>No scans recorded yet.</p>';
    }
}

// Enqueue the color picker and media uploader scripts
function sharering_link_admin_enqueue_scripts( $hook ) {
    if ( 'settings_page_sharering_link' !== $hook ) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script(
        'sharering-link-admin-js',
        plugins_url( 'admin/js/sharering-link-admin.js', __FILE__ ),
        array( 'wp-color-picker', 'jquery' ),
        '1.0',
        true
    );
}
add_action( 'admin_enqueue_scripts', 'sharering_link_admin_enqueue_scripts' );
