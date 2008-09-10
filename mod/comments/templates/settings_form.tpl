{START_FORM}
<div class="box-title"><h1>{TITLE}</h1></div>
<br />
{SUBMIT}
<table class="form-table">
  <tr>
    <td style="width:30%">{ORDER_LABEL}</td>
    <td style="width:70%">{ORDER}</td>
  </tr>
  <tr>
    <td>{CAPTCHA_LABEL}</td>
    <td>{CAPTCHA}</td>
  </tr>
  <tr>
    <td>{ALLOW_SIGNATURES_LABEL}</td>
    <td>{ALLOW_SIGNATURES}</td>
  </tr>
  <tr>
    <td>{ALLOW_IMAGE_SIGNATURES_LABEL}</td>
    <td>{ALLOW_IMAGE_SIGNATURES}</td>
  </tr>
  <tr>
    <td>{ALLOW_AVATARS_LABEL}</td>
    <td>{ALLOW_AVATARS}</td>
  </tr>
  <tr>
    <td>{LOCAL_AVATARS_LABEL}</td>
    <td>{LOCAL_AVATARS}</td>
  </tr>
  <tr>
    <td>{ANONYMOUS_NAMING_LABEL}</td>
    <td>{ANONYMOUS_NAMING}</td>
  </tr>
  <tr>
    <td>{RECENT_COMMENTS_LABEL}</td>
    <td>{RECENT_COMMENTS}</td>
  </tr>
  <tr>
    <td>{DEFAULT_APPROVAL_LABEL}</td>
    <td>{DEFAULT_APPROVAL}</td>
  </tr>
  <tr>
    <td>{MONITOR_POSTS_LABEL}</td>
    <td>{MONITOR_POSTS}</td>
  </tr>
  <tr>
    <td>{ALLOW_USER_MONITORS_LABEL}</td>
    <td>{ALLOW_USER_MONITORS}</td>
  </tr>
  <tr>
    <td>{EMAIL_SUBJECT_LABEL}</td>
    <td>{EMAIL_SUBJECT}</td>
  </tr>
  <tr>
    <td>{EMAIL_TEXT_LABEL}</td>
    <td>{EMAIL_TEXT}</td>
  </tr>
</table>

<br />
{SUBMIT}
<br />
<br />

<div class="box-title"><h1>{RANK_TABLE_TEXT}</h1></div>
{RANK_TABLE_HELP}<br />

<!-- BEGIN rank_usergroups -->
<br />
<h2>{USERGROUP_NAME}</h2>
{ALLOW_LOCAL_CUSTOM_AVATARS} {ALLOW_LOCAL_CUSTOM_AVATARS_LABEL} {MINIMUM_LOCAL_CUSTOM_POSTS} {MINIMUM_LOCAL_CUSTOM_POSTS_LABEL}<br />
{ALLOW_REMOTE_CUSTOM_AVATARS} {ALLOW_REMOTE_CUSTOM_AVATARS_LABEL} {MINIMUM_REMOTE_CUSTOM_POSTS} {MINIMUM_REMOTE_CUSTOM_POSTS_LABEL}<br />

<!-- BEGIN rank_rows -->
	<a href="javascript: expandCollapse('editrank_{RANK_ID}');" title="{EDIT_HELP}">{EDIT_ICON}</a>
	&nbsp;&nbsp;&nbsp;&nbsp;
	{RANK_MIN_TXT_LABEL}: {RANK_MIN_TXT}
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	{RANK_TITLE_LABEL}: {RANK_TITLE_TXT}
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	{RANK_IMAGE_PIC}

	<table id="editrank_{RANK_ID}" style="margin-left:5%; border: 1px solid rgb(144, 238, 144); padding: 5px; text-align: left; width: 95%; display: none" cellpadding="2" cellspacing="2">
	  <tbody>
	    <tr>
	      <td>{RANK_TITLE_LABEL} </td>
	      <td>{RANK_TITLE}</td>
	    </tr>
	    <tr>
	      <td>{RANK_IMAGE_LABEL} </td>
	      <td>
	      	<span class="smaller">{RANK_IMAGE_HELP}<br /></span>
	      	{RANK_IMAGE}
	      </td>
	    </tr>
        <tr>
          <td>{RANK_REPEAT_IMAGE_LABEL}</td>
          <td>{RANK_REPEAT_IMAGE} {RANK_REPEAT_TIMES}</td>
        </tr>
	    <tr>
	      <td>{RANK_MIN_LABEL} </td>
	      <td>{RANK_MIN}</td>
	    </tr>
	    <tr>
	      <td>{RANK_USERGROUP_LABEL} </td>
	      <td>{RANK_USERGROUP}</td>
	    </tr>
	    <tr>
	      <td>{RANK_STACK_LABEL}&nbsp;&nbsp;</td>
	      <td>{RANK_STACK_1_LABEL}{RANK_STACK_1} {RANK_STACK_2_LABEL}{RANK_STACK_2}</td>
	    </tr>
	  </tbody>
	</table>
<br />
<!-- END rank_rows -->
<!-- END rank_usergroups -->

<br />
<br />

<h2>{RANK_NEW_TITLE}</h2>

<!-- BEGIN add_new_rank -->
	<span style="display: none">
		{RANK_MIN_TXT_LABEL}{RANK_MIN_TXT}{RANK_TITLE_LABEL}{RANK_TITLE_TXT}{RANK_IMAGE_PIC}
	</span>
	<table id="editrank_{RANK_ID}" style="margin-left:5%; border: 1px solid rgb(144, 238, 144); padding: 5px; text-align: left; width: 95%" cellpadding="2" cellspacing="2">
	  <tbody>
	    <tr>
	      <td>{RANK_TITLE_LABEL} </td>
	      <td>{RANK_TITLE}</td>
	    </tr>
	    <tr>
	      <td>{RANK_IMAGE_LABEL} </td>
	      <td>
	      	<span class="smaller">{RANK_IMAGE_HELP}<br /></span>
	      	{RANK_IMAGE}
	      </td>
	    </tr>
        <tr>
          <td>{RANK_REPEAT_IMAGE_LABEL}</td>
          <td>{RANK_REPEAT_IMAGE} {RANK_REPEAT_TIMES}</td>
        </tr>
	    <tr>
	      <td>{RANK_MIN_LABEL} </td>
	      <td>{RANK_MIN}</td>
	    </tr>
	    <tr>
	      <td>{RANK_USERGROUP_LABEL} </td>
	      <td>{RANK_USERGROUP}</td>
	    </tr>
	    <tr>
	      <td>{RANK_STACK_LABEL}</td>
	      <td>{RANK_STACK_1_LABEL}{RANK_STACK_1} {RANK_STACK_2_LABEL}{RANK_STACK_2}</td>
	    </tr>
	  </tbody>
	</table>
<!-- END add_new_rank -->

<br />
<br />
{SUBMIT}
{END_FORM}
