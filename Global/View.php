<?php

/**
 * View assists developers with the display of their content.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class View extends Data {

    /**
     * Returns the data in this object as XML
     * @return string
     */
    public function getXML()
    {

    }

    /**
     * Returns the data in this object in JSON encoded format
     * @return string
     */
    public function getJSON()
    {
        return json_encode($this->getVars());
    }

    /**
     * Returns the data in this object as HTML
     * @return string
     */
    public function getHTML()
    {

    }
}

?>