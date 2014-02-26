Updated February 2014

We are now using Bootstrap datepicker by Stefan Petre at http://www.eyecon.ro/

The original Datepicker was written by Marc Grabanski and Keith Wood.
http://api.jqueryui.com/datepicker/

Usage
-----------------------------
$vars['id'] = 'date-field';
$vars['name'] = 'pick_a_date';
$vars['value'] = $current_date; // default format '%m/%d/%Y'

$input = javascript('datepicker', $vars);

echo '<form>' . $input . '</form>';

/////////////////////////////////////

If you are using the Form class you could just do the following:

$form = new PHPWS_Form('date-form');
$form->addText('start_date', strftime('%m/%d/%Y'));
$form->setExtra('start_date', 'class="datepicker"');