{BACK}

<h1>{TOP_LABEL}</h1>

<!-- BEGIN MESSAGE -->
<h4>{MESSAGE}</h4>
<!-- END MESSAGE -->

{START_FORM}
<b>{LABEL_LABEL}:</b><br />
{LABEL}<br /><br />

<b>{URL_LABEL}:</b>
<!-- BEGIN URL_NOTE -->
<i>{URL_NOTE}</i>
<!-- END URL_NOTE -->
<br />
{URL}<br /><br />

{SAVE}
{END_FORM}

<!-- BEGIN YES_NO -->
{YES} | {NO}
<!-- END YES_NO -->

<hr />

<h1>{SITE_LIST_LABEL}</h1>

<p>{USAGE}<br /><br /></p>

<table cellpadding="4" cellspacing="1" width="100%">
  <tr>
    <th>{LIST_LABEL} {LABEL_SORT}</th>
    <th>{LIST_URL} {URL_SORT}</th>
    <th>{LIST_UPDATED} {UPDATED_SORT}</th>
    <th>{LIST_ACTIONS}</th>
  </tr>
<!-- BEGIN listrows -->
  <tr{TOGGLE}>
    <td>{LABEL}</td>
    <td>{URL}</td>
    <td>{UPDATED}</td>
    <td>{ACTIONS}</td>
  </tr>
<!-- END listrows -->
<!-- BEGIN empty_message -->
  <tr{TOGGLE}>
    <td colspan="4">{EMPTY_MESSAGE}</td>
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