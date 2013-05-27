/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

$(window).ready(function() {
    var editor_called = false;
    var dblock = $('#block-form-dialog');
    dblock.dialog(
            {
                title : 'Add block to this page',
                autoOpen: false,
                width: 650,
                buttons: [
                    {
                        text: 'Save',
                        click: function() {
                            $('#block-form').submit();
                        }
                    },
                    {
                        text: 'Cancel',
                        click: function() {
                            dblock.dialog('close');
                        }
                    }
                ]
            });
    $('#add-block').click(function() {
        link = 'index.php?module=block&action=js_block_edit&key_id=' +
                $(this).data('key-id') + '&authkey=' + $(this).data('auth-key');
        $.get(link, function(data) {
            dblock.html(data);
            console.log(data);
            if (!editor_called) {
                CKEDITOR.replace('block-form_block_content');
                editor_called = true;
            }
            dblock.dialog('open');
        });
    });
});
