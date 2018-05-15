if(!$.trim($("#main-theme-content .right-side").html())) {
  $('#main-theme-content .right-side').hide()
  $('#main-theme-content .left-side').removeClass('col-md-8 col-lg-9')
  $('#main-theme-content .left-side').addClass('col-12')
}
/*
if ($.trim($("#main-theme-content .right-side").html())) {
  $('#main-theme-content .left-side').removeClass('col-12')
  $('#main-theme-content .left-side').addClass('col-md-8 col-lg-9')
}
*/
