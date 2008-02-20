Check all
by Matthew McNaney
-----------------------------------------------------------------

This script makes a "Uncheck / Check all" button for a specific array of
check boxes. The example is below.

-------------------------- Example -------------------------------

<input type="checkbox" name="color[]" value="red" /> Red
<input type="checkbox" name="color[]" value="blue" /> Blue
<input type="checkbox" name="color[]" value="yellow" /> Yellow


<?php
echo javascript('check_all', array('checkbox_name' => 'color'));
?>

--------------------------- End ----------------------------------
