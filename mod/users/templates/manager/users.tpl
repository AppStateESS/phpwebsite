<div class="pull-right">{SEARCH}</div>
<div style="margin-bottom: 10px">
  <a href="{NEW_USER_URI}" class="btn btn-success"><i class="fa fa-user"></i> Add user</a>
</div>
<table class="table table-striped table-hover sans" id="user-manager">
  <tr>
    <th>{ACTIONS_LABEL}</th>
    <th>{DISPLAY_NAME_SORT}</th>
    <th>{USERNAME_SORT}</th>
    <th>{EMAIL_SORT}</th>
    <th>{LAST_LOGGED_SORT}</th>
    <th>{ACTIVE_SORT}</th>
  </tr>
  <!-- BEGIN listrows -->
  <tr>
    <td class="admin-icons">{ACTIONS}</td>
    <td>{DISPLAY_NAME}</td>
    <td>{USERNAME}</td>
    <td><small>{EMAIL}</small></td>
    <td style="font-size: .8em;">{LAST_LOGGED}</td>
    <td align="left">{ACTIVE}</td>
  </tr>
  <!-- END listrows -->
</table>

<!-- BEGIN empty_message -->
<div>{EMPTY_MESSAGE}</div>
<!-- END empty_message -->

<div style="text-align: center;">
  {PAGE_LABEL} {PAGES}<br /> {LIMIT_LABEL} {LIMITS}
</div>

{START_FORM}

<div class="row">
  <div class="col-lg-3">
    <div class="panel panel-default">
      <div class="panel-heading">Filter users by group membership</div>
      <div class="panel-body">
        <div class="row">
          <div class="col-lg-12">
            <div class="form-group">
              <label class="radio-inline"> {QGROUP_2} {QGROUP_2_LABEL_TEXT} </label>
              <label class="radio-inline"> {QGROUP_1} {QGROUP_1_LABEL_TEXT} </label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="form-group">
              <label for="group-search_search_group" style="display:none;">Group</label>{SEARCH_GROUP}
            </div>
            <div class="form-group">
              <button type="submit" class="btn btn-default">{GROUP_SUB_VALUE}</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{END_FORM}
