<h1>WhoDis?</h1>
<p>{ADMIN_LINKS}</p>
{START_FORM}
<div>{PURGE_LABEL} {DAYS_OLD} {VISIT_LIMIT_LABEL} {VISIT_LIMIT}
{SUBMIT}</div>
<table width="99%" cellpadding="4">
    <tr>
        <th>&nbsp;</th>
        <th>{URL_LABEL} {URL_SORT}</th>
        <th>{CREATED_LABEL} {CREATED_SORT}</th>
        <th>{UPDATED_LABEL} {UPDATED_SORT}</th>
        <th>{VISITS_LABEL} {VISITS_SORT}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr>
        <td>{CHECKBOX}</td>
        <td class="align-left">{URL}</td>
        <td>{CREATED}</td>
        <td>{UPDATED}</td>
        <td>{VISITS}</td>
    </tr>
    <!-- END listrows -->
</table>
{CHECK_ALL} {DELETE_CHECKED} {END_FORM} {EMPTY_MESSAGE}
<div align="center"><b>{PAGE_LABEL}</b><br />
{PAGES}<br />
{LIMITS}</div>
{SEARCH}
