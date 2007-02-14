{START_FORM}
<div class="top-label">
    <div class="padded">{TITLE_LABEL}<br />{TITLE}</div>
    <div class="padded">{SUMMARY_LABEL}<br />{SUMMARY}</div>
    <div class="padded">{ENTRY_LABEL}<br />{ENTRY}</div>
    <table>
    <tr>
<!-- BEGIN image -->
    <td>
        <div class="padded"><label>{IMAGE_LABEL}</label><div>{IMAGE_MANAGER}</div></div>
    </td>
<!-- END image -->
    <td>
        <div class="padded">
            {PUBLISH_DATE_LABEL} <span class="smaller">{EXAMPLE}</span><br />    
            {PUBLISH_DATE}
        </div>
<!-- BEGIN comments -->
        <div class="padded">
            {ALLOW_COMMENTS} {ALLOW_COMMENTS_LABEL}<br />
            {ALLOW_ANON} {ALLOW_ANON_LABEL}
        </div>
<!-- END comments -->
    </td>
    </tr>
    </table>
</div>
{SUBMIT}
{END_FORM}
