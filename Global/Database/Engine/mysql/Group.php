<?php

namespace Database\Engine\mysql;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Group {

    public function allowedType($type)
    {
        return in_array($type, array(GROUP_BASE, GROUP_ROLLUP));
    }

}

?>
