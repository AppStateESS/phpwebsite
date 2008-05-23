function oversized(image_id, force_resize) {
    if (confirm(confirm_message)) {
        file_type = 7;
    } else if (!force_resize) {
        file_type = 1;
    } else {
        return false;
    }

    window.location.href = 'index.php?module=filecabinet&fop=pick_file&mw=' + mw + '&mh=' + mh + 
                           '&cm=' + cm + '&authkey=' + authkey + '&itn=' + itn +
                           '&file_type=' + file_type + '&id=' + image_id + '&fid=' + fid;
    return false;
}

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

