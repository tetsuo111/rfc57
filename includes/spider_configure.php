<?php
/*
  $Id: spider_configure.php,v 1.2 2004/11/08 01:52:18 ptosh Exp $
*/

 define ('SPIDER_USE_KILLER','true'); // false to deactivate

 // Leave as it is ! (set flags)
 $spider_checked_for_spider = false; // DO NOT CHANGE!
 $spider_kill_sid = false;           // DO NOT CHANGE!

 // If you want to ban IPs add them like:
 // $spider_ip[] = "127.0.0.1"; 
 // $spider_ip[] = "127.0.0."; // this banns 127.0.0.xxx
 // $spider_ip[] = "127.0.0"; // this banns 127.0.0xx.xxx

 // Add more Spiders as you find them (lowercase)
 // i.e. http://www.robotstxt.org/wc/active/html/

 $spider_agent = array(
 'bot',
 'crawler',
 'empas',
 'google',
 'ia_archiver',
 'slurp',
 'spider',
 'teoma',
 );

?>