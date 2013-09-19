<script type="text/javascript">

$(document).ready(function() {
    $('.checkin-note .note-link').click(
       function() {
           $('.checkin-form').hide();
           $(this).parent().find('span.checkin-form').show();
       }
    );

    timeout = window.setTimeout('location.reload()', 60000);

    $('#add-student').click(function(){

        window.clearTimeout(timeout);

        $('#add-student-dialog').dialog({
            minWidth : 640,
            minHeight : 600,
            buttons: { "Cancel": function() {
                    timeout = window.setTimeout('location.reload()', 60000);
                    $(this).dialog("close");
                } }
        });
    });

    $('#visitor_submit_form').click(function(){
        if ($('#visitor_firstname').val() == '' ||
            $('#visitor_lastname').val() == '' ||
            $('#reason-id').val() == 0 ||
            $('#visitor_email').val() == ''

        ) {
            $('#add-student-dialog div.error').html('<p style="background-color : red; color : white">Please fill in all fields and select a reason.</p>');
            return false;
        }
        return true;
    });
});

function close_note() {
  $('.checkin-form').hide();
}

</script>
