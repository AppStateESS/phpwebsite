<?php

namespace Database\Engine\pgsql;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Group extends \Database\Group {

    /**
     * GROUP_SET is supported in Postgresql but the code isn't here yet
     *
     * @param integer $type
     * @return boolean
     */
    public function allowedType($type)
    {
        return in_array($type,
                array(GROUP_BASE, GROUP_ROLLUP, GROUP_CUBE));
    }

     /**
     *
     * @return string
     * @throws \Exception If the wrong group type is set
     */
    public function __toString()
    {
        // GROUP_BASE leaves this as null
        $type = null;

        if ($this->type == GROUP_ROLLUP) {
            $type = 'ROLLUP';
        } elseif ($this->type == GROUP_CUBE) {
            $type = 'CUBE';
        } elseif ($this->type != GROUP_BASE) {
            throw new \Exception(t('Unknown group type'));
        }
        $fields = implode(',', $this->fields);
        return "GROUP BY $type($fields)";
    }

}

?>
