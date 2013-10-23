<div style="margin-bottom : 10px">
  <a href="{NEW_PAGE_LINK_URI}" class="btn btn-success"><i class="fa fa-file-text"></i> {NEW_PAGE_LINK_TEXT}</a>
</div>
<table class="table table-striped" style="width:100%">
    <tr>
        <th width="8%">{ID_SORT} {ID_LABEL}</th>
        <th>{TITLE_SORT}</th>
        <th width="25%">{LAST_UPDATED_SORT}</th>
        <th width="15%">{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr>
        <td>{ID}</td>
        <td>{TITLE} <!-- BEGIN subpages -->
        <div class="subpage">{SUBPAGES}</div>
        <!-- END subpages --></td>
        <td>{LAST_UPDATED}</td>
        <td class="admin-icons">{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div align="center">
{TOTAL_ROWS}
<b>{PAGE_LABEL}</b><br />
{PAGES}<br />
{LIMITS}</div>
{SEARCH}
