{TITLE}
<div style="margin : 10px 0px">{NEW_GROUP}</div>
{NAV_INFO}
<table class="table table-striped">
    <tr>
        <th>{NAME_SORT}</th>
        <th>{MEMBERS_LABEL}</th>
        <th>{ACTIONS_LABEL}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{NAME}</td>
        <td>{MEMBERS}</td>
        <td>{ACTIONS}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE} {DEFAULT_SUBMIT}
<br />
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
