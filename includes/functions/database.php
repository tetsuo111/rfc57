<?php
/*
  $Id: database.php,v 1.1.1.1 2003/02/20 01:03:53 ptosh Exp $
  $Id: database.php,v 1.3 2007/02/17 07:53:30 ptosh Exp $
*/

use Carbon\Carbon;

function tep_db_connect($server = DB_SERVER, $username = DB_SERVER_USERNAME, $password = DB_SERVER_PASSWORD, $database = DB_DATABASE, $link = 'db_link') {
    global $$link;

	mb_language("uni"); //���������R�[�h��ύX�@130730
	mb_internal_encoding("utf-8"); //���������R�[�h��ύX�@130730
	mb_http_input("auto"); //���������R�[�h��ύX�@130730
	mb_http_output("utf-8"); //���������R�[�h��ύX�@130730

    if (USE_PCONNECT == 'true') {
      //$$link = mysqli_pconnect($server, $username, $password);
      $$link = mysqli_connect($server, $username, $password);
    } else {
      $$link = mysqli_connect($server, $username, $password);
    }

    //if ($$link) mysqli_select_db($database);
    if ($$link) mysqli_select_db($$link,$database);
      //mysqli_query("SET NAMES utf8",$$link); //�N�G���̕����R�[�h��ݒ�@130730
      mysqli_query($$link,"SET NAMES utf8"); //�N�G���̕����R�[�h��ݒ�@130730
    //@mysqli_query("SET NAMES utf8");
    return $$link;
  }

  function tep_db_close($link = 'db_link') {
    global $$link;

    return mysqli_close($$link);
  }

  function tep_db_error($query, $errno, $error) { 
    die('<font color="#000000"><b>' . $errno . ' - ' . $error . '<br><br>' . $query . '<br><br><small><font color="#ff0000">[TEP STOP]</font></small><br><br></b></font>');
  }

  function tep_db_query($query, $link = 'db_link') {
    global $$link;

    if (DB_TRANSACTIONS == 'true') {
      error_log('QUERY ' . $query . "\n", 3, PAGE_PARSE_TIME_LOG);
    }

    //$result = mysqli_query($query, $$link) or tep_db_error($query, mysqli_errno(), mysqli_error());
    //$result = mysqli_query($query, $$link);
    $result = mysqli_query($$link, $query);

//    echo $query.'<Br>';
    if (DB_TRANSACTIONS == 'true') {
       $result_error = mysqli_error();
       error_log('RESULT ' . $result . ' ' . $result_error . "\n", 3, PAGE_PARSE_TIME_LOG);
    }

    return $result;
  }

  function tep_db_perform($table, $data, $action = 'insert', $parameters = '', $link = 'db_link') {


    reset($data);
    if ($action == 'insert') {
      $query = 'insert into ' . $table . ' (';
      while (list($columns, ) = each($data)) {
        $query .= $columns . ', ';
      }
      $query = substr($query, 0, -2) . ') values (';
      reset($data);
      while (list(, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= 'now(), ';
            break;
          case 'null':
            $query .= 'null, ';
            break;
          default:
            $query .= '\'' . tep_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ')';
    } elseif ($action == 'update') {
      $query = 'update ' . $table . ' set ';
      while (list($columns, $value) = each($data)) {
        switch ((string)$value) {
          case 'now()':
            $query .= $columns . ' = now(), ';
            break;
          case 'null':
            $query .= $columns .= ' = null, ';
            break;
          default:
            $query .= $columns . ' = \'' . tep_db_input($value) . '\', ';
            break;
        }
      }
      $query = substr($query, 0, -2) . ' where ' . $parameters;
    }

//    echo $query;



    return tep_db_query($query, $link);
  }

  function tep_db_fetch_array($db_query) {
	return mysqli_fetch_array($db_query,MYSQLI_ASSOC);
  }

  function tep_db_trancate($table,$link='db_link'){
    return tep_db_query('TRUNCATE TABLE '.$table,$link);
  }
  
  function tep_db_num_rows($db_query) {
	return mysqli_num_rows($db_query);
  }

  function tep_db_data_seek($db_query, $row_number) {
    return mysqli_data_seek($db_query, $row_number);
  }

  function tep_db_insert_id($link = 'db_link') {
    global $$link;
    return mysqli_insert_id($$link);
  }

  function tep_db_free_result($db_query) {
    return mysqli_free_result($db_query);
  }

  function tep_db_fetch_fields($db_query) {
    return mysqli_fetch_field($db_query);
  }

  function tep_db_output($string) {
    return stripslashes($string);
  }

  /* r5
  function tep_db_input($string) {
    return addslashes($string);
  }
  r5_eof r8 */
  function tep_db_input($string, $link = 'db_link') {
    global $$link;

    if (function_exists('mysqli_real_escape_string')) {
      return mysqli_real_escape_string($$link, $string);
    } elseif (function_exists('mysqli_escape_string')) {
      return mysqli_escape_string($string);
    }

    return addslashes($string);
  }
  //r8_eof

  function tep_db_prepare_input($string) {
    if (is_string($string)) {
      return trim(stripslashes($string));
    } elseif (is_array($string)) {
      reset($string);
      while (list($key, $value) = each($string)) {
        $string[$key] = tep_db_prepare_input($value);
      }
      return $string;
    } else {
      return $string;
    }
  }

  //記事と会員区分の紐付け（毎回一旦削除して作り直す）
  function tep_db_tagging($article_type,$article_id,$target_array){

    $query = "DELETE FROM tag00000 WHERE article_type='$article_type' AND article_id='$article_id'";

    tep_db_query($query);

    foreach($target_array as $t){
      $data = [
          'article_type' => $article_type,
          'article_id' => $article_id,
          'bctype' => $t,
          'inputdate' => Carbon::now(),
          'editdate' => Carbon::now()
      ];
      tep_db_perform('tag00000',$data,'insert');
    }

  }

?>
