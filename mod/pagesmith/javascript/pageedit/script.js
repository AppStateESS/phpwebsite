var block_id = 0;
var page_id = 0;
var section_id = 0;
var current_block;
var editor = {};
$(document).ready(function () {
    editor = CKEDITOR.replace('block-edit-textarea',
            {
                on:
                        {
                            instanceReady: function (ev)
                            {
                                this.dataProcessor.writer.indentationChars = '  ';

                                this.dataProcessor.writer.setRules('th',
                                        {
                                            indent: true,
                                            breakBeforeOpen: true,
                                            breakAfterOpen: false,
                                            breakBeforeClose: false,
                                            breakAfterClose: true
                                        });
                                this.dataProcessor.writer.setRules('li',
                                        {
                                            indent: true,
                                            breakBeforeOpen: true,
                                            breakAfterOpen: false,
                                            breakBeforeClose: false,
                                            breakAfterClose: true
                                        });
                                this.dataProcessor.writer.setRules('p',
                                        {
                                            indent: true,
                                            breakBeforeOpen: true,
                                            breakAfterOpen: true,
                                            breakBeforeClose: true,
                                            breakAfterClose: true
                                        });
                            }
                        }
            }
    );
    enforceFocus();
    localStorage.clear();
    initializeDialog(editor);
    initializePageTitleEdit();
    editBlock(editor);
    $('#page-title-edit').popover({html: true, placement: 'auto', trigger: 'hover', content: '<span style="margin:0px;padding:0px;font-size:16px;font-weight:bold">Click on title to edit</span>'});
    $('.block-edit').popover({html: true, placement: 'auto', trigger: 'hover', content: '<span style="font-size:16px;font-weight:bold">Click on text to edit</span>'});
});
function editBlock(editor)
{
    $('.block-edit').click(function () {
        current_block = $(this);
        block_id = $(this).data('block-id');
        page_id = $(this).data('page-id');
        section_id = $(this).attr('id');
        if (localStorage[block_id] !== undefined) {
            editor.setData(localStorage[block_id]);
            openBlockEdit();
        } else {
            $.get('index.php',
                    {'module': 'pagesmith',
                        'aop': 'block_info',
                        'pid': page_id,
                        'bid': block_id,
                        'section_id': section_id
                    },
            function (data) {
                editor.setData(data);
                openBlockEdit();
            }
            );
        }
    });
}

function enforceFocus()
{
    $.fn.modal.Constructor.prototype.enforceFocus = function () {
        modal_this = this
        $(document).on('focusin.modal', function (e) {
            if (modal_this.$element[0] !== e.target && !modal_this.$element.has(e.target).length
                    && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_select')
                    && !$(e.target.parentNode).hasClass('cke_dialog_ui_input_text')) {
                modal_this.$element.focus()
            }
        })
    };
}

function openBlockEdit()
{
    $('#edit-section').modal('show');
}

function openTitleEdit()
{
    $('#title-edit-popup').dialog('open');
    openOverlay('title-dialog');
}

function openOverlay(class_name)
{
    $('body').attr('style', 'overflow:hidden');
    $('.' + class_name).before('<div style="position: fixed ;width : 100%; height: 100%;background-color:none" class="ui-widget-overlay dialog-overlay" />');
    clickOutside();
}

function clickOutside()
{
    $('.ui-widget-overlay').click(function () {
        ck_data = editor.getData();
        localStorage[block_id] = ck_data;
        closeBlockEdit();
    });
}

function closeBlockEdit()
{
    $('#block-edit-popup').dialog('close');
    closeOverlay();
}

function closeOverlay()
{
    $('body').attr('style', 'overflow:auto');
    $('.dialog-overlay').remove();
}

function initializePageTitleEdit()
{
    $('#page-title-edit').click(function () {
        if (!$('#page-title-edit').data('new')) {
            $('#page-title-input').val($('#page-title-edit').html());
        }
        openTitleEdit();
    });
}

function initializeDialog(editor)
{
    $('#edit-section').on('hide.bs.modal', function (e) {
        ck_data = editor.getData();
        localStorage[block_id] = ck_data;
    });


    $('#save-page').click(function () {
        updateBlock(editor);
        $('#edit-section').modal('hide');
    });

    $('#title-edit-popup').dialog(
            {
                position: {my: 'center', at: 'center', of: this},
                dialogClass: 'title-dialog',
                autoOpen: false,
                width: 650,
                title: 'Edit page title',
                buttons: [{text: "Save",
                        click: function () {
                            var title_input = $('#page-title-input').val();
                            title_input = title_input.replace('/[<>]/gi', '');
                            $('#page-title-hidden').val(title_input);
                            $('#page-title-edit').html(title_input);
                            $('#page-title-edit').css('color', 'inherit');
                            $(this).dialog('close');
                        }
                    }],
                close: function () {
                    closeOverlay();
                }
            }
    );

}

function updateBlock(editor) {
    localStorage.removeItem(block_id);
    content = editor.getData();
    $.post('index.php',
            {
                'module': 'pagesmith',
                'aop': 'save_block',
                'pid': page_id,
                'bid': block_id,
                'content': content,
                'section_id': section_id
            }, function (data) {
        if (content === '') {
            content = '[Click to edit]';
        }
        current_block.html(content);
    });
}

/*
 * JQuery center fix by Andreas Lagerkvist
 */
jQuery.fn.center = function (absolute) {
    return this.each(function () {
        var t = jQuery(this);
        t.css({position: absolute ? 'absolute' : 'fixed', left: '50%', top: '50%', zIndex: '99'}).css({marginLeft: '-' + (t.outerWidth() / 2) + 'px', marginTop: '-' + (t.outerHeight() / 2) + 'px'});
        if (absolute) {
            t.css({marginTop: parseInt(t.css('marginTop'), 10) + jQuery(window).scrollTop(), marginLeft: parseInt(t.css('marginLeft'), 10) + jQuery(window).scrollLeft()})
        }
    })
};
