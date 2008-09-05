<script type="text/javascript">
//<![CDATA[

var html_view = false;
$(document).ready(function() {
    $('#view-link').click(function() {
        if (html_view) {
            $('#clip').html();
            var htmlStr = $('#clip').text();
            $('#clip').html(htmlStr);
            $('#clip textarea').remove();
            html_view = false;
        }
    });

    $('#source-link').click(function() {
        if (!html_view) {
            var htmlStr = $('#clip').html();
            $('#clip').text(htmlStr);
            $('#clip').wrapInner('<textarea></textarea>');
            html_view = true;
        }
    });

});

//]]>
</script>
