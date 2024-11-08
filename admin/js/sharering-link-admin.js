jQuery(document).ready(function($){
    // Initialize the WordPress color picker for color fields
    $('.color-field').wpColorPicker();

    // Media uploader for QR code logo
    $('#upload_qr_logo_button').click(function(e) {
        e.preventDefault();
        var image = wp.media({
            title: 'Upload Logo',
            multiple: false
        }).open()
        .on('select', function(){
            var uploaded_image = image.state().get('selection').first();
            var image_id = uploaded_image.toJSON().id;
            var image_url = uploaded_image.toJSON().url;

            // Set the image ID in the hidden input field
            $('#qr_logo').val(image_id);

            // Update the image preview
            $('#qr_logo_preview').attr('src', image_url);
        });
    });

    // Remove QR code logo
    $('#remove_qr_logo_button').click(function(e) {
        e.preventDefault();
        // Clear the hidden input field
        $('#qr_logo').val('');

        // Remove the image preview
        $('#qr_logo_preview').attr('src', '');
    });

    // Additional code for handling data mapping field (if applicable)
    // Expandable textarea for data mapping
    $('#data_mapping').on('input', function() {
        var $this = $(this);
        $this.height(0).height(this.scrollHeight);
    }).trigger('input');

    // Any other admin-specific JavaScript code can be added here
});
