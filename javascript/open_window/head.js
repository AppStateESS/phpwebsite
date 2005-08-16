<script type="text/javascript">
//<![CDATA[

function open_window(page, width, height) {
   x = (640 - width)/2, y = (480 - height)/2;

    if (screen) {
        y = (screen.availHeight - height)/2;
        x = (screen.availWidth - width)/2;
    }

  popup = window.open(page, 'CtrlWindow', 'toolbar={toolbar},top='+ y +',left='+ x +',screenY='+ y +',screenX='+ x +',scrollbars={scrollbars},menubar={menubar},location={location},resizable={resizable},width=' + width + ',height=' + height);
}

//]]>
</script>
