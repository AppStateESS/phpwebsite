<?php
/**
 * @version $Id$
 * @author  Matt McNaney <mcnaney at gmail dot com>
 * @package Core
 */

class javascript_check_all extends Javascript {
    /**
     * Type of check all interface.
     * button, link, or checkbox
     * @var string
     */
    private $type = 'button';

    /**
     * Name of checkboxes to flip
     * @var string
     */
    private $checkbox_name = null;

    /**
     * Class of checkboxes to flip
     * @var unknown_type
     */
    private $checkbox_class = null;

    /**
     * Set the check all interface type
     * @param mixed $type
     */
    public function setType($type)
    {
        switch (gettype($type)) {
            case 'string':
                $type = strtolower($type);
                if ($type == 'button' ||
                $type == 'link' ||
                $type == 'checkbox') {
                    $this->type = $type;
                }
                break;

            case 'object':

                break;

            default:

                break;
        }
    }

    public function setCheckName($name)
    {
        $this->checkbox_name = $name;
    }

    public function setCheckClass($class)
    {
        $this->checkbox_class = $class;
    }


    public function loadDemo()
    {
        $demo_code = <<<EOF
\$js = new Javascript('check_all');
\$js->setType('button'); // button is default, may also use link or checkbox
\$js->setCheckClass('fruits');

\$js2 = new Javascript('check_all');
\$js2->setType('link');
\$js2->setCheckClass('veggies');
EOF;
        $js = Javascript::factory('check_all');
        $js->setType('button'); // button is default, may also use link or checkbox
        $js->setCheckClass('fruits');

        $js2 = Javascript::factory('check_all');
        $js2->setType('link');
        $js2->setCheckClass('veggies');

        $js->prepare();
        $js2->prepare();

        $demo1 = (string)$js;
        $demo2 = (string)$js2;

        $demo_example = <<<EOF
<table cellpadding="6"><tr><td>
<p><input type="checkbox" name="apple" class="fruits" value="1" /> Apple<br />
<input type="checkbox" name="banana" class="fruits" value="1" /> Banana<br />
<input type="checkbox" name="grape" class="fruits" value="1" /> Grape<br />
<input type="checkbox" name="melon" class="fruits" value="1" /> Melon<br />
<input type="checkbox" name="orange" class="fruits" value="1" /> Orange</p>
        $demo1
</td>
<td><p><input type="checkbox" name="carrot" class="veggies" value="1" /> Carrot<br />
<input type="checkbox" name="pepper" class="veggies" value="1" /> Pepper<br />
<input type="checkbox" name="lettuce" class="veggies" value="1" /> Lettuce<br />
<input type="checkbox" name="celery" class="veggies" value="1" /> Celery<br />
<input type="checkbox" name="cucumber" class="veggies" value="1" /> Cucumber</p>
        $demo2
</td>
</tr>
</table>
EOF;
        $this->prepare();
        $this->setDemoCode($demo_code);
        $this->setDemoExample($demo_example);
        $this->setBodyScript(null);
    }

    public function prepare()
    {
        $event_name = $this->checkbox_class . '-checkall';
        $uncheck_name = dgettext('core', 'Uncheck all');
        $check_name = dgettext('core', 'Check all');

        $head = <<<EOF
\$(document).ready(function() {
    var checked_vars = new Array();
    \$('.check-all').click(function() {
        class_name = $(this).attr('id');
        class_name = class_name.replace('-checkall', '');

        if (checked_vars[class_name]) {
            if (this.tagName == 'INPUT') {
                $(this).attr('value', '$check_name');
            } else {
                $(this).html('$check_name');
            }
            $('.' + class_name).attr('checked', '');
            checked_vars[class_name] = 0;
        } else {
            if (this.tagName == 'INPUT') {
                $(this).attr('value', '$uncheck_name');
            } else {
                $(this).html('$uncheck_name');
            }
            $('.' + class_name).attr('checked', 'checked');
            checked_vars[class_name] = 1;
        }
        return false;
    });
});
EOF;
        $this->setHeadScript($head, true, true);

        switch ($this->type) {
            case 'link':
                $body = '<a href="#" class="check-all" id="' . $event_name . '">' . $check_name . '</a>';
                break;

            case 'checkbox':
            case 'button':
                $body = '<input type="'.$this->type.'" class="check-all" id="' . $event_name . '" name="check-all" value="'. $check_name .'" />';
                break;
        }
        $this->setBodyScript($body);
    }
}
?>