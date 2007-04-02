Ajax script by Matthew McNaney
------------------------------

Initiates an XMLHttpRequest. This script is not usable alone. It
requires direction and purpose.

-----------------

$file_directory = 'output.php?who=world';
$success = "alert(requester.responseText)";
$failure = "alert(\'A problem occurred\')"; 
echo '<a href="#" onclick="loadRequester($file_directory, $success, $failure)">Click me</a>';
$vars['onload'] = false;

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
