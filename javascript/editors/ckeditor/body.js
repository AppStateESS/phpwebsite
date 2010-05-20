<textarea cols="80" id="{ID}" name="{NAME}" rows="10">{VALUE}</textarea>
<script type="text/javascript">
CKEDITOR.replace( '{ID}', {customConfig : '{source_http}javascript/editors/ckeditor/phpws/config.js',
filebrowserBrowseUrl: 'index.php?module=layout&action=ckeditor',
filebrowserImageWindowWidth : '1200',
filebrowserImageWindowHeight : '600'
} );
</script>