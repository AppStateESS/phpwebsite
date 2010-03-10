{START_FORM}
<p>{ALLOW_DENY_ENABLED} {ALLOW_DENY_ENABLED_LABEL} {GO}</p>
<h2>{ALLOW_TITLE}</h2>
<div class="align-center">{ALLOW_ADDRESS} {ADD_ALLOW_ADDRESS}</div>
<table cellpadding="4" width="99%">
    <tr>
        <th width="2%">&nbsp;</th>
        <th>{IP_ADDRESS_LABEL}</th>
        <th width="10%">{ACTIVE_LABEL}</th>
        <th width="15%">{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN allow-all -->
    <tr>
        <th colspan="4" class="align-center">{ALLOW_ALL_MESSAGE}</th>
    </tr>
    <!-- END allow-all -->
    <!-- BEGIN allow_rows -->
    <tr>
        <td>{ALLOW_CHECK}</td>
        <td>{ALLOW_IP_ADDRESS}</td>
        <td>{ALLOW_ACTIVE}</td>
        <td>{ALLOW_ACTION}</td>
    </tr>
    <!-- END allow_rows -->
</table>
{CHECK_ALL_ALLOW}
<div class="align-center">{ALLOW_ACTION} {ALLOW_ACTION_SUBMIT}</div>
{ALLOW_MESSAGE}
<hr style="margin: 20px 0 20px 0;" />
<h2>{DENY_TITLE}</h2>
<div class="align-center">{DENY_ADDRESS} {ADD_DENY_ADDRESS}</div>
<table cellpadding="4" width="99%">
    <tr>
        <th width="2%">&nbsp;</th>
        <th>{IP_ADDRESS_LABEL}</th>
        <th width="10%">{ACTIVE_LABEL}</th>
        <th width="15%">{ACTION_LABEL}</th>
    </tr>
    <!-- BEGIN deny-all -->
    <tr>
        <th colspan="4" class="align-center">{DENY_ALL_MESSAGE}</th>
    </tr>
    <!-- END deny-all -->
    <!-- BEGIN deny_rows -->
    <tr>
        <td>{DENY_CHECK}</td>
        <td>{DENY_IP_ADDRESS}</td>
        <td>{DENY_ACTIVE}</td>
        <td>{DENY_ACTION}</td>
    </tr>
    <!-- END deny_rows -->
</table>
{CHECK_ALL_DENY}
<div class="align-center">{DENY_ACTION} {DENY_ACTION_SUBMIT}</div>
{DENY_MESSAGE} {END_FORM}
<p class="align-center"><strong>{WARNING}</strong></p>
