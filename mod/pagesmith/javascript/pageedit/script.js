var block_id = 0;
var page_id = 0;
var section_id = 0;
var current_block;
$(document).ready(function() {
    var editor = CKEDITOR.replace('block-edit-textarea');
    initializeDialog(editor);
    initializePageTitleEdit();
    editBlock(editor);
    $('#page-title-edit').popover({html: true, placement: 'auto', trigger: 'hover', content: '<span style="margin:0px;padding:0px;font-size:16px;font-weight:bold">Click on title to edit</span>'});
    $('.block-edit').popover({html: true, placement: 'auto', trigger: 'hover', content: '<span style="font-size:16px;font-weight:bold">Click on text to edit</span>'});
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
            $('#block-edit-popup').modal('show');
        }
        );
    });
}

function initializePageTitleEdit()
{
    $('#page-title-edit').click(function() {
        if (!$('#page-title-edit').data('new')) {
            $('#page-title-input').val($('#page-title-edit').html());
        }
        $('#title-edit-popup').modal('show');
        $('.ui-dialog').before('<div style="position: fixed ;width : 100%; height: 100%;background-color:none" class="ui-widget-overlay dialog-overlay" />');
    });
}

function initializeDialog(editor)
{
    $('#modal-save').click(function() {
        updateBlock(editor);
        $('#block-edit-popup').modal('hide');
    });
    $('#modal-save-title').click(function() {
        var title_input = $('#page-title-input').val();
        title_input = title_input.replace('/[<>]/gi', '');
        $('#title-edit-popup').modal('hide');
        $('#page-title-hidden').val(title_input);
        $('#page-title-edit').html(title_input);
        $('#page-title-edit').css('color', 'inherit');
    });
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