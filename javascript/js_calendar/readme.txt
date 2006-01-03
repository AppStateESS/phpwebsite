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

echo '<input type="text" name="my_date" />';

$js_vars['date_name'] = 'my_date';
$js_vars['type']      = 'text';
echo javascript('js_calendar', $js_vars);

-- For a select input:
<select name='my_date_month'> <month inputs ...> </select>
<select name='my_date_day'> <day inputs ...> </select>
<select name='my_date_year'> <year inputs ...> </select>

$js_vars['date_name'] = 'my_date';
$js_vars['type']      = 'text';
echo javascript('js_calendar', $js_vars);


-- Quick and dirty
echo '<input type="text" name="date" />';
echo javascript('js_calendar');


The variables
----------------------------
date_name : name of the form input linked to the script.
            Defaults to 'date'.
            If you are using the select feature, the 3 select boxes
            need to be suffixes with _month, _day, and _year.

type      : either text or select. Defaults to text.
