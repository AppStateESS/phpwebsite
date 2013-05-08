<?php

namespace Database\Datatype;
/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * Description of Date
 *
 * @author matt
 */
class Date extends \Database\Datatype {

    /**
     * Loads an string variable into the default parameter.
     */
    protected function loadDefault()
    {
        $this->default = new \Variable\Date(null, 'default');
        $this->default->allowNull(true);
    }

}

?>
