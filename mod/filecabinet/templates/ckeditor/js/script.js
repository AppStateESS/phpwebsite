$(function() {
    var folder_type = 'image';
    var folder_span = $('li.folder span');
    $(folder_span).click(function() {
        var line_item = $(this).parent();
        var folder_id = line_item.attr('rel');
        var link = 'index.php?module=filecabinet&aop=ck_folder_contents&ftype=' + folder_type + '&folder_id=' + folder_id;

        $.get(link, function(data) {
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
                alert('hi');
            });

        });
    });
});