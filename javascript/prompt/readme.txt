Using prompt

$vars['question']   = 'What would you like to name this?';
$vars['address']    = 'index.php?module=mymode&amp;command=change_name';
$vars['answer']     = 'Type the name here...';
$vars['value_name'] = 'new_name';
$vars['link']       = 'Click on me to name this';
$vars['type']       = 'link'; // or button
$vars['class']      = 'css-class';
$vars['title']       = 'Hover text';

echo javascript('prompt', $vars);


----------------------------------------------------------------
Make sure to run addslashes() on your variables!
