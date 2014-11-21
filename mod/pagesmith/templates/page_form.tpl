{START_FORM}
<input type="hidden" name="title" id="page-title-hidden" value="{PAGE_TITLE}" />
{PAGE_TEMPLATE}
<hr />
<p style="margin-top : 1em">
<input id="hide-title" type="checkbox" name="hide_title" value="1" {HIDE_CHECK} /> <label for="hide-title">Hide title on display</label>
</p>
<div class="align-center">{SUBMIT} {SAVE_SO_FAR}</div>
<hr />
<p>
<label for="publish-date">{PUBLISH_DATE_LABEL}</label><input type="datetime-local" name="publish_date" value="{PUBLISH_VALUE}" />
</p>
<hr />
{TEMPLATE_LIST} {CHANGE_TPL} {ORPHAN_LINK}
{END_FORM}
<!-- BEGIN orphans -->
{ORPHANS}
<!-- END orphans -->
{TITLE_MODAL}
{CONTENT_MODAL}