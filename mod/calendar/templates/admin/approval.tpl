<table class="table table-striped table-hover">
    <tr>
        <th>{TITLE_LABEL}</th>
        <th>{LOCATION_LABEL}</th>
        <th>{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td width="60%">
        <h2>{SUMMARY} - {START_TIME} {TO} {END_TIME}</h2>
        <div class="overflow">{DESCRIPTION}</div>
        </td>
        <td width="20%">{LOCATION}</td>
        <td width="20%">{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<br />
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}{PAGE_DROP}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
