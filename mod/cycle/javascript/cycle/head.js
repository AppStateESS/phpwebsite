<script type="text/javascript" src="{source_http}mod/cycle/javascript/cycle/jquery.cycle.all.js"></script>
<script type="text/javascript">

var bg_width = '{bg_width}px';
var bg_height = '{bg_height}px';

var url = new Array();
{urls}

$(function() {

    $('#goto1').click(function() {
        $('#cycle-full').cycle(0);
        return false;
    });
    $('#goto2').click(function() {
        $('#cycle-full').cycle(1);
        return false;
    });
    $('#goto3').click(function() {
        $('#cycle-full').cycle(2);
        return false;
    });
    $('#goto4').click(function() {
        $('#cycle-full').cycle(3);
        return false;
    });
    $('.cycle-pic').click(function() {
        id = $(this).attr('id').replace('pic', '');
        document.location.href = url[id];
    });


    $('#cycle-full').cycle({
        fx:     'fade',
        speed:  2000,
        timeout: 10000,
        fit : 1,
        width : bg_width,
        height : bg_height
    });
});

</script>