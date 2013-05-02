<?php

namespace Database\Engine\pgsql;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Group extends \Database\Group {

    public function allowedType($type)
    {
        return in_array($type,
                array(GROUP_BASE, GROUP_ROLLUP, GROUP_CUBE, GROUP_SET));
    }

}

?>
