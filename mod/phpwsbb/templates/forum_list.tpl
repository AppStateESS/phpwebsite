<!-- BEGIN ANCHOR -->
{ANCHOR}
<!-- END ANCHOR -->
<table class="box phpwsbb" cellspacing="1" cellpadding="4">
	<tr class="box-title">
	  <th width="70%" scope="col" colspan="2">{FORUM_TITLE}</th>
	  <th width="10%" valign="middle" style="text-align:center" scope="col">{TOPICS_TITLE}</th>
	  <th width="5%" valign="middle" style="text-align:center" scope="col">{POSTS_TITLE}</th>
	  <th width="20%" valign="middle" style="text-align:center;white-space:nowrap" scope="col">{LASTPOST_TITLE}</th>
	</tr>



<!-- BEGIN cat_list --> 
	<!-- BEGIN CAT_ROW -->
	<tr>
		<td colspan="5" class="phpwsbb_forum_category_header">
		<!-- BEGIN CATEGORY_IMAGE -->
			<div style="float:left; padding-right:10px">
				{CATEGORY_ICON}
			</div>
		<!-- END CATEGORY_IMAGE -->
		    <h2>
		    	{SECTION_TITLE} : {CATEGORY_NAME}
		    </h2>
		</td>
	</tr>
	<!-- END CAT_ROW -->
	<!-- BEGIN cat_forum_list --> 
	<tr>
		<td style="vertical-align:middle">{FORUM_HAS_NEW}</td>
		<td style="vertical-align:middle">
			<span style="float: right;">
				{FORUM_LOCKED}
			</span>
			<span style="white-space:nowrap">{FORUM_TITLE_LINK}</span><br />
			<span class="smaller">{FORUM_DESCRIPTION}</span>
		</td>
		<td style="text-align:center;vertical-align:middle">{FORUM_TOPICS}</td>
		<td style="text-align:center;vertical-align:middle">{FORUM_POSTS}</td>
		<td class="smaller" style="text-align:left;vertical-align:middle;white-space:nowrap">
            {FORUM_LASTPOST_POST_LINK} {FORUM_LASTPOST_DATE_SHORT}<br style="clear:left" />
            {IN} {FORUM_LASTPOST_TOPIC_LINK}<br />
            {BY} {FORUM_LASTPOST_AUTHOR}
		</td>
	</tr>
	<!-- END cat_forum_list --> 
<!-- END cat_list --> 



</table>
