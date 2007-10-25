<script type="text/javascript">
//<![CDATA[

    function clear_image(itemname, width, height) {
        image = document.getElementById('image-manager-' + itemname);
        hidden = document.getElementById(itemname + '_hidden_value');
        link = document.getElementById(itemname + '-clear');
        url = link.href;
        url_new = url.replace(/current=\d*'/gi, "current=0'");

        link.href = url;

        if (image) {
            image.src = '{src}';
            image.width = width;
            image.height = height;
            image.title = '{title}';
            image.alt   = '{alt}';
        }

        if (hidden) {
            hidden.setAttribute('value', '0');
        }
    }

//]]>
</script>
