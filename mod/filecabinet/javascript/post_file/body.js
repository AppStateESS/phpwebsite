<script type="text/javascript">
    //<![CDATA[
    image = opener.document.getElementById('{itemname}-current-image').childNodes[0].firstChild;
hidden = opener.document.getElementById('{itemname}-current-image').childNodes[1];

image.src = '{src}';
image.width = {width};
image.height = {height};
image.title = '{title}';
image.alt   = '{alt}';

hidden.setAttribute('value', '{image_id}');
window.close();
//]]>
</script>
