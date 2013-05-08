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
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

class Character extends \Database\Datatype {

    /**
     * If true, char is actually a varchar!
     * @var boolean
     */
    protected $varchar = false;

    /**
     * Creates a database varchar
     * @param string $name
     * @param integer $length
     */
    public function __construct(\Database\Table $table, $name, $length=255)
    {
        parent::__construct($table, $name);
        $this->size = new \Variable\Integer(null, $this->varName());
        $this->size->setRange(0, 255);
        $this->setSize($length);
        $this->default = new \Variable\String(null, $this->varName());
        $this->default->setLimit($length);
        $this->default->allowNull(true);
    }

    /**
     * Loads an string variable into the default parameter.
     */
    protected function loadDefault()
    {
        $this->default = new \Variable\String(null, 'default');
    }

    private function varName()
    {
        return $this->varchar ? 'VARCHAR' : 'CHAR';
    }
}

?>
