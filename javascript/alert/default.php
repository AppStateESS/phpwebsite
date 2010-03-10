<?php

if (!substr("\'", $data['content'])) {
    $data['content'] = str_replace("'", "\'", $data['content']);
}

$data['content'] = strip_tags($data['content']);

if (empty($data['label'])) {
    $headfile = $base . 'javascript/alert/head2.js';
}

?>