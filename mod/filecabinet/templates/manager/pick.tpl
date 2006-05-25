<style>

div.image-info {
    float : left;
    width : 85px;
    background-color : white;
    display : inline;
    margin : 10px 10px 0px 0px;
    text-align : center;
}

div.image-info img {
    text-align : center;
}

#buttons {
    border : 1px solid black;
    width : 95%;
    background-color : white;
    position : fixed;
    bottom : 0px;
    z-index : 2;
    padding : 4px;
}

#clear-both {
    clear  : both;
    margin-bottom : 40px;
}

div.tn-image-block {
     background-repeat : no-repeat;
}

div.dimensions {
     font-size : .8em;
     position : relative;
     bottom : 2px;
}

div.img-holder {
     height : 80px;
     margin-bottom : 5px;
}

div.img-holder {
    border : 2px transparent solid;
}

</style>

<div id="image-manager-list">
<!-- BEGIN thumbnail-list -->

<div class="image-info" style="border : 2px solid transparent"
onclick="highlight('{TN_ID}','{ID}')">
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
