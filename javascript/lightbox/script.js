function initLightbox(tag) {
   $(tag).lightBox({
        imageLoading: lbImageLoading,
        imageBtnClose: lbImageBtnClose,
        imageBtnPrev: lbImageBtnPrev,
        imageBtnNext: lbImageBtnNext,
        imageBlank: lbImageBlank,
        txtImage: lbTxtImage,
        txtOf: lbTxtOf
    })
};