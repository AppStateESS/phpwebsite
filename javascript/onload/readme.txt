Onload
-----------------------------
This javascript lets you call a javascript function on a page load.
You may never use this function as most of the time your window.onload
function will be in the head.js of some other script.

However, occasionally you may want to _conditionally_ call a
window.onload depending on logic in your php code.

How to use
------------------------------
javascript('onload', array('function'=>'name_of_function()');


Example
------------------------------

<?php

if ($user->not_logged_in) {
   javascript('onload', array('function'=>"alert('GO AWAY!')"));
}

?>
