<table cellpadding="4" cellspacing="1" width="98%">
    <tr style="vertical-align: top">
        <th width="25%">{TITLE_SORT}</th>
        <th width="30%">{SUMMARY}</th>
        <th width="25%">{CREATE_DATE_SORT}<br />
        {PUBLISH_DATE_SORT}<br />
        {EXPIRE_DATE_SORT}</th>
        <th width="20%">{ACTION}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{TITLE}</td>
        <td>{SUMMARY}</td>
        <td><span class="smaller">{CREATE_DATE}<br />
        {PUBLISH_DATE}<br />
        {EXPIRE_DATE}</span></td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
{CSV_REPORT}
