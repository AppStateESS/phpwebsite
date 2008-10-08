{HOME_LINK} :: 
<!-- BEGIN CATEGORY -->
{CATEGORY_LINKS} :: 
<!-- END CATEGORY -->
{FORUM_TITLE_LINK}
<br />

<!-- BEGIN DESCRIPTION -->
<p class="smaller">{FORUM_DESCRIPTION}</p>
<!-- END DESCRIPTION -->

<!-- BEGIN CAT -->
<p>{CATEGORY_TEXT}: {CATEGORY_LINKS}</p>
<!-- END CAT -->
<!-- BEGIN MODRTRS -->
<p>{FORUM_MODERATORS_LBL}: {FORUM_MODERATORS}</p>
<!-- END MODRTRS -->

{FORUM_ADD_TOPIC_BTN}
<br />
<br />
<br />

<table class="box phpwsbb" cellspacing="1" cellpadding="6">
<tr class="box-title">
    <th scope="col" colspan="2">
        {PHPWS_KEY_TITLE_SORT}
        <br />
        {PHPWS_KEY_CREATOR_SORT}
    </th>
    <th valign="middle" style="text-align:center" scope="col">{TOTAL_POSTS_SORT}</th>
<!-- BEGIN VIEWS_LABEL -->
    <th valign="middle" style="text-align:center" scope="col">{PHPWS_KEY_TIMES_VIEWED_SORT}</th>
<!-- END VIEWS_LABEL -->
    <th valign="middle" style="text-align:center;white-space:nowrap" scope="col">{LASTPOST_DATE_SORT}</th>
</tr>



<!-- BEGIN message -->
<tr>
    <td>
        {EMPTY_MESSAGE}
    </td>
</tr>
<!-- END message -->



<!-- BEGIN listrows -->
<tr{TOGGLE}>
    <td style="vertical-align:middle">{TOPIC_HAS_NEW}</td>
    <td width="60%">
        {THREAD_TITLE_LINK} 
        
        <br />
        <span style="float: right;">
            {TOPIC_STICKY} {TOPIC_LOCKED} {TOPIC_IS_HOT}
                        <!-- BEGIN PGLNKS --><span class="phpwsbb_pagelist smaller">({THREAD_PAGES})</span><!-- END PGLNKS -->
        </span>
        <span class="smaller">{THREAD_AUTHOR}</span>
    </td>
    <td style="text-align:center">{THREAD_REPLIES}</td>
<!-- BEGIN VIEWS -->
    <td style="text-align:center">{THREAD_VIEWS}</td>
<!-- END VIEWS -->
    <td class="smaller" style="text-align:left; margin-right:2em; width:25%; white-space:nowrap">{THREAD_LASTPOST_INFO}</td>
</tr>
<!-- END listrows -->


</table>


<!-- BEGIN navigate -->
<div>
    <div class="align-center smaller" style="float: right;">
        {TOTAL_ROWS}<br />
        {PAGE_LABEL} {PAGES}<br />
        {LIMIT_LABEL} {LIMITS}<br />
    </div>
    <br />
    {FORUM_ADD_TOPIC_BTN}
</div>
<!-- END navigate -->

<br />
<br />
<br />
<br />

<!-- BEGIN statuslist -->
    {FORUM_LABEL} :: {HOME_LINK} :: {FORUM_TITLE_LINK}<br />
    {FORUM_FLAGS}<br />
<!-- END statuslist -->
    {STATUS_FLAGS}
