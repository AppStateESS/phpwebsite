<div class="pull-right">
{DOWNLOAD}
</div>

<h2 class="text-center"><!-- BEGIN title -->{TITLE} -<!-- END title -->
    {FULL_MONTH_NAME}, {FULL_YEAR}
</h2>

<div class="pull-right">
{LIST} {WEEK} {DAY_LINK}
</div>

<div class="view-links btn-group">
    {LEFT_ARROW}{RIGHT_ARROW}
</div>

{SUGGEST}


<div class="align-center smaller">
    {SCHEDULE_PICK}
</div>

<div class="month-view-full">
<table>
    <tr>
        <!-- BEGIN calendar-weekdays -->
        <th>{FULL_WEEKDAY}</th>
        <!-- END calendar-weekdays -->
    </tr>
    <!-- BEGIN calendar-row -->
    <tr>
        {CAL_ROW}
        <!-- BEGIN calendar-col -->
        <td class="{CLASS}">
        <div class="cal-day">{DAY}</div>
        <!-- BEGIN calendar-events -->
        <div class="cal-events">{COUNT} <!-- BEGIN show-events -->
        <ul class="event-listing">
            <!-- BEGIN event-list -->
            <li><small>{EVENT}</small></li>
            <!-- END event-list -->
        </ul>
        <!-- END show-events --></div>
        <!-- END calendar-events --></td>
        <!-- END calendar-col -->
    </tr>
    <!-- END calendar-row -->
</table>
</div>
