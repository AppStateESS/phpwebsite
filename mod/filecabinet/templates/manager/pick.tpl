<style>
div.thumbnail {
float: left;
margin : 10px;
height : 102px;
width  : 80px;
position : relative;
border : 1px gray solid;
}

div.image-info {
width : 80px;
position : absolute;
bottom : 0px;
background-color : white;
}

div.image-info img {
margin-left : 2px;
margin-top  : 2px;
}

#buttons {
position : absolute;
bottom : 4px;
width : 100%;
background-color : white;
}


</style>
<div class="bgcolor2 padded"><h1>{TITLE}</h1></div>
<!-- BEGIN thumbnail-list -->
<div class='thumbnail'>
  <span  id="image-{TN_ID}" onclick="highlight('{TN_ID}', '{ID}');">{THUMBNAIL}</span>
  <div class="image-info">
    <a class="smaller" href="javascript:show_image('{ID}', '{TN_ID}', {WIDTH}, {HEIGHT});">{VIEW}</a>
    <span class="smaller"> {WIDTH} x {HEIGHT} </span>
  </div>
</div>
<!-- END thumbnail-list -->
{MESSAGE}
<div style="clear : both"> </div>
<div id="buttons" class="bgcolor2">
<input type="button" name="upload" value="{UPLOAD}" onclick="upload_new('{UPLOAD_LINK}')" />
<input type="button" name="delete" value="{DELETE}" onclick="delete_pick()" />
<input type="button" name="ok" value="{OK}" onclick="post_pick('{MOD_TITLE}', '{ITEMNAME}')" />
<input type="button" name="cancel" value="{CANCEL}" onclick="cancel()"
/>

</div>
