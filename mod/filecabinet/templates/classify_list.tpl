{START_FORM}
<table cellpadding="4" width="100%">
   <tr>
      <th width="1%">&nbsp;</th>
      <th>{FILENAME_LABEL}</th>
      <th>{FILETYPE_LABEL}</th>
      <th>{ACTION_LABEL}</th>
   </tr>
<!-- BEGIN file-list -->
   <tr{ERROR}>
      <td>{CHECK}</td>
      <td>{FILE_NAME}</td>
      <td>{FILE_TYPE}<!-- BEGIN message --><br /><div class="smaller">({MESSAGE})</div><!-- END message --></td>
      <td>{ACTION}</td>
   </tr>
<!-- END file-list -->
</table>
<hr />
{CHECK_ALL}&nbsp;&nbsp;{AOP} {SUBMIT}
{END_FORM}
