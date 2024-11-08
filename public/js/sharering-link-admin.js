jQuery(document).ready(function($){
    // Initialize color picker
    $('.sharering-color-field').wpColorPicker();

    // Handle logo upload
    var mediaUploader;

    $('#sharering_link_upload_logo').on('click', function(e) {
        e.preventDefault();
        // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }
        // Extend the wp.media object
        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose QR Code Logo',
            button: {
                text: 'Choose Logo'
            },
            multiple: false
        });

        // When a file is selected, grab the URL and set it as the text field's value
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#sharering_link_qr_logo').val(attachment.id);
            $('#sharering_link_logo_preview').html('<img src="' + attachment.url + '" alt="QR Code Logo" style="max-width:150px; margin-top:10px;">');
            $('#sharering_link_remove_logo').show();
        });

        // Open the uploader dialog
        mediaUploader.open();
    });

    // Handle logo removal
    $('#sharering_link_remove_logo').on('click', function(e) {
        e.preventDefault();
        $('#sharering_link_qr_logo').val('');
        $('#sharering_link_logo_preview').html('');
        $(this).hide();
    });
});
