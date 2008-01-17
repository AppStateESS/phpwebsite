function oversized(image_id) {
    if (confirm(confirm_message)) {
        file_type = 7;
    } else {
        file_type = 1;
    }

    window.location.href = 'index.php?module=filecabinet&fop=pick_file&mw=' + mw + '&mh=' + mh + 
                           '&cm=' + cm + '&authkey=' + authkey + '&itn=' + itn +
                           '&file_type=' + file_type + '&id=' + image_id;
    return false;
}

