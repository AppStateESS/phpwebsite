<form class="form-horizontal {FORM_CLASS}" id="{FORM_ID}" action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}" method="{FORM_METHOD}" {FORM_ENCODE}> 
{HIDDEN_FIELDS}


<div class="control-group">
  <label class="control-label" for="{TITLE_ID}">{TITLE_LABEL_TEXT}</label>
  <div class="controls">
    {TITLE}
  </div>
</div>

<div class="control-group">
  <label class="control-label" for="{TEMPLATE_ID}">{TEMPLATE_LABEL_TEXT}</label>
  <div class="controls">
    {TEMPLATE}
  </div>
</div>

<div class="control-group">
  <div class="controls">
    <label class="checkbox">
      {PIN_ALL} {PIN_ALL_LABEL_TEXT}
    </label>
  </div>
</div>

<div class="control-group">
  <div class="controls">
    <button type="submit" id="{SUBMIT_ID}" class="btn btn-primary" name="{SUBMIT_NAME}">{SUBMIT_VALUE}</button>
  </div>
</div>

{END_FORM}