<script type="text/javascript">
    $(document).ready(function() {
    $('.report').click(function() {
        view_report($(this).attr('id'));
    });

});


function view_report(id) {
    $.get('index.php?module=properties&aop=report_view', { id: id }, function (data) {
        $('#report-view').html(data);
        $('#close-view').click(function() {
            $('#report-view').html('');
        });
        $('#pop-background').click(function() {
            $('#report-view').html('');
        });
    });
}


</script>