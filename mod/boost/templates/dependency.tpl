<h1>{TITLE}</h1>
<table cellpadding="4" width="99%">
    <tr>
        <th>{MODULE_NAME_LABEL}</th>
        <th>{VERSION_NEEDED_LABEL}</th>
        <th>{CURRENT_VERSION_LABEL}</th>
        <th>{STATUS_LABEL}</th>
        <th>{URL_LABEL}</th>
    </tr>
    <!-- BEGIN module-row -->
    <tr>
        <td>{MODULE_NAME}</td>
        <td>{VERSION_NEEDED}</td>
        <td>{CURRENT_VERSION}</td>
        <td><!-- BEGIN pass --><span
                style="font-weight: bold; color: #10BD0A"
                >{STATUS_GOOD}</span><!-- END pass --> <!-- BEGIN fail --><span
                style="font-weight: bold; color: #F9060A"
                >{STATUS_BAD}</span><!-- END fail --></td>
        <td>{URL}</td>
    </tr>
    <!-- END module-row -->
</table>
