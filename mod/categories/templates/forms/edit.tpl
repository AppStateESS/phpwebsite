{START_FORM}
<table border="0" width="100%" cellpadding="4" cellspacing="4">
  <tr>
    <td width="15%"><b>{PARENT_LABEL}:</b></td>
    <td>{PARENT}</td>
  </tr>
  <tr>
    <td><b>{TITLE_LABEL}:</b></td>
    <td>{TITLE}
	<!-- BEGIN title-error -->
	<div class="cat-error">{TITLE_ERROR}</div>
	<!-- END title-error -->
    </td>
  </tr>
  <tr>
    <td><b>{CAT_DESCRIPTION_LABEL}:</b></td>
    <td>{CAT_DESCRIPTION}</td>
  </tr>
  <tr>
    <td><b>{IMAGE_LABEL}:</b></td>
    <td>{IMAGE_FILE}<br />
	<b>{IMAGE_TITLE_LABEL}</b>: {IMAGE_TITLE}<br />
	{IMAGE_SELECT}
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
