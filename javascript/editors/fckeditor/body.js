<textarea id="{ID}" name="{NAME}" cols="60" rows="15">{VALUE}</textarea>
<script type="text/javascript">
//<![CDATA[
   var oFCKeditor = new FCKeditor( '{NAME}' , {WIDTH}, {HEIGHT}, 'phpws') ;
   oFCKeditor.BasePath = '{home_http}javascript/editors/fckeditor/';
   oFCKeditor.Config["CustomConfigurationsPath"] = '{config}';
<!-- BEGIN style-sheet -->oFCKeditor.Config["EditorAreaCSS"] = '{base_http}themes/{current_theme}/fckeditor.css';<!-- END style-sheet -->
   oFCKeditor.ReplaceTextarea() ;
//]]>
</script>
