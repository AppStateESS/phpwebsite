function carousel(caro_id, vertical_var, scroll_var, total_size) {
    id_tag = 'div#' + caro_id + ' li a';
    initLightbox(id_tag);
    id_tag = 'div#' + caro_id + ' ul';
    $(id_tag).jcarousel({vertical: vertical_var,scroll: scroll_var});
    if (vertical_var) {
        sty1 = 'div#' + caro_id + ' .jcarousel-clip-vertical';
        sty2 = 'div#' + caro_id + ' .jcarousel-container-vertical';
        dim = 'height';
    } else {
        sty1 = 'div#' + caro_id + ' .jcarousel-clip-vertical';
        sty2 = 'div#' + caro_id + ' .jcarousel-container-vertical';
        dim = 'width';
    }
    $(sty1).css(dim, total_size);
    $(sty2).css(dim, total_size);
}
