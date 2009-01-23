<textarea id="{ID}" name="{NAME}" cols="60" rows="15">{VALUE}</textarea>
<script type="text/javascript">
//<![CDATA[
     var oFCKeditor = new FCKeditor( '{NAME}' , {WIDTH}, {HEIGHT}, 'phpws') ;
   oFCKeditor.BasePath = basepath
   oFCKeditor.Config["CustomConfigurationsPath"] = '{config}';
<!-- BEGIN style-sheet -->oFCKeditor.Config["EditorAreaCSS"] = '../../../../themes/{current_theme}/fckeditor.css';<!-- END style-sheet -->
   oFCKeditor.ReplaceTextarea() ;
//]]>
</script>
