<p>{DISTROSERVER}<br />{SELREL}&nbsp;&nbsp;{SETMYREL}</p>
<table id="ngbsttbds" class="ngtable">
  <thead class="ngthead">
    <tr>
        <th>{DISTRO_LABEL}</th>
        <th>{MODULE_LABEL}</th>
        <th>{VERSION_LABEL}</th>
        <th>{LASTNEW_LABEL}</th>
        <th>{ISHERE_LABEL}</th>
        <th>{OP_LABEL}</th>
   </tr>
  </thead>
  <tbody class="ngtbody">
    <!-- BEGIN row -->
    <tr id="ngmltr{MOD}" class="bgcolor{ZEBRA}">
        <td>{DISTRO}</td>
        <td>{MODULE}</td>
        <td id="{VERSION_ID}">{VERSION}</td>
        <td>{LASTNEW}</td>
        <td>{ISHERE}</td>
        <td>{OP}</td>
    </tr>
    <!-- END row -->
	</tbody>
</table>
