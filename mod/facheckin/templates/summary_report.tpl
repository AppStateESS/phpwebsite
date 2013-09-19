{START_FORM}
{START_DATE} to {END_DATE} {ASSIGNED}<br />
{VISITOR_NAME_LABEL} {VISITOR_NAME}
{SUBMIT}
{END_FORM}
{EMPTY}
<!-- BEGIN results -->
<table cellpadding="4" width="500px" border="1">
<tr>
<th style="width : 5%">No.</th>
<th style="width : 30%">Visitor</th>
<th style="width : 25%">Date</th>
<th style="width : 20%">Waited time</th>
<th style="width : 20%">Meeting time</th>
</tr>
</table>
<div style="overflow : auto; max-height : 400px">
<table cellpadding="4" width="500px" border="1">
<!-- BEGIN rows -->
<tr>
<td style="width : 5%;">{VISIT}</td>
<td style="width : 30%;">{VISITOR}</td>
<td style="width : 25%;">{DATE}</td>
<td style="width : 20%;">{WAITED}</td>
<td style="width : 20%">{MEETING}</td>
</tr>
<!-- END rows -->
</table>
</div>
<hr />
<p><strong>Total days</strong> : {TOTAL_DAYS}<br />
<strong>Total visits</strong> : {TOTAL_VISITS}<br />
<strong>Average visits per day</strong> : {AVG_VISITS}<br />
<strong>Total waiting</strong> : {TOTAL_WAIT}<br />
<strong>Total meeting</strong> : {TOTAL_MEETING}<br />
<strong>Average wait</strong> : {AVG_WAIT}<br />
<strong>Average meeting</strong> : {AVG_MEETING}<br />
<strong>Incomplete meetings</strong> : {INCOMPLETE_MEETINGS}
</p>
<!-- END results -->