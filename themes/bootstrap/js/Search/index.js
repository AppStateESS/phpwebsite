/* global $ */
let searchToggle = false

$(document).ready(() => {
  $('#search-button').click(() => {
    if (searchToggle) {
      hideMenu()
    } else {
      showMenu()
    }
  })

  $('#close-search').click(() => {
    hideMenu()
  })
})

const showMenu = () => {
  $('body').css('overflow', 'hidden')
  searchToggle = true
  $('#search-menu').show()
}

const hideMenu = () => {
  $('body').css('overflow', 'inherit')
  searchToggle = false
  $('#search-menu').hide()
}

$('#search-form').submit((e) => {
  e.preventDefault()
  const form = $(e.target)
  const inputs = form.find('.form-check-input')
  const getUrl = window.location
  let baseUrl = getUrl.host + "/" + getUrl.pathname.split('/')[1]

  if ($(inputs[1]).prop('checked')) {
    baseUrl = 'appstate.edu'
  }
  let searchQuery = $('#search-input').val()
  if (searchQuery.length) {
    searchQuery = searchQuery.replace(/[^a-zA-Z\d\s]/g, '')
    searchQuery = searchQuery.replace(/\s+/g, '+')
    searchQuery = searchQuery.replace(/\+$/, '')
    window.location.href = '//duckduckgo.com/?q=site%3A' + baseUrl + '+' +
        searchQuery
  }
})
