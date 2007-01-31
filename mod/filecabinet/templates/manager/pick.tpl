<table id="directory" cellpadding="5" width="100%">
  <tr>
    <td width="10%"><strong>{DIRECTORY_LABEL}</strong></td>
    <td>{DIRECTORY}</td>
  </tr>
</table>

<div id="image-manager-list">
<!-- BEGIN thumbnail-list -->

<div class="image-info" style="border : 2px solid transparent" onclick="highlight('{TN_ID}','{ID}')">
   <div class="img-holder">
   <img src="{THUMBNAIL}" id="image-{TN_ID}"  />
   </div>
   <div class="dimensions"><a class="smaller"
   href="javascript:show_image('{ID}', '{TN_ID}', {POP_WIDTH},
   {POP_HEIGHT});">{VIEW}</a>
   {WIDTH} x {HEIGHT}</div>
</div>

<!-- END thumbnail-list -->

{MESSAGE}
<div id="clear-both"> </div>
<div id="buttons">
<input type="button" name="upload" value="{UPLOAD}" onclick="upload_new('{UPLOAD_LINK}')" />
<input type="button" name="delete" value="{DELETE}" onclick="delete_pick()" />
<input type="button" name="ok" value="{OK}" onclick="post_pick('{MOD_TITLE}', '{ITEMNAME}')" />
<input type="button" name="cancel" value="{CANCEL}" onclick="cancel()" />
</div>
