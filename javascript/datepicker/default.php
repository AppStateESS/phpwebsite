<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

loadDatepicker();
function loadDatepicker() {
    javascript('jquery');
    $source_http = PHPWS_SOURCE_HTTP;
    $script = <<<EOF
<script type="text/javascript" src="{$source_http}javascript/datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript">
$(window).load(function() {
    /*
    $('.datepicker').datepicker().on('changeDate', function(ev){
            $('.datepicker').datepicker('hide');
        });
    */

    $('.datepicker').datepicker({
        autoclose: true
    })

});
</script>
EOF;
\Layout::addJSHeader($script);
\Layout::addStyle('datepicker', '../../../javascript/datepicker/css/datepicker.css');
}
?>
