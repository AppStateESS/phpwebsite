{BACK}

<h1>{IMAGE_UPLOAD_LABEL}</h1>

<!-- BEGIN MESSAGE -->
<h4>{MESSAGE}</h4>
<!-- END MESSAGE -->

{START_FORM}
<b>{FILENAME_LABEL}:</b><br />
{FILENAME}<br /><br />

<b>{SUMMARY_LABEL}:</b><br />
{SUMMARY}<br /><br />

{SAVE}
{END_FORM}

<hr />

<h1>{IMAGE_LIST_LABEL}</h1>

<p>{USAGE}<br /><br /></p>

<table cellpadding="4" cellspacing="1" width="100%">
  <tr>
    <th>{LIST_FILENAME} {FILENAME_SORT}</th>
    <th>{LIST_SIZE} {SIZE_SORT}</th>
    <th>{LIST_TYPE} {TYPE_SORT}</th>
    <th>{LIST_OWNER} {OWNER_ID_SORT}</th>
    <th>{LIST_CREATED} {CREATED_SORT}</th>
    <th>{LIST_ACTIONS}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr{TOGGLE}>
    <td>{FILENAME}</td>
    <td>{SIZE}</td>
    <td>{TYPE}</td>
    <td>{OWNER}</td>
    <td>{CREATED}</td>
    <td>{ACTIONS}</td>
  </tr>
  <tr{TOGGLE}>
    <td colspan="6">{SUMMARY}</td>
  </tr>
<!-- END listrows -->
<!-- BEGIN empty_message -->
  <tr{TOGGLE}>
    <td colspan="6">{EMPTY_MESSAGE}</td>
  </tr>
<!-- END empty_message -->
</table>

<!-- BEGIN navigation -->
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}
</div>
<!-- END navigation -->
<!-- BEGIN search -->
<div class="align-right">
{SEARCH}
</div>
<!-- END search -->