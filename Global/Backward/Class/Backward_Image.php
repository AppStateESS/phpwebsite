<?php
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package Global
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class Backward_Image extends \Tag\Image {
    public function setStyle($style) {
        $this->addStyle($style);
    }
}

?>
