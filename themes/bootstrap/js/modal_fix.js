// moves the modal login out of the navbar in bootstrap because it is bugged.
$(window).load(function(){
    $('body').append($('.modal'));
});