<script type="text/javascript">
//<![CDATA[

    function clear_image(itemname) {
        image = document.getElementById('image-manager-' + itemname);
        hidden = document.getElementById(itemname + '_hidden_value');
        link = document.getElementById(itemname + '-current-image').childNodes[1];
        url = link.getAttribute('onclick');
        url_new = url.replace(/current=\d*'/gi, "current=0'");

        link.setAttribute('onclick', url_new);

        if (image) {
            image.src = '{src}';
            image.width = {width};
            image.height = {height};
            image.title = '{title}';
            image.alt   = '{alt}';
        }

        if (hidden) {
            hidden.setAttribute('value', '0');
        }
    }

//]]>
</script>
