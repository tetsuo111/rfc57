<?php

$cart_page = basename($_SERVER['PHP_SELF'],'.php');

if(
	($cart_page == 'order_regular_cart')
	OR($cart_page == 'order_regular')
){
	$cart_sort = "'c'";
	$js_sort = 'c';
}else{
	$cart_sort = "'a','b'";
	$js_sort = '';
}


//----- リスト作成 -----//
//▼選択中のカート
$query_crt =  tep_db_query("
	SELECT
		`c`.`user_o_cart_id`       AS `id`,
		`c`.`plan_id`,
		`c`.`user_o_cart_number`   AS `num`,
		`c`.`user_o_cart_sort`     AS `sort`,
		`a`.`m_plan_name`          AS `name`,
		`a`.`m_plan_rank_id`       AS `rankid`,
		`a`.`m_plan_grank_id`      AS `grankid`,
		`a`.`m_plan_sum_resource`  AS `resource`,
		`a`.`m_plan_o_limit_times` AS `l_time`,
		`a`.`m_plan_o_limit_piece` AS `l_piece`,
		`a`.`m_plan_taxtype`       AS `taxtype`
	FROM `".TABLE_USER_O_CART."` `c`
	LEFT JOIN `".TABLE_M_PLAN."` `a` ON `a`.`m_plan_id` = `c`.`plan_id`
	WHERE `c`.`state`       = '1'
	AND   `c`.`user_id`     = '".tep_db_input($user_id)."'
	AND   `c`.`user_o_cart_condition` = '1'
	AND   `c`.`user_o_cart_sort` IN (".$cart_sort.")
	AND   `a`.`state`       = '1'
");


while($cr = tep_db_fetch_array($query_crt)) {
	$cart_ar[$cr['id']] = $cr;
	$for_get_plan.= (($for_get_plan)? ",'":"'").$cr['plan_id']."'";
}

//▼対応商品一覧
$query =  tep_db_query("
	SELECT
		`pi`.`m_plan_id`             AS `plan_id`,
		SUM(`ii`.`m_item_fixamount` * `pi`.`m_plan_item_num`) AS `plan_sum`,
		`ii`.`m_item_currency_id`    AS `cur_id`
	FROM       `".TABLE_M_PLAN_ITEM."` `pi`
	LEFT JOIN  `".TABLE_M_ITEM."` `ii`  ON `pi`.`m_item_id` = `ii`.`m_item_id`
	WHERE `ii`.`state` = '1'
	AND   `pi`.`state` = '1'
	AND   `pi`.`m_plan_id` IN (".$for_get_plan.")
	GROUP BY `pi`.`m_plan_id`,`ii`.`m_item_currency_id`
	ORDER BY `pi`.`m_plan_id`
");


while($a = tep_db_fetch_array($query)) {
	$plan_ar[$a['plan_id']][$a['cur_id']] = $a;
}


//▼品目一覧
$query =  tep_db_query("
	SELECT
		`m_item_id`          AS `id`,
		`m_item_name`        AS `name`,
		`m_item_fixamount`   AS `famt`,
		`m_item_currency_id` AS `cur_id`
	FROM  `".TABLE_M_ITEM."`
	WHERE `state` = '1'
	ORDER BY `m_item_id` ASC
");

while($a = tep_db_fetch_array($query)) {
	$item_ar[$a['id']] = $a;
}


//▼通貨別支払方法
$query =  tep_db_query("
	SELECT
		`m_payment_id`     AS `paymentid`,
		`m_payment_name`   AS `name`,
		`m_payment_target` AS `target`
	FROM  `".TABLE_M_PAYMENT."`
	WHERE `state` = '1'
	ORDER BY `m_payment_code` ASC
");

while($a = tep_db_fetch_array($query)) {
	
	//▼対応通貨配列
	$tmp_ar = explode('-',trim($a['target'],'-'));
	
	//▼通貨毎に　支払方法を振り分け
	foreach($tmp_ar AS $vcurid){
		$pk = $a['paymentid'];
		$pay_way_ar[$vcurid][$pk] = $a['name'];
	}
}

$pay_way_ar['a']['a'] = ' 内容ごとに個別に選択';


//▼消費税率
$tax_rate = zGetSysSetting('sys_tax_rate');
?>