<table cellpadding="4" width="100%">
<tr>
<th>Visitor</th>
<th>Date</th>
<th>Waited time</th>
<th>Meeting time</th>
</tr>
<!-- BEGIN rows -->
<tr>
<td>{VISIT}</td>
<td>{DATE}</td>
<td>{WAITED}</td>
<td>{MEETING}</td>
</tr>
<!-- END rows -->
<tr>
<td colspan="2">Total waiting : {TOTAL_WAIT}</td>
<td colspan="2">Total meeting : {TOTAL_MEETING}</td>
</tr>
<tr>
<td colspan="2">Average wait : {AVG_WAIT}</td>
<td colspan="2">Average meeting : {AVG_MEETING}</td>
</tr>
</table>
Incomplete meetings: {INCOMPLETE_MEETINGS}