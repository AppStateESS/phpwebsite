{START_FORM}
<table width="100%" cellpadding="3">
  <tr>
    <th width="3%">&nbsp;</th>
    <th width="30%">{KEYWORD_LABEL} {KEYWORD_SORT}</th>
    <th width="40%">{URL_LABEL} {URL_SORT}</th>
    <th width="10%">{ACTIVE_LABEL}</th>
    <th width="17%">{ACTION_LABEL}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr {TOGGLE}>
    <td>{CHECKBOX}</td>
    <td>{KEYWORD}</td>
    <td>{URL}</td>
    <td>{ACTIVE}</td>
    <td>{ACTION}</td>
  </tr>
<!-- END listrows -->
</table>
<!-- BEGIN empty --><div class="align-center">{EMPTY_MESSAGE}</div><!-- END empty -->
{CHECK_ALL_SHORTCUTS}
<div class="align-center">{LIST_ACTION} {SUBMIT}</div>
{END_FORM}
