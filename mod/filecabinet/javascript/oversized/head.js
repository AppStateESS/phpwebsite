<script type="text/javascript">
//<![CDATA[

var failure_message = '{failure_message}';
var confirm_message = '{confirmation}';
var maxwidth        = {maxwidth};
var maxheight       = {maxheight};



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


//]]>
</script>
