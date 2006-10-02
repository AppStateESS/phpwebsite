<script type="text/javascript">
//<![CDATA[
image = opener.document.getElementById('{itemname}-current-image').childNodes[0].firstChild;
oLink = opener.document.getElementById('{itemname}-current-image').childNodes[0];
hidden = opener.document.getElementById('{itemname}-current-image').childNodes[1];
new_current = oLink.href.replace(/current=\d*\'/gi, 'current={image_id}\'');

oLink.href = new_current;

image.src = '{src}';
image.width = {width};
image.height = {height};
image.title = '{title}';
image.alt   = '{alt}';

hidden.setAttribute('value', '{image_id}');
window.close();
//]]>
</script>
