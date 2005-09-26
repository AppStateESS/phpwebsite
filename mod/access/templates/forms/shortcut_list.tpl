{SHORTCUT_LINK}
{START_FORM}
<table width="100%" cellpadding="3">
  <tr>
    <th width="3%">&nbsp;</th>
    <th width="35%">{KEYWORD_LABEL} {KEYWORD_SORT}</th>
    <th width="45%">{URL_LABEL} {URL_SORT}</th>
    <th width="17%">{ACTION_LABEL}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{CHECKBOX}</td>
    <td>{KEYWORD}</td>
    <td>{URL}</td>
    <td>{ACTION}</td>
  </tr>
<!-- END listrows -->
</table>
<div align="right">{LIST_ACTION} {SUBMIT}</div>
{END_FORM}
{EMPTY_MESSAGE}
