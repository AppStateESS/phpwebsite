<table width="99%" cellpadding="4">
    <tr>
        <th>{TITLE_HEADER} {TITLE_SORT}</th>
        <th>{OPENS_HEADER} {OPENING_SORT}</th>
        <th>{CLOSES_HEADER} {CLOSING_SORT}</th>
        <th>{CANDIDATES_HEADER}</th>
        <!-- BEGIN votes_header --><th>{VOTES_HEADER}</th><!-- END votes_header -->
        <th>&nbsp;</th>
    </tr>
<!-- BEGIN listrows -->
    <tr {TOGGLE}>
        <td>{TITLE}</td>
        <td>{OPENS}</td>
        <td>{CLOSES}</td>
        <td>{CANDIDATES}</td>
        <!-- BEGIN votes --><td>{VOTES}</td><!-- END votes -->
        <td>{ACTION}</td>
    </tr>
    <tr {TOGGLE}>
        <td colspan="6" class="smaller"><!-- BEGIN thumbnail --><div style="float: left; margin: 0 .5em .2em 0;">{THUMBNAIL}</div><!-- END thumbnail -->{DESCRIPTION}</td>
    </tr>
<!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">
    {TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}
</div>
<div class="align-right">
    {SEARCH}
</div>
