<?php

$keys = array_keys($data);
$js_var_array[$keys[0]] = $data[$keys[0]];
$js_var_array[$keys[1]] = $data[$keys[1]];

$sort = TRUE;
if(isset($js_var_array['sorting']))
  $sort = $js_var_array['sorting'];

$js_var_array[$keys[0]] = array_diff($js_var_array[$keys[0]], $js_var_array[$keys[1]]);

$extra_options_unsel = '';
$extra_options_sel = '';
if(isset($data['extra_options_unsel'])) 
   $extra_options_unsel = $data['extra_options_unsel'];

if(isset($data['extra_options_unsel'])) 
   $extra_options_unsel = $data['extra_options_unsel'];

$js = "<table border=\"0\" cellpadding=\"4\" cellspacing=\"0\">\n
<tr><td><select name=\"" . $keys[0] . "[]\" multiple=\"multiple\" size=\"10\" $extra_options_unsel ondblclick=\"move(this.form.elements['" . $keys[0] . "[]'],this.form.elements['" . $keys[1] . "[]'], ".$sort.")\">\n";

foreach($js_var_array[$keys[0]] as $key => $value) {
    $js .= "<option value=\"" . $key . "\">" . $value . "</option>\n";
}

$js .= "</select></td>\n

<td align=\"center\" valign=\"middle\">\n
<input type=\"button\" onclick=\"move(this.form.elements['" . $keys[0] . "[]'],this.form.elements['" . $keys[1] . "[]'], ".$sort.")\" value=\"" . _('Add') . " &gt;&gt;\" />\n
<br /><br />\n
<input type=\"button\" onclick=\"move(this.form.elements['" . $keys[1] . "[]'],this.form.elements['" . $keys[0] . "[]'], ".$sort.")\" value=\"&lt;&lt; " . _('Remove') . "\" />\n
</td>\n";

$js .= "<td><select name=\"" . $keys[1] . "[]\" multiple=\"multiple\" size=\"10\" $extra_options_sel ondblclick=\"move(this.form.elements['" . $keys[1] . "[]'],this.form.elements['" . $keys[0] . "[]'], ".$sort.")\">\n";

foreach($js_var_array[$keys[1]] as $key => $value) {
    $js .= "<option value=\"" . $key . "\">" . $value . "</option>\n";
}

$js .= "</select></td></tr></table>\n";

$data['SWAPPER_BODY'] = $js;

foreach($data as $key=>$value) {
    if($key != 'SWAPPER_BODY') {
        unset($data[$key]);
    }
}

?>