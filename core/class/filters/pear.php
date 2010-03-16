<?php
/**
 * Uses the pear bbcode parser
 *
 * There are 6 filters
 * Basic - default
 * Email
 * Extended
 * Images
 * Links
 * Lists
 *
 * You can add/remove filters from the PEAR_BB_FILTERS.
 *
 * @version $Id$
 */

require_once 'HTML/BBCodeParser.php';

define('PEAR_BB_FILTERS', 'Email,Extended,Images,Links,Lists');

function pear_filter($text)
{
    $parser = new HTML_BBCodeParser();
    $filters = explode(',', PEAR_BB_FILTERS);
    foreach ($filters as $flt) {
        $parser->addFilter(trim($flt));
    }

    $parser->setText($text);
    $parser->parse();
    return $parser->getParsed();
}

?>