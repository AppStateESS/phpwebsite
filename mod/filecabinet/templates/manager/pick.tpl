<style>

div.image-info {
    float : left;
    width : 80px;
    background-color : white;
    display : inline;
    margin : 10px 10px 0px 0px;
}

#buttons {
    position : absolute;
    bottom : 4px;
    z-index : 2;
    padding : 2px;
}

div.tn-image-block {
     background-repeat : no-repeat;
}

</style>

<div class="bgcolor2 padded"><h1>{TITLE}</h1></div>
<!-- BEGIN thumbnail-list -->

<div class="image-info" style="border : 2px solid transparent" onclick="highlight('{TN_ID}','{ID}')">
   <img src="{THUMBNAIL}" id="image-{TN_ID}"  />
   <a class="smaller" href="javascript:show_image('{ID}', '{TN_ID}', {POP_WIDTH}, {POP_HEIGHT});">{VIEW}</a>
   <span class="smaller"> {WIDTH} x {HEIGHT} </span>
</div>

<!-- END thumbnail-list -->
{MESSAGE}
<div style="clear : both"> </div>
<div id="buttons" class="bgcolor2">
<input type="button" name="upload" value="{UPLOAD}" onclick="upload_new('{UPLOAD_LINK}')" />
<input type="button" name="delete" value="{DELETE}" onclick="delete_pick()" />
<input type="button" name="ok" value="{OK}" onclick="post_pick('{MOD_TITLE}', '{ITEMNAME}')" />
<input type="button" name="cancel" value="{CANCEL}" onclick="cancel()" />
</div>
