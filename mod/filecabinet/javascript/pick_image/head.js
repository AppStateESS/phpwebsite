<script type="text/javascript">

var requester = null;
var image_id = 0;

function showImage(id)
{
    image_id = id;

    if (requester != null && requester.readyState != 0 && requester.readyState != 4) {
            requester.abort();
    }
    try	{
        requester = new XMLHttpRequest();
    }
    catch (error) {
        try {
            requester = new ActiveXObject("Microsoft.XMLHTTP");
        }
        catch (error) {
            requester = null;
            return false;
        }
    }

    requester.onreadystatechange = getImage;
    requester.open("GET", "index.php?module=filecabinet&action=get_image_xml&id=" + image_id);
    requester.send(null);
}

function getImage()
{
    if (requester.readyState == 4) {
        if (requester.status == 200) {
            show_image();
        }
        else {
            alert('Unable to get file');
        }
    }
    return true;
}

function show_image()
{
    image_src = requester.responseXML.getElementsByTagName('src')[0].firstChild.nodeValue;
    image_width = requester.responseXML.getElementsByTagName('width')[0].firstChild.nodeValue;
    image_height = requester.responseXML.getElementsByTagName('height')[0].firstChild.nodeValue;
    image_title  =requester.responseXML.getElementsByTagName('title')[0].firstChild.nodeValue;
    image_alt  =requester.responseXML.getElementsByTagName('alt')[0].firstChild.nodeValue;
    image_desc  =requester.responseXML.getElementsByTagName('desc')[0].firstChild.nodeValue;

    //    document.getElementById('image-details').style.display = 'block';

    image_tag = '<img src="' + image_src + '" width="' + image_width +'" height="' + image_height + '" title="' + image_title + '" alt="' + image_alt + '" />';
    document.getElementById('image-tag').innerHTML = image_tag;
    document.getElementById('image-desc').innerHTML = image_desc;
    document.getElementById('image-title').innerHTML = image_title;

    document.getElementById('pick-link').href = '{pick_link}' + image_id;
}

</script>

    
