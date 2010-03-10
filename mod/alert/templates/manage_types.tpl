<p>{ADD_TYPE}</p>
<table cellpadding="4" cellspacing="1" width="98%">
    <tr style="vertical-align: top">
        <th>{TITLE_SORT} {TITLE_LABEL}</th>
        <th width="10%"><abbr title="{EMAIL_LABEL}">{EMAIL_ABBR}</abbr></th>
        <th width="10%"><abbr title="{RSSFEED_LABEL}">{RSSFEED_ABBR}</abbr></th>
        <th width="30%">{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td>{TITLE}</td>
        <td>{EMAIL}</td>
        <td>{RSSFEED}</td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
