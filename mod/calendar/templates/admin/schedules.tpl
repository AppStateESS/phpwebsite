<div style="margin-bottom:1em">{ADD_CALENDAR}</div>
<table class="table table-striped table-hover">
    <tr>
        <th>{ADMIN_LABEL}</th>
        <th>{TITLE_SORT}</th>
        <th>{PUBLIC_SORT}</th>
    </tr>
    <!-- BEGIN listrows -->
    <tr{TOGGLE}>
        <td class="admin-icons">{ADMIN}</td>
        <td>{TITLE}</td>
        <td>{AVAILABILITY}</td>
    </tr>
    <!-- END listrows -->
</table>
{EMPTY_MESSAGE}
<div class="align-center">{TOTAL_ROWS}<br />
{PAGE_LABEL} {PAGES}<br />
{LIMIT_LABEL} {LIMITS}</div>

<div class="modal fade" id="schedule-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">Create Schedule</h4>
            </div>
            <div class="modal-body">{SCHEDULE_FORM}</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>