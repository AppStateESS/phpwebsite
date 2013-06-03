<form class="form-horizontal {FORM_CLASS}" id="{FORM_ID}" action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}" method="{FORM_METHOD}" {FORM_ENCODE}> 
{HIDDEN_FIELDS}

  <div class="control-group">
    <label class="control-label" for="{MAX_LINK_CHARACTERS_ID}">{MAX_LINK_CHARACTERS_LABEL_TEXT}</label>
    <div class="controls">
      {MAX_LINK_CHARACTERS}
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {FLOAT_MODE} {FLOAT_MODE_LABEL_TEXT}
      </label>
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {DRAG_SORT} {DRAG_SORT_LABEL_TEXT}
      </label>
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {MINIADMIN} {MINIADMIN_LABEL_TEXT}
      </label>
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {HOME_LINK} {HOME_LINK_LABEL_TEXT}
      </label>
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {SHOW_ALL_ADMIN} {SHOW_ALL_ADMIN_LABEL_TEXT}
      </label>
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {ALWAYS_ADD} {ALWAYS_ADD_LABEL_TEXT}
      </label>
    </div>
  </div>
  
        
<div class="control-group">
  <div class="controls">
    <button type="submit" id="{SUBMIT_ID}" class="btn btn-primary" name="{SUBMIT_NAME}">{SUBMIT_VALUE}</button>
  </div>
</div>

{END_FORM}
