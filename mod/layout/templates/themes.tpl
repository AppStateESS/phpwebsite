<form class="form-horizontal {FORM_CLASS}" id="{FORM_ID}" action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}" method="{FORM_METHOD}" {FORM_ENCODE}>
{HIDDEN_FIELDS}
<div class="control-group">
  <label class="control-label" for="{DEFAULT_THEME_ID}">{DEFAULT_THEME_LABEL_TEXT}</label>
  <div class="controls">
    {DEFAULT_THEME}
  </div>
</div>
<div class="control-group">
  <label class="control-label" for="{INCLUDE_CSS_ORDER_ID}">{INCLUDE_CSS_ORDER_LABEL_TEXT}</label>
  <div class="controls">
    {INCLUDE_CSS_ORDER}
  </div>
</div>
<button type="submit" id="{UPDATE_ID}" class="btn btn-primary" name="{UPDATE_NAME}">{UPDATE_VALUE}</button>
</form>