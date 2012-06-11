$(function() {
    $('input.close-dialog').click(function(){
        $('#dialog').dialog();
    });

    $('a.access-link').click(function() {
        var id = $(this).attr('id').substr(1);
        var link = 'index.php?module=filecabinet&dop=add_access&document_id=' + id + '&authkey=' + auth;
        submitAccess(id, link);
    });
});

function submitAccess(id, link) {
    $.getJSON(link, function(data) {
        if (data.success) {
            $('#dialog').html(data.message);
            $('#dialog').dialog({
                buttons: {
                    "Ok": function() {
                        $(this).dialog("close");
                    }
                }
            });
        } else {
            var title = prompt(data.message, data.keyword);
            if (title==null) {
                return;
            }
            link += '&keyword=' + title;
            submitAccess(id, link);
        }
    });
}