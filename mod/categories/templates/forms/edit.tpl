<div class="page-title">{PAGE_TITLE}</div>
{START_FORM}
<table border="0" width="100%" cellpadding="4" cellspacing="4">
  <tr>
    <td><b>{PARENT_LBL}:</b></td>
    <td>{PARENT}</td>
  </tr>
  <tr>
    <td><b>{TITLE_LBL}:</b></td>
    <td>{TITLE}
	<!-- BEGIN title-error -->
	<div class="cat-error">{TITLE_ERROR}</div>
	<!-- END title-error -->
    </td>
  </tr>
  <tr>
    <td><b>{DESC_LBL}:</b></td>
    <td>{DESCRIPTION}</td>
  </tr>
  <tr>
    <td><b>{IMAGE_LBL}:</b></td>
    <td>{IMAGE}
	<!-- BEGIN image-error -->
	<div class="cat-error">{IMAGE_ERROR}</div>
	<!-- END image-error -->
	<!-- BEGIN current-image -->
	<br />
	<span style="font-size : .9em"><b>{CURRENT_IMG_LABEL}:</b> {CURRENT_IMG}</span>
	<!-- END current-image -->
    </td>
  </tr>
</table>
<br />
{SUBMIT}
{END_FORM}
