<form class="form" id="{FORM_ID}" action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}" method="{FORM_METHOD}" {FORM_ENCODE}>
  {HIDDEN_FIELDS}
  <fieldset>
    <legend>{ACCT_INFO}</legend>
    <div class="form-group {DISPLAY_NAME_ERROR_CLASS}">
      <label class="control-label" for="{DISPLAY_NAME_ID}">{DISPLAY_NAME_LABEL_TEXT}</label>
      {DISPLAY_NAME}
      <!-- BEGIN display-name-error -->
      <span class="help-inline">{DISPLAY_NAME_ERROR}</span>
      <!-- END display-name-error -->
    </div>
    <!-- BEGIN password-show -->
    <div class="form-group {PASSWORD_ERROR_CLASS}">
      {SHOW_PW}
      <label>Change password</label>
      <input type="password" name="password1" class="form-control" title="Enter password" placeholder="Enter new password"/>
      <!-- BEGIN password-error -->
      <span class="help-inline">{PASSWORD_ERROR}</span>
      <!-- END password-error -->
      <br />
      <input type="password" name="password2" class="form-control" title="Password confirm" placeholder="Retype above password"/>
    </div>
    <!-- END password-show -->
    <div class="control-group {EMAIL_ERROR_CLASS}">
      <label class="control-label" for="{EMAIL_ID}">{EMAIL_LABEL_TEXT}</label>
      <div class="controls">
        {EMAIL}
        <!-- BEGIN email-error -->
        <span class="help-inline">{EMAIL_ERROR}</span>
        <!-- END email-error -->
      </div>
    </div>
  </fieldset>
  <div style="margin-top: 1em">{SUBMIT}</div>
</form>
