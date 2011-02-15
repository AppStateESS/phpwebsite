{TITLE}
<table cellpadding="2">
  <tr>
    <td>{LOCAL_VERSION_LABEL} =</td><td>{LOCAL_VERSION}</td>
  </tr>
  <tr>
    <td>{STABLE_VERSION_LABEL} =</td><td>{STABLE_VERSION}</td>
  </tr>
</table>
{NO_UPDATE}
<!-- BEGIN update -->
<h2><b>{UPDATE_AVAILABLE}</b></h2>
<p style="line-height:1em;" class="b">
{PU_LINK_LABEL}:
{PU_LINK}&nbsp;
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
<b>{CHANGES_LABEL}</b>
<pre style="height: 80px; width:99%; overflow:auto;">{CHANGES}</pre>
<hr />
<b>{MD5_LABEL}</b>
<div style="line-height:1em;">{MD5}</div>
<!-- END update -->
