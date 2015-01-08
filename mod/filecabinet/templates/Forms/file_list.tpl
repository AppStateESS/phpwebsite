<div id="dialog"></div>
<div id="fc-file-list">
<p><!-- BEGIN admin-links -->{ADMIN_LINKS}<br />
<!-- END admin-links --></p>
<hr />
<table class="table table-striped table-hover">
    <tr>
        <th style="width : 50%">{TITLE_SORT} - {FILE_NAME_SORT}</th>
        <th>{FILE_TYPE_SORT}</th>
        <th>{SIZE_SORT} {SIZE_LABEL}</th>
        <!-- BEGIN download -->
        <th>{DOWNLOADED_SORT}</th>
        <!-- END download -->
        <th style="width : 15%">{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td class="fc-title">{TITLE} - <i>{FILE_NAME}</i></td>
        <td class="fc-file-type">{FILE_TYPE}</td>
        <td class="fc-size">{SIZE}</td>
        <!-- BEGIN download-col -->
        <td>{DOWNLOADED}</td>
        <!-- END download-col -->
        <td class="admin-icons">{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
</div>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
