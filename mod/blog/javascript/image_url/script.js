var url;
var select;
$(window).load(function(){
    url = $('#image-url');
    select = $('#edit-blog_image_link');
    checkSelect();
    select.change(function(){
        checkSelect();
    });
});

function checkSelect()
{
    // string
    var option;
    option = $(':selected', select).val();
    if (option == 'url') {
        toggleUrl(1);
    } else {
        toggleUrl(0);
    }
}

function toggleUrl(show)
{
    if (show) {
        url.slideDown();
    } else {
        url.slideUp();
    }
}


/*
function toggleUrl(select) {
    url = document.getElementById('image-url');
    textbox = document.getElementById('edit-blog_image_url');

    if (select.value == 'url') {
        url.style.opacity = '1';
        textbox.disabled = false;
    } else {
        url.style.opacity = '.5';
        textbox.disabled = true;
    }
}
*/