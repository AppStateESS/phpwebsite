<?php
namespace Http;
/**
 * Temporary Redirection - to clarify further, use either SeeOtherResponse (303) or 
 * TemporaryRedirectResponse (307)
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
abstract class FoundResponse extends RedirectResponse
{
    protected function getHttpResponseCode()
    {
        return 302;
    }
}

?>
