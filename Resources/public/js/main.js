$(document).ready(function() {

    //show TOTP Secret
    $('#fungio-totp-enter-btn').on('click', function() {
        $('#fungio-totp-secret').show();
    });

    //reload QR Code
    $('#fungio-totp-reload-btn').on('click', function() {
        $.ajax({
            method: 'GET',
            url: Routing.generate('fungio_reload_totp'),
            dataType: 'json'
        }).done(function(json) {
            $('#fungio-totp-qrcode').attr('src', json.qr_code);
            $('#totp-secret').val(json.totp_secret);
            $('#fungio-totp-secret').text(json.totp_secret);
        });
    });
});
