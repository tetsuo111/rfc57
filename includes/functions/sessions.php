<?php
/*
  $Id: sessions.php,v 1.1.1.1 2003/02/20 01:03:53 ptosh Exp $
*/

  if (STORE_SESSIONS == 'mysql') {
    if (!$SESS_LIFE = get_cfg_var('session.gc_maxlifetime')) {
      $SESS_LIFE = 1440;
    }

    function _sess_open($save_path, $session_name) {
      return true;
    }

    function _sess_close() {
      return true;
    }

    function _sess_read($key) {
      
      /*  140616_cut
      $qid = tep_db_query("select value from ". TABLE_SESSIONS . " where sesskey = '". $key . "' and expiry > '". time() . "'");

      $value = tep_db_fetch_array($qid);
      if ($value['value']) {
        return $value['value'];
      }
      */

      return false;
    }

    function _sess_write($key, $val) {
      /*  140616_cut
      global $SESS_LIFE;

      $expiry = time() + $SESS_LIFE;
      $value = addslashes($val);
      $qid = tep_db_query("select count(*) as total from ". TABLE_SESSIONS . " where sesskey = '". $key . "'");
      $total = tep_db_fetch_array($qid);
      
      if ($total['total'] > 0) {
        return tep_db_query("update ". TABLE_SESSIONS . " set expiry = '". $expiry . "', value = '". $value . "' where sesskey = '". $key . "'");
      } else {
        return tep_db_query("insert into ". TABLE_SESSIONS . " values ('". $key . "', '". $expiry . "', '". $value . "')");
      }
      */
    }

    function _sess_destroy($key) {
      /*  140616_cut
      return tep_db_query("delete from ". TABLE_SESSIONS . " where sesskey = '". $key . "'");
      */
    }

    function _sess_gc($maxlifetime) {
      tep_db_query("delete from ". TABLE_SESSIONS . " where expiry < '". time() . "'");
      return true;
    }

    session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
  }

  function tep_session_start() {
    return session_start();
  }

  function tep_session_register($variable) {
    //return session_register($variable);
    return true;
  }

  function tep_session_is_registered($variable) {
    //return session_is_registered($variable);
    return isset($_SESSION[$variable]);
  }

  function tep_session_unregister($variable) {
    //return session_unregister($variable);
    unset($_SESSION[$variable]);
    return true;
  }

  function tep_session_id($sessid = '') {
    if (!empty($sessid)) {
      return session_id($sessid);
    } else {
      return session_id();
    }
  }

  function tep_session_name($name = '') {
    if (!empty($name)) {
      return session_name($name);
    } else {
      return session_name();
    }
  }

  function tep_session_close() {
    if (function_exists('session_close')) {
      return session_close();
    }
  }

  function tep_session_destroy() {
    return session_destroy();
  }

  function tep_session_save_path($path = '') {
    if (!empty($path)) {
      return session_save_path($path);
    } else {
      return session_save_path();
    }
  }

  // return new session ID
  function tep_session_newid() {
    mt_srand( (double)microtime() * 1000000 );
    return md5( uniqid( mt_rand() ) );
  }

  // recreate new session
  function tep_session_recreate() {
    if (PHP_VERSION >= 4.1) {
      $session_backup = $_SESSION;

      unset($_COOKIE[tep_session_name()]);

      tep_session_destroy();

      if (STORE_SESSIONS == 'mysql') {
        session_set_save_handler('_sess_open', '_sess_close', '_sess_read', '_sess_write', '_sess_destroy', '_sess_gc');
      }

      $saved_value = ini_set('session.use_trans_sid','0');
      session_id(tep_session_newid());
      define('SID_NEW', tep_session_name().'='.tep_session_id());
      tep_session_start();
      ini_set('session.use_trans_sid', $saved_value);

      $_SESSION = $session_backup;
      unset($session_backup);
    }
  }

?>
