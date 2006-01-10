<?php

if (!substr("\'", $data['content'])) {
    $data['content'] = str_replace("'", "\'", $data['content']);
}

if (empty($data['label'])) {
    $bodyfile = $base . 'javascript/alert/body2.js';
 }

?>