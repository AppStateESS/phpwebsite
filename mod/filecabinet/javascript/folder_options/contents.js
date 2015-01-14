var FolderModal = new FolderModal;
var FolderElements = new FolderElements;
$(window).load(function()
{
    FolderModal.initialize();
    FolderElements.initialize();
});

function FolderElements() {
    var t = this;

    this.initialize = function()
    {
        this.initializeFileButtons();
    };

    this.initializeFileButtons = function()
    {
        var type;
        var file_id;
        var folder_id;
        var command;
        var authkey;
        
        $('.delete-file').click(function() {
            type = $(this).data('type');
            folder_id = $(this).data('folderId');
            file_id = $(this).data('id');
            command = $(this).data('command');
            authkey = $(this).data('authkey');
            if (confirm('Are you sure you want to delete this file?')) {
                var href = 'index.php?module=filecabinet&' + type + '=' + command + '&file_id=' + file_id +
                        '&folder_id=' + folder_id + '&authkey=' + authkey;
                window.location.href = href;
            }
        });

        $('.edit-file').click(function() {
            type = $(this).data('type');
            folder_id = $(this).data('folderId');
            file_id = $(this).data('id');
            command = $(this).data('command');
            authkey = $(this).data('authkey');
            t.pullFileForm(file_id, type, command, folder_id, authkey);
        });
    };

    this.pullFileForm = function(file_id, operation, command, folder_id, authkey)
    {
        var destination = 'index.php?' + operation + '=' + command
        $.get(destination, {
            module: 'filecabinet',
            folder_id: folder_id,
            file_id: file_id,
            authkey: authkey
        }, function(data) {
            //console.log(data);
        }, 'json').done(function(data) {
            $('#folder-form .modal-title').html(data.title);
            $('#folder-form .modal-body').html(data.content);
            $('#folder-form').modal('show');
            readySaveButton();
        });
    };

}

function FolderModal() {
    var t = this;

    this.initialize = function()
    {
        $('.show-modal').click(function() {
            var operation = $(this).data('operation');
            var command = $(this).data('command');
            var folder_id = $(this).data('folderId');
            var file_id = $(this).data('id');
            var authkey = $(this).data('authkey');
            t.pullFolderForm(file_id, operation, command, folder_id, authkey);
        });
    };

    this.pullFolderForm = function(file_id, operation, command, folder_id, authkey)
    {
        var dest = 'index.php?' + operation + '=' + command;
        $.get(dest, {
            module: 'filecabinet',
            folder_id: folder_id,
            file_id: file_id,
            authkey: authkey
        }, function(data) {
            //console.log(data);
        }, 'json')
                .done(function(data) {
                    $('#folder-form .modal-title').html(data.title);
                    $('#folder-form .modal-body').html(data.content);
                    $('#folder-form').modal('show');
                    readySaveButton();
                });
    };

}

readySaveButton = function()
{
    $('.save-element').click(function() {
        $('#file-form').submit();
    });
};