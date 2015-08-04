$(document).ready(function(){
    Pagers.callback = readyPurge;
});

function readyPurge() {
    $('.purge').click(function(event){
        var page_id = $(event.target).data('pageId');
        $.get('index.php', {
            module : 'pagesmith',
            aop : 'purgePage',
            id : page_id
        }).always(function(){
            Pagers.reload('purge-list');
        });
    });
    $('.restore').click(function(event){
        var page_id = $(event.target).data('pageId');
        $.get('index.php', {
            module : 'pagesmith',
            aop : 'restorePage',
            id : page_id
        }).always(function(){
            Pagers.reload('purge-list');
        });
    });
}