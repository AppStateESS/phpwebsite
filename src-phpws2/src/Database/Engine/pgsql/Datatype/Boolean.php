<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace phpws2\Database\Engine\pgsql\Datatype;

/**
 * Description of Boolean
 *
 * @author Matthew McNaney <mcnaneym@appstate.edu>
 */
class Boolean extends \phpws2\Database\Datatype\Boolean
{
       /**
     * Checks some conditionals for the default value.
     * @return string
     */
    public function getDefaultString()
    {
        if (is_null($this->default)) {
            return null;
        }
        if ($this->default->IsNull() && !$this->is_null) {
            return 'default false';
        }
        return $this->default ? 'default true' : 'default false';
    }
}
