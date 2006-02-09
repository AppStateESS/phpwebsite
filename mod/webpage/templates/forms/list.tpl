{TOTAL_ROWS}
{START_FORM}
<table width="100%">
  <tr>
    <th width="1%">&nbsp;</th>
    <th>{TITLE_SORT} {TITLE_LABEL}</th>
    <th>{DATE_CREATED_SORT}&nbsp;{DATE_CREATED_LABEL}<br />
        {DATE_UPDATED_SORT}&nbsp;{DATE_UPDATED_LABEL}</th>
    <th>{CREATED_USER_SORT}&nbsp;{CREATED_USER_LABEL}<br />
        {UPDATED_USER_SORT}&nbsp;{UPDATED_USER_LABEL}</th>
    <th>{FRONTPAGE_SORT}&nbsp;{FRONTPAGE_LABEL}</th>
    <th>{ACTION_LABEL}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr <!-- BEGIN tog -->class="bgcolor1"{TOGGLE}<!-- END tog -->>
    <td>{CHECKBOX}</td>
    <td>{TITLE}</td>
    <td>{DATE_CREATED}<br />{DATE_UPDATED}</td>
    <td>{CREATED_USER}<br />{UPDATED_USER}</td>
    <td>{FRONTPAGE}</td>
    <td>{ACTION}</td>
  </tr>
<!-- END listrows -->
</table>
<div class="bgcolor2 padded">{WP_ADMIN} {SUBMIT} {CHECK_ALL}</div>
{END_FORM}
{EMPTY_MESSAGE}
<!-- BEGIN navigate -->
<hr />
<div class="align-center">
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}<br /><br />
{SEARCH}
</div>
<!-- END navigate -->
