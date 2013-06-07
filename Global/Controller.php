<?php

/**
 * Controller Interface.  An instance of Controller must be returned by the 
 * getController method of your Module instance.  This is the entry point into 
 * your actual module code.  Your module may provide as many different 
 * Controller objects as it needs to.
 *
 * Please note that this interface is available for convenience and for backward 
 * compatibility, but we STRONGLY recommend that you extend from the 
 * HttpController abstract class instead.
 *
 * @package Global
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

interface Controller
{
    /**
     * The routing process will eventually call this function, which is the 
     * entry point into your module code.
     *
     * @param $request Request The Request Object
     */
    public function execute(Request $request);
}

?>
