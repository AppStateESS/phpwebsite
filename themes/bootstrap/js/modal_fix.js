// moves the modal login out of the navbar in bootstrap because it is bugged.
$(window).load(function(){
    $('#modal-storage').append($('#user-signin'));
});