<table cellpadding="6" cellspacing="1" width="100%">
  <tr>
    <th width="40%">{TITLE}&nbsp;{TITLE_SORT}</th>
    <th width="25%">{FILENAME}&nbsp;{FILENAME_SORT}</th>
    <th width="15%">{MODULE}&nbsp;{MODULE_SORT}</th>
    <th width="10%">{SIZE}&nbsp;{SIZE_SORT}</th>
    <th width="10%">{ACTION}</th>
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

<div align="center"><b>{PAGE_LABEL}</b><br />{PAGES}<br />{LIMITS}</div>
