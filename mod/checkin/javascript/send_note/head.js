<script type="text/javascript">
//<![CDATA[

$(document).ready(function() {
    $('.checkin-note .note-link').click(
       function() {
           $('.checkin-form').hide();
           $(this).parent().find('span.checkin-form').show();
       }
    );
}
);

function close_note() {
  $('.checkin-form').hide();
}

//]]>
</script>
