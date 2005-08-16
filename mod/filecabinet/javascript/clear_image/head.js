<script type="text/javascript">
//<![CDATA[

    function clear_image(itemname) {
        image = document.getElementById(itemname + '-current-image').childNodes[0].firstChild;
        hidden = document.getElementById(itemname + '-current-image').childNodes[1];
        
        image.src = '{src}';
        image.width = {width};
        image.height = {height};
        image.title = '{title}';
        image.alt   = '{alt}';
        hidden.setAttribute('value', '0');
    }

//]]>
</script>
