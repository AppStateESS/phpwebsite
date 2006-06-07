Ajax script by Matthew McNaney
------------------------------

Initiates an XMLHttpRequest. This script is not usable alone. It
requires direction and purpose.

Required variables
-------------------

file_directory   - address to file returning data. include get variables
                   if needed

success_function - name of function from another script to call on
                   success of connection

failure_function - name of failure function on unsuccessful connection


onload           - if true, the loadRequester will be initialize on
                   page load. If false, you will need to trigger the
                   function manually. Defaults to false.

Example:

Your code
-----------------

echo '<a href="#" onclick="loadRequester()">Click me</a>';
$vars['onload'] = false;
$vars['file_directory'] = 'output.php?who=world';
$vars['success_function'] = "alert(requester.responseText)";
$vars['failure_function'] = "alert('A problem occurred')"; 

javascript('ajax', $vars);


output.php
------------------
<?php

header("Content-type: text/plain");
$who = $_GET['who'];

echo "Hello $who!";

?>

Clicking the Click Me link will then pop up an alert box with "Hello
World!" within.

Thanks to Cameron Adams for basics of script:
http://www.sitepoint.com/article/remote-scripting-ajax
