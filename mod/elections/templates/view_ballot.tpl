{START_FORM}
<div align="right">{BALLOT_LINKS}</div>
<strong>{TITLE}</strong>
<!-- BEGIN file -->
<div style="float: right; margin: 0 0 .2em .5em;">{FILE}</div>
<!-- END file -->
<!-- BEGIN description -->
<p>{DESCRIPTION}</p>
<!-- END description -->
<br style="clear: both;" />
<!-- BEGIN msg -->
<p>{MSG}</p>
<!-- END msg -->
<!-- BEGIN candidates -->
<div class="elections-candidate">
<fieldset><legend>{CANDIDATE_TITLE}</legend> <!-- BEGIN candidate_thumbnail -->
<div style="float: right; margin: 0 0 .2em .5em;">{CANDIDATE_THUMBNAIL}</div>
<!-- END candidate_thumbnail -->
<p>{DESCRIPTION}</p>
<!-- BEGIN custom1 -->
<div><strong>{CUSTOM1_LABEL}:</strong> {CUSTOM1}</div>
<!-- END custom1 --> <!-- BEGIN custom2 -->
<div><strong>{CUSTOM2_LABEL}:</strong> {CUSTOM2}</div>
<!-- END custom2 --> <!-- BEGIN custom3 -->
<div><strong>{CUSTOM3_LABEL}:</strong> {CUSTOM3}</div>
<!-- END custom3 --> <!-- BEGIN custom4 -->
<div><strong>{CUSTOM4_LABEL}:</strong> {CUSTOM4}</div>
<!-- END custom4 -->
<p style="clear: both;"><!-- BEGIN vote_box -->
<div style="float: right;">{VOTE_BOX}</div>
<!-- END vote_box --> {LINKS}</p>
</fieldset>
</div>
<!-- END candidates -->
<div align="right"><!-- BEGIN vote_button -->{VOTE_BUTTON}<!-- END vote_button --><!-- BEGIN submit -->{SUBMIT}<!-- END submit --></div>
<!-- BEGIN opening -->
<b>{OPENING_TEXT}:</b>
{OPENING}
<br />
<!-- END opening -->
<!-- BEGIN closing -->
<b>{CLOSING_TEXT}:</b>
{CLOSING}
<br />
<!-- END closing -->
<!-- BEGIN minchoice -->
<b>{MINCHOICE_TEXT}:</b>
{MINCHOICE}
<br />
<!-- END minchoice -->
<!-- BEGIN maxchoice -->
<b>{MAXCHOICE_TEXT}:</b>
{MAXCHOICE}
<br />
<!-- END maxchoice -->
<!-- BEGIN pubview -->
<b>{PUBVIEW_TEXT}:</b>
{PUBVIEW}
<br />
<!-- END pubview -->
<!-- BEGIN pubvote -->
<b>{PUBVOTE_TEXT}:</b>
{PUBVOTE}
<br />
<!-- END pubvote -->
<!-- BEGIN votegroups -->
<b>{VOTEGROUPS_TEXT}:</b>
{VOTEGROUPS}
<br />
<!-- END votegroups -->
<!-- BEGIN showin_block -->
<b>{SHOWIN_BLOCK_TEXT}:</b>
{SHOWIN_BLOCK}
<br />
<!-- END showin_block -->
{END_FORM}
