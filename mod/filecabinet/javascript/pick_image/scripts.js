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

