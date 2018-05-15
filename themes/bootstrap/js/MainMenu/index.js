"use strict"
/* global $ */
let menuOpen = false

const toggleMenu = () => {
  $('#top-menu .menu-top-view li a').click((e) => {
    if (menuOpen) {
      $('ul.menu-top-links').hide()
      $('ul.menu-top-view a.active').removeClass('active')
    }
    $(e.target).addClass('active')
    menuOpen = true
    $(e.target).siblings('ul.menu-top-links').toggle()
  })
}

const bodyClear = () => {
  $('body').click((e) => {
    const target = $(e.target)
    if (!target.parents('ul.menu-top-view').length) {
      menuOpen = false
      $('ul.menu-top-view a.active').removeClass('active')
      $('ul.menu-top-links').hide()
    }
  })
}


const stickyMenu = () => {
  if (window.pageYOffset > 0) {
    stickit()
  }
  window.onscroll = () => {
    stickit()
  }
}

const stickit = () => {
  // if the window is small enough for phone view or the control panel is in use
  // then don't bother with the sticky
  const windowWidth = $(window).width()
  if (windowWidth <= 768) {
    return
  }
  const offset = window.pageYOffset
  const snapPadding = 0
  const snapLine = Math.floor(infoBarPositionY - blackBarHeight + snapPadding)
  if (offset >= snapLine) {
    infobar.css('visibility', 'hidden')
    cloneInfobar.show()
  } else {
    infobar.css('visibility', 'visible')
    cloneInfobar.hide()
  }
}

const blackBarHeight = $('#theme-top').height()
const infobar = $('#title-menu')
const infobarWidth = infobar.width()
const infoBarPositionY = infobar.offset().top
const cloneInfobar = infobar.clone()
cloneInfobar.removeAttr('id')
cloneInfobar.css("top", blackBarHeight)
cloneInfobar.css("width", infobarWidth + 'px')
cloneInfobar.addClass("sticky-bar")
cloneInfobar.hide()
$('#sticky-container').prepend(cloneInfobar)


$(document).ready(() => {
  toggleMenu()
  bodyClear()
  stickyMenu()

  $('body').append($('#user-signin'))
})
