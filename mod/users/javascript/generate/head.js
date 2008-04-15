<script type="text/javascript">

var pass = '';

$(document).ready(function() {
    $('#edit-user_create_pw').click(function() {
        for (i=0;i<10;i++) {
            pass = pass + createLetter();
        }
        $('#generated-password').html(pass);
        $('#edit-user_password1').val(pass);
        $('#edit-user_password2').val(pass);
        pass = '';
    });
});


function createLetter()
{
   return String.fromCharCode(97 + Math.round(Math.random() * 25));
}

</script>