Calendar select script from DHTMLGoodies.com
---------------------------------------------

This script was copied from http://www.dhtmlgoodies.com.
It has been altered slightly to work with phpWebSite.

Copyright information can be found in the header of:
/javascript/js_calendar/dhtmlgoodies_calendar/dhtmlgoodies_calendar.js

We sincerely appreciate them allowing us to distribute it with
phpWebSite.


Example
--------------------------
This script uses two different form inputs. You can use either a set
of drop down selects or just a text box.

-- For a text box:

echo '
  <form id="example_form">
     <input type="text" name="my_date" />
  </form>';

$js_vars['form_name'] = 'example_form';
$js_vars['date_name'] = 'my_date';
$js_vars['type']      = 'text';
echo javascript('js_calendar', $js_vars);

-- For a select input:
<form id="select_example">
<select name='my_date_month'> <month inputs ...> </select>
<select name='my_date_day'> <day inputs ...> </select>
<select name='my_date_year'> <year inputs ...> </select>
</form>

$js_vars['form_name'] = 'select_example';
$js_vars['date_name'] = 'my_date';
$js_vars['type']      = 'select';
echo javascript('js_calendar', $js_vars);

**** Month and day MUST be in double digit format ****
**** Text date format should be in YYYY/MM/DD HH:MM ****
If you want to use the clock select from the calendar, use type
"select_clock" instead. You will obviously need a "hour" and "minute"
select box.


The variables
----------------------------
form_name : id of the form containing the input. The default value is
            'phpws_form' to match the Form class

date_name : name of the form input linked to the script.
            Defaults to 'date'.
            If you are using the select feature, the 3 select boxes
            need to be suffixes with _month, _day, and _year.

type      : either text, text_clock, select or select_clock. Defaults to text.
