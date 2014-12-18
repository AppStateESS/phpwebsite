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
    <div class="form-group {PASSWORD_ERROR_CLASS}">
      <label class="control-label" for="{PASSWORD1_ID}">{PASSWORD1_LABEL_TEXT}</label>
        {PASSWORD1}
        <!-- BEGIN password-error -->
        <span class="help-inline">{PASSWORD_ERROR}</span>
        <!-- END password-error -->
        <br />
        {PASSWORD2}
    </div>
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
  <fieldset style="margin-top:1em">
    <legend>{LOCAL_INFO}</legend>
    <div class="control-group {LANGUAGE_ERROR_CLASS}">
      <label class="control-label" for="{LANGUAGE_ID}">{LANGUAGE_LABEL_TEXT}</label>
      <div class="controls">
        {LANGUAGE}
        <!-- BEGIN LANGUAGE-error -->
        <span class="help-inline">{LANGUAGE_ERROR}</span>
        <!-- END LANGUAGE-error -->
      </div>
    </div>
    <div class="control-group {TIMEZONE_ERROR_CLASS}">
      <label class="control-label" for="{TIMEZONE_ID}">{TIMEZONE_LABEL_TEXT}</label>
      <div class="controls">
        {TIMEZONE}
        <!-- BEGIN TIMEZONE-error -->
        <span class="help-inline">{TIMEZONE_ERROR}</span>
        <!-- END TIMEZONE-error -->
      </div>
    </div>
    <div class="control-group {DST_ERROR_CLASS}">
      <div class="controls">
        <label class="checkbox">
          {DST} {DST_LABEL_TEXT}
        </label>
        <!-- BEGIN DST-error -->
        <span class="help-inline">{DST_ERROR}</span>
        <!-- END DST-error -->
      </div>
    </div>
    <div class="control-group">
      <div class="controls">
        {SUBMIT}
      </div>
    </div>
  </fieldset>
</form>
