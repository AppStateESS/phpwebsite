<!-- BEGIN hr -->
<!-- BEGIN event-tab -->
<div class="event-tabs">
<ul class="cal-tab">
    <li id="event-pick" class="active"><a
        href="javascript:changeTab(0)"
    >{EVENT_TAB}</a></li>
    <li id="repeat-pick" class="inactive"><a
        href="javascript:changeTab(1)"
    >{REPEAT_TAB}</a></li>
</ul>
<!-- BEGIN repeat-warning -->
<div style="margin: 10px">{REPEAT_WARNING}</div>
<!-- END repeat-warning --></div>
<!-- END event-tab -->
<!-- BEGIN error -->
<h2 class="error">{ERROR}</h2>
<!-- END error -->
<hr />
<!-- END hr -->
{START_FORM}
<div id="event-tab" class="col-sm-12">
   <div class="form-group">
    <div class="col-xs-2">{SUMMARY_LABEL}</div>
    <div class="col-xs-10">{SUMMARY}</div>
   </div>
     <div class="form-group">
      <div class="col-xs-2">{LOCATION_LABEL}</div>
      <div class="col-xs-10">{LOCATION}</div>
    </div>
    <div class="form-group">
        <div class="col-xs-2">{LOC_LINK_LABEL}</div>
        <div class="col-xs-10">{LOC_LINK}</div>
    </div>
     <div class="form-group">
        <div class="col-xs-2">{DESCRIPTION_LABEL}</div>
        <div class="col-xs-10">{DESCRIPTION}</div>
    </div>
  
    
<table class="table">
    <tr>
        <td>{START_DATE_LABEL}<br><small style="display:inline; font-size: .7em">(YYYY/MM/DD)</small></td>
        <td>{START_DATE}</td><td>{START_CAL}</td><td><span id="start-time" style="display: inline">{START_TIME_HOUR} {START_TIME_MINUTE}</span></td>
        <td>{ALL_DAY_LABEL}</td><td> {ALL_DAY}</td>
    </tr> 
    
       
    <tr>
        <td>{END_DATE_LABEL}<br><small style="display:inline; font-size: .7em">(YYYY/MM/DD)</small></td>
        <td>{END_DATE}</td><td>{END_CAL}</td><td><span id="end-time" style="display: inline">{END_TIME_HOUR} {END_TIME_MINUTE}</span></td>
        <td>{SHOW_BUSY_LABEL}</td><td> {SHOW_BUSY}</td>
    </tr> 
    
</table>
</div>
<div id="repeat-tab" class="col-sm-12" style="display: none">{REPEAT_EVENT}
{REPEAT_EVENT_LABEL}
<table class="table">
    <tr>
        <td>{END_REPEAT_DATE_LABEL}</td>
        <td>{END_REPEAT_DATE} {END_REPEAT}</td>
    </tr>
    <tr class="bgcolor2">
        <td>{REPEAT_MODE_1} {REPEAT_MODE_1_LABEL}</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>{REPEAT_MODE_2} {REPEAT_MODE_2_LABEL}</td>
        <td><span>{WEEKDAY_REPEAT_1}
        {WEEKDAY_REPEAT_1_LABEL}</span> <span>{WEEKDAY_REPEAT_2}
        {WEEKDAY_REPEAT_2_LABEL}</span> <span>{WEEKDAY_REPEAT_3}
        {WEEKDAY_REPEAT_3_LABEL}</span> <span>{WEEKDAY_REPEAT_4}
        {WEEKDAY_REPEAT_4_LABEL}</span> <span>{WEEKDAY_REPEAT_5} 
        {WEEKDAY_REPEAT_5_LABEL}</span> <span>{WEEKDAY_REPEAT_6}
        {WEEKDAY_REPEAT_6_LABEL}</span> <span>{WEEKDAY_REPEAT_7}
        {WEEKDAY_REPEAT_7_LABEL}</span></td>
    </tr>
    <tr class="bgcolor2">
        <td>{REPEAT_MODE_3} {REPEAT_MODE_3_LABEL}</td>
        <td>{MONTHLY_REPEAT}</td>
    </tr>
    <tr>
        <td>{REPEAT_MODE_4} {REPEAT_MODE_4_LABEL}</td>
        <td></td>
    </tr>
    <tr class="bgcolor2">
        <td>{REPEAT_MODE_5} {REPEAT_MODE_5_LABEL}</td>
        <td>{EVERY_REPEAT_NUMBER} {EVERY_REPEAT_WEEKDAY}
        {EVERY_REPEAT_FREQUENCY}</td>
    </tr>
</table>
</div>
<div>{SAVE}{SAVE_SOURCE}{SAVE_COPY} {SYNC}</div>
<div class="align-center">{CLOSE}</div>
{END_FORM}
