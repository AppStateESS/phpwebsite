<table cellpadding="4" cellspacing="1" width="99%">
    <tr>
        <th>{TITLE_LABEL}&#160;{TITLE_SORT}</th>
        <th>{PARENT_LABEL}&#160;{PARENT_SORT}</th>
        <th>{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{TITLE}</td>
        <td>{PARENT}</td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<br />
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}{PAGE_DROP}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
