<?php

if (!substr("\'", $data['content'])) {
    $data['content'] = str_replace("'", "\'", $data['content']);
}

?>