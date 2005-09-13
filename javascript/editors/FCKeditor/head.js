<script type="text/javascript" src="./javascript/editors/FCKeditor/fckeditor.js"></script>
<script type="text/javascript">
//<![CDATA[
window.onload = FCKinit;
function FCKinit()
{
    var oFCKeditor = new FCKeditor( '{NAME}' , 500, 300, "phpws") ;
    oFCKeditor.BasePath = './javascript/editors/FCKeditor/';
    oFCKeditor.Config["CustomConfigurationsPath"] = 'custom.php?module={module}';
    oFCKeditor.ReplaceTextarea() ;
}
//]]>
</script>
