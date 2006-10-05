{START_FORM}
<table class="form-table">
  <tr>
    <td class="label">{DISPLAY_NAME_LABEL}</td>
    <td>{DISPLAY_NAME}</td>
  </tr>
  <!-- BEGIN display-error -->
  <tr><td colspan="2"><span class="error-message">{DISPLAY_ERROR}</span></td></tr>
  <!-- END display-error -->
  <!-- BEGIN password-change -->
  <tr>
    <td class="label">{PASSWORD1_LABEL}</td><td>{PASSWORD1}&nbsp;{PASSWORD2}</td>
  </tr>
  <!-- BEGIN password-error -->
  <tr><td colspan="2"><span class="error-message">{PASSWORD_ERROR}</span></td></tr>
  <!-- END password-error -->
  <!-- END password-change -->
  <tr>
    <td class="label">{EMAIL_LABEL}</td>
    <td>{EMAIL}</td>
  </tr>
  <!-- BEGIN email-error -->
  <tr><td colspan="2"><span class="error-message">{EMAIL_ERROR}</span></td></tr>
  <!-- END email-error -->
  <tr>
    <td class="label">{LANGUAGE_LABEL}</td>
    <td>{LANGUAGE}</td>
  </tr>
  <tr>
    <td class="label"><label for="timezone">{TIMEZONE_LABEL}</td>
    <td>{TIMEZONE}</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>{DST} {DST_LABEL}</td>
  </tr>
</table>
{SUBMIT}
{END_FORM}
