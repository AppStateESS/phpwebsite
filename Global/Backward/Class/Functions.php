<?php

/**
 * Procedural functions used in previous versions of phpWebSite
 */

function javascript($directory, $data = NULL, $base = null)
{
    return Layout::getJavascript($directory, $data, $base);
}

?>
