<div style="margin-bottom : 10px">{NEW}</div>{TOTAL_ROWS}
<table cellpadding="5" width="95%">
    <tr>
        <th width="5%">{ID_SORT} {ID_LABEL}</th>
        <th>{TITLE_SORT}</th>
        <th width="25%">{CREATE_DATE_SORT}<br />
        {LAST_UPDATED_SORT}</th>
        <th width="15%">{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr>
        <td>{ID}</td>
        <td>{TITLE} <!-- BEGIN subpages -->
        <div class="subpage">{SUBPAGES}</div>
        <!-- END subpages --></td>
        <td>{CREATE_DATE}<br />
        {LAST_UPDATED}</td>
        <td class="smaller">{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div align="center"><b>{PAGE_LABEL}</b><br />
{PAGES}<br />
{LIMITS}</div>
{SEARCH}
