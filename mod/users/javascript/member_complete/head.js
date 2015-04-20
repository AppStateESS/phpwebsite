$(window).load(function() {
    $("#memberList_search_member").autocomplete(
            {
                source: 'index.php?module=users&action=admin&command=search_members',
                delay: 500,
                minChars: 2
            }
    );
});