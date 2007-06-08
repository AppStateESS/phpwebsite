select_confirm
by Matthew McNaney
---------------------------------------------------
Lets you confirm ONE action in a select drop down. Usually reserved
for deletes. If that action is not selected, the button will react
like a normal submit button.


Example Code
---------------------------------------------------
$js_vars['value']        = _('Go');
$js_vars['select_id']    = 'list_action'; // the name of your select input
$js_vars['action_match'] = 'delete';
$js_vars['message']      = _('Are you sure you want to delete the checked items?');

$template['SUBMIT'] = javascript('select_confirm', $js_vars);

Creates the following in the SUBMIT tag of your form:

<input
type="button"
value="Go"
onclick="confirmSelect(this.form,
                       'list_action',
                       'delete',
                       'Are you sure you want to delete the checked items?'
                       )" />

* The arrangement above is for readability only. The actual input will
  be on one line.

Should go without saying that your {SUBMIT} tag must be with in your
<form></form> or {START_FORM}{END_FORM} tags.


Logic
-----------------------------------------------------
The button sends the form object. The confirmSelect function grabs the
'list_action' select box and checks its value. If the value is equal
to 'delete' it asks the confirm question ('Are your sure ...'). If the
answer is OK then the form is committed. If cancel is chosen, nothing
happens.

If 'delete' was NOT the value of 'list_action' the form is submitted
as normal.


IMPORTANT
------------------------------------------------------
If you are using the form class, make sure you include the form name
in the select_id.

For example

$form = new PHPWS_Form('my_pets');
$form->addSelect('dogs', array('view'=>'View', 'delete'=>'Delete'));

$tpl = $form->getTemplate();

$js_vars['value']        = 'Go';
$js_vars['select_id']    = 'my_pets_dogs';
$js_vars['action_match'] = 'delete';
$js_vars['message']      = 'Are you sure you wish to delete these dogs?';
$tpl['SUBMIT'] = javascript('select_confirm', $js_vars);

The form name is prefixed to each element to form its id.
