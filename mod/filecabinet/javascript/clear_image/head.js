<script type="text/javascript">
//<![CDATA[

    function clear_image(itemname) {
        image = document.getElementById(itemname + '-current-image').childNodes[0].firstChild;
        hidden = document.getElementById(itemname + '_hidden_value');

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
