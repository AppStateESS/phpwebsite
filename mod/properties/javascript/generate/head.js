<script type="text/javascript">

var pass = '';

$(document).ready(function() {
    $('#make-password').click(function() {
        for (i=0;i<10;i++) {
            pass = pass + createLetter();
        }
        $('#password-created').html(pass);
        $('#contact_password').val(pass);
        $('#contact_pw_check').val(pass);
        pass = '';
    });
});


function createLetter()
{
   return String.fromCharCode(97 + Math.round(Math.random() * 25));
}

</script>