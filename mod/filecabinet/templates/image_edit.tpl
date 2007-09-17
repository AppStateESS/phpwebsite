<script type="text/javascript">
function voila(foo) {
    spanner = document.getElementById('link-url');
    if (foo.value == 'url') {
        spanner.style.visibility = 'visible';
    } else {
        spanner.style.visibility = 'hidden';
    }
}
</script>

{START_FORM}
<!-- BEGIN error-list -->
<ul>
<!-- BEGIN errors -->
<li class="error-text">{ERROR}</li>
<!-- END errors -->
</ul>
<!-- END error-list -->

<table class="form-table">
<!-- BEGIN current-image -->
  <tr>
    <td><strong>{CURRENT_IMAGE_LABEL}</strong></td><td>{CURRENT_IMAGE}<br />{SIZE}</td>
  </tr>
<!-- END current-image -->
  <tr>
    <td>{FILE_NAME_LABEL}</td><td>{FILE_NAME}</td>
  </tr>
  <tr>
    <td>{TITLE_LABEL}</td><td>{TITLE}</td>
  </tr>
  <tr>
    <td>{ALT_LABEL}</td><td>{ALT}</td>
  </tr>
  <tr>
    <td>{DESCRIPTION_LABEL}</td><td>{DESCRIPTION}</td>
  </tr>
  <tr>
    <td>{LINK_LABEL}</td>
    <td>{LINK}</td>
  </tr>
  <tr>
    <td>{RESIZE_LABEL}</td>
    <td>{RESIZE}</td>
  </tr>
  <tr>
    <td>{ROTATE_LABEL}</td>
    <td>{ROTATE}</td>
  </tr>
  <tr style="visibility : {VISIBLE};" id="link-url">
    <td>{URL_LABEL}</td>
    <td>{URL}</td>
  </tr>
</table>
{SUBMIT} {CANCEL}
{END_FORM}
<br />
{MAX_SIZE_LABEL}: {MAX_SIZE}<br />
{MAX_WIDTH_LABEL}: {MAX_WIDTH}px<br />
{MAX_HEIGHT_LABEL}: {MAX_HEIGHT}px<br />
