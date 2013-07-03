<?php

/**
 * View assists developers with the display of their content.
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

interface View {
    /**
     * This function must return a STRING REPRESENTATION of the set data.
     *
     * @return string The rendered data
     */
    public function render();

    /**
     * This function must return the Content-type of the data after it is 
     * rendered.  The Content-type will be used in determining how to decorate 
     * your view.
     *
     * @return string The MIME-type of the rendered view
     */
    public function getContentType();
}

?>
