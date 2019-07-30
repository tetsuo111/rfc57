<?php
/*
  $Id: html_output.php,v 1.5 2004/04/25 02:29:01 ptosh Exp $
  $Id: html_output.php,v 1.6 2004/11/05 08:31:35 ptosh Exp $
*/
////XSSセキュリティ
foreach($_POST as $key => $row) {
	
	if(count($row) > 0){
		foreach($row AS $k=>$v){
			$_POST[$k] = htmlspecialchars($v);
		}
	}else{
		$_POST[$key] = htmlspecialchars($row);
	}
}
foreach($_GET as $key => $value) {
	$_GET[$key] = htmlspecialchars($value);
}

////
// The HTML href link wrapper function
  function tep_href_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true) {

  // r8
  // Start IN-Solution SpiderKiller
    global $spider_agent, $spider_ip, $spider_checked_for_spider, $spider_kill_sid, $HTTP_SERVER_VARS;
  // END IN-Solution SpiderKiller
  // r8_eof
    if (!tep_not_null($page)) {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine the page link!<br><br>');
    }

    if ($connection == 'NONSSL') {
      $link = HTTP_SERVER . DIR_WS_DOCUMENT_ROOT;
    } elseif ($connection == 'SSL') {
      if (ENABLE_SSL == true) {
        $link = HTTPS_SERVER . DIR_WS_DOCUMENT_ROOT;
      } else {
        $link = HTTP_SERVER . DIR_WS_DOCUMENT_ROOT;
      }
    } else {
      die('</td></tr></table></td></tr></table><br><br><font color="#ff0000"><b>Error!</b></font><br><br><b>Unable to determine connection method on a link!<br><br>Known methods: NONSSL SSL</b><br><br>');
    }

    if (tep_not_null($parameters)) {
      $link .= $page . '?' . tep_output_string($parameters);
      $separator = '&';
    } else {
      $link .= $page;
      $separator = '?';
    }

    while ( (substr($link, -1) == '&') || (substr($link, -1) == '?') ) $link = substr($link, 0, -1);

// Add the session ID when moving from HTTP and HTTPS servers or when SID is defined
    if ( (ENABLE_SSL == true ) && ($connection == 'SSL') && ($add_session_id == true) ) {
      $sid = tep_session_name() . '=' . tep_session_id();
    } elseif ( ($add_session_id == true) && (tep_not_null(SID)) ) {
      $sid = defined('SID_NEW') ? SID_NEW : SID; // 2004/04/25
    }

  //r8
  // Start IN-Solution SpiderKiller
    if (SPIDER_USE_KILLER == 'true') {
      // Did we check before?
      if ( !$spider_checked_for_spider ) {
        $spider_checked_for_spider = true;
        // get useragent and force to lowercase just once
        $useragent = strtolower($HTTP_SERVER_VARS["HTTP_USER_AGENT"]);
        if (is_array($spider_agent)) {
          foreach ($spider_agent as $name) {
            if (!(strpos($useragent, strtolower($name)) === false)) {
              // found a spider, kill the sid
              $spider_kill_sid = true;
              break;
            }
          }
        }
        // get remote_addr
        $userip = $HTTP_SERVER_VARS["REMOTE_ADDR"];
        if (is_array($spider_ip) && !$spider_kill_sid) {
          foreach ($spider_ip as $ip) {
            if (!(strpos($userip, $ip) === false)) {
              if (strpos($userip, $ip) == 0) {
                // found a spider, kill the sid
                $spider_kill_sid = true;
                break;
              }
            }
          }
        }
      }
      if ( $spider_kill_sid ) $sid = NULL;
    }
  // END IN-Solution SpiderKiller
  //r8_eof

    if ( (SEARCH_ENGINE_FRIENDLY_URLS == 'true') && ($search_engine_safe == true) ) {
      while (strstr($link, '&&')) $link = str_replace('&&', '&', $link);

      $link = str_replace('?', '/', $link);
      $link = str_replace('&', '/', $link);
      $link = str_replace('=', '/', $link);

      $separator = '?';
    }
    
    if (isset($sid)) {
      $link .= $separator . tep_output_string($sid);
    }

    return $link;
  }

////
// The HTML image wrapper function
  function tep_image($src, $alt = '', $width = '', $height = '', $parameters = '') {
    if ( (empty($src) || ($src == DIR_WS_IMAGES)) && (IMAGE_REQUIRED == 'false') ) {
      return false;
    }

// alt is added to the img tag even if it is null to prevent browsers from outputting
// the image filename as default
    $image = '<img src="' . tep_output_string($src) . '" border="0" alt="' . tep_output_string($alt) . '"';

    if (tep_not_null($alt)) {
      $image .= ' title=" ' . tep_output_string($alt) . ' "';
    }

    if ( (CONFIG_CALCULATE_IMAGE_SIZE == 'true') && (empty($width) || empty($height)) ) {
      if ($image_size = @getimagesize($src)) {
        if (empty($width) && tep_not_null($height)) {
          $ratio = $height / $image_size[1];
          $width = (int)$image_size[0] * $ratio;
        } elseif (tep_not_null($width) && empty($height)) {
          $ratio = $width / $image_size[0];
          $height = (int)$image_size[1] * $ratio;
        } elseif (empty($width) && empty($height)) {
          $width = (int)$image_size[0];
          $height = (int)$image_size[1];

// delete r8 --------------------------------------------------------------------------------------
        } elseif ($image_size[0] > $image_size[1]) {
        // 画像が横長の場合
          $ratio = $width / $image_size[0];
          $oheight = $height;
          $height = $image_size[1] * $ratio;
          if ($oheight > $height) {
            $vspace = ($oheight - $height)/2;
            $image .= ' vspace="' . intval($vspace) . '"';
          }
        } else {
        // 画像が縦長の場合
          $ratio = $height / $image_size[1];
          $owidth = $width;
          $width = $image_size[0] * $ratio;
          if ($owidth > $width) {
            $hspace = ($owidth - $width)/2;
            $image .= ' hspace="' . intval($hspace) . '"';
          }
// ---------------------------------------------------------------------------------- delete r8_eof

        }
      } elseif (IMAGE_REQUIRED == 'false') {
        return false;
      }
    }

    if (tep_not_null($width) && tep_not_null($height)) {
      $image .= ' width="' . tep_output_string($width) . '" height="' . tep_output_string($height) . '"';
    }

    if (tep_not_null($parameters)) $image .= ' ' . $parameters;

    $image .= ' />';

    return $image;
  }

////
// The HTML form submit button wrapper function
// Outputs a button in the selected language
  function tep_image_submit($image, $alt = '', $parameters = '') {

    $image_submit = '<input type="image" src="' . tep_parse_input_field_data($image, array('"' => '&quot;')) . '" border="0" alt="' . tep_parse_input_field_data($alt, array('"' => '&quot;')) . '"';

    if (tep_not_null($alt)) $image_submit .= ' title=" ' . tep_parse_input_field_data($alt, array('"' => '&quot;')) . ' "';

    if (tep_not_null($parameters)) $image_submit .= ' ' . $parameters;

    $image_submit .= '>';

    return $image_submit;
  }

////
// Output a function button in the selected language
  function tep_image_button($image, $alt = '', $parameters = '') {

    return tep_image($image, $alt, '', '', $parameters);
  }

////
// Output a separator either through whitespace, or with an image
  function tep_draw_separator($image = 'pixel_trance.gif', $width = '100%', $height = '1') {
    return tep_image($image, '', $width, $height);
  }

////
// Output a form
  function tep_draw_form($name, $action, $method = 'post', $parameters = '') {
    $form = '<form name="' . tep_parse_input_field_data($name, array('"' => '&quot;')) . '" action="' . tep_parse_input_field_data($action, array('"' => '&quot;')) . '" method="' . tep_parse_input_field_data($method, array('"' => '&quot;')) . '"';

    if (tep_not_null($parameters)) $form .= ' ' . $parameters;

    $form .= '>';

    return $form;
  }

////
// Output a form input field
  function tep_draw_input_field($name, $value = '', $parameters = '', $type = 'text', $reinsert_value = true) {
    $field = '<input type="' . tep_parse_input_field_data($type, array('"' => '&quot;')) . '" name="' . tep_parse_input_field_data($name, array('"' => '&quot;')) . '"';

    if ( ($global_value = tep_get_global_value($name)) && ($reinsert_value == true) ) { //2003-07-25 hiroshi_sato
      $field .= ' value="' . tep_parse_input_field_data($global_value, array('"' => '&quot;')) . '"'; //2003-07-25 hiroshi_sato
    } elseif (tep_not_null($value)) {
      $field .= ' value="' . tep_parse_input_field_data($value, array('"' => '&quot;')) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    return $field;
  }

////
// Output a form password field
  function tep_draw_password_field($name, $value = '', $parameters = 'maxlength="40"') {
    return tep_draw_input_field($name, $value, $parameters, 'password', false);
  }

////
// Output a selection field - alias function for tep_draw_checkbox_field() and tep_draw_radio_field()
  function tep_draw_selection_field($name, $type, $value = '', $checked = false, $parameters = '') {
    $selection = '<input type="' . tep_parse_input_field_data($type, array('"' => '&quot;')) . '" name="' . tep_parse_input_field_data($name, array('"' => '&quot;')) . '"';

    if (tep_not_null($value)) $selection .= ' value="' . tep_parse_input_field_data($value, array('"' => '&quot;')) . '"';

    if ( ($checked == true) || (tep_get_global_value($name) == 'on') || ( (isset($value)) && (tep_get_global_value($name) == $value) ) ) {
      $selection .= ' CHECKED';
    }

    if (tep_not_null($parameters)) $selection .= ' ' . $parameters;

    $selection .= '>';

    return $selection;
  }

////
// Output a form checkbox field
  function tep_draw_checkbox_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'checkbox', $value, $checked, $parameters);
  }

////
// Output a form radio field
  function tep_draw_radio_field($name, $value = '', $checked = false, $parameters = '') {
    return tep_draw_selection_field($name, 'radio', $value, $checked, $parameters);
  }

////
// Output a form textarea field
  function tep_draw_textarea_field($name, $wrap, $width, $height, $text = '', $parameters = '', $reinsert_value = true) {
    $field = '<textarea name="' . tep_parse_input_field_data($name, array('"' => '&quot;')) . '" wrap="' . tep_parse_input_field_data($wrap, array('"' => '&quot;')) . '" cols="' . tep_parse_input_field_data($width, array('"' => '&quot;')) . '" rows="' . tep_parse_input_field_data($height, array('"' => '&quot;')) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if ( ($global_value = tep_get_global_value($name)) && ($reinsert_value == true) ) {
      $field .= stripslashes($global_value);
    } elseif (tep_not_null($text)) {
      $field .= $text;
    }

    $field .= '</textarea>';

    return $field;
  }

////
// Output a form hidden field
  function tep_draw_hidden_field($name, $value = '', $parameters = '') {
    $field = '<input type="hidden" name="' . tep_output_string($name) . '"';

    if (tep_not_null($value)) {
      $field .= ' value="' . tep_output_string($value) . '"';
    } elseif($global_value = tep_get_global_value($name)) {
      $field .= ' value="' . tep_output_string(stripslashes($global_value)) . '"';
    }

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    return $field;
  }

////
// Hide form elements
  function tep_hide_session_id() {
    if (defined('SID') && tep_not_null(SID)) return tep_draw_hidden_field(tep_session_name(), tep_session_id());
  }

////
// Output a form pull down menu
  function tep_draw_pull_down_menu($name, $values, $default = '', $parameters = '', $required = false) {
    $field = '<select name="' . tep_parse_input_field_data($name, array('"' => '&quot;')) . '"';

    if (tep_not_null($parameters)) $field .= ' ' . $parameters;

    $field .= '>';

    if (empty($default)) $default = tep_get_global_value($name);

    for ($i=0, $n=sizeof($values); $i<$n; $i++) {
      $field .= '<option value="' . tep_parse_input_field_data($values[$i]['id'], array('"' => '&quot;')) . '"';
      if ($default == $values[$i]['id']) {
        $field .= ' SELECTED';
      }

      $field .= '>' . tep_parse_input_field_data($values[$i]['text'], array('"' => '&quot;', '\'' => '&#039;', '<' => '&lt;', '>' => '&gt;')) . '</option>';
    }
    $field .= '</select>';

    if ($required == true) $field .= TEXT_FIELD_REQUIRED;

    return $field;
  }

//// 2003-07-25 hiroshi_sato add
// Creates a get global value
// added for Japanese localize
  function tep_get_global_value($name) {
/*  $value = null;
    if (isset($GLOBALS[$name])) { $value = $GLOBALS[$name]; }
    elseif (isset($_GET[$name])) { $value = $_GET[$name]; }
    elseif (isset($_POST[$name])) { $value = $_POST[$name]; }
    elseif (isset($_SESSION[$name])) { $value = $_SESSION[$name]; }
    elseif (isset($_COOKIE[$name])) { $value = $_COOKIE[$name]; }
    return $value;
*/
    if ( ($value = $GLOBALS[$name]) || ($value = $_REQUEST[$name]) || ($value = $_SESSION[$name]) ) {
      return $value;
    } else {
      return null;
    }
  }
?>
