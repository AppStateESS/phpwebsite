<script type="text/javascript">
$(document).ready(function(){
    $('a.thumb-nav').click(function() {
        sid = $(this).attr('id');
        sort = sid.replace('goto', '');
        address = 'index.php?module=cycle&aop=form&sid=' + sort;
        $.get(address, function(data) {
            $('#cycle-cell').html(data);
        });
        return false;
    });
});
</script>