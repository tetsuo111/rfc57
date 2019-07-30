<?php
/*
  $Id: password_funcs.php,v 1.1.1.1 2003/02/20 01:03:53 ptosh Exp $
*/

////
// This funstion validates a plain text password with an
// encrpyted password
//  function tep_validate_password($plain, $encrypted) {
  function tep_validate_password($plain, $encrypted, $algo = 'sha256') {
    if (tep_not_null($plain) && tep_not_null($encrypted)) {
// split apart the hash / salt
      $stack = explode(':', $encrypted);

      if (sizeof($stack) != 2) return false;

      if (tep_not_null($algo) && hash($algo, $stack[1] . $plain) == $stack[0]) {
        return true;
      } elseif (md5($stack[1] . $plain) == $stack[0]) {
        return true;
      }
    }

    return false;
  }
  function tep_validate_password2($plain, $encrypted, $algo = 'sha256') {
    if (tep_not_null($plain) && tep_not_null($encrypted) && tep_not_null($algo)) {
// split apart the hash / salt
      $stack = explode(':', $encrypted);

      if (sizeof($stack) != 2) return false;

      if (hash($algo, $stack[1] . $plain) == $stack[0]) {
        return true;
      }
    }

    return false;
  }

////
// This function makes a new password from a plaintext password. 
//  function tep_encrypt_password($plain) {
//    $password = '';
//
//    for ($i=0; $i<10; $i++) {
//      $password .= tep_rand();
//    }
//
//    $salt = substr(md5($password), 0, 2);
//
//    $password = md5($salt . $plain) . ':' . $salt;
//
//    return $password;
//  }
  function tep_encrypt_password($plain, $algo = 'sha256') {
    $password = '';

    for ($i=0; $i<intval(substr(microtime(), -1)) + 10; $i++) {
      $password .= tep_rand();
    }

    $salt = substr(md5($password), 0, 20);

    $password = hash($algo, $salt . $plain) . ':' . $salt;

    return $password;
  }
?>
