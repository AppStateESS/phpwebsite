var FolderList = new FolderList;
var CKEDITOR = window.parent.CKEDITOR;
CKEDITOR.config.allowedContent = true;

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
    var t = this;
    this.current_folder;
    this.dropzone;

    this.init = function() {
        this.loadFolderSelect();
        this.loadUploadButton();
    };

    this.loadFolderSelect = function() {
        this.setCurrentFolder($('#folder-list li.folder.active'));

        $('#folder-list li.folder').click(function() {
            t.setCurrentFolder(this);
        });
    };

    this.setCurrentFolder = function(folder)
    {
        t.current_folder = new Folder(folder);
        t.current_folder.init();
        //console.log(t.current_folder);
        //t.current_folder.setActive();
    };

    this.loadContent = function(editor)
    {
        var content = '';
        $.each(this.current_folder.selected_rows, function(index, value) {
            $.get('index.php',
                    {
                        module: 'filecabinet',
                        ckop: 'get_file',
                        ftype: t.current_folder.ftype,
                        id: value
                    }).
                    success(function(data) {
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
        $('.upload-file').click(function() {
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
    };
}

function Folder(folder) {
    var t = this;
    this.folder = $(folder);
    this.id = this.folder.data('folderId');
    this.ftype = this.folder.data('ftype');
    this.order = 1; // 0 descend (z-a), 1 ascend (a-z)
    this.selected_rows = [];
    this.lock_deletion = true;

    this.init = function()
    {
        this.setActive();
        this.loadFiles();
        this.loadLockIcon();
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
                    ftype: this.ftype,
                    folder_id: this.id,
                    order: this.order
                }, function(data) {
            $('#files').html(data);
        }).success(function() {
            t.fileLoadComplete();
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
    }

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
                        ftype: t.ftype,
                        id: file_id
                    }).
                    success(function()
                    {
                        file_row.hide();
                        t.loadFiles();
                    });
        });
    };

}