{START_FORM}

<fieldset>
	<legend>{PERSONAL_HEADER}:</legend>
	<h2>{LOCATION_LABEL}</h2>
	{LOCATION}
	<br />
	<br />
	<h2>{WEBSITE_LABEL}</h2>
	{WEBSITE}
<!-- BEGIN SIG -->
	<br />
	<br />
	<h2>{SIGNATURE_LABEL}</h2>
	{SIGNATURE_HELP}
	<br />
	{SIGNATURE}
	<br />
	<br />
	{ORDER_PREF_LABEL}
	<br />
	{ORDER_PREF}
<!-- END SIG -->
</fieldset>

<!-- BEGIN PERS_IMG -->
<fieldset>
	<legend>{AVATAR_LABEL}:</legend>
<!-- BEGIN CURRAVATAR -->
    {CURRENT_AVATAR_LABEL}
    <br />
    {CURRENT_AVATAR_IMG}
    <br />
 <!-- END CURRAVATAR -->
    <br />
    {GALLERY_AVATAR_LABEL}
    <div style="margin-left:5em">
        {GALLERY_AVATAR}
    </div>
<!-- BEGIN AVATAR1 -->
	{LOCAL_AVATAR_LABEL}
    <div style="margin-left:5em">
    	{LOCAL_AVATAR}
    </div>
<!-- END AVATAR1 -->
<!-- BEGIN AVATAR2 -->
	<br />
	{REMOTE_AVATAR_LABEL}
    <div style="margin-left:5em">
    	{REMOTE_AVATAR}
    </div>
<!-- END AVATAR2 -->
	{AVATAR_NOTE}
</fieldset>
<!-- END PERS_IMG -->

<fieldset>
	<legend>{MONITOR_HEADER}:</legend>
	{MONITORDEFAULT_HELP}
	<br />
	<br />
	{MONITORDEFAULT}{MONITORDEFAULT_LABEL}
	<br />
	{SUSPENDMONITORS} {SUSPENDMONITORS_LABEL}
	<br />
	<br />
	<b>{REMOVE_ALL_MONITORS_HELP}</b>
	<br />
	{REMOVE_ALL_MONITORS} {REMOVE_ALL_MONITORS_LABEL}
</fieldset>

 {SUBMIT}

{END_FORM}
