<?php

define('COOKIE_HASH', md5(SITE_HASH . $_SERVER['HTTP_HOST']));

class PHPWS_Cookie {

  function write($name, $value, $time=NULL)
  {
    if (empty($time)) {
      $time = time() + 31536000;
    }
    $cookie_index = COOKIE_HASH . "[$name]";
    if (!setcookie($cookie_index, $value, $time)) {
      exit('error');
    }
  }

  function read($name)
  {
    if (isset($_COOKIE[COOKIE_HASH][$name])) {
      return $_COOKIE[COOKIE_HASH][$name];
    } else {
      return NULL;
    }
  }

  function delete($name)
  {
    $cookie_index = COOKIE_HASH . "[$name]";
    setcookie($cookie_index, '', time() - 3600);
    unset($_COOKIE[COOKIE_HASH][$name]);
  }


}

?>