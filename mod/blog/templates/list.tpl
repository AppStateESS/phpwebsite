<p>
  <a href="{ADD_URI}" title="{ADD_TEXT}" style="margin-bottom: 10px;" class="btn btn-success pull-right">{ADD_TEXT}</a>
</p>
<table style="border-spacing:1px;width:98%;">
    <tr style="vertical-align: top">
        <th style="width:25%;padding:4px">{TITLE_SORT}</th>
        <th style="width:30%;padding:4px">{SUMMARY}</th>
        <th style="width:25%;padding:4px">{CREATE_DATE_SORT}<br />
        {PUBLISH_DATE_SORT}<br />
        {EXPIRE_DATE_SORT}</th>
        <th style="width:20%;padding:4px">{ACTION}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td style="padding:4px">{TITLE}</td>
        <td style="padding:4px">{SUMMARY}</td>
        <td style="padding:4px"><span class="smaller">{CREATE_DATE}<br />
        {PUBLISH_DATE}<br />
        {EXPIRE_DATE}</span></td>
        <td style="padding:4px">{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div style="text-align:center;margin:auto;">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
<a href="{EXPORT_URI}" class="btn pull-right"><i class="icon-download-alt"></i> Export to Spreadsheet</a>
<div class="align-right">{SEARCH}</div>