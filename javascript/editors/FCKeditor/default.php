<?php

Layout::addOnLoad('FCKinit();');
$data['VALUE'] = str_replace('./images/', PHPWS_Core::getHomeHttp() . 'images/', $data['VALUE']);


?>