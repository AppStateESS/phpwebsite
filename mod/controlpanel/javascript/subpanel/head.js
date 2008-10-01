<script type="text/javascript">
//<![CDATA[

$(document).ready(function()
{
    $('#cp-subpanel').hide();
    $('#cp-panel-link').hover(
         function() 
         {
             $('#cp-subpanel').show('fast');
         },
         function()
         {
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