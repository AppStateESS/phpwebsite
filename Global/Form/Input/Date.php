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

    protected $min;

    protected $max;

    protected $step;

    /**
     *
     * @param string $value Date in format YYYY-MM-DD
     * @return type
     * @throws \Exception
     */
    public function setValue($value) {
        if (empty($value)) {
            return;
        }
        if (is_int($value)) {
            $value = strftime('%Y-%m-%d', strtotime($value));
        }

        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $value)) {
            throw new \Exception(t('Date format is YYYY-MM-DD: %s', $value));
        }
        parent::setValue($value);
    }

    /**
     *
     * @param string $min Date in format YYYY-MM-DD
     * @throws \Exception
     */
    public function setMin($min)
    {
        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $min)) {
            throw new \Exception(t('Date format is YYYY-MM-DD: %s', $min));
        }
        $this->min = $min;
    }

    /**
     *
     * @param string $max Date in format YYYY-MM-DD
     * @throws \Exception
     */
    public function setMax($max)
    {
        if (!preg_match('/\d{4}-\d{2}-\d{2}/', $max)) {
            throw new \Exception(t('Date format is YYYY-MM-DD: %s', $max));
        }
        $this->max = $max;
    }

    /**
     *
     * @param integer $step
     */
    public function setStep($step)
    {
        $this->step = (int) $step;
    }
}

?>