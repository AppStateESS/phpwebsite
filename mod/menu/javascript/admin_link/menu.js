function move_link(menu_id, link_id, dir)
{
    forward = 'index.php?module=menu&command=move_link&dir=' + dir
    + '&menu_id=' + menu_id + '&link_id=' + link_id
    + '&key_id=' + ref_key + '&authkey=' + authkey;
    forward_url(forward, menu_id);
}

function add_keyed_link(menu_id, parent_id)
{
    forward = 'index.php?module=menu&command=ajax_add_link&menu_id=' + menu_id 
        + '&key_id=' + ref_key + '&parent=' +  parent_id + '&authkey=' + authkey;
    forward_url(forward, menu_id);
}


function add_unkeyed_link(menu_id, parent_id, url, link_title)
{
    forward = 'index.php?module=menu&command=ajax_add_link&menu_id=' + menu_id 
        + '&parent=' +  parent_id + '&link_title=' + link_title + '&ref_key=' + ref_key
        + '&url=' + url + '&authkey=' + authkey;
    forward_url(forward, menu_id);
}

function delete_link(menu_id, link_id, title)
{
    if (confirm('{delete_question} ' + title)) {
        forward = 'index.php?module=menu&command=delete_link&link_id=' + link_id
            + '&menu_id=' + menu_id + '&key_id=' + ref_key + '&ajax=1&authkey=' + authkey;
        forward_url(forward, menu_id);
    }
}

function forward_url(forward_address, menu_id) {
    $.get(forward_address, function(data) {
        $('#menu_' + menu_id).html(data);
    });
}
