<p>
  <a href="{ADD_URI}" title="{ADD_TEXT}" style="margin-bottom: 10px;" class="btn btn-success"><i class="fa fa-file-text"></i> {ADD_TEXT}</a>
</p>
<table class="table table-striped table-hover sans" style="width:98%;">
    <tr style="vertical-align: top">
        <th style="width:10%;">{ACTION}</th>
        <th style="width:35%;">{TITLE_SORT}</th>
        <th style="width:30%;">{SUMMARY}</th>
        <th style="width:25%;">{CREATE_DATE_SORT}<br />
           {PUBLISH_DATE_SORT}<br />
           {EXPIRE_DATE_SORT}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr>
        <td class="admin-icons">{ACTION}</td>
        <td>{TITLE}</td>
        <td>{SUMMARY}</td>
        <td><small>{CREATE_DATE}<br />
          {PUBLISH_DATE}<br />
          {EXPIRE_DATE}</small></td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div style="text-align:center;margin:auto;">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<a href="{EXPORT_URI}" class="btn btn-default pull-right"><i class="fa fa-download-alt"></i> Export to Spreadsheet</a>
<div class="align-right">{SEARCH}</div>