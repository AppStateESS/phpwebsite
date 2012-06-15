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

$(function() {
    var folder_type = 'image';
    var folder_span = $('li.folder span');
    $(folder_span).click(function() {
        var line_item = $(this).parent();
        var folder_id = line_item.attr('rel');
        var folder_link = 'index.php?module=filecabinet&aop=ck_folder_contents&ftype=' + folder_type + '&folder_id=' + folder_id;

        $.get(folder_link, function(data) {
            line_div = line_item.children('div');
            if (data) {
                line_div.html(data);
                if ((line_div).is(':hidden')) {
                    line_div.slideDown('slow');
                    line_item.children('img.folder-image').attr('src', folder_open);
                } else {
                    line_div.slideUp();
                    line_item.children('img.folder-image').attr('src', folder_closed);
                }
            }

            $('div.pick-image').click(function(){
                ftype = $(this).attr('rel');
                file_id = $(this).attr('id');
                var file_link = 'index.php?module=filecabinet&aop=ck_file_info&ftype=' + ftype + '&file_id=' + file_id;
                $.getJSON(file_link, function(data) {
                    $('div#files').html(data.html);
                    insert_text = data.insert;
                });
            });

        });
    });
});