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

<h2><b>{UPDATE_AVAILABLE}</b></h2><br />
<p class="b">
{PU_LINK_LABEL}: {PU_LINK}&nbsp;
{DL_PATH_LABEL}:<br /> {DL_PATH}
</p>
<hr />
<!-- BEGIN dependency-listing -->
<h2>{DEPENDENCY_LABEL}</h2>
<table cellpadding="5" width="99%">
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
<pre>{CHANGES}</pre>
<hr />
<h2>{MD5_LABEL}</h2>
<p>
{MD5}
</p>
<!-- END update -->
