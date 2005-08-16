<script type="text/javascript">

var requester = null;
var image_id  = 0;
var mod_title = '{mod_title}';
var current_tn = 0;
var current_image = 0;

function getX(width)
{
    x = (640 - width)/2;
    if (screen) {
        return (screen.availWidth - width)/2;
    }
}

function getY(height)
{
    y = (480 - height)/2;
    if (screen) {
        return (screen.availHeight - height)/2;
    }
}

function upload_new(address)
{

width = {upload_width};
height = {upload_height};

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

function highlight(tn_id, image_id) {
    removehighlight(current_tn);
    current_image = image_id;
    
    span = document.getElementById('image-' + tn_id).parentNode;
    span.setAttribute('style', 'border : 1px solid orange; background-color : orange;');
    current_tn = tn_id;
}

function removehighlight(tn_id) {
    if (tn_id < 1) {
        return;
    }
    span = document.getElementById('image-' + tn_id).parentNode;
    span.setAttribute('style', '');

}


function cancel()
{
    window.close();
}

function post_pick(mod_title, itemname)
{
    if (current_image < 1) {
        alert('{image_warning}');
        return;
    }
    document.location.href = 'index.php?module=filecabinet&action=post_pick&mod_title=' + mod_title + '&itemname=' + itemname + '&image_id=' + current_image;
}


</script>

    
