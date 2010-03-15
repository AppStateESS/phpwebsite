{HOME_LINK}
<br />
<table class="box phpwsbb" cellspacing="1" cellpadding="6">
    <tr class="box-title">
        <th scope="col">{PHPWS_KEY_TITLE_SORT} <br />
        {PHPWS_KEY_CREATOR_SORT}</th>
        <th valign="middle" style="text-align: center" scope="col">{TOTAL_POSTS_SORT}</th>
        <!-- BEGIN VIEWS_LABEL -->
        <th valign="middle" style="text-align: center" scope="col">{PHPWS_KEY_TIMES_VIEWED_SORT}</th>
        <!-- END VIEWS_LABEL -->
        <th valign="middle"
            style="text-align: center; white-space: nowrap" scope="col"
        >{LASTPOST_DATE_SORT}</th>
        <th valign="middle" nowrap="nowrap" style="text-align: center"
            scope="col"
        >{PHPWSBB_FORUMS_TITLE_SORT}</th>
    </tr>
    <!-- BEGIN message -->
    <tr>
        <td>{EMPTY_MESSAGE}</td>
    </tr>
    <!-- END message -->
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td style="width: 50%">{THREAD_TITLE_LINK} <br />
        <span class="smaller">{THREAD_AUTHOR}</span></td>
        <td style="text-align: center">{THREAD_REPLIES}</td>
        <!-- BEGIN VIEWS -->
        <td style="text-align: center">{THREAD_VIEWS}</td>
        <!-- END VIEWS -->
        <td class="smaller"
            style="text-align: left; margin-right: 2em; width: 20%; white-space: nowrap"
        >{THREAD_LASTPOST_INFO}</td>
        <td>{FORUM_TITLE_LINK}</td>
    </tr>
    <!-- END listrows -->
</table>
<!-- BEGIN navigate -->
<hr />
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}<br />
<br />
</div>
<!-- END navigate -->
