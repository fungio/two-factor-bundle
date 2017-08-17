$(document).ready(function() {

    //show TOTP Secret
    $('#twofas-totp-enter-btn').on('click', function() {
        $('#twofas-totp-secret').show();
    });

    //reload QR Code
    $('#twofas-totp-reload-btn').on('click', function() {
        $.ajax({
            method: 'GET',
            url: Routing.generate('twofas_reload_totp'),
            dataType: 'json'
        }).done(function(json) {
            $('#twofas-totp-qrcode').attr('src', json.qr_code);
            $('#totp-secret').val(json.totp_secret);
            $('#twofas-totp-secret').text(json.totp_secret);
        });
    });
});
