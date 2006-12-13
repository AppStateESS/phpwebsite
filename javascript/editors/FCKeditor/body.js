<textarea id="{ID}" name="{NAME}">{VALUE}</textarea>
<script type="text/javascript">
//<![CDATA[
   var oFCKeditor = new FCKeditor( '{NAME}' , 500, 250, 'phpws') ;
   oFCKeditor.BasePath = basepath
   oFCKeditor.Config["CustomConfigurationsPath"] = '{config}';
   oFCKeditor.ReplaceTextarea() ;

//]]>
</script>
