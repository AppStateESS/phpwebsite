<script type="text/javascript">
//<![CDATA[


$(document).ready(function() {
    $('#clip-content').show();
    $('#clip-source').hide();
    $('#clip-smarttag').hide();

    $('#view-link').click(function() {
        $('#clip-content').show();
        $('#clip-source').hide();
        $('#clip-smarttag').hide();
    });

    $('#source-link').click(function() {
        $('#clip-content').hide();
        $('#clip-source').show();
        $('#clip-smarttag').hide();
    });

    $('#smart-link').click(function() {
        $('#clip-content').hide();
        $('#clip-source').hide();
        $('#clip-smarttag').show();
    });
});

//]]>
</script>
