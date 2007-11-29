function source_trigger(checkbox) {
    image_list = document.getElementById('image-list').childNodes;

    for (i=0; i < image_list.length; i++) {
        node = image_list[i];

        if (node.className == 'child-image') {
            if (checkbox.checked == true) {
                node.style.display = 'none';
            } else {
                node.style.display = 'inline';
            }
        }
    }
}

function show_images(image_html, folder_id)
{
    var button = document.getElementById('image-button');

    if (!image_html) {
        images.innerHTML = error_message;
        button.style.visibility = 'hidden';
    } else {
        button.style.visibility = 'visible';
        button_update = button.innerHTML.replace(/folder_id=\d*&/gi, 'folder_id='+folder_id+'&');
        button.innerHTML = button_update;
        image_list = document.getElementById('images');
        image_list.innerHTML = image_html;
    }
}
