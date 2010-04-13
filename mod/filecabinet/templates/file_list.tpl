<div id="fc-file-list">
<p><!-- BEGIN admin-links -->{ADMIN_LINKS}<br />
<!-- END admin-links --></p>
<hr />
<table width="100%" cellpadding="2">
    <tr>
        <th>{TITLE_SORT}<br />
        {FILE_NAME_SORT}</th>
        <th>{FILE_TYPE_SORT}</th>
        <th>{SIZE_SORT} {SIZE_LABEL}</th>
        <!-- BEGIN download -->
        <th>{DOWNLOADED_SORT}</th>
        <!-- END download -->
        <th>{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td class="fc-title">{TITLE}<br />
        <i>{FILE_NAME}</i></td>
        <td class="fc-file-type">
        {FILE_TYPE}</td>
        <td class="fc-size">{SIZE}</td>
        <!-- BEGIN download-col -->
        <td>{DOWNLOADED}</td>
        <!-- END download-col -->
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
</div>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
