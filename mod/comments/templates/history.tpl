<!-- BEGIN message -->
<div>
    {EMPTY_MESSAGE}
</div>
<!-- END message -->

<!-- BEGIN comment-history -->
<div class="comments">
    <table id="cm_{COMMENT_ID}" class="comment-history{TOGGLE}" style="width:100%">
<!-- BEGIN listrows -->
        <tr>
            <td class="comment-body" style="width:70%">
                <h2>{VIEW_LINK}</h2>
                <div class="entry">{ENTRY}</div>
            </td>
            <td class="author-info bgcolor1" valign="top">
                {CREATE_TIME}<br />
                {TOPIC_LBL}: {TOPIC_LINK}<br />
                {REPLY_LBL}: {REPLIES}<br />
            </td>
         </tr>
<!-- END listrows -->
     </table>
</div>
<!-- END comment-history -->


<!-- BEGIN navigate -->
<hr />
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}<br /><br />
</div>
<!-- END navigate -->
