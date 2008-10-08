

<!-- BEGIN message -->
<div>
    {EMPTY_MESSAGE}
</div>
<!-- END message -->



<!-- BEGIN listrows -->
<div class="comments">
    <table id="cm_{COMMENT_ID}" class="comment-table{TOGGLE}" style="width:100%">
        <tr>
            <td colspan="2" class="comment-header bgcolor3" >
                <div style="float: right">
                    {FORUM_LBL}: {FORUM_LINK}
                </div>
                <div style="padding-top: .25em;">
                    {CREATE_TIME}
                </div>
            </td>
        <tr>
            <td class="comment-body" style="width:70%">
                <h2>{VIEW_LINK}</h2>
                <div class="entry">{ENTRY}</div>
            </td>
            <td class="author-info bgcolor1" valign="top">
               <h2>{POSTED_BY} {AUTHOR_NAME}</h2>
                <div class="smaller">
                    {CREATE_TIME}<br />
                    {FORUM_LBL}: {FORUM_LINK}<br />
                    {TOPIC_LBL}: {TOPIC_LINK}<br />  
                    {REPLY_LBL}: {REPLIES}<br />
                    {VIEWS_LBL}: {TOTAL_VIEWS}<br />
                </div>
            </td>
         </tr>
     </table>
 </div>
<!-- END listrows -->




<!-- BEGIN navigate -->
<hr />
<div class="align-center">
{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}<br /><br />
</div>
<!-- END navigate -->
