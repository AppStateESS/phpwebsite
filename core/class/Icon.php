<?php
/**
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 *
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class Icon {

    public function __construct($type)
    {
        // params contain information on the current icon set
        static $params = null;

        if (empty($params)) {
            $this->loadParams();
        }
    }


    public function __toString()
    {
        return sprintf('<img src="%s" />', $thing);
    }

    private function loadParams()
    {
        $filename = PHPWS_SOURCE_HTTP . 'core/conf/icons.php';
    }
}

?>