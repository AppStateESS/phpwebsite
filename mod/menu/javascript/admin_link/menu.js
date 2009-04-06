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
    if (confirm(delete_question + ' ' + title)) {
        forward = 'index.php?module=menu&command=delete_link&link_id=' + link_id
            + '&menu_id=' + menu_id + '&key_id=' + ref_key + '&ajax=1&authkey=' + authkey;
        forward_url(forward, menu_id);
    }
}

function forward_url(forward_address, menu_id) {
    $.get(forward_address, function(data) {
        $('#menu-' + menu_id).html(data);
        sort_links();
        indent();
    });
}

$(document).ready(
    function(){
        sort_links();
        indent();
    }
);

function indent()
{
    $(".menu-indent").bind("click", function(){
        info = $(this).attr('id');
        info = info.replace(/menu-indent-/, '');
        ids = info.split('-');
        menu_id = ids[0];
        link_id = ids[1];
        forward = 'index.php?module=menu&command=indent_link&link_id=' + link_id 
                      + '&menu_id=' + menu_id + '&key_id=' + ref_key + '&authkey=' + authkey;
        forward_url(forward, menu_id);
        }
    );

    $(".menu-outdent").bind("click", function(){
        info = $(this).attr('id');
        info = info.replace(/menu-outdent-/, '');
        ids = info.split('-');
        menu_id = ids[0];
        link_id = ids[1];
        forward = 'index.php?module=menu&command=outdent_link&link_id=' + link_id 
                      + '&menu_id=' + menu_id + '&key_id=' + ref_key + '&authkey=' + authkey;
        forward_url(forward, menu_id);
        }
    );

}

function sort_links()
{
    if (!drag_sort) {
        return;
    }

    $(".menu-links").sortable({
        opacity : .40,
        update : show,
        items : "li",
        revert : true,
        tree : true
    });

}

function show(e, ui)
{
    new_parent = ui.item.parent().attr('id');
    if (new_parent.match('menu-parent-')) {
        new_parent = new_parent.replace(/menu-parent-/, '');
    } else {
        new_parent = 0;
    }

    prev_link = ui.item.prev().attr('id');
    if (prev_link) {
        prev_link = prev_link.replace(/menu-link-/, '');
    } else {
        prev_link = 0;
    }
    

    menu_id_raw = $(this).attr('id');
    menu_id = menu_id_raw.replace(/sort-menu-/, '');

    moved = ui.item.attr('id').replace(/menu-link-/, '');

    forward = 'index.php?module=menu&command=sort_menu_links&menu_id=' + menu_id + '&parent=' + new_parent
        + '&key_id=' + ref_key + '&authkey=' + authkey + '&moved=' + moved + '&under=' + prev_link;

    forward_url(forward, menu_id);
}



