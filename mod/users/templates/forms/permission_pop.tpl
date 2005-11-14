<div class="box">
  <div class="box-title"><h1>{TITLE}<h1></div>
  <!-- BEGIN message --><div class="padded"><h3>{MESSAGE}</h3></div><!-- END message -->
  <div class="box-content">
  {START_FORM}
  <h3>{VIEW_SELECT_LABEL}</h3>
  <table cellpadding="4" width="100%">
    <tr>
      <td>
        <ul class="no-bullet smaller">
          <li>{VIEW_PERMISSION_1} {VIEW_PERMISSION_1_LABEL}</li>
          <li>{VIEW_PERMISSION_2} {VIEW_PERMISSION_2_LABEL}</li>
          <li>{VIEW_PERMISSION_3} {VIEW_PERMISSION_3_LABEL}</li>
        </ul>
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
