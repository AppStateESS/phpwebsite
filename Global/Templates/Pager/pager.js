$(window).load(function() {
    Pager.init();
});

var Pager = {};

Pager.init = function() {
    Pager.rows = new Array;
    Pager.getPagerIds();
    $('a.sort-header').click(function() {
    });

    Pager.pager_ids.forEach(Pager.loadPager);

    //Pager.pager_ids.forEach(Pager.getData);
};

Pager.loadPager = function(pager_id)
{
    Pager.pagers[pager_id].getData(pager_id);
    console.log(pager_id);
}

Pager.getData = function(pager_id) {
    var url = Pager.currentURL();
    $.get(url, {
        'pager_id': pager_id
    },
    function(data) {
        if (data.error) {
            return;
        }
        var rows = data[pager_id].rows;
        Pager.fillRows(pager_id, rows);
        //console.log(rows);
        //console.log(data[pager_id].rows);
        //console.log(rows);
    }, 'json');
}

Pager.fillRows = function(pager_id, rows) {
    var tpl_rows = $('#' + pager_id + ' .pager-row');
    console.log(rows);
}


Pager.currentURL = function() {
    var unfiltered_url = document.URL;
    return unfiltered_url.replace(/\&.*$/g, '');
};

Pager.plugRows = function() {
}

Pager.getPagerIds = function() {
    Pager.pager_ids = new Array();
    var i = 0;
    $('.pager').each(function() {
        Pager.pager_ids[i] = $(this).attr('id');
        i++;
    });
}