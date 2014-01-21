<script type="text/javascript">
$(document).ready(function() {
    init();
});

function init()
{
    $('#photo-form').submit(function() {
        if ($('#photo-form_title').val() == '') {
            alert('Photo title may not be blank.');
            return false;
        } else {
            return true;
        }
    });


    $('.delete-photo').click(function() {
        $.get('index.php?module=properties&{CMD}=delete_photo&id=' + this.id + '&authkey={AUTH}', function(data) {
            $('#thumbnails').html(data);
            init();
        });
        photo_id = '#p' + this.id;
        $(photo_id).remove();
    });

    $('.photo').click(function() {
        $.get('index.php?module=properties&{CMD}=make_main&id=' + this.id.replace(/p/, '') + '&authkey={AUTH}', function(data) {
            //alert(data);
        });
        $('#default').remove();
        $('.delete-photo',  this).after('<div id="default">Default Image</div>');
    });
}
</script>
<style type="text/css">
div.photo {
    background-repeat: no-repeat;
    background-position: center;
    background-color: #e3e3e3;
    position: relative;
    width : {WIDTH}px;
    height : {HEIGHT}px;
    float: left;
    margin: 10px 0px 0px 10px;
    border: 1px solid #e3e3e3;
}

#default {
    opacity: 0.6;
    filter: alpha(opacity = 60);
    width: 95%;
    background-color: white;
    text-align: center;
    font-weight: bold;
    font-size: 14px;
    margin: 50px auto;
    color: black;
    padding : 0px 10px;
}

.not-default {
    display : none;
}

div#thumbnails {
    overflow: auto;
}

a.delete-photo {
    position: absolute;
    cursor: pointer;
    left: 120px;
    top: 0px;
}
</style>

<p>All photos are resized to under 640 by 480 pixels.</p>
{START_FORM}
<p>{TITLE_LABEL}<br />
{TITLE} <span style="color : red">* required</span></p>
<p>{PHOTO}</p>
{SUBMIT} {END_FORM}
<hr />
<h2>Current photos</h2>
<div id="thumbnails">{THUMBNAILS}</div>