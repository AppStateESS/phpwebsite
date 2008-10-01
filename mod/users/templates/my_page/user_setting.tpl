{START_FORM}
<fieldset style="margin-bottom : 2em"><legend>{ACCT_INFO}</legend>
<table width="99%" cellpadding="4">
  <tr>
    <td>{DISPLAY_NAME_LABEL}</td>
    <td>{DISPLAY_NAME}</td>
  </tr>
  <!-- BEGIN display-error -->
  <tr><td colspan="2"><span class="error-message">{DISPLAY_ERROR}</span></td></tr>
  <!-- END display-error -->
  <!-- BEGIN password-change -->
  <tr>
    <td>{PASSWORD1_LABEL}</td><td>{PASSWORD1}&nbsp;{PASSWORD2}</td>
  </tr>
  <!-- BEGIN password-error -->
  <tr><td colspan="2"><span class="error-message">{PASSWORD_ERROR}</span></td></tr>
  <!-- END password-error -->
  <!-- END password-change -->
  <tr>
    <td>{EMAIL_LABEL}</td>
    <td>{EMAIL}</td>
  </tr>
  <!-- BEGIN email-error -->
  <tr><td colspan="2"><span class="error-message">{EMAIL_ERROR}</span></td></tr>
  <!-- END email-error -->
</table></fieldset>

<fieldset style="margin-bottom : 2em"><legend>{PREF}</legend>
<table width="99%" cellpadding="4">
  <tr>
    <td>{EDITOR_LABEL}</td>
    <td>{EDITOR}</td>
  </tr>
  <tr>
    <td>{CP_LABEL}</td>
    <td>{CP}</td>
  </tr>
</table>
</fieldset>

<fieldset><legend>{LOCAL_INFO}</legend>
  <table width="99%" cellpadding="4">
  <tr>
    <td>{LANGUAGE_LABEL}</td>
    <td>{LANGUAGE}</td>
  </tr>
  <tr>
    <td>{TIMEZONE_LABEL}</td>
    <td>{TIMEZONE}</td>
  </tr>
  <tr>
    <td>{DST_LABEL}</td><td>{DST}</td>
  </tr>
  <tr>
    <td colspan="2">{REMEMBER_ME} {REMEMBER_ME_LABEL}</td>
  </tr>
</table>
</fieldset>
{SUBMIT}
{END_FORM}
