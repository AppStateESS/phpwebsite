{START_FORM}
<fieldset><legend>{PERSONAL_HEADER}:</legend>
<p><strong>{LOCATION_LABEL}</strong><br />
{LOCATION}</p>
<p><strong>{WEBSITE_LABEL}</strong><br />
{WEBSITE}</p>
<!-- BEGIN SIG -->
<p><strong>{SIGNATURE_LABEL}</strong><br />
{SIGNATURE_HELP}<br />
{SIGNATURE}</p>
<!-- END SIG -->
<p><strong>{ORDER_PREF_LABEL}</strong><br />
{ORDER_PREF}</p>
</fieldset>
<!-- BEGIN PERS_IMG -->
<fieldset><legend>{AVATAR_LABEL}:</legend>
<p>{AVATAR_MESSAGE}</p>
<!-- BEGIN CURRAVATAR --> {CURRENT_AVATAR_LABEL} <br />
{CURRENT_AVATAR_IMG} <br />
<!-- END CURRAVATAR --> <br />
{GALLERY_AVATAR_LABEL}
<div style="margin-left: 5em">{GALLERY_AVATAR}</div>
<!-- BEGIN AVATAR1 --> {LOCAL_AVATAR_LABEL}
<div style="margin-left: 5em">{LOCAL_AVATAR}</div>
<!-- END AVATAR1 --> <!-- BEGIN AVATAR2 --> <br />
{REMOTE_AVATAR_LABEL}
<div style="margin-left: 5em">{REMOTE_AVATAR}</div>
<!-- END AVATAR2 --> <span class="smaller">{AVATAR_NOTE}</span></fieldset>
<!-- END PERS_IMG -->
<fieldset><legend>{MONITOR_HEADER}:</legend>
{MONITORDEFAULT_HELP} <br />
<br />
{MONITORDEFAULT}{MONITORDEFAULT_LABEL} <br />
{SUSPENDMONITORS} {SUSPENDMONITORS_LABEL} <br />
<br />
<b>{REMOVE_ALL_MONITORS_HELP}</b> <br />
{REMOVE_ALL_MONITORS} {REMOVE_ALL_MONITORS_LABEL}</fieldset>
{SUBMIT} {END_FORM}
