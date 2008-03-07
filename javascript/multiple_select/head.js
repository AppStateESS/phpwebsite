<script type="text/javascript" src="javascript/jquery/jquery.js"></script>
<script type="text/javascript" src="javascript/jquery/jquery.selectboxes.js"></script>
<script type="text/javascript">
/**
 * Uses a set of options to create a more user friendly multiple select.
 * 
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

function add_value(id, sname) {
    sel_var = '#' + id + '-select';
    selection_value = $(sel_var).val();

    if (selection_value == null) {
        return;
    }

    sel_opt = '#' + id + '-option-' + selection_value;
    select_option   = $(sel_opt);
    selection_name  = select_option.html();

    sel_box = '#' + id + '-select-box';
    select_box = $(sel_box);

    line_id = id + '-add-' + selection_value;
    select_option.remove();

    link = '<a href="#" onclick="remove(\'' + id + '\', \'' + selection_value + '\'); return false;">' + selection_name + '</a>';
    input = '<input id="' + id + '-hidden" type="hidden" name="' + sname + '[]" value="' + selection_value  + '" />';
    
    select_box.append('<div id="' + line_id + '">' + link + input + '</div>');
}


function remove(id, value)
{
    selection_value = $('#' + id + '-select');
    line_name = '#' + id + '-add-' + value;
    line_item = $(line_name);
    selection_name = $(line_name + ' a').html();

    selection_value.prepend('<option id="' + id + '-option-' + value + '" value="' +  value + '">' + selection_name + '</option>');
    selection_value.sortOptions();
    line_item.remove();
}

</script>
