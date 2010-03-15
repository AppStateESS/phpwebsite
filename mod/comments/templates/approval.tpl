{START_FORM}
<table width="100%" cellpadding="5">
    <tr>
        <th width="1%">{CHECK_ALL}</th>
        <th>{SUBJECT_SORT}</th>
        <th>{AUTHOR_SORT} / {CREATE_TIME_SORT}</th>
        <th><!-- action --></th>
    </tr>
    <!-- BEGIN listrows -->
    <tr class="highlight">
        <td>{CHECKBOX}</td>
        <td><b>{SUBJECT}</b><br />
        {ENTRY}{FULL}</td>
        <td>{AUTHOR}<br />
        <span class="smaller">{CREATE_TIME}</span></td>
        <td>{ACTION}</td>
    </tr>
    <!-- END listrows -->
</table>
{AOP}{SUBMIT} {END_FORM} {EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>
