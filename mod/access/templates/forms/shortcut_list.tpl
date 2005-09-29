{SHORTCUT_LINK}
{START_FORM}
<table width="100%" cellpadding="3">
  <tr>
    <th width="3%">&nbsp;</th>
    <th width="30%">{KEYWORD_LABEL} {KEYWORD_SORT}</th>
    <th width="40%">{URL_LABEL} {URL_SORT}</th>
    <th width="10%">{ACCEPTED_LABEL}</th>
    <th width="17%">{ACTION_LABEL}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{CHECKBOX}</td>
    <td>{KEYWORD}</td>
    <td>{URL}</td>
    <td>{ACCEPTED}</td>
    <td>{ACTION}</td>
  </tr>
<!-- END listrows -->
</table>
{CHECK_ALL_SHORTCUTS}
<div align="right">{LIST_ACTION} {SUBMIT}</div>
{END_FORM}
{EMPTY_MESSAGE}
