<h1>{TITLE}</h1>
<table cellpadding="2">
    <tr>
        <td>{LOCAL_VERSION_LABEL}:</td><td>{LOCAL_VERSION}</td>
    </tr>
    <tr>
        <td>{STABLE_VERSION_LABEL}:</td><td>{STABLE_VERSION}</td>
    </tr>
</table>
<br />
{NO_UPDATE}
<!-- BEGIN update -->

<h2>{UPDATE_AVAILABLE}</h2>
<p class="b">
    {UPDATE_PATH_LABEL}: {UPDATE_PATH}
</p>
<hr />
<!-- BEGIN dependency-listing -->
<h2>{DEPENDENCY_LABEL}</h2>
<table cellpadding="5" width="50%">
    <tr>
        <th>{DEP_TITLE_LABEL}</th>
        <th>{DEP_VERSION_LABEL}</th>
        <th>{DEP_STATUS_LABEL}</th>
        <th>&nbsp;</th>
    </tr>
    <!-- BEGIN dependent-mods -->
    <tr>
        <td>{DEP_TITLE}</td>
        <td>{DEP_VERSION}</td>
        <td style="color : {DEP_STATUS_CLASS}">{DEP_STATUS}</td>
        <td>{DEP_ADDRESS}</td>
    </tr>
    <!-- END dependent-mods -->
</table>
<hr />
<!-- END dependency-listing -->
<h2>{CHANGES_LABEL}</h2>
<p>{CHANGES}</p>
<hr />
<h2>{MD5_LABEL}</h2>
<p>
    {MD5}
</p>
<!-- END update -->
