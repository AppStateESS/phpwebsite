
$(window).load(function() {
    Pagers = new PagerList;
    Pagers.loadPagers();
    console.log(Pagers);
});

function PagerList() {
    this.pagers = new Object;

    this.loadPagers = function()
    {
        $this = this;
        $('.pager').each(function() {
            var pager_dom = $(this);
            var pager_id = pager_dom.attr('id');

            new_pager = new Pager(pager_id, pager_dom);
            new_pager.init();
            if (new_pager.rows !== undefined) {
                $this.pagers[new_pager.id] = new_pager;
            }
        });
    };
}


function Pager(id, page) {
    var $this = this;
    this.id = id;
    this.page = page;


    this.loadData = function() {
        var url = this.currentURL();
        var all_good = true;
        $.ajax({
            'url': url,
            'dataType': 'json',
            'data': {'pager_id': $this.id},
            'async': false,
            'success': function(data) {
                if (data.error || data.rows.length < 1) {
                    return;
                } else {
                    $this.rows = data.rows;
                }
            }
        });
    };

    this.loadRowTemplate = function() {
        this.row_template = $('#' + this.id + ' .pager-row');
        this.columns = this.row_template.find('.pager-column');
        //this.row_template.remove();
    };


    this.currentURL = function() {
        var unfiltered_url = document.URL;
        return unfiltered_url.replace(/\&.*$/g, '');
    };

    this.plugRows = function() {
    };

    this.init = function() {
        this.loadRowTemplate();
        this.columns.each(function() {
            $(this).html('test');
        });

        var result = this.loadData();
        this.loadData();
    };

    //var column_example = $('.animal').children(this.row_template);
    //console.log(column_example.html());
}

