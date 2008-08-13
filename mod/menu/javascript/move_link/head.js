<script type="text/javascript">
//<![CDATA[

function move_link(menu_id, link_id, key_id, dir)
{
    url = 'index.php?module=menu&command=move_link&dir=' + dir
    + '&menu_id=' + menu_id + '&link_id=' + link_id
    + '&key_id=' + key_id + '&authkey={authkey}';

    $.get(url, function(data) {
        $('#menu_' + menu_id).html(data);
    });
}

//]]>
</script>