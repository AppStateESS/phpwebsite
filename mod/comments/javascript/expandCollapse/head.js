<script type="text/javascript">
var status = new Array();

$(document).ready(function() {
    $('a.expander').click( function() {
        attr_id = $(this).attr('id');
        trig_id = '#user-rank-' + attr_id;

        if (status[attr_id] == '' || status[attr_id] == 0) {
            $(trig_id).hide();
            $(this).html('[+]');
            status[attr_id] = 1;
        } else {
            $(trig_id).show();
            $(this).html('[-]');
            status[attr_id] = 0;
        }
    });
});
</script>
