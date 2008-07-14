To use:

// Question asked by confirm box
$js_variables['QUESTION'] = 'Are you sure you want to delete this?';

// Address to go to if they the user clicks yes
$js_variables['ADDRESS'] = 'index.php?module=mymod&amp;command=delete_it';

// What they are clicking on - text or image
$js_variables['LINK']     = 'Delete This';

// Title for confirm link
// make sure to add slashes
$js_variables['TITLE'] = 'Delete link';

// class for link
$js_variables['CLASS'] = 'confirm-link';

$link = Layout::getJavascript('confirm', $js_variables);


----------------------------------------------------------------
Make sure to run addslashes() on your variables!
