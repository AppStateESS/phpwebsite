$(window).load(function() {
    $('#move-boxes').click(function() {
        fillModal();
        $('#box-move').modal('show');
    });
    $('#box-move').on('hidden.bs.modal', function (e) {
        window.location.reload();
    });
});

function fillModal()
{
    $.get('index.php', {
        module: 'layout',
        action: 'admin',
        command: 'boxMoveForm'
    }).success(function(data) {
        $('#box-move .modal-body').html(data);
        setChange();
    });
}

function setChange()
{
    $('.move-box').change(function(e) {
        var value = e.target.value;
        var id = $(e.target).data('id');

        $.get('index.php', {
            module: 'layout',
            action: 'admin',
            command: 'moveBox',
            box_source: id,
            box_dest: value
        }).success(function() {
            fillModal();
        });
    });
}