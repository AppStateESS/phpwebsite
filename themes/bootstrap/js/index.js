"use strict"
/* global $, jQuery */
import 'bootstrap'
import '@fortawesome/fontawesome'
import '@fortawesome/fontawesome-free-solid'
import '@fortawesome/fontawesome-free-regular'
import '@fortawesome/fontawesome-free-brands'
import './Search'
import './MainMenu'
import './EmptyRight'

// this is a fill for the $(window).load function which is deprecated
// $(window).on('load', ()=>{}) is the accepted method
jQuery.fn.load = function(callback){ $(window).on("load", callback) }
