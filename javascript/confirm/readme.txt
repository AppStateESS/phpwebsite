To use:

// Question asked by confirm box
$js_variables['QUESTION'] = 'Are you sure you want to delete this?';

// Address to go to if they the user clicks yes
$js_variables['ADDRESS'] = 'index.php?&amp;module=mymod&amp;command=delete_it';

// What they are clicking on - text or image
$js_variables['LINK']     = 'Delete This';

$link = Layout::getJavascript('confirm', $js_variables);
