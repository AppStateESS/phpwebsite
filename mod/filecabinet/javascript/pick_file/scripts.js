function oversized_media (media_id, force_resize) {
    if (confirm(confirm_message)) {
        file_type = 8;
    } else if (!force_resize) {
        file_type = 3;
    } else {
        return false;
    }

    window.location.href = 'index.php?module=filecabinet&fop=pick_file&mw=' + mw + '&mh=' + mh + 
                           '&cm=' + cm + '&authkey=' + authkey + '&itn=' + itn +
                           '&file_type=' + file_type + '&id=' + media_id + '&fid=' + fid;
    return false;
}

var previous_panel_id = 0;

function slider(id)
{
    slide_id = '#image-thumbnail-' + id;
    panel = '#panel-' + id;

    if (previous_panel_id != id) {
        previous_panel = '#panel-' + previous_panel_id;
        $(panel).show('fast');
        $(previous_panel).hide('fast');
        previous_panel_id = id;
    }
    else {
        $(panel).hide('fast');
        previous_panel_id = 0;
    }

    return false;
}
