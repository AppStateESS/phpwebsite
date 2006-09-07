
<!-- BEGIN error --><h2 class="error">{ERROR}</h2><!-- END error -->

{START_FORM}
<table class="form-table">
  <tr>
    <td>{SUMMARY_LABEL}</td>
    <td>{SUMMARY}</td>
  </tr>
  <tr>
    <td>{LOCATION_LABEL}</td>
    <td>{LOCATION}</td>
  </tr>
  <tr>
    <td>{LOC_LINK_LABEL}</td>
    <td>{LOC_LINK}</td>
  </tr>
  <tr>
    <td>{DESCRIPTION_LABEL}</td>
    <td>{DESCRIPTION}</td>
  </tr>
  <tr>
    <td>
        {START_DATE_LABEL}<br />
        <span style="font-weight:normal" class="smaller">YYYY/MM/DD</span>
    </td>
    <td>
        {START_DATE} {START_CAL}
        <span id="start-time" style="display : none">{START_TIME_HOUR}:{START_TIME_MINUTE}</span>
        {ALL_DAY} {ALL_DAY_LABEL}
    </td>
  </tr>
  <tr>
    <td>{END_DATE_LABEL}<br /><span style="font-weight:normal" class="smaller">YYYY/MM/DD</span></td>
    <td>
        {END_DATE} {END_CAL}
        <span id="end-time" style="display : none">{END_TIME_HOUR}:{END_TIME_MINUTE}</span>
        {SHOW_BUSY} {SHOW_BUSY_LABEL}
    </td>
  </tr>
</table>
{SUBMIT}
<div class="align-right">{CLOSE}</div>
{END_FORM}
