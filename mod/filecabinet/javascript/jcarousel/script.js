function carousel(caro_id, vertical_var, scroll_var, total_size) {
    id_tag = 'div#' + caro_id + ' li a';

    $(id_tag).lightBox({
        imageLoading: 'javascript/modules/filecabinet/lightbox/loading.gif',
        imageBtnClose: 'javascript/modules/filecabinet/lightbox/close.gif',
        imageBtnPrev: 'javascript/modules/filecabinet/lightbox/prev.gif',
        imageBtnNext: 'javascript/modules/filecabinet/lightbox/next.gif',
        imageBlank: 'javascript/modules/filecabinet/lightbox/blank.gif',
        txtImage: image,
        txtOf: of
    });
    
    id_tag = 'div#' + caro_id + ' ul';
    $(id_tag).jcarousel({vertical: vertical_var,scroll: scroll_var});
}
