<p style="font-size : 110%; font-weight: bold">{LOGIN}</p>
<div id="roommates"><a id="top"></a>
<div id="prop-paging"><span id="page-listing">{PAGE_LABEL} {PAGES}</span>
<div>Roommates shown: {TOTAL_ROWS}</div>
</div>
<div>{LIMIT_LABEL} {LIMITS} {SEARCH}</div>
<table class="table table-striped">
    <tr>
        <th>{NAME_SORT}</th>
        <th>{MONTHLY_RENT_SORT}</th>
        <th>Share {SHARE_BEDROOM_SORT} /
        {SHARE_BATHROOM_SORT}</th>
        <th>{CAMPUS_DISTANCE_SORT}</th>
        <th>{MOVE_IN_DATE_SORT}</th>
        <!-- BEGIN listrows -->
    <tr>
        <td>{NAME}</td>
        <td>${MONTHLY_RENT}</td>
        <td>{SHARE_BEDROOM} / {SHARE_BATHROOM}</td>
        <td>{CAMPUS_DISTANCE}</td>
        <td>{MOVE_IN_DATE}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}</div>
<a href="#top">Back to top</a>
