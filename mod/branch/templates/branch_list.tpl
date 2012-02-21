{TOTAL_ROWS}
<div style="text-align: center"><b>{PAGE_LABEL}</b><br />
{PAGES}<br />
{LIMITS}<br />
{SEARCH}</div>
<table width="98%" cellpadding="4" cellspacing="1">
    <tr>
        <th>{BRANCH_NAME_LABEL} {BRANCH_NAME_SORT}</th>
        <th>{DIRECTORY_LABEL}</th>
        <th>{URL_LABEL}</th>
        <th>{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{BRANCH_NAME}</td>
        <td>{DIRECTORY}</td>
        <td>{URL}</td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div style="text-align: center"><b>{PAGE_LABEL}</b><br />
{PAGES}<br />
{LIMITS}</div>
