<div class="align-right padded">{ADD_CALENDAR}</div>
<table class="table table-striped table-hover">
    <tr>
        <th width="25%">{ADMIN_LABEL}</th>
        <th>{TITLE_SORT}</th>
        <th>{PUBLIC_SORT}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td class="admin-icons">{ADMIN}</td>
        <td>{TITLE}</td>
        <td>{AVAILABILITY}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>