{UPLOAD}
<table cellpadding="6" cellspacing="1" width="100%">
  <tr>
    <th width="25%">{THUMBNAIL}</th>
    <th width="20%">{TITLE}&nbsp;{TITLE_SORT}</th>
    <th width="20%">{FILE_NAME}&nbsp;{FILE_NAME_SORT}</th>
    <th width="20%">{SIZE}&nbsp;{SIZE_SORT}</th>
    <th width="15%">{ACTION}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{THUMBNAIL}</td>
    <td>{TITLE}</td>
    <td>{FILE_NAME}</td>
    <td>{SIZE}</td>
    <td>{ACTION}</td>   
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
