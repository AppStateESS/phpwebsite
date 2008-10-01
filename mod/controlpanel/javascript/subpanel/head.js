<script type="text/javascript">
//<![CDATA[

var wait_a_sec = 0;

$(document).ready(function()
{
    $('#cp-subpanel').hide();
    $('#cp-panel-link').hover(
         function() 
         {
             if (wait_a_sec != 0) {
                 clearTimeout(wait_a_sec);
             }
             wait_a_sec = setTimeout(function() {
                wait_a_sec = 0;
                $('#cp-subpanel').show('fast');
	     }, 500);
         },
         function()
         {
             if(wait_a_sec != 0) {
                 clearTimeout(wait_a_sec);
             }
             $('#cp-subpanel').hide();
         }
    );
    $('#cp-subpanel').hover(
         function()
         {
             $(this).show();
         },
         function()
         {
             $(this).hide();
         }
    );
});

//]]>
</script>