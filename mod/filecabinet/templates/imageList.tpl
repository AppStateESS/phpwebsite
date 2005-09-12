{START_FORM}
{MOD_TITLE}{SUBMIT}
{END_FORM}
<table cellpadding="6" cellspacing="1" width="100%">
  <tr>
    <th width="25%">{TITLE}&nbsp;{TITLE_SORT}</th>
    <th width="25%">{FILENAME}&nbsp;{FILENAME_SORT}</th>
    <th width="15%">{MODULE}&nbsp;{MODULE_SORT}</th>
    <th width="10%">{SIZE}&nbsp;{SIZE_SORT}</th>
    <th width="20%">{ACTION}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{TITLE}</td>
    <td>{FILENAME}</td>
    <td>{MODULE}</td>
    <td>{SIZE}</td>
    <td>{ACTION}</td>   
  </tr>
<!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div style="text-align : center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<div style="text-align : right">
{SEARCH}
</div>
