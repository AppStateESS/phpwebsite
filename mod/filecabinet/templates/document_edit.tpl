<h1>{FORM_TITLE}</h1>
{START_FORM}
<!-- BEGIN error-list -->
<ul>
<!-- BEGIN errors -->
<li class="error">{ERROR}</li>
<!-- END errors -->
</ul>
<!-- END error-list -->

<table class="form-table">
  <tr>
    <td><strong>{CURRENT_DOCUMENT_LABEL}</strong></td><td>{CURRENT_DOCUMENT_ICON} 
{CURRENT_DOCUMENT_FILE}</td>
  </tr>
  <tr>
    <td>{FILE_NAME_LABEL}</td><td>{FILE_NAME}</td>
  </tr>
  <tr>
    <td>{TITLE_LABEL}</td><td>{TITLE}</td>
  </tr>
  <tr>
    <td>{DESCRIPTION_LABEL}</td><td>{DESCRIPTION}</td>
  </tr>
</table>
{SUBMIT} {CANCEL}
{END_FORM}
<br />
{MAX_SIZE_LABEL}: {MAX_SIZE}<br />
