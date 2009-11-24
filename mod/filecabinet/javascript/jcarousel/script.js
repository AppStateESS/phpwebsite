function carousel(caro_id, vertical_var, scroll_var, total_size) {
    id_tag = 'div#' + caro_id + ' a';

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
    horz_tag1 = 'div#' + caro_id + ' .jcarousel-skin-tango .jcarousel-clip-horizontal';
    horz_tag2 = 'div#' + caro_id + ' .jcarousel-skin-tango .jcarousel-container-horizontal';
    
    vert_tag1 = 'div#' + caro_id + ' .jcarousel-skin-tango .jcarousel-clip-vertical';
    vert_tag2 = 'div#' + caro_id + ' .jcarousel-skin-tango .jcarousel-container-vertical';
    total_size = total_size + 'px';
    
    
    $(horz_tag1).css('width', total_size);
    $(horz_tag2).css({'width': total_size});
    $(vert_tag1).css({'height': total_size});
    $(vert_tag2).css({'height': total_size});
}
