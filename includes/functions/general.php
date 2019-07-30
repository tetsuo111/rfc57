<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require dirname(__FILE__).'/../../vendor/autoload.php';

/*
  $Id: general.php,v 1.16 2004/01/13 10:20:05 ptosh Exp $
*/

////
// Stop from parsing any further PHP code
  function tep_exit() {
   tep_session_close();
   exit();
  }

////
// Redirect to another page or site
  function tep_redirect($url) {
    if ( (ENABLE_SSL == true) && (getenv('HTTPS') == 'on') ) { // We are loading an SSL page
      if (substr($url, 0, strlen(HTTP_SERVER)) == HTTP_SERVER) { // NONSSL url
        $url = HTTPS_SERVER . substr($url, strlen(HTTP_SERVER)); // Change it to SSL
      }
    }

    header('Location: ' . $url);

    tep_exit();
  }

////
// Parse the data used in the html tags to ensure the tags will not break 
     function tep_parse_input_field_data($data, $parse) { 
       return strtr(trim($data), $parse); 
     } 
    
     function tep_output_string($string, $translate = false, $protected = false) { 
       if ($protected == true) { 
         return htmlspecialchars($string); 
       } else { 
         if ($translate == false) { 
           return tep_parse_input_field_data($string, array('"' => '&quot;')); 
         } else { 
           return tep_parse_input_field_data($string, $translate); 
         } 
       } 
     } 
    
     function tep_output_string_protected($string) { 
       return tep_output_string($string, false, true); 
     } 
    
     function tep_sanitize_string($string) { 
       $string = ereg_replace(' +', ' ', trim($string)); 
    
       return preg_replace("/[<>]/", '_', $string); 
     } 
    
//// 
// Error message wrapper
// When optional parameters are provided, it closes the application
// (ie, halts the current application execution task)
  function tep_error_message($error_message, $close_application = false, $close_application_error = '') {
    echo $error_message;

    if ($close_application == true) {
      die($close_application_error);
    }
  }

////
// Return all HTTP GET variables, except those passed as a parameter
  function tep_get_all_get_params($exclude_array = '') {
    //global $_GET;

    if (!is_array($exclude_array)) $exclude_array = array();

    $get_url = '';
    if (is_array($_GET) && (sizeof($_GET) > 0)) {
      reset($_GET);
      while (list($key, $value) = each($_GET)) {
        if ( (strlen($value) > 0) && ($key != tep_session_name()) && ($key != 'error') && (!in_array($key, $exclude_array)) && ($key != 'x') && ($key != 'y') ) {
          $get_url .= $key . '=' . rawurlencode(stripslashes($value)) . '&';
        }
      }
    }

    return $get_url;
  }

////
// Returns the clients browser
  function tep_browser_detect($component) {
    //global $_SERVER['HTTP_USER_AGENT'];

    return stristr($_SERVER['HTTP_USER_AGENT'], $component);
  }

////
// Output a raw date string in the selected locale date format
// $raw_date needs to be in this format: YYYY-MM-DD HH:MM:SS
// NOTE: Includes a workaround for dates before 01/01/1970 that fail on windows servers
  function tep_date_short($raw_date) {
    if ( ($raw_date == '0000-00-00 00:00:00') || ($raw_date == '') ) return false;

    $year = substr($raw_date, 0, 4);
    $month = (int)substr($raw_date, 5, 2);
    $day = (int)substr($raw_date, 8, 2);
    $hour = (int)substr($raw_date, 11, 2);
    $minute = (int)substr($raw_date, 14, 2);
    $second = (int)substr($raw_date, 17, 2);

    if (@date('Y', mktime($hour, $minute, $second, $month, $day, $year)) == $year) {
      return date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $year));
    } else {
      $base_year = tep_is_leap_year($year) ? 2036 : 2037;
      return ereg_replace((string)$base_year, $year, date(DATE_FORMAT, mktime($hour, $minute, $second, $month, $day, $base_year)));
    }
  }

////
// Parse search string into indivual objects
  function tep_parse_search_string($search_str = '', &$objects) {
    $search_str = trim(strtolower($search_str));

// Break up $search_str on whitespace; quoted string will be reconstructed later
    $pieces = split('[[:space:]]+', $search_str);
    $objects = array();
    $tmpstring = '';
    $flag = '';

    for ($k=0; $k<count($pieces); $k++) {
      while (substr($pieces[$k], 0, 1) == '(') {
        $objects[] = '(';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 1);
        } else {
          $pieces[$k] = '';
        }
      }

      $post_objects = array();

      while (substr($pieces[$k], -1) == ')')  {
        $post_objects[] = ')';
        if (strlen($pieces[$k]) > 1) {
          $pieces[$k] = substr($pieces[$k], 0, -1);
        } else {
          $pieces[$k] = '';
        }
      }

// Check individual words

      if ( (substr($pieces[$k], -1) != '"') && (substr($pieces[$k], 0, 1) != '"') ) {
        $objects[] = trim($pieces[$k]);

        for ($j=0; $j<count($post_objects); $j++) {
          $objects[] = $post_objects[$j];
        }
      } else {
/* This means that the $piece is either the beginning or the end of a string.
   So, we'll slurp up the $pieces and stick them together until we get to the
   end of the string or run out of pieces.
*/

// Add this word to the $tmpstring, starting the $tmpstring
        $tmpstring = trim(ereg_replace('"', ' ', $pieces[$k]));

// Check for one possible exception to the rule. That there is a single quoted word.
        if (substr($pieces[$k], -1 ) == '"') {
// Turn the flag off for future iterations
          $flag = 'off';

          $objects[] = trim($pieces[$k]);

          for ($j=0; $j<count($post_objects); $j++) {
            $objects[] = $post_objects[$j];
          }

          unset($tmpstring);

// Stop looking for the end of the string and move onto the next word.
          continue;
        }

// Otherwise, turn on the flag to indicate no quotes have been found attached to this word in the string.
        $flag = 'on';

// Move on to the next word
        $k++;

// Keep reading until the end of the string as long as the $flag is on

        while ( ($flag == 'on') && ($k < count($pieces)) ) {
          while (substr($pieces[$k], -1) == ')') {
            $post_objects[] = ')';
            if (strlen($pieces[$k]) > 1) {
              $pieces[$k] = substr($pieces[$k], 0, -1);
            } else {
              $pieces[$k] = '';
            }
          }

// If the word doesn't end in double quotes, append it to the $tmpstring.
          if (substr($pieces[$k], -1) != '"') {
// Tack this word onto the current string entity
            $tmpstring .= ' ' . $pieces[$k];

// Move on to the next word
            $k++;
            continue;
          } else {
/* If the $piece ends in double quotes, strip the double quotes, tack the
   $piece onto the tail of the string, push the $tmpstring onto the $haves,
   kill the $tmpstring, turn the $flag "off", and return.
*/
            $tmpstring .= ' ' . trim(ereg_replace('"', ' ', $pieces[$k]));

// Push the $tmpstring onto the array of stuff to search for
            $objects[] = trim($tmpstring);

            for ($j=0; $j<count($post_objects); $j++) {
              $objects[] = $post_objects[$j];
            }

            unset($tmpstring);

// Turn off the flag to exit the loop
            $flag = 'off';
          }
        }
      }
    }

// add default logical operators if needed
    $temp = array();
    for($i=0; $i<(count($objects)-1); $i++) {
      $temp[sizeof($temp)] = $objects[$i];

      if ( ($objects[$i] != 'and') &&
           ($objects[$i] != 'or') &&
           ($objects[$i] != '(') &&
           ($objects[$i] != ')') &&
           ($objects[$i+1] != 'and') &&
           ($objects[$i+1] != 'or') &&
           ($objects[$i+1] != '(') &&
           ($objects[$i+1] != ')') ) {
        $temp[sizeof($temp)] = ADVANCED_SEARCH_DEFAULT_OPERATOR;
      }
    }
    $temp[sizeof($temp)] = $objects[$i];
    $objects = $temp;

    $keyword_count = 0;
    $operator_count = 0;
    $balance = 0;
    for($i=0; $i<count($objects); $i++) {
      if ($objects[$i] == '(') $balance --;
      if ($objects[$i] == ')') $balance ++;
      if ( ($objects[$i] == 'and') || ($objects[$i] == 'or') ) {
        $operator_count ++;
      } elseif ( ($objects[$i]) && ($objects[$i] != '(') && ($objects[$i] != ')') ) {
        $keyword_count ++;
      }
    }

    if ( ($operator_count < $keyword_count) && ($balance == 0) ) {
      return true;
    } else {
      return false;
    }
  }

////
// Check date
  function tep_checkdate($date_to_check, $format_string, &$date_array) {
    $separator_idx = -1;

    $separators = array('-', ' ', '/', '.');
    $month_abbr = array('jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
    $no_of_days = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    $format_string = strtolower($format_string);

    if (strlen($date_to_check) != strlen($format_string)) {
      return false;
    }

    $size = sizeof($separators);
    for ($i=0; $i<$size; $i++) {
      $pos_separator = strpos($date_to_check, $separators[$i]);
      if ($pos_separator != false) {
        $date_separator_idx = $i;
        break;
      }
    }

    for ($i=0; $i<$size; $i++) {
      $pos_separator = strpos($format_string, $separators[$i]);
      if ($pos_separator != false) {
        $format_separator_idx = $i;
        break;
      }
    }

    if ($date_separator_idx != $format_separator_idx) {
      return false;
    }

    if ($date_separator_idx != -1) {
      $format_string_array = explode( $separators[$date_separator_idx], $format_string );
      if (sizeof($format_string_array) != 3) {
        return false;
      }

      $date_to_check_array = explode( $separators[$date_separator_idx], $date_to_check );
      if (sizeof($date_to_check_array) != 3) {
        return false;
      }

      $size = sizeof($format_string_array);
      for ($i=0; $i<$size; $i++) {
        if ($format_string_array[$i] == 'mm' || $format_string_array[$i] == 'mmm') $month = $date_to_check_array[$i];
        if ($format_string_array[$i] == 'dd') $day = $date_to_check_array[$i];
        if ( ($format_string_array[$i] == 'yyyy') || ($format_string_array[$i] == 'aaaa') ) $year = $date_to_check_array[$i];
      }
    } else {
      if (strlen($format_string) == 8 || strlen($format_string) == 9) {
        $pos_month = strpos($format_string, 'mmm');
        if ($pos_month != false) {
          $month = substr( $date_to_check, $pos_month, 3 );
          $size = sizeof($month_abbr);
          for ($i=0; $i<$size; $i++) {
            if ($month == $month_abbr[$i]) {
              $month = $i;
              break;
            }
          }
        } else {
          $month = substr($date_to_check, strpos($format_string, 'mm'), 2);
        }
      } else {
        return false;
      }

      $day = substr($date_to_check, strpos($format_string, 'dd'), 2);
      $year = substr($date_to_check, strpos($format_string, 'yyyy'), 4);
    }

    if (strlen($year) != 4) {
      return false;
    }

    if (!settype($year, 'integer') || !settype($month, 'integer') || !settype($day, 'integer')) {
      return false;
    }

    if ($month > 12 || $month < 1) {
      return false;
    }

    if ($day < 1) {
      return false;
    }

    if (tep_is_leap_year($year)) {
      $no_of_days[1] = 29;
    }

    if ($day > $no_of_days[$month - 1]) {
      return false;
    }

    $date_array = array($year, $month, $day);

    return true;
  }

////
// Check if year is a leap year
  function tep_is_leap_year($year) {
    if ($year % 100 == 0) {
      if ($year % 400 == 0) return true;
    } else {
      if (($year % 4) == 0) return true;
    }

    return false;
  }

////
// Return a customer greeting
  function tep_customer_greeting() {
    // 2003.03.08 Add
    if ( $_SESSION['customer_name_b'] || $_SESSION['customer_name_a'] ) {
      $s_name = $_SESSION['customer_name_b'] . ' ' . $_SESSION['customer_name_a'];
    }

    if (tep_session_is_registered('customer_name_a') && tep_session_is_registered('customer_id')) {
      $greeting_string = sprintf(TEXT_GREETING_PERSONAL, $s_name, tep_href_link(FILENAME_PRODUCTS_NEW));
    } else {
      $greeting_string = sprintf(TEXT_GREETING_GUEST, tep_href_link(FILENAME_LOGIN, '', 'SSL'), tep_href_link(FILENAME_CUSTOMER_CREATE_F, '', 'SSL'));
    }

    return $greeting_string;
  }

////
//! Send email (text/html) using MIME
// This is the central mail function. The SMTP Server should be configured
// correct in php.ini
// Parameters:
// $to_name           The name of the recipient, e.g. "Jan Wildeboer"
// $to_email_address  The eMail address of the recipient, 
//                    e.g. jan.wildeboer@gmx.de 
// $email_subject     The subject of the eMail
// $email_text        The text of the eMail, may contain HTML entities
// $from_email_name   The name of the sender, e.g. Shop Administration
// $from_email_adress The eMail address of the sender, 
//                    e.g. info@mytepshop.com

  function tep_mail_old($to_name, $to_email_address, $email_subject, $email_text, $from_email_name, $from_email_address) {
    if (SEND_EMAILS != 'true') return false;

    // Instantiate a new mail object
    $message = new email(array('X-Mailer: V3A Embedded.V2R Mailer'));

    // Build the text version
    $text = strip_tags($email_text);
    if (EMAIL_USE_HTML == 'true') {
      $message->add_html($email_text, $text);
    } else {
      $message->add_text($text);
    }

    // Send message
    $message->build_message();
    $message->send($to_name, $to_email_address, $from_email_name, $from_email_address, $email_subject);
  }

function tep_mail($to_name, $to_email_address, $email_subject, $email_text, $from_email_name, $from_email_address)
{
  $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
  try {
    mb_internal_encoding("UTF-8");
    //Server settings
    $mail->setLanguage('ja');

    $from_address = CERT_EMAIL_FROM_ADDR;

    $from_name = CERT_EMAIL_FROM_NAME;
    $mail->Encoding = CERT_EMAIL_ENCODING;
    $mail->SMTPDebug = 0;                                 // Enable verbose debug output
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host  = CERT_EMAIL_SMTP_HOST;  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = $from_address;                 // SMTP username
    $mail->Password = CERT_EMAIL_PASSWORD;
    $mail->SMTPSecure = CERT_EMAIL_SMTP_SECURE;                            // Enable TLS encryption, `ssl` also accepted
//    $mail->SMTPSecure = false;
//    $mail->SMTPAutoTLS = false;
    // Enable TLS encryption, `ssl` also accepted
    $mail->Port = CERT_EMAIL_PORT;                                    // TCP port to connect to(465,587)
    $mail->CharSet = CERT_EMAIL_CHARSET;
    //Recipients
    $mail->setFrom($from_address, $from_name);
    $mail->addAddress($to_email_address, $to_name);     // Add a recipient
//      $mail->addAddress('ellen@example.com');               // Name is optional
    $mail->addReplyTo($from_address,$from_name);
//      $mail->addCC('cc@example.com');
//      $mail->addBCC('bcc@example.com');

    //Attachments
//      $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//      $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = mb_encode_mimeheader($email_subject);
//      $mail->Body = 'This is the HTML message body <b>in bold!</b>';
    $email_html = nl2br($email_text);
    $mail->Body = mb_convert_encoding($email_html,'UTF8');
    $mail->AltBody = $email_text;
//      $mail->Body = mb_convert_encoding(mb_convert_kana($email_text, "KV"), 'JIS', 'UTF-8');;
//      $mail->AltBody = mb_convert_encoding(mb_convert_kana($email_text, "KV"), 'JIS', 'UTF-8');;

//    echo '<pre>';
//    var_dump($mail);
//    echo '</pre>';
    $mail->send();
//      echo 'Message has been sent';
  } catch (Exception $e) {
//    echo 'Message could not be sent. Mailer Error: ', nl2br($mail->ErrorInfo);
  }
}


function tep_create_random_value($length, $type = 'mixed') {
    if ( ($type != 'mixed') && ($type != 'chars') && ($type != 'digits')) return false;

    $rand_value = '';
    while (strlen($rand_value) < $length) {
      if ($type == 'digits') {
        $char = tep_rand(0,9);
      } else {
        $char = chr(tep_rand(0,255));
      }
      if ($type == 'mixed') {
        //if (eregi('^[a-z0-9]$', $char)) $rand_value .= $char;
        if (preg_match('(^[a-z0-9]$)', $char)) $rand_value .= $char;
      } elseif ($type == 'chars') {
        //if (eregi('^[a-z]$', $char)) $rand_value .= $char;
        if (preg_match('(^[a-z]$)', $char)) $rand_value .= $char;
      } elseif ($type == 'digits') {
        //if (ereg('^[0-9]$', $char)) $rand_value .= $char;
        if (preg_match('(^[0-9]$)', $char)) $rand_value .= $char;
      }
    }

    return $rand_value;
  }

  function tep_output_warning($warning) {
    new errorBox(array(array('text' => tep_image(DIR_WS_ICONS . 'warning.gif', ICON_WARNING) . ' ' . $warning)));
  }

  function tep_array_to_string($array, $exclude = '', $equals = '=', $separator = '&') {
    if (!is_array($exclude)) $exclude = array();

    $get_string = '';
    if (sizeof($array) > 0) {
      while (list($key, $value) = each($array)) {
        if ( (!in_array($key, $exclude)) && ($key != 'x') && ($key != 'y') ) {
          $get_string .= $key . $equals . $value . $separator;
        }
      }
      $remove_chars = strlen($separator);
      $get_string = substr($get_string, 0, -$remove_chars);
    }

    return $get_string;
  }

  function tep_not_null($value) {
    if (is_array($value)) {
      if (sizeof($value) > 0) {
        return true;
      } else {
        return false;
      }
    } else {
      if (($value != '') && ($value != 'NULL') && (strlen(trim($value)) > 0)) {
        return true;
      } else {
        return false;
      }
    }
  }

  function tep_string_to_int($string) {
    return (int)$string;
  }

////
// Return a random value
  function tep_rand($min = null, $max = null) {
    static $seeded;

    if (!isset($seeded)) {
      mt_srand((double)microtime()*1000000);
      $seeded = true;
    }

    if (isset($min) && isset($max)) {
      if ($min >= $max) {
        return $min;
      } else {
        return mt_rand($min, $max);
      }
    } else {
      return mt_rand();
    }
  }

  // Convert "zen-kaku" alphabets and numbers to "han-kaku"
  // tamura 2002/12/30
  function tep_an_zen_to_han($string) {
    return mb_convert_kana($string, "a");
  }

////
// Return fullname
  function tep_get_fullname($name_a, $name_b) {
    $separator = ' ';
    return $name_b.$separator.$name_a;
  }
?>
