<?php

namespace Database\Datatype;

/*
 * See docs/AUTHORS and docs/COPYRIGHT for relevant info.
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 *
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class Varchar extends Character {

    protected $varchar = true;

    public function __construct(\Database\Table $table, $name, $length = 255)
    {
        parent::__construct($table, $name);
        $this->size->setRange(0, 255);
        $this->setSize($length);
        $this->default->setLimit($length);
    }

}

?>
