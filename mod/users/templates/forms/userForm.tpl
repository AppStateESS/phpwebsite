<!-- BEGIN message -->
<span class="smalltext">{MESSAGE}</span>
<hr />
<!-- END message -->
{START_FORM}
<table cellspacing="0" cellpadding="4" width="100%">
  <tr class="bg-light">
    <td><b>{USERNAME_LABEL}</b></td><td>{USERNAME}</td>
  </tr>
  <!-- BEGIN username-error -->
  <tr><td class="user-error" colspan="2">{USERNAME_ERROR}</td></tr>
  <!-- END username-error -->
  <tr class="bg-light">
    <td><b>{DISPLAY_NAME_LABEL}</b></td><td>{DISPLAY_NAME}</td>
  </tr>
  <tr>
    <td><b>{PASSWORD1_LABEL}</b></td><td>{PASSWORD1}&nbsp;{PASSWORD2}</td>
  </tr>
  <!-- BEGIN password-error -->
  <tr><td class="user-error" colspan="2">{PASSWORD_ERROR}</td></tr>
  <!-- END password-error -->
  <tr class="bg-light">
    <td><b>{EMAIL_LABEL}</b></td><td>{EMAIL}</td>
  </tr>
  <!-- BEGIN email-error -->
  <tr><td class="user-error" colspan="2">{EMAIL_ERROR}</td></tr>
  <!-- END email-error -->
</table>
{SUBMIT}
{END_FORM}
