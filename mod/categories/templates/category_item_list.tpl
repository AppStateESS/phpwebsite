<table cellpadding="2" width="99%">
    <tr>
        <th>{TITLE_LABEL}&#160;{TITLE_SORT}</th>
        <th>{CREATE_DATE_LABEL}&#160;{CREATE_DATE_SORT}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{URL} <br>
        {SUMMARY}</td>
        <td>{CREATE_DATE}</td>
    </tr>
    <!-- END listrows -->
</table>
<br />
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}{PAGE_DROP}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
