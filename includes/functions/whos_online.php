<?php
/*
  $Id: whos_online.php,v 1.3 2003/03/15 10:35:21 ptosh Exp $
*/

  function tep_update_whos_online() {
    //global $_SESSION['customer_id'];

    if (tep_session_is_registered('customer_id')) {
      $wo_customer_id = $_SESSION['customer_id'];

      $customer_query = tep_db_query("select customers_name_a, customers_name_b from ". TABLE_CUSTOMERS . " where customers_id = '". $_SESSION['customer_id'] . "'");
      $customer = tep_db_fetch_array($customer_query);

      $wo_full_name = addslashes(tep_get_fullname($customer['customers_name_a'], $customer['customers_name_b']));
    } else {
      $wo_customer_id = '';
      $wo_full_name = 'Guest';
    }

    $wo_session_id = tep_session_id();
    $wo_ip_address = getenv('REMOTE_ADDR');
    $wo_last_page_url = addslashes(getenv('REQUEST_URI'));

    $current_time = time();
    $xx_mins_ago = ($current_time - 900);

// remove entries that have expired
    tep_db_query("delete from ". TABLE_WHOS_ONLINE . " where time_last_click < '". $xx_mins_ago . "'");

    $stored_customer_query = tep_db_query("select count(*) as count from ". TABLE_WHOS_ONLINE . " where session_id = '". $wo_session_id . "'");
    $stored_customer = tep_db_fetch_array($stored_customer_query);

    if ($stored_customer['count'] > 0) {
      tep_db_query("update ". TABLE_WHOS_ONLINE . " set customer_id = '". $wo_customer_id . "', full_name = '". $wo_full_name . "', ip_address = '". $wo_ip_address . "', time_last_click = '". $current_time . "', last_page_url = '". $wo_last_page_url . "' where session_id = '". $wo_session_id . "'");
    } else {
      tep_db_query("insert into ". TABLE_WHOS_ONLINE . " (customer_id, full_name, session_id, ip_address, time_entry, time_last_click, last_page_url) values ('". $wo_customer_id . "', '". $wo_full_name . "', '". $wo_session_id . "', '". $wo_ip_address . "', '". $current_time . "', '". $current_time . "', '". $wo_last_page_url . "')");
    }
  }
?>
