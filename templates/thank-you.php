<?php
/**
 * Template Name: ShareRing Thank You
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>

<div class="sharering-thank-you">
    <h1>Thank You!</h1>
    <p>Your information has been received successfully.</p>

    <?php
    // Optionally display user data
    echo do_shortcode( '[sharering_link_display]' );
    ?>
</div>

<?php
get_footer();
