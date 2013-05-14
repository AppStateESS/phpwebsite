var block_id = 0;
var page_id = 0;
var section_id = 0;
var current_block;
$(document).ready(function() {
    var editor = CKEDITOR.replace('block-edit-textarea', {customConfig: 'phpws_config.js'});
    initializeDialog(editor);
    editBlock(editor);
    $.fn.powerTip.defaults.followMouse = 'true';
    $.fn.powerTip.defaults.offset = '10';
    $('.block-edit').powerTip();
});
function editBlock(editor)
{
    $('.block-edit').click(function() {
        current_block = $(this);
        block_id = $(this).data('block-id');
        page_id = $(this).data('page-id');
        section_id = $(this).attr('id');
        $.get('index.php',
                {'module': 'pagesmith',
                    'aop': 'block_info',
                    'pid': page_id,
                    'bid': block_id,
                    'section_id': section_id
                },
        function(data) {
            editor.setData(data);
            $('#block-edit-popup').dialog('open');
        }
        );
    });
}

function initializeDialog(editor)
{
    $('#block-edit-popup').dialog(
            {
                position: {my: 'center top', at: 'center top', of: document},
                autoOpen: false,
                width: 650,
                title: 'Edit text area',
                buttons: [{text: "Save",
                        click: function() {
                            updateBlock(editor);
                            $(this).dialog('close');
                        }
                    }]
            }
    );
}

function updateBlock(editor) {
    content = editor.getData();
    $.post('index.php',
            {
                'module': 'pagesmith',
                'aop': 'save_block',
                'pid': page_id,
                'bid': block_id,
                'content': content,
                'section_id': section_id
            }, function(data) {
        if (content === '') {
            content = '[Click to edit]';
        }
        current_block.html(content);
    });
}