<div class="box mini-calendar">
    <div class="box-title">
        <h2 class="align-center">{FULL_MONTH_NAME}, {PARTIAL_YEAR}</h2>
    </div>
    <div class="box-content">
        <div class="month-view-mini">
            <table>
                <tr>
                    <!-- BEGIN calendar-weekdays -->
                    <th>{LETTER_WEEKDAY}</th>
                    <!-- END calendar-weekdays -->
                </tr>
                <!-- BEGIN calendar-row -->
                <tr>
                    {CAL_ROW}
                    <!-- BEGIN calendar-col -->
                    <td class="{CLASS}">{DAY}</td>
                    <!-- END calendar-col -->
                </tr>
                <!-- END calendar-row -->
            </table>
        </div>
    </div>
</div>
