<script type="text/javascript">
//<![CDATA[
//current_image = opener.document.getElementById('{itemname}-current-image');
//current_image.innerHTML = "{image_link}\n{hidden}";
    image = opener.document.getElementById('{itemname}-current-image').childNodes[0].firstChild;
    hidden = opener.document.getElementById('{itemname}-current-image').childNodes[1];

new_url = '{src}';

image.src = new_url;
image.width = {width};
image.height = {height};
image.title = '{title}';

hidden.setAttribute('value', '{image_id}');
window.close();
//]]>
</script>
