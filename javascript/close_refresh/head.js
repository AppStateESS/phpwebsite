<script type="text/javascript">
//<![CDATA[
var timeout = {timeout};
var refresh = {refresh};

setTimeout('closeWindow()', timeout * 1000);
     
 function closeWindow() {
     if (refresh) {
         window.opener.location.href = window.opener.location.href;
     
         if (window.opener.progressWindow) {
             window.opener.progressWindow.close();
         }
     }
     
     window.close();
 }
//]]>
</script>
