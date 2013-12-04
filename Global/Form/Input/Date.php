<?php

namespace Form\Input;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Date extends Text {
    public function setValue($value) {
        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $value)) {
            throw new \Exception(t('Date format is YYYY-MM-DD: %s', $value));
        }
        parent::setValue($value);
    }
}

?>