<script type="text/javascript">

function report(id, link) {
    $.get('index.php?module=comments&uop=report_comment&cm_id=' + id);
    $(link).replaceWith('{reported}');
}

</script>