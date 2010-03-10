{EMPTY_MESSAGE} {TOTAL_ROWS} {START_FORM}
<table cellpadding="5" width="100%">
    <tr>
        <th width="1%">&nbsp;</th>
        <th>{KEYWORD_LABEL} {KEYWORD_SORT}</th>
        <th>{TOTAL_QUERY_LABEL} {TOTAL_QUERY}</th>
        <th>{LAST_CALL_DATE_LABEL} {LAST_CALLED_SORT}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{CHECKBOX}</td>
        <td>{KEYWORD}</td>
        <td>{TOTAL_QUERY}</td>
        <td>{LAST_CALLED}</td>
    </tr>
    <!-- END listrows -->
</table>
<div class="bgcolor2 padded">{COMMAND} {SUBMIT} {CHECK_ALL}</div>
{END_FORM}
<div class="align-center">
<p>{PAGE_LABEL} {PAGES} {PAGE_DROP}</p>
<p>{LIMIT_LABEL} {LIMITS}</p>
<p>{SEARCH}</p>
</div>
