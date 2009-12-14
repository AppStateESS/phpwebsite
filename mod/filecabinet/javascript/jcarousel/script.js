function carousel(caro_id, vertical_var, scroll_var, total_size) {
    id_tag = 'div#' + caro_id + ' li a';
    initLightbox(id_tag);
    id_tag = 'div#' + caro_id + ' ul';
    $(id_tag).jcarousel({vertical: vertical_var,scroll: scroll_var});
}
