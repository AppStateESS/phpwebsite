<script type="text/javascript">
var requester = null;
var image_id  = 0;
var mod_title = '{mod_title}';
var current_image = 0;
var itemname = '{itemname}';
var authkey = '{authkey}';

function post_pick(mod_title, itemname)
{
    if (current_image < 1) {
        alert('{image_warning}');
        return;
    }
    document.location.href = 'index.php?module=filecabinet&action=post_pick&mod_title=' + mod_title + '&itemname=' + itemname + '&image_id=' + current_image;
}

function delete_pick() {
    if (current_image < 1) {
        alert('{image_warning}');
        return;
    }

    if (confirm('{confirm_delete}')) {
        document.location.href = 'index.php?module=filecabinet&action=delete_pick&mod_title=' + mod_title + '&itemname=' + itemname + '&image_id=' + current_image;
    }

}

function upload_new(address) {
width = {upload_width};
height = {upload_height};

x = getX(width);
y = getY(height);

window_vars = 'toolbar=no,top='+ y +',left='+ x +',screenY='+ y +',screenX='+ x +',scrollbars=yes,menubar=no,location=no,resizable=yes,width=' + width + ',height=' + height;

upload = window.open(address, 'upload_window', window_vars);
}

</script>
<script type="text/javascript" src="./javascript/modules/filecabinet/pick_image/scripts.js"></script>
