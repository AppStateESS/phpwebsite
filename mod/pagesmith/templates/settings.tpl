<form class="form-horizontal {FORM_CLASS}" id="{FORM_ID}" action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}" method="{FORM_METHOD}" {FORM_ENCODE}>
  {HIDDEN_FIELDS}
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {AUTO_LINK} {AUTO_LINK_LABEL_TEXT}
      </label>
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
      <label class="checkbox">
        {BACK_TO_TOP} {BACK_TO_TOP_LABEL_TEXT}
      </label>
    </div>
  </div>
  
  <div class="control-group">
    <div class="controls">
        <button type="submit" class="btn btn-primary">{SUBMIT_VALUE}</button>
    </div>
  </div>
</form>

<h3>Actions</h3>

<p>
  <a href="{SHORTEN_MENU_LINKS_URI}" class="btn">Shorten Menu Links</a><br />
  <small class="muted ">Example: index.php?module=pagesmith&amp;uop=view_page&amp;id=2 <strong>to</strong> pagesmith/2</small>
</p>

<p>
  <a href="{LENGTHEN_MENU_LINKS_URI}" class="btn">Lengthen Menu Links</a><br />
  <small class="muted">Example: pagesmith/2 <strong>to</strong> index.php?module=pagesmith&amp;uop=view_page&amp;id=2</small>
</p>