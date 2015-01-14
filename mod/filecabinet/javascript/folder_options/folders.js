var FolderModal = new FolderModal;

$(window).load(function()
{
    FolderModal.initialize();
});

function FolderModal() {
    var t = this;

    this.initialize = function()
    {
        $('.show-modal').click(function() {
            var operation = $(this).data('operation');
            var command = $(this).data('command');
            var folder_id = $(this).data('folderId');
            var ftype = $(this).data('ftype');
            var file_id = $(this).data('id');
            var authkey = $(this).data('authkey');
            t.pullFolderForm(file_id, ftype, operation, command, folder_id, authkey);
        });

        $('.delete-folder').click(function() {
            authkey = $(this).data('authkey');
            folder_id = $(this).data('folderId');
            if (confirm('Are you sure you want to delete this folder?')) {
                var href = 'index.php?module=filecabinet&aop=delete_folder&folder_id=' + folder_id + '&authkey=' + authkey;
                window.location.href = href;
            }
        });


    };

    this.pullFolderForm = function(file_id, ftype, operation, command, folder_id, authkey)
    {
        var dest = 'index.php?' + operation + '=' + command;
        $.get(dest, {
            module: 'filecabinet',
            folder_id: folder_id,
            file_id: file_id,
            ftype: ftype,
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