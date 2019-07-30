<?php

/*
  $Id: application_bottom.php,v 1.1.1.1 2003/02/20 01:03:53 ptosh Exp $
*/

// close session (store variables)
tep_session_close();
/*
  if (PAGE_PARSE_TIME == 'true') {
    $time_start = explode(' ', PAGE_PARSE_START_TIME);
    $time_end = explode(' ', microtime());
    $parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
    error_log(strftime(PARSE_DATE_TIME_FORMAT) . ' - ' . getenv('REQUEST_URI') . ' (' . $parse_time . 's)' . "\n", 3, PAGE_PARSE_TIME_LOG);

    if (DISPLAY_PAGE_PARSE_TIME == 'true') {
      echo '<span class="smallText">Parse Time: ' . $parse_time . 's</span>';
    }
  }

  if ( (GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded == true) && ($ini_zlib_output_compression < 1) ) {
    if ( (PHP_VERSION < '4.0.4') && (PHP_VERSION >= '4') ) {
      tep_gzip_output(GZIP_LEVEL);
    }
  }
*/
?>
