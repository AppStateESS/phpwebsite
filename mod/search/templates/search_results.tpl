<!-- BEGIN search-found -->
<div id="searchresults">
<table width="100%" cellpadding="4">
    <tr>
        <th>{TITLE_LABEL} {TITLE_SORT}</th>
        <th>{MODULE_LABEL} {MODULE_SORT}</th>
        <!-- BEGIN listrows -->
        <tr {TOGGLE}>
            <td width="80%">{URL}<br />
            {SUMMARY}</td>
            <td width="20%">{MODULE}</td>
        </tr>
        <!-- END listrows -->
</table>
</div>
<div id="searchresultspager">
<!-- END search-found -->
{EMPTY_MESSAGE}
<hr />
<!-- BEGIN ignored -->
<div class="smaller"><i>{REMOVED_LABEL}:</i> {IGNORED_WORDS}</div>
<!-- END ignored -->
{TOTAL_ROWS}
<br />
{PAGE_LABEL} {PAGES}
<br />
{LIMIT_LABEL} {LIMITS}
</div>