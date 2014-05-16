{START_FORM}
<input type="hidden" name="title" id="page-title-hidden" value="{PAGE_TITLE}" />
{PAGE_TEMPLATE}
<hr />
<div class="align-center">{SUBMIT} {SAVE_SO_FAR}</div>
<hr />
{PUBLISH_DATE_LABEL} {PUBLISH_DATE}
<hr />
{TEMPLATE_LIST} {CHANGE_TPL} {ORPHAN_LINK}
{END_FORM}
<!-- BEGIN orphans -->
{ORPHANS}
<!-- END orphans -->

<div class="modal fade" id="block-edit-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="width:90%;height:50%">
    <div class="modal-content">
      <div class="modal-header">
          <span>Edit content</span>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      </div>
      <div class="modal-body">
        <textarea id="block-edit-textarea"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" id="modal-save" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="title-edit-popup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="width:90%">
    <div class="modal-content">
      <div class="modal-header">
          <span>Edit page title</span>
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
      </div>
      <div class="modal-body">
        <input type="text" id="page-title-input" name="page_title" class="form-control" value="{PAGE_TITLE}" />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" id="modal-save-title" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>