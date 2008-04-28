<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

if (isset($data['question'])) {
    $data['QUESTION'] = $data['question'];
}

if (isset($data['link'])) {
    $data['LINK'] = $data['link'];
}

if (isset($data['address'])) {
    $data['ADDRESS'] = $data['address'];
}

$data['QUESTION'] = preg_replace("/(?<!\\\)'/", "\'", $data['QUESTION']);
$data['QUESTION'] = str_replace('"', '&quot;', $data['QUESTION']);


?>