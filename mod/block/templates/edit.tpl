<form class="{FORM_CLASS}" id="{FORM_ID}" action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}" method="{FORM_METHOD}" {FORM_ENCODE}>
    {HIDDEN_FIELDS}

    <label for="{TITLE_ID">{TITLE_LABEL_TEXT}</label> {TITLE}

<label class="checkbox">
           {HIDE_TITLE} {HIDE_TITLE_LABEL_TEXT}
</label>

<label class="checkbox">
           {HIDE_NARROW} {HIDE_NARROW_LABEL_TEXT}
</label>

<div style="margin-bottom: 1em;">
           {BLOCK_CONTENT}
</div>

<button type="submit" class="btn btn-primary">{SUBMIT_VALUE}</button>
           {CANCEL}
</form>