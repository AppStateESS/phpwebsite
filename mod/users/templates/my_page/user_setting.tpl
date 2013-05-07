<form class="form-horizontal {FORM_CLASS}" id="{FORM_ID}" action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}" method="{FORM_METHOD}" {FORM_ENCODE}>
  {HIDDEN_FIELDS}
  <fieldset>
    <legend>{ACCT_INFO}</legend>
    <div class="control-group {DISPLAY_NAME_ERROR_CLASS}">
      <label class="control-label" for="{DISPLAY_NAME_ID}">{DISPLAY_NAME_LABEL_TEXT}</label>
      <div class="controls">
        {DISPLAY_NAME}
        <!-- BEGIN display-name-error -->
        <span class="help-inline">{DISPLAY_NAME_ERROR}</span>
        <!-- END display-name-error -->
      </div>
    </div>
    <div class="control-group {PASSWORD_ERROR_CLASS}">
      <label class="control-label" for="{PASSWORD1_ID}">{PASSWORD1_LABEL_TEXT}</label>
      <div class="controls">
        {PASSWORD1}
        <!-- BEGIN password-error -->
        <span class="help-inline">{PASSWORD_ERROR}</span>
        <!-- END password-error -->
        <br />
        {PASSWORD2}
      </div>
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
    <div class="control-group">
      <div class="controls">
        {SUBMIT}
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend>{PREF}</legend>
    <div class="control-group {EDITOR_ERROR_CLASS}">
      <label class="control-label" for="{EDITOR_ID}">{EDITOR_LABEL_TEXT}</label>
      <div class="controls">
        {EDITOR}
        <!-- BEGIN editor-error -->
        <span class="help-inline">{EDITOR_ERROR}</span>
        <!-- END editor-error -->
      </div>
    </div>
    <div class="control-group {CP_ERROR_CLASS}">
      <div class="controls">
        <label class="checkbox">
          {CP} {CP_LABEL_TEXT}
        </label>
        <!-- BEGIN cp-error -->
        <span class="help-inline">{CP_ERROR}</span>
        <!-- END cp-error -->
      </div>
    </div>
    <div class="control-group">
      <div class="controls">
        {SUBMIT}
      </div>
    </div>
  </fieldset>
  <fieldset>
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
