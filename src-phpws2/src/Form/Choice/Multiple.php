<?php
namespace phpws2\Form\Choice;
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package phpws2
  * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Multiple extends \phpws2\Form\Choice\Select {
    /**
     * @var boolean Switches the Select into multiple mode
     */
    protected $multiple = true;

    /**
     * Adds two square brackets if the name doesn't include them.
     * @param string $name
     */
    public function setName($name)
    {
        if (!preg_match('/\[\w*\]$/', $name)) {
            $name .= '[]';
        }
        parent::setName($name);
    }
}
