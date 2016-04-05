<?php
namespace Http;
/**
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class MovedPermanentlyResponse extends RedirectResponse
{
    protected function getHttpResponseCode()
    {
        return 301;
    }
}

?>
