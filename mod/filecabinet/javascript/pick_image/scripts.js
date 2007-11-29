var current_tn = 0;

function getX(width) {
    x = (640 - width)/2;
    if (screen) {
        return (screen.availWidth - width)/2;
    }
}

function getY(height) {
    y = (480 - height)/2;
    if (screen) {
        return (screen.availHeight - height)/2;
    }
}

function upload_new(address) {
width = 640;
height = 480;

x = getX(width);
y = getY(height);

window_vars = 'toolbar=no,top='+ y +',left='+ x +',screenY='+ y +',screenX='+ x +',scrollbars=yes,menubar=no,location=no,resizable=yes,width=' + width + ',height=' + height;

upload = window.open(address, 'upload_window', window_vars);
}


function show_image(image_id, tn_id, width, height) {
x = getX(width);
y = getY(height);

height = height + 100;

address = "index.php?module=filecabinet&action=view_image&image_id=" + image_id;

window_vars = 'toolbar=no,top='+ y +',left='+ x +',screenY='+ y +',screenX='+ x +',scrollbars=yes,menubar=no,location=no,resizable=yes,width=' + width + ',height=' + height;

upload = window.open(address, 'view_window', window_vars);

}

function highlight(image_id) {
    removehighlight(current_tn);
    current_image = image_id;
    
    span = document.getElementById('image-' + tn_id).parentNode;
    span.setAttribute('style', 'border : 2px solid red;');
    current_tn = tn_id;
}

function removehighlight(image_id) {
    if (tn_id < 1) {
        return;
    }
    span = document.getElementById('image-' + tn_id).parentNode;
    span.setAttribute('style', 'border : 2px solid transparent');
}


function cancel()
{
    window.close();
}

function pick_image(image_id, src, title, width, height) {
    span = opener.document.getElementById(itemname + '-current-image');
    image = opener.document.getElementById('image-manager-' + itemname);
    hidden = opener.document.getElementById(itemname + '_hidden_value');
    image.src = src;
    image.title = title;
    image.width = width;
    image.height = height;

    hidden.setAttribute('value', image_id);
    span_update = span.innerHTML.replace(/current=\d*\'/gi, 'current=' + image_id + '\'');

    span.innerHTML = span_update;
    window.close();
}

function oversized(image_id, src, title, width, height) {
    var link = 'index.php?module=filecabinet&aop=resize_image&authkey=' + authkey + '&mw=' + maxwidth + '&mh=' + maxheight + '&image_id=' + image_id;
    var success = 'resize_update(requester.responseXML)';
    var failure = 'alert(failure_message)';

    if (confirm(confirm_message)) {
        loadRequester(link, success, failure);
    } else {
        pick_image(image_id, src, title, width, height);
    }
    return false;
}

function resize_update(response)
{
    src = response.documentElement.getElementsByTagName('path')[0].firstChild.data;
    image_id = response.documentElement.getElementsByTagName('id')[0].firstChild.data;
    title = response.documentElement.getElementsByTagName('title')[0].firstChild.data;
    width = response.documentElement.getElementsByTagName('width')[0].firstChild.data;
    height = response.documentElement.getElementsByTagName('height')[0].firstChild.data;

    if (!response) {
        alert(failure_message);
    } else {
        pick_image(image_id, src, title, width, height);
        window.location.href = window.location.href;
    }
}
