<textarea id="{ID}" name="{NAME}">{VALUE}</textarea>
<script type="text/javascript">
//<![CDATA[
     var oFCKeditor = new FCKeditor( '{NAME}' , {WIDTH}, {HEIGHT}, 'phpws') ;
   oFCKeditor.BasePath = basepath
   oFCKeditor.Config["CustomConfigurationsPath"] = '{config}';
   oFCKeditor.ReplaceTextarea() ;

//]]>
</script>
