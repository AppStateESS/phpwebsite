<div id="fc-file-list">
<p>
<!-- BEGIN admin-links -->{ADMIN_LINKS}<br /><!-- END admin-links -->
</p>
<hr />
<table width="100%" cellpadding="2">
  <tr>
    <th>{TITLE_SORT} {TITLE_LABEL}</th>
    <th>{FILE_NAME_SORT} {FILE_NAME_LABEL}</th>
    <th colspan="2">{FILE_TYPE_SORT} {FILE_TYPE_LABEL}</th>
    <th>{SIZE_SORT} {SIZE_LABEL}</th>
    <th>{ACTION_LABEL}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}
    <td class="fc-title">{TITLE}</td>
    <td class="fc-file-name">{FILE_NAME}</td>
    <td class="fc-icon">{ICON}</td>
    <td class="fc-type">{FILE_TYPE}</td>
    <td class="fc-size">{SIZE}</td>
    <td>{ACTION}</td>
  </tr>
<!-- END listrows -->
</table>
</div>
{EMPTY_MESSAGE}
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<div class="align-right">
{SEARCH}
</div>
