var insert_text = null;
var CKEDITOR = window.parent.CKEDITOR;
var oEditor = CKEDITOR.instances.editorName;

var okListener = function(ev) {
    this._.editor.insertHtml(insert_text);
    CKEDITOR.dialog.getCurrent().removeListener("ok", okListener);
    CKEDITOR.dialog.getCurrent().removeListener("cancel", cancelListener);
}

var cancelListener = function(ev) {
    CKEDITOR.dialog.getCurrent().removeListener("ok", okListener);
    CKEDITOR.dialog.getCurrent().removeListener("cancel", cancelListener);
};

CKEDITOR.event.implementOn(CKEDITOR.dialog.getCurrent());
CKEDITOR.dialog.getCurrent().on("ok", okListener);
CKEDITOR.dialog.getCurrent().on("cancel", cancelListener);

// defaults as an image folder.
var folder_type = 'image';

// span tag inside li.folder that contains the folder icon and name
var folder_span;

/**
 * Script initializer
 */
$(function() {
    readyFolder();
    folderTypeChange();
    shadeType();
});

/**
 * changes the folder type (image, document, or multimedia when clicked
 */
function folderTypeChange()
{
    $('select#folder-type').change(function() {
        folder_type = $(this).find(':selected').attr('value');
        refreshFolder();
        shadeType();
    });
}

function shadeType()
{
    $('#image-button').parent().removeClass('current-type');
    $('#document-button').parent().removeClass('current-type');
    $('#media-button').parent().removeClass('current-type');
    switch (folder_type) {
        case 'image':
            $('#image-button').parent().addClass('current-type');
            break;

        case 'document':
            $('#document-button').parent().addClass('current-type');
            break;

        case 'multimedia':
            $('#media-button').parent().addClass('current-type');
            break;
    }
}

/**
 * Prepares folder for a click action which populates it with a list of files
 */
function readyFolder()
{
    folder_span = $('li.folder span');
    folder_span.click(function() {
        folderContents($(this));
    });
}

function refreshFolder()
{
    var refresh_link = 'index.php?module=filecabinet&aop=ck_folder_listing&ftype=' + folder_type;

    $.get(refresh_link, function(data) {
        $('div#folder-listing').html(data);
        $('div#files').html('');
        readyFolder();
    });
}

/**
 * folder_line : current folder used for content request
 */
function folderContents(folder_line)
{
    var line_item = folder_line.parent();
    var folder_id = line_item.attr('rel');
    var folder_link = 'index.php?module=filecabinet&aop=ck_folder_contents&ftype=' + folder_type + '&folder_id=' + folder_id;

    $.get(folder_link, function(data) {
        line_div = line_item.children('div');
        if (data) {
            line_div.html(data);
            if ((line_div).is(':hidden')) {
                line_div.slideDown();
                line_item.find('img.folder-image').attr('src', folder_open);
            } else {
                line_div.slideUp();
                line_item.find('img.folder-image').attr('src', folder_closed);
            }
        }

        readyFilePick(folder_line);
    });
}

function readyFilePick(folder_line)
{
    var file_pick_str = 'div.pick-' + folder_type;
    var file_pick_obj = $(file_pick_str);
    file_pick_obj.click(function(){
        ftype = $(this).attr('rel');
        file_id = $(this).attr('id');
        var file_link = 'index.php?module=filecabinet&aop=ck_file_info&ftype=' + folder_type + '&file_id=' + file_id;
        $.getJSON(file_link, function(data) {
            $('div#files').html(data.html);
            insert_text = data.insert;
        });
    });
}