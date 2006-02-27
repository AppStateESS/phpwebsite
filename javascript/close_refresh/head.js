<script type="text/javascript">
//<![CDATA[
var timeout = {timeout};
var refresh = {refresh};
var loc_new = '{location}';

setTimeout('closeWindow()', timeout * 1000);
     
 function closeWindow() {
     if (refresh) {
         if (loc_new) {
             alert(loc_new);
             window.opener.location.href = loc_new;
         } else {
             window.opener.location.href = window.opener.location.href;
         }
     
         if (window.opener.progressWindow) {
             window.opener.progressWindow.close();
         }
     }
     
     window.close();
 }
//]]>
</script>
