<div class="pull-right">{LINKS}</div>
{START_FORM} {CHECK_ALL} {UPDATE}
<!-- BEGIN module -->
<div class="permission">
    <h3>{MODULE_NAME}</h3>
    <table class="table table-striped">
        <tr>
            <td style="width: 250px">{PERMISSION_0}<br />
                <!-- BEGIN partial --> {PERMISSION_1}<br />
                <!-- END partial --> {PERMISSION_2}</td>
            <!-- BEGIN subpermissions -->
            <td>{SUBPERMISSIONS}</td>
            <!-- END subpermissions -->
        </tr>
    </table>
</div>
<!-- END module -->
{UPDATE} {END_FORM}
