<h2>Likebox Settings</h2>

<form class="form-horizontal {FORM_CLASS}" id="{FORM_ID}" action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}" method="{FORM_METHOD}" {FORM_ENCODE}>
{HIDDEN_FIELDS}

  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {ENABLED} {ENABLED_LABEL_TEXT}
      </label>
    </div>
  </div>
  
  <div class="control-group">
    <label class="control-label" for="{DEFAULT_THEME_ID}">{FB_URL_LABEL_TEXT}</label>
    <div class="controls">
      {FB_URL}
    </div>
  </div>
  
  <div class="control-group">
    <label class="control-label" for="{DEFAULT_THEME_ID}">{WIDTH_LABEL_TEXT}</label>
    <div class="controls">
      {WIDTH}
    </div>
  </div>
  
  <div class="control-group">
    <label class="control-label" for="{DEFAULT_THEME_ID}">{HEIGHT_LABEL_TEXT}</label>
    <div class="controls">
      {HEIGHT}
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {SHOW_HEADER} {SHOW_HEADER_LABEL_TEXT}
      </label>
      <span class="help-block">(i.e. "Find us on Facebook" header bar)</span>
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {SHOW_BORDER} {SHOW_BORDER_LABEL_TEXT}
      </label>
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {SHOW_STREAM} {SHOW_STREAM_LABEL_TEXT}
      </label>
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {SHOW_FACES} {SHOW_FACES_LABEL_TEXT}
      </label>
    </div>
  </div>

<div class="control-group">
    <div class="controls">
      <button type="submit" id="{SUBMIT_ID}" class="btn btn-primary" name="{SUBMIT_NAME}">{SUBMIT_VALUE}</button>
    </div>
  </div>

{END_FORM}