<script type="text/javascript">
var FCK = window.opener.FCKeditorAPI.GetInstance('{instance}');

function insertHTML(data) {
    FCK.InsertHtml(data);
    window.close();
}

$(document).ready(function() {
    initFolders();
    $('#folders-listing').html('{pick}');
    $('#folders-listing').ajaxSend(function() {
        $(this).html('<div style="margin-top : 150px; text-align : center"><img src="./images/core/ajax-loader-big.gif" /></div>');
    });

});

function initFolders()
{
    $('#image-nav').click(function() {
        $('#folders-listing').html('');
        $('#fck-doc-type').attr('src', './images/mod/filecabinet/file_manager/file_type/document80_bw.png');
        $('#fck-img-type').attr('src', './images/mod/filecabinet/file_manager/file_type/image80.png');
        $('#fck-mm-type').attr('src', './images/mod/filecabinet/file_manager/file_type/media80_bw.png');
        getImgFolders();
    });

    $('#doc-nav').click(function() {
        $('#folders-listing').html('');
        $('#fck-img-type').attr('src', './images/mod/filecabinet/file_manager/file_type/image80_bw.png');
        $('#fck-doc-type').attr('src', './images/mod/filecabinet/file_manager/file_type/document80.png');
        $('#fck-mm-type').attr('src', './images/mod/filecabinet/file_manager/file_type/media80_bw.png');
        getDocFolders();
    });

    $('#media-nav').click(function() {
        $('#folders-listing').html('');
        $('#fck-img-type').attr('src', './images/mod/filecabinet/file_manager/file_type/image80_bw.png');
        $('#fck-doc-type').attr('src', './images/mod/filecabinet/file_manager/file_type/document80_bw.png');
        $('#fck-mm-type').attr('src', './images/mod/filecabinet/file_manager/file_type/media80.png');
        getMediaFolders();
    });

}

function getImgFolders()
{
    $('#folders-listing').load('index.php?module=filecabinet&aop=fck_img_folders');
}


function getDocFolders()
{
    $('#folders-listing').load('index.php?module=filecabinet&aop=fck_doc_folders');
}

function getMediaFolders()
{
    $('#folders-listing').load('index.php?module=filecabinet&aop=fck_mm_folders');
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
    } else if (ftype == 3) {
        getMediaFolders();
        aop = 'fck_multimedia';
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
