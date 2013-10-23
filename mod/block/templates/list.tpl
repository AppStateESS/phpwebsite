<p>
    <a href="{NEW_BLOCK_URI}" class="btn btn-success"><i class="fa fa-file-text"></i> Create a New Block</a>
</p>

<table class="table table-striped table-hover" width="100%">
    <tbody>
        <tr>
            <th>{TITLE_SORT}</th>
            <th>{CONTENT}</th>
            <th>{ACTION}</th>
        </tr>
        <!-- BEGIN listrows -->
        <tr>
            <td>{TITLE}</td>
            <td>{CONTENT}</td>
            <td class="admin-icons">{ACTION}</td>
        </tr>
        <!-- END listrows -->
    </tbody>
</table>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
    {PAGE_LABEL} {PAGES}<br />
    {LIMIT_LABEL} {LIMITS}</div>
<div class="align-right">{SEARCH}</div>
