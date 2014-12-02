/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$(window).load(function () {
    var editor_called = false;
    $('#save-block').click(function () {
        $('#block-form').submit();
    });

    $('#add-block').click(function () {
        link = 'index.php?module=block&action=js_block_edit&key_id=' +
                $(this).data('key-id') + '&authkey=' + $(this).data('auth-key');
        $.get(link, function (data) {
            $('#block-form-modal .modal-body').html(data);
            $('#block-form-modal').modal('show');
            if (!editor_called) {
                CKEDITOR.replace('block-form_block_content');
                editor_called = true;
            }

        });
    });
});
