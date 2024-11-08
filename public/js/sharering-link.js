jQuery(document).ready(function($) {
    // Define the onInit function
    window.onInit = function(data) {
        console.log('onInit:', data);

        // Generate QR code
        var qrCodeData = data.qrcode;
        if (!qrCodeData) {
            console.error('QR code data is missing.');
            return;
        }

        // Clear any existing QR code
        $('#custom-qrcode').empty();

        // Generate the QR code using jQuery.qrcode
        $('#custom-qrcode').qrcode({
            render: 'canvas',
            width: sharering_link_params.qr_code_size,
            height: sharering_link_params.qr_code_size,
            text: qrCodeData,
            foreground: sharering_link_params.qr_foreground_color,
            background: sharering_link_params.qr_background_color
        });

        // Optionally, add a logo to the center of the QR code
        if (sharering_link_params.qr_logo_url) {
            // Wait for the QR code to render
            setTimeout(function() {
                var canvas = $('#custom-qrcode canvas')[0];
                var context = canvas.getContext('2d');
                var logo = new Image();
                logo.onload = function() {
                    var logoSize = canvas.width * 0.2; // Adjust logo size as needed
                    var x = (canvas.width - logoSize) / 2;
                    var y = (canvas.height - logoSize) / 2;
                    context.drawImage(logo, x, y, logoSize, logoSize);
                };
                logo.src = sharering_link_params.qr_logo_url;
            }, 500);
        }
    };

    // Updated onScan function
    window.onScan = function(data) {
        console.log('onScan data:', data);

        // Extract the session ID from the data
        var sessionId = data.sessionId || data.session_id;

        if (!sessionId) {
            console.error('Session ID not found in data.');
            alert('An error occurred. Please try again.');
            return;
        }

        console.log('Session ID:', sessionId);

        alert('Thank you for scanning the QR code!');

        // Start polling the server for data using the session ID
        startPollingForData(sessionId);
    };

    function startPollingForData(sessionId) {
        var pollingInterval = 2000; // Poll every 2 seconds
        var maxAttempts = 30; // Max attempts before giving up
        var attempts = 0;

        var polling = setInterval(function() {
            attempts++;
            console.log('Polling for data... Attempt:', attempts);

            $.ajax({
                url: sharering_link_params.polling_url,
                method: 'GET',
                data: { session_id: sessionId },
                success: function(response) {
                    if (response && response.data_received) {
                        clearInterval(polling);
                        console.log('Data received:', response.data);
                        displayUserData(response.data);
                    } else if (attempts >= maxAttempts) {
                        clearInterval(polling);
                        alert('Data not received in time. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Polling error:', xhr.responseText);
                    clearInterval(polling);
                    alert('An error occurred while retrieving data.');
                }
            });
        }, pollingInterval);
    }

    function displayUserData(data) {
        // Display the data to the user
        console.log('Displaying data to user:', data);
        alert('Your data has been received!');
        // Optionally, redirect the user
        if (sharering_link_params.redirect_url) {
            window.location.href = sharering_link_params.redirect_url;
        }
    }
});
