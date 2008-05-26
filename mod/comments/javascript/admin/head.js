<script type="text/javascript" src="javascript/jquery/jquery.js"></script>
<script type="text/javascript">

function punish_user(user_id, link, type)
{
    $.ajax({
             type: 'GET',
             url: 'index.php',
             data: 'module=comments&aop=' + type + '&id=' + user_id + '&authkey={authkey}',
             success: function(data) {
                 $(link).replaceWith(data);
             }
     });
}

$(document).ready(function() {
    $(".full-view").hide();
});

function quick_view(id)
{
    $(id).toggle();
}

function ignore()
{
    if ($('#reported_aop').val() != '') {
        $('#reported').submit();
    }
}

</script>