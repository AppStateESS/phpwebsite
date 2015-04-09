var FolderList = new FolderList;
var CKEDITOR = window.parent.CKEDITOR;
CKEDITOR.config.allowedContent = true;
// ftype and accepted_files are loaded before this script

var okListener = function(ev) {
    //this._.editor.insertHtml(FolderList.getContent());
    FolderList.loadContent(this._.editor);
    CKEDITOR.dialog.getCurrent().removeListener("ok", okListener);
    //CKEDITOR.dialog.getCurrent().removeListener("cancel", cancelListener);
};

var cancelListener = function(ev) {
    CKEDITOR.dialog.getCurrent().removeListener("ok", okListener);
    //CKEDITOR.dialog.getCurrent().removeListener("cancel", cancelListener);
};

//CKEDITOR.event.implementOn(CKEDITOR.dialog.getCurrent());
CKEDITOR.dialog.getCurrent().on("ok", okListener);
CKEDITOR.dialog.getCurrent().on("cancel", cancelListener);

$(window).load(function() {
    FolderList.init();
});

function FolderList() {
    this.current_folder;
    this.dropzone;
    this.modal;
    this.active_folder = 0;
    this.show_thumbnail = 0;

    var t = this;

    this.init = function() {
        // pull folder listing
        this.loadModal();
        this.loadFolderList(0);
        this.loadUploadButton();
        this.loadNewFolderButton();
        this.loadThumbnailSwitch();
        this.loadSearch();
    };

    this.searchRows = function(query) {
        query = query.replace(/[^\w\s]/i, '');
        if (query.length > 0) {
            var exp = new RegExp(query, 'gi');
            $.each(this.current_folder.file_rows, function(index, value) {
                var title = $('td.file-title', value).text();
                if (title.match(exp)) {
                    $(value).show();
                } else {
                    $(value).hide();
                }
            });
        } else {
            $.each(this.current_folder.file_rows, function(index, value) {
                $(value).show();
            });
        }
    };

    this.loadThumbnailSwitch = function() {
        if (ftype === 1) {
            // image folder
            $('.show-thumbs, .hide-thumbs').click(function(){
                t.show_thumbnail = Math.abs(t.show_thumbnail - 1);
                if (t.show_thumbnail) {
                    $('.show-thumbs').hide();
                    $('.hide-thumbs').show();
                } else {
                    $('.show-thumbs').show();
                    $('.hide-thumbs').hide();
                }
                t.current_folder.loadFiles();
            });
        }
    };

    this.loadSearch = function() {
        var search = null;

        $('#search-field').keyup(function() {
            _this = $(this);
            clearTimeout(search);
            search = setTimeout(function() {
                t.searchRows(_this.val());
            }, 1000);
        });

        $('#clear-search').click(function() {
            $('#search-field').val('');
            t.searchRows('');
        });

        $('#save-search').click(function() {
            t.searchRows($('#search-field').val());
        });
    };
    /**
     * Pulls folder listing
     * @returns void
     */
    this.loadFolderList = function() {
        $.get('index.php', {
            module: 'filecabinet',
            ckop: 'list_folders',
            ftype: ftype,
            active_folder: this.active_folder
        }).done(function(data) {
            $('#folder-list ul').html(data);
            t.loadFolderSelect();
            t.loadFolderEdit();
        });
    };

    this.clearModalOnHide = function() {
        this.modal.self_node.on('hidden.bs.modal', function(e) {
            t.modal.clearBody();
            t.modal.clearTitle();
            $('#save-folder-submit').remove();
            $('#save-file-submit').remove();
        });
    }


    this.loadFolderEdit = function() {
        this.clearModalOnHide();
        $('.edit-folder').click(function() {
            t.modal.title('Update folder');
            t.addFolderFormToBody();
            $('#folder-name').val(t.current_folder.title);
            t.loadFolderSaveButton(t.current_folder.id);
            t.modal.show();
        });
    };

    this.loadModal = function() {
        this.modal = new myModal();
        this.modal.boot();
    };

    this.loadNewFolderButton = function() {
        this.clearModalOnHide();
        $('#create-folder').click(function() {
            t.modal.title('Create folder');
            t.addFolderFormToBody();
            t.loadFolderSaveButton(0);
            t.modal.show();
        });
    };
    this.addFolderFormToBody = function() {
        var create_form = '<input maxlength="30" type="text" id="folder-name" name="folder_name" class="form-control" placeholder="Enter folder name" value="" />';
        t.modal.body(create_form);
        t.modal.self_node.on('shown.bs.modal', function() {
            $('#folder-name').focus();
        });
    };

    this.loadFolderSaveButton = function(folder_id) {
        t.modal.footer('<button class="btn btn-success" id="save-folder-submit">Save</button>');
        $('#save-folder-submit').click(function() {
            var title = $('#folder-name').val();
            if (title.length > 0) {
                $.post('index.php', {
                    module: 'filecabinet',
                    ckop: 'save_folder',
                    title: title,
                    ftype: ftype,
                    folder_id: folder_id
                }).done(function(data) {
                    t.active_folder = data;
                    t.modal.hide();
                    t.loadFolderList();
                }).fail(function(e) {
                    t.modal.clearBody();
                    t.modal.body(e.responseText);
                    t.addFolderFormToBody();
                });
            } else {
                t.modal.hide();
            }
        });
    };
    this.loadFolderSelect = function() {
        this.setCurrentFolder($('#folder-list li.folder.active'));
        $('#folder-list li.folder').click(function() {
            t.setCurrentFolder(this);
        });
    };
    this.setCurrentFolder = function(folder)
    {
        t.current_folder = new Folder(folder, this);
        t.current_folder.init();
    };
    this.loadContent = function(editor)
    {
        var content = '';
        $.each(this.current_folder.selected_rows, function(index, value) {
            $.get('index.php',
                    {
                        module: 'filecabinet',
                        ckop: 'get_file',
                        ftype: ftype,
                        id: value
                    }).
                    done(function(data) {
                        editor.insertHtml(data);
                    });
        });
    };
    this.loadDropzone = function()
    {
        this.dropzone = new Dropzone('#dropzone-area', {
            maxFilesize: 50,
            uploadMultiple: true,
            addRemoveLinks: true,
            createImageThumbnails: true,
            acceptedFiles: accepted_files
        });
    };
    this.loadUploadButton = function()
    {
        $('#upload-file').click(function() {
            $('#dropzone-background').show({
                complete: function() {
                    $('#dz-folder-id').val(t.current_folder.id);
                    if (t.dropzone === undefined) {
                        t.loadDropzone();
                    }
                }
            });
            $('#close-dropzone button').click(function() {
                $('#dropzone-background').hide({
                    complete: function() {
                        t.dropzone.removeAllFiles();
                        t.current_folder.loadFiles();
                    }
                });
            });
        });
    }
    ;
}

function myModal() {
// the modal itself
    var self_node;
    var title_node;
    var body_node;
    var footer_node;
    this.boot = function() {
        this.self_node = $('#edit-file-form');
        this.title_node = $('#edit-file-form .modal-title');
        this.body_node = $('#edit-file-form .modal-body');
        this.footer_node = $('#edit-file-form .modal-footer');
    };
    this.title = function(title) {
        this.title_node.text(title);
    };
    this.clearBody = function() {
        this.body_node.text('');
    };
    this.clearTitle = function() {
        this.title_node.text('');
    };
    this.body = function(body) {
        this.body_node.append(body);
    };
    this.footer = function(footer) {
        this.footer_node.append(footer);
    };
    this.show = function() {
        this.self_node.modal('show');
    };
    this.hide = function() {
        this.self_node.modal('hide');
        this.clearBody();
        this.clearTitle();
    };
}

function Folder(folder, parent) {
    var t = this;
    this.parent = parent;
    this.folder = $(folder);
    this.id = this.folder.data('folderId');
    this.order = 1; // 0 descend (z-a), 1 ascend (a-z)
    this.selected_rows = [];
    this.lock_deletion = true;
    this.title = this.folder.text().trim();
    this.file_rows;

    this.init = function()
    {
        this.setActive();
        this.loadFiles();
        this.loadLockIcon();
    };


    this.copyFileRows = function() {
        this.file_rows = $('.file-row');
    };

    this.setActive = function()
    {
        $('.folder').removeClass('active');
        this.folder.addClass('active');
    };

    this.loadFiles = function() {
        $.get('index.php',
                {
                    module: 'filecabinet',
                    ckop: 'list_folder_files',
                    ftype: ftype,
                    folder_id: this.id,
                    order: this.order,
                    thumbnail: this.parent.show_thumbnail,
                    active: this.parent.active_folder
                }, function(data) {
            $('#files').html(data);
        }).success(function() {
            t.fileLoadComplete();
            t.parent.searchRows($('#search-field').val());
        });
    };
    this.setOrder = function(order) {
        this.order = order;
    };
    this.resetSelectedRows = function() {
        var file_rows = $('.file-list .file-row');
        file_rows.each(function(index, value) {
            var id = $(value).data('id');
            if ($.inArray(id, t.selected_rows) >= 0) {
                $(value).addClass('success');
            }
        });
    };
    /**
     * Run at completion of all folder rows displayed by loadFiles
     * @returns
     */
    this.fileLoadComplete = function() {
        this.loadRowSelection();
        this.resetSelectedRows();
        this.initializeDelete();
        this.initializeZoom();
        this.initializeEdit();
        this.copyFileRows();
    };


    this.addFileFormToBody = function() {
        var create_form = '<input maxlength="30" type="text" id="file-name" name="file_name" class="form-control" placeholder="Enter file title" value="" />';
        t.parent.modal.body(create_form);
        t.parent.modal.self_node.on('shown.bs.modal', function() {
            $('#file-name').focus();
        });
    };

    this.initializeEdit = function() {
        $('.edit-file').click(function() {
            t.parent.modal.title('Update file');
            t.addFileFormToBody();
            var file_id = $(this).data('id');
            var file_title = $(this).parents('td').siblings('td.file-title').text().trim();
            $('#file-name').val(file_title);
            t.loadFileSaveButton(file_id);
            t.parent.modal.show();
        });
    };

    this.loadFileSaveButton = function(file_id) {
        t.parent.modal.footer('<button class="btn btn-success" id="save-file-submit">Save</button>');
        $('#save-file-submit').click(function() {
            var title = $('#file-name').val();
            if (title.length > 0) {
                $.post('index.php', {
                    module: 'filecabinet',
                    ckop: 'save_file',
                    title: title,
                    ftype: ftype,
                    file_id: file_id
                }).done(function(data) {
                    t.parent.modal.hide();
                    t.loadFiles();
                }).fail(function(e) {
                    t.parent.modal.clearBody();
                    t.parent.modal.body(e.responseText);
                    t.addFileFormToBody();
                });
            } else {
                t.modal.hide();
            }
        });
    };

    this.initializeZoom = function() {
        $('.view-file').popover({
            content: function() {
                return '<img src="' + $(this).data('url') + '" />';
            },
            html: true,
            trigger: 'hover'
        });
    };
    this.initializeDelete = function() {
        if (t.lock_deletion) {
            this.lockDelete();
        } else {
            this.unlockDelete();
        }
    };
    /**
     * Can delete, it is unlocked
     * @returns void
     */
    this.unlockDelete = function() {
        t.lock_deletion = false;
        $('#delete-lock').removeClass('fa-lock');
        $('#delete-lock').addClass('fa-unlock');
        $('.delete-file').addClass('pointer');
        $('.delete-file').removeClass('locked');
        this.loadDeleteButton();
    };
    /**
     * Cannot delete, it is locked
     * @returns void
     */
    this.lockDelete = function() {
        t.lock_deletion = true;
        $('#delete-lock').removeClass('fa-unlock');
        $('#delete-lock').addClass('fa-lock');
        $('.delete-file').removeClass('pointer');
        $('.delete-file').addClass('locked');
        this.unloadDeleteButton();
    };
    this.loadLockIcon = function() {
        $('#delete-lock').click(function() {
            if (t.lock_deletion) {
                t.unlockDelete();
            } else {
                t.lockDelete();
            }
        });
    };
    this.loadRowSelection = function() {
        $('.file-row').click(function() {
            t.selectRow(this);
        });
    };
    this.selectRow = function(selected) {
        var row_id = $(selected).data('id');
        if ($(selected).hasClass('success')) {
            this.removeSelectedRow(row_id);
            $(selected).removeClass('success');
        } else {
            t.selected_rows.push(row_id);
            $(selected).addClass('success');
        }
    };
    this.removeSelectedRow = function(row_id) {
        t.selected_rows.splice($.inArray(row_id, t.selected_rows), 1);
    };
    this.unloadDeleteButton = function() {
        $('.delete-file').unbind('click');
    };
    this.loadDeleteButton = function() {
        $('.delete-file').click(function() {
            var file_row = $(this).parents('tr');
            var row_id = $(file_row).data('id');
            var file_id = $(this).data('id');
            $.post('index.php',
                    {
                        module: 'filecabinet',
                        ckop: 'delete_file',
                        authkey: authkey,
                        ftype: ftype,
                        id: file_id
                    }).
                    done(function()
                    {
                        file_row.hide();
                    });
        });
    };
}