<script type="text/javascript">
function confirm_delete()
{
    if (confirm('{question}')) {
        tpl = $('#upload-templates_page_templates').val();
        location.href = '{address}&tpl=' + tpl;
    }
}
</script>