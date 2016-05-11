<script type="text/javascript">
var pass = '';

$(window).load(function() {
    $('#make-password').click(function() {
        for (i = 0; i < 10; i++) {
            pass = pass + createLetter();
        }
        $('#password-created').html(pass);
        $('#contact_password').val(pass);
        $('#contact_pw_check').val(pass);
        pass = '';
    });

    console.log($('#contact_private').prop('checked'));

    if ($('#contact_private').prop('checked')) {
        $('#contact_company_name').val('Private renter');
        $('.company-info').hide();
    }

    $('#contact_private').change(function(e) {
        if (e.target.checked) {
            $('.company-info').hide();

        } else {
            $('.company-info').show();
        }
    });
});


function createLetter()
{
    return String.fromCharCode(97 + Math.round(Math.random() * 25));
}

</script>