<script type="text/javascript">
//<![CDATA[
var view_groups = null;
window.onload = function() {
     view_groups = document.getElementById('view_groups');
     for (i = 0; i < 3; i++) {
         current_radio = document.getElementById('choose_permissions_view_permission_' + i);

         if (current_radio.checked) {
             hideSelect(current_radio.value);
             return;
         }
     }
}

function hideSelect(radio_value)
{

    if (!view_groups) {
        return;
    }
    if (radio_value != '2') {
        view_groups.disabled = 'disabled';
    } else {
        view_groups.disabled = '';
    }

}
//]]>
</script>

<div class="box">
  <div class="box-title"><h1>{TITLE}<h1></div>
  <!-- BEGIN message --><div class="padded"><h3>{MESSAGE}</h3></div><!-- END message -->
  <div class="box-content">
  {START_FORM}
  <h3>{VIEW_SELECT_LABEL}</h3>
  <table cellpadding="4" width="100%">
    <tr>
      <td>
          {VIEW_PERMISSION_1} {VIEW_PERMISSION_1_LABEL}<br />
          {VIEW_PERMISSION_2} {VIEW_PERMISSION_2_LABEL}<br />
          {VIEW_PERMISSION_3} {VIEW_PERMISSION_3_LABEL}
      </td>
      <td>
        <div class="smaller">{VIEW_SELECT}</div>
      </td>
    </tr>
  </table>
  <h3>{EDIT_SELECT_LABEL}</h3>
  <div class="smaller">{EDIT_SELECT}</div>
  <hr />
  {SUBMIT} {CANCEL}
  {END_FORM}
  </div>
</div>
