<textarea cols="80" id="{ID}" name="{NAME}" rows="10">{VALUE}</textarea>
<script type="text/javascript">
CKEDITOR.replace( '{ID}', {customConfig : '{source_http}javascript/editors/ckeditor/phpws/config.js',
filebrowserBrowseUrl: '{source_http}javascript/editors/ckeditor/filemanager/index.php?sh=' + sh,
//filebrowserBrowseUrl : source_http + 'phpws/browse.php',
//filebrowserUploadUrl : source_http + 'phpws/upload.php',
filebrowserImageWindowWidth : '1200',
filebrowserImageWindowHeight : '600'
} );
</script>