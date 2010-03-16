<script type="text/javascript">
function voila(foo) {
    spanner = document.getElementById('link-url');
    if (foo.value == 'url') {
        spanner.style.display = 'table-row';
    } else {
        spanner.style.display = 'none';
    }
}
</script>
<h1>{FORM_TITLE}</h1>
<!-- BEGIN errors -->
<p class="error">{ERRORS}</p>
<!-- END errors -->
{START_FORM}
<table class="form-table">
    <!-- BEGIN current-image -->
    <tr>
        <td><strong>{CURRENT_IMAGE_LABEL}</strong></td>
        <td>{CURRENT_IMAGE}<br />
        {SIZE}</td>
    </tr>
    <!-- END current-image -->
    <tr>
        <td>{FILE_NAME_LABEL}</td>
        <td>{FILE_NAME}</td>
    </tr>
    <tr>
        <td>{TITLE_LABEL}</td>
        <td>{TITLE}</td>
    </tr>
    <tr>
        <td>{ALT_LABEL}</td>
        <td>{ALT}</td>
    </tr>
    <tr>
        <td>{DESCRIPTION_LABEL}</td>
        <td>{DESCRIPTION}</td>
    </tr>
    <tr>
        <td>{LINK_LABEL}</td>
        <td>{LINK}</td>
    </tr>
    <tr style="display: { VISIBLE" id="link-url">
        <td>{URL_LABEL}</td>
        <td>{URL}</td>
    </tr>
    <tr>
        <td>{RESIZE_LABEL}</td>
        <td>{RESIZE}</td>
    </tr>
    <tr>
        <td>{ROTATE_LABEL}</td>
        <td>{ROTATE}</td>
    </tr>
    <!-- BEGIN move -->
    <tr>
        <td>{MOVE_TO_FOLDER_LABEL}</td>
        <td>{MOVE_TO_FOLDER}</td>
    </tr>
    <!-- END move -->
</table>
{SUBMIT} {CANCEL} {END_FORM}
<br />
{MAX_SIZE_LABEL}: {MAX_SIZE}
<br />
{MAX_DIMENSION_LABEL}: {MAX_DIMENSION}px
<br />
