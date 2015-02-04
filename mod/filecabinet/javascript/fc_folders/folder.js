var FolderList = new FolderList;
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

    this.init = function()
    {
        this.setActive();
        this.loadFiles();
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
        });
    };

    this.setOrder = function(order) {
        this.order = order;
    };

}