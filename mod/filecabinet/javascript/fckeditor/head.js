<script type="text/javascript">
var FCK = window.opener.FCKeditorAPI.GetInstance('{instance}');


function insertHTML(type, id) {
    $.get('index.php?module=filecabinet&aop=fck_'+type +'_data&id='+id, function(data) {
            FCK.InsertHtml(data);
            window.close();
        });
}


$(document).ready(function() {
    initFolders();
    $('#folders-listing').html('{pick}');
    $('#folders-listing').ajaxStart(function() {
        $(this).html('<div style="margin-top : 150px; text-align : center"><img src="./images/core/ajax-loader-big.gif" /></div>');
    });
});

function initFolders() {
    $('#image-nav').click(function() {
        $('#folders-listing').html('');
        $('#fck-doc-type').attr('src', './images/mod/filecabinet/file_manager/file_type/document80_bw.png');
        $('#fck-img-type').attr('src', './images/mod/filecabinet/file_manager/file_type/image80.png');
        $('#fck-mm-type').attr('src', './images/mod/filecabinet/file_manager/file_type/media80_bw.png');
        getFolders(1, 0);
    });
    $('#doc-nav').click(function() {
        $('#folders-listing').html('');
        $('#fck-img-type').attr('src', './images/mod/filecabinet/file_manager/file_type/image80_bw.png');
        $('#fck-doc-type').attr('src', './images/mod/filecabinet/file_manager/file_type/document80.png');
        $('#fck-mm-type').attr('src', './images/mod/filecabinet/file_manager/file_type/media80_bw.png');
        getFolders(2, 0);
    });
    $('#media-nav').click(function() {
        $('#folders-listing').html('');
        $('#fck-img-type').attr('src', './images/mod/filecabinet/file_manager/file_type/image80_bw.png');
        $('#fck-doc-type').attr('src', './images/mod/filecabinet/file_manager/file_type/document80_bw.png');
        $('#fck-mm-type').attr('src', './images/mod/filecabinet/file_manager/file_type/media80.png');
        getFolders(3, 0);
    });
}
function getFolders(type, id) {
    if (type == 1) {
        ftype = 'img';
        aop = 'images';
    } else if (type == 2) {
        ftype = 'doc';
        aop = 'documents';
    } else if (type == 3) {
        ftype = 'mm';
        aop = 'multimedia';
    }
    if (id==0) {
        $('#folders-listing').load('index.php?module=filecabinet&aop=fck_'+ftype +'_folders');
    } else {
        $('#folders-listing').load('index.php?module=filecabinet&aop=fck_'+ftype+'_folders', function() {
            $.get('index.php?module=filecabinet&aop=fck_'+aop+'&fid=' + id, function(data) {
                $('#folder-' + id).html(data);
                initFiles();
            });
        });
    }
}
function initFiles() {
    $('.show-thumb').hover(
    function() {
        $(this).siblings('.thumbnail').show();
    },
    function() {
        $(this).siblings('.thumbnail').hide();
    });
}
function pull_folder(id, ftype) {
    getFolders(ftype, id);
}
function show_thumb(id) {
    $('.thumbnail').toggle();
}
</script>
