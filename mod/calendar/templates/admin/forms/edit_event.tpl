
<!-- BEGIN error --><h2 class="error">{ERROR}</h2><!-- END error -->

{START_FORM}
<table class="form-table">
  <tr>
    <td>{TITLE_LABEL}</td>
    <td>{TITLE}</td>
  </tr>
  <tr>
    <td>{SUMMARY_LABEL}</td>
    <td>{SUMMARY}</td>
  </tr>
  <tr>
    <td>{EVENT_TYPE_LABEL}</td>
    <td>
      {EVENT_TYPE_1} {EVENT_TYPE_1_LABEL}<br />
      {EVENT_TYPE_2} {EVENT_TYPE_2_LABEL}<br />
      {EVENT_TYPE_3} {EVENT_TYPE_3_LABEL}<br />
      {EVENT_TYPE_4} {EVENT_TYPE_4_LABEL}
    </td>
  </tr>
  <tr>
    <td>{START_DATE_LABEL}<br /><span style="font-weight:normal" class="smaller">YYYY/MM/DD</span></td>
    <td>{START_DATE} {START_CAL} <span id="start-time">{START_TIME_HOUR}:{START_TIME_MINUTE}</span></td>
  </tr>
  <tr>
    <td>{END_DATE_LABEL}<br /><span style="font-weight:normal" class="smaller">YYYY/MM/DD</span></td>
    <td>{END_DATE} {END_CAL} <span id="end-time">{END_TIME_HOUR}:{END_TIME_MINUTE}</span></td>
  </tr>
</table>
{SUBMIT}
<div class="align-right">{CLOSE}</div>
{END_FORM}
