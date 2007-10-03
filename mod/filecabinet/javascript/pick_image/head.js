<script type="text/javascript">
//<![CDATA[

if (!opener) {
    document.location.href = 'index.php';
}

var itemname = '{itemname}';
var maxsize = {maxsize};
var maxwidth = {maxwidth};
var maxheight = {maxheight};

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
    var link = 'index.php?module=filecabinet&aop=resize_image&authkey={authkey}&mw=' + maxwidth + '&mh=' + maxheight + '&image_id=' + image_id;
    var success = 'resize_update(requester.responseXML)';
    var failure = 'alert("{failure_message}")';

    if (confirm('{confirmation}')) {
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
        alert("{failure_message}");
    } else {
        pick_image(image_id, src, title, width, height);
        window.location.href = window.location.href;
    }
}

//]]>
</script>
<script type="text/javascript" src="./javascript/modules/filecabinet/pick_image/scripts.js"></script>
