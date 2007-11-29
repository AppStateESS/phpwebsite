var popup = 0;

function open_window(page, width, height, window_name) {

    if(popup) {
        if(!popup.closed) {
            if (popup.focus) {
                popup.focus();
            }
        }
    }

    x = (640 - width)/2, y = (480 - height)/2;

    if (screen) {
        y = (screen.availHeight - height)/2;
        x = (screen.availWidth - width)/2;
    }

    popup = window.open(page, window_name, 'toolbar={toolbar},top='+ y +',left='+ x +',screenY='+ y +',screenX='+ x +',scrollbars={scrollbars},menubar={menubar},location={location},resizable={resizable},width=' + width + ',height=' + height);

   return false;
}
