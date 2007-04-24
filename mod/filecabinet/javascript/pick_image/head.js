<script type="text/javascript">
//<![CDATA[

function pick_image(image_id, src, title) {
  span = opener.document.getElementById('{itemname}-current-image');
  image = opener.document.getElementById('{itemname}-current-image').firstChild.firstChild;
  hidden = opener.document.getElementById('{itemname}_hidden_value');
  image.src = src;
  image.title = title;

  hidden.setAttribute('value', image_id);

  span_update = span.innerHTML.replace(/current=\d*\'/gi, 'current=' + image_id + '\'');

  span.innerHTML = span_update;
  window.close();
}

function oversized(image_id, width, height, src, title) {
    var link = 'index.php?module=filecabinet&aop=resize_image&authkey={authkey}&mw=' + width + '&mh=' + height + '&image_id=' + image_id;
    var success = 'resize_update(requester.responseXML)';
    var failure = 'alert("{failure_message}")';

    if (confirm('{confirmation}')) {
        loadRequester(link, success, failure);
    } else {
        pick_image(image_id, src, title);
    }
    return false;
}

function resize_update(response)
{
    src = response.documentElement.getElementsByTagName('thumbnail')[0].firstChild.data;
    image_id = response.documentElement.getElementsByTagName('id')[0].firstChild.data;
    title = response.documentElement.getElementsByTagName('title')[0].firstChild.data;

    if (!response) {
        alert("{failure_message}");
    } else {
        pick_image(image_id, src, title);
        window.location.href = window.location.href;
    }
}

//]]>
</script>
<script type="text/javascript" src="./javascript/modules/filecabinet/pick_image/scripts.js"></script>
