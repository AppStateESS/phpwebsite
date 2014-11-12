<div style="margin-bottom:1em">{ADD_CALENDAR}</div>
<table class="table table-striped table-hover">
    <tr>
        <th>{TITLE_SORT}</th>
        <th>{PUBLIC_SORT}</th>
        <th>{ADMIN_LABEL}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{TITLE}</td>
        <td>{AVAILABILITY}</td>
        <td class="admin-icons">{ADMIN}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
{SCHEDULE_FORM}
{EVENT_FORM}