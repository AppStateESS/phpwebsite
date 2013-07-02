<form class="{FORM_CLASS}" id="{FORM_ID}" action="{FORM_ACTION}" autocomplete="{FORM_AUTOCOMPLETE}" method="{FORM_METHOD}" {FORM_ENCODE}>
  {HIDDEN_FIELDS}
<fieldset>
  <legend>{VIEW_LABEL}</legend>

  <label class="checkbox">
    {HOME_PAGE_DISPLAY} {HOME_PAGE_DISPLAY_LABEL_TEXT}
  </label>
  
  {BLOG_LIMIT_LABEL} {BLOG_LIMIT}
  {PAST_ENTRIES_LABEL} {PAST_ENTRIES}<br />
  <span class="help-block">{PAST_NOTE}</span>
  
  {SHOW_RECENT_LABEL} {SHOW_RECENT}
  
  <label class="checkbox">
  {LOGGED_USERS_ONLY} {LOGGED_USERS_ONLY_LABEL_TEXT}
  </label>
  
  {VIEW_ONLY_LABEL} {VIEW_ONLY}
</fieldset>

<fieldset>
  <legend>{CATEGORY_LABEL}</legend>
  <label class="checkbox">
    {SHOW_CATEGORY_LINKS} {SHOW_CATEGORY_LINKS_LABEL_TEXT}
  </label>
  <label class="checkbox">
    {SHOW_CATEGORY_ICONS} {SHOW_CATEGORY_ICONS_LABEL_TEXT}
  </label>
  <label class="checkbox" style="margin-left:1.5em;">
    {SINGLE_CAT_ICON} {SINGLE_CAT_ICON_LABEL_TEXT}
  </label>
</fieldset>

<fieldset>
  <legend>{SUBMISSION_LABEL}</legend>
  <label class="checkbox">
    {ALLOW_ANONYMOUS_SUBMITS} {ALLOW_ANONYMOUS_SUBMITS_LABEL_TEXT}
  </label>
  <!-- BEGIN menu-link -->
  <small>{MENU_LINK}</small>
  <!-- END menu-link -->
  <label class="checkbox">
    {CAPTCHA_SUBMISSIONS} {CAPTCHA_SUBMISSIONS_LABEL_TEXT}
  </label>
</fieldset>

<fieldset>
  <legend>{COMMENT_LABEL}</legend>
  <label class="checkbox">
    {ALLOW_COMMENTS} {ALLOW_COMMENTS_LABEL_TEXT}
  </label>
  <label class="checkbox">
    {ANONYMOUS_COMMENTS} {ANONYMOUS_COMMENTS_LABEL_TEXT}
  </label>
</fieldset>

<fieldset>
  <legend>Image Manager</legend>
  <label class="checkbox">
    {SIMPLE_IMAGE} {SIMPLE_IMAGE_LABEL}
  </label>
  <label class="checkbox">
    {MOD_FOLDERS_ONLY} {MOD_FOLDERS_ONLY_LABEL}
  </label>
  {MAX_WIDTH_LABEL} {MAX_WIDTH}
  {MAX_HEIGHT_LABEL} {MAX_HEIGHT}
</fieldset>

<!-- BEGIN purge -->
<fieldset>
  <legend>Purge</legend>
  {PURGE_DATE_LABEL} {PURGE_DATE} <button class="btn" id="{PURGE_CONFIRM_ID}">{PURGE_CONFIRM_VALUE}</button>
</fieldset>
<!-- END purge -->

<hr />

<button class="btn btn-primary" type="submit" id="{SUBMIT_ID}">{SUBMIT_VALUE}</button>


{END_FORM}