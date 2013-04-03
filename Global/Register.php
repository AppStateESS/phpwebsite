<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

abstract class Register extends Data {

    /**
     * Module object of module wanting to register to the parent object
     * @var ModuleAbstract
     */
    protected $registrant;

    /**
     *
     * @param ModuleAbstract $module The module other module register to;
     * the parent of this register object
     */
    public function __construct($module, ModuleAbstract $registrant)
    {
        $this->setParent($module);
        $this->setRegistrant($registrant);
    }

    protected function setRegistrant(ModuleAbstract $registrant)
    {
        $this->registrant = $registrant;
    }
}
?>
