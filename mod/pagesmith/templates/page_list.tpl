<div style="float: right">{SEARCH}</div>
<div style="margin-bottom : 10px">
  <a href="{NEW_PAGE_LINK_URI}" class="btn btn-success"><i class="fa fa-file-o"></i> {NEW_PAGE_LINK_TEXT}</a>
</div>
<table class="table table-striped" style="width:100%">
    <tr>
        <th width="15%">{ACTION_LABEL}</th>
        <th width="8%">{ID_SORT} {ID_LABEL}</th>
        <th>{TITLE_SORT}</th>
        <th width="30%">{LAST_UPDATED_SORT}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr>
        <td class="admin-icons">{ACTION}</td>
        <td>{ID}</td>
        <td>{TITLE} <!-- BEGIN subpages -->
        <div class="subpage">{SUBPAGES}</div>
        <!-- END subpages --></td>
        <td>{LAST_UPDATED} by {UPDATER}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div align="center">
{TOTAL_ROWS}
<b>{PAGE_LABEL}</b><br />
{PAGES}<br />
{LIMITS}</div>
