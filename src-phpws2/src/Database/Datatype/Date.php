<?php

namespace phpws2\Database\Datatype;
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
class Date extends \phpws2\Database\Datatype {

    /**
     * Loads an string variable into the default parameter.
     */
    protected function loadDefault()
    {
        $this->default = new \phpws2\Variable\DateVar(null, 'default');
        $this->default->allowNull(true);
    }

}

