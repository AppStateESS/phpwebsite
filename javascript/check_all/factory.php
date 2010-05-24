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
EOF;

        $demo_example = <<<EOF
<p><input type="checkbox" name="apple" class="fruits" value="1" /> Apple<br />
<input type="checkbox" name="banana" class="fruits" value="1" /> Banana<br />
<input type="checkbox" name="grape" class="fruits" value="1" /> Grape<br />
<input type="checkbox" name="melon" class="fruits" value="1" /> Melon<br />
<input type="checkbox" name="orange" class="fruits" value="1" /> Orange</p>
EOF;
        $this->setDemoCode($demo_code);
        $this->setDemoExample($demo_example);
        $this->setCheckClass('fruits');
    }

    public function loadScript()
    {
        $this->setType('link');
        $event_name = $this->checkbox_class . '-checkall';
        $uncheck_name = dgettext('core', 'Uncheck all');
        $check_name = dgettext('core', 'Check all');

        switch ($this->type) {
            case 'link':
                $body = '<a href="." id="' . $event_name . '">' . $check_name . '</a>';
                break;

            case 'checkbox':
            case 'button':
                $body = '<input type="'.$this->type.'" class="check-all" id="' . $event_name . '" name="check-all" value="'. $check_name .'" />';
                break;
        }


        $head = <<<EOF
\$(document).ready(function() {
    var checked_vars = new Array();
    \$('#$event_name').click(function() {
        if (checked_vars['$event_name']) {
            if (this.tagName == 'INPUT') {
                $('#$event_name').attr('value', '$check_name');
            } else {
                $('#$event_name').html('$check_name');
            }
            $('.$this->checkbox_class').attr('checked', '');
            checked_vars['$event_name'] = 0;
        } else {
            if (this.tagName == 'INPUT') {
                $('#$event_name').attr('value', '$uncheck_name');
            } else {
                $('#$event_name').html('$uncheck_name');
            }
            $('.$this->checkbox_class').attr('checked', 'checked');
            checked_vars['$event_name'] = 1;
        }
        return false;
    });
});
EOF;

        $this->addHeadScript($head, true);
        $this->setBodyScript($body);
    }

    public function get()
    {

        if (1) {
            return sprintf('<input type="%s" name="check_all" value="{check_label}" onclick="CheckAll(this, \'{checkbox_name}\');" />', 1 );
        }

    }
}
?>