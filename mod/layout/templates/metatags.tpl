<form class="form-horizontal {FORM_CLASS}" id="{FORM_ID}" action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}" method="{FORM_METHOD}" {FORM_ENCODE}>
{HIDDEN_FIELDS}

<div class="control-group">
  <label class="control-label" for="{PAGE_TITLE_LABEL_ID}">{PAGE_TITLE_LABEL_TEXT}</label>
  <div class="controls">
    {PAGE_TITLE}
  </div>
</div>

<div class="control-group">
  <label class="control-label" for="{META_KEYWORDS_ID}">{META_KEYWORDS_LABEL_TEXT}</label>
  <div class="controls">
    {META_KEYWORDS}
  </div>
</div>

<div class="control-group">
  <label class="control-label" for="{META_DESCRIPTION_ID}">{META_DESCRIPTION_LABEL_TEXT}</label>
  <div class="controls">
    {META_DESCRIPTION}
  </div>
</div>

<div class="control-group">
  <div class="controls">
    <label class="checkbox">
    {USE_KEY_SUMMARIES} {USE_KEY_SUMMARIES_LABEL_TEXT}
    </label>
  </div>
</div>


<label>{ROBOT_LABEL}</label>

<div class="control-group">
  <div class="controls">
    <label class="checkbox">
    {INDEX} {INDEX_LABEL_TEXT}
    </label>
  </div>
  <div class="controls">
    <label class="checkbox">
    {FOLLOW} {FOLLOW_LABEL_TEXT}
    </label>
  </div>
</div>

<div class="control-group">
  <div class="controls">
    <button type="submit" id="{SUBMIT_ID}" class="btn btn-primary" name="{SUBMIT_NAME}">{SUBMIT_VALUE}</button>
  </div>
</div>

{END_FORM}