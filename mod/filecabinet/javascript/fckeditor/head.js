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
        $('#document-folders').html('');
        getImgFolders();
    });

    $('#doc-nav').click(function() {
        $('#image-folders').html('');
        getDocFolders();
    });
}

function getImgFolders()
{
    $.get('index.php?module=filecabinet&aop=fck_img_folders', function(data) {
        $('#image-folders').html(data);
    });
}


function getDocFolders()
{
    $.get('index.php?module=filecabinet&aop=fck_doc_folders', function(data) {
        $('#document-folders').html(data);
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

function pull_folder(id, ftype) {

    if (ftype == 1) {
        getImgFolders();
        aop = 'fck_images';
    } else if (ftype == 2) {
        getDocFolders();
        aop = 'fck_documents';
    }

    $.get('index.php?module=filecabinet&aop=' + aop + '&fid=' + id, function(data) {
        $('#folder-' + id).html(data);
        initFiles();
    });
}

function show_thumb(id) {
    $('.thumbnail').toggle();
}

</script>
