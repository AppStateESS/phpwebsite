addModalAlert = function (content) {
    $('#access-shortcut .alert').remove();
    var alert_div = '<div class="alert alert-warning alert-dismissible" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert">' +
            '<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>' +
            '</button><div class="alert-content">' + content + '</div></div>';
    $('#access-shortcut .modal-header').after(alert_div);
};

$(document).ready(function () {
    var error_found = 3;
    $('#save-shortcut').click(function () {
        var schedule_id = $('#sch-id').val();
        var keyword = $('#keyword').val();
        if (keyword.length > 1) {
            $.post('index.php', {
                module: 'access',
                command: 'post_shortcut',
                key_id: $('#key-id').val(),
                sch_id: schedule_id,
                keyword: keyword,
                authkey: $('#authkey').val()
            }, function (data) {
                if (data.error) {
                    addModalAlert(data.message);
                } else if (schedule_id > 0) {
                    location.reload();
                } else {
                    location.href = data.keyword;
                }
            }, 'json');
        }
    });

    $('#add-shortcut').click(function (e) {
        var key_id = $(this).data('key');
        $.get('index.php', {
            module: 'access',
            command: 'edit_shortcut',
            key_id: $(this).data('key'),
            authkey: $(this).data('authkey')
        }, function (data) {
            $('#access-shortcut .modal-body').html(data);
            $('#access-shortcut').modal('show');
        });
    });

    $('.edit-shortcut').click(function (e) {
        $.get('index.php', {
            module: 'access',
            command: 'edit_shortcut',
            sch_id: $(this).data('schid'),
            authkey: $(this).data('authkey')
        }, function (data) {
            $('#access-shortcut .modal-body').html(data);
            $('#access-shortcut').modal('show');
        });
    });
});
