<div style="margin-bottom: 1em">{BLOCKED}</div>
<table class="table table-striped">
    <tr style="vertical-align: top">
        <th width="20%">{DATE_SENT_SORT}</th>
        <th>Message</th>
        <th>Reason for report</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr class="report" id="{ID}" style="cursor : pointer">
        <td>{DATE_SENT}</td>
        <td>{MESSAGE}</td>
        <td>{REASON}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
{CSV_REPORT}
<div id="report-view"></div>