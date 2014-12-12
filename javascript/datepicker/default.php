<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */


    javascript('jquery');
    $source_http = PHPWS_SOURCE_HTTP;
    $script = <<<EOF
<link href="{$source_http}javascript/datepicker/css/datepicker.css" rel="stylesheet" />
<script type="text/javascript" src="{$source_http}javascript/datepicker/js/bootstrap-datepicker.js"></script>
<script type="text/javascript">
$(window).load(function() {
    $('.datepicker').datepicker({
        autoclose: true
    })

});
</script>
EOF;
\Layout::addJSHeader($script);

?>
