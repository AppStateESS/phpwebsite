<style>
div.thumbnail {
float: left;
margin : 10px;
height : 100px;
position : relative;
border : 1px gray solid;
padding : 2px;
}

#buttons {
position : absolute;
bottom : 0px;
width : 100%;
background-color : white;
}


</style>
<div class="bgcolor2 padded"><h1>{TITLE}</h1></div>
<!-- BEGIN thumbnail-list -->

  <div class='thumbnail'>
    <span id="image-{TN_ID}" onclick="highlight('{TN_ID}', '{ID}');">{THUMBNAIL}</span>
  <div style="position : absolute; bottom : 0px;"><a class="smaller" href="javascript:show_image('{ID}', '{TN_ID}', {WIDTH}, {HEIGHT});">{VIEW}</a></div>
  </div>
<!-- END thumbnail-list -->

<div style="clear : both"> </div>
<div id="buttons" class="bgcolor2">
<input type="button" name="upload" value="{UPLOAD}" onclick="upload_new('{UPLOAD_LINK}')" />
<input type="button" name="ok" value="{OK}" onclick="post_pick('{MOD_TITLE}', '{ITEMNAME}')" />
<input type="button" name="cancel" value="{CANCEL}" onclick="cancel()" />
</div>
