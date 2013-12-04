<?php

namespace Form\Input;

/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @subpackage Form
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class File extends Text {

    protected $accept;
    private $accepted_array;

    public function addAccept($file_type)
    {
        if (is_array($file_type)) {
            foreach ($file_type as $f) {
                $this->addAccept($f);
            }
            return $this;
        }

        if (!preg_match('@(\.\w+)|(\w+/\w+[\.\w\-]+)@', $file_type)) {
            throw new \Exception(t('Unacceptable accept type: %s', $file_type));
        }
        $this->accepted_array[] = $file_type;
        return $this;
    }

    public function getAccept()
    {
        return implode(', ', $this->accepted_array);
    }

    protected function buildTag()
    {
        if (!empty($this->accepted_array)) {
            $this->accept = $this->getAccept();
        }
        return parent::buildTag();
    }

}
?>