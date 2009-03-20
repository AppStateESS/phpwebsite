<script type="text/javascript">
var FCK = window.opener.FCKeditorAPI.GetInstance('{instance}');

function insertHTML(data) {
    FCK.InsertHtml(data);
    window.close();
}

$(document).ready(function() {
    initFolders();
});

function initFolders()
{
    $('#image-nav').click(function() {
        getFolders();
    });
}

function getFolders()
{
    $.get('index.php?module=filecabinet&aop=fck_img_folders', function(data) {
        $('#image-folders').html(data);
    });
}

function initFiles()
{
    $('.show-thumb').hover(
    function() {
        $(this).siblings('.thumbnail').show();
    },
    function() {
        $(this).siblings('.thumbnail').hide();
    });

}

function pull_folder(id) {
    getFolders();
    $.get('index.php?module=filecabinet&aop=fck_images&fid=' + id, function(data) {
        $('#folder-' + id).html(data);
        initFiles();
    });
}

function show_thumb(id) {
    $('.thumbnail').toggle();
}

</script>
