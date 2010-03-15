<!-- BEGIN T1 -->
{AVATAR}
<br />
<br />
<!-- END T1 -->
<!-- BEGIN T2 -->
{USER_RANK_LABEL}: {USER_RANK}
<br />
<br />
<!-- END T2 -->
<!-- BEGIN T3 -->
{JOINED_DATE_LABEL}: {JOINED_DATE}
<br />
<br />
<!-- END T3 -->
<!-- BEGIN T4 -->
{LOCATION_LABEL}: {LOCATION}
<br />
<br />
<!-- END T4 -->
<div class="comments"><a name="comments"></a>
<div class="page-select bgcolor1">{START_FORM}<!-- BEGIN page-select --><strong>{PAGE_LABEL}:</strong>
{PAGES}&nbsp;|&nbsp; <!-- END page-select -->{NEW_POST_LINK}
{TIME_PERIOD}{ORDER}{SUBMIT}{END_FORM}</div>
<div class="padded">{EMPTY_MESSAGE}</div>
<!-- BEGIN listrows --> {ANCHOR}
<table class="comment-table">
    <tr>
        <td colspan="2" class="bgcolor3 smaller padded">
        {RELATIVE_CREATE} {THREAD_TITLE_LABEL}: {THREAD_TITLE_LINK} <!-- BEGIN response -->
        - {RESPONSE_LABEL} {RESPONSE_NAME}<!-- END response --></td>
        <tr>
            <td class="comment-body">
            <h2>{VIEW_LINK}</h2>
            <div class="entry" style="border-top: 1px gray dotted">{ENTRY}</div>
            </td>
        </tr>
</table>
<!-- END listrows --></div>
<!-- BEGIN page-select2 -->
<div class="align-center"><strong>{PAGE_LABEL}:</strong> {PAGES}<br />
{LIMITS}</div>
<!-- END page-select2 -->
