{START_FORM}
<table class="form-table">
  <tr>
    <td><span class="label">{DISPLAY_NAME_LABEL}</span></td>
    <td>{DISPLAY_NAME}</td>
  </tr>
  <!-- BEGIN display-error -->
  <tr><td colspan="2"><span class="error-message">{DISPLAY_ERROR}</span></td></tr>
  <!-- END display-error -->
  <!-- BEGIN password-change -->
  <tr>
    <td><span class="label">{PASSWORD1_LABEL}</span></td><td>{PASSWORD1}&nbsp;{PASSWORD2}</td>
  </tr>
  <!-- BEGIN password-error -->
  <tr><td colspan="2"><span class="error-message">{PASSWORD_ERROR}</span></td></tr>
  <!-- END password-error -->
  <!-- END password-change -->
  <tr class="bg-light">
    <td><span class="label">{EMAIL_LABEL}</span></td><td>{EMAIL}</td>
  </tr>
  <!-- BEGIN email-error -->
  <tr><td colspan="2"><span class="error-message">{EMAIL_ERROR}</span></td></tr>
  <!-- END email-error -->
</table>
{SUBMIT}
{END_FORM}
