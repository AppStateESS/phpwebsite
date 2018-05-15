"use strict"
/* global $, jQuery */
import 'bootstrap'
import '@fortawesome/fontawesome'
import '@fortawesome/fontawesome-free-solid'
import '@fortawesome/fontawesome-free-regular'
import '@fortawesome/fontawesome-free-brands'
jQuery.fn.load = function(callback){ $(window).on("load", callback) }
