<?php

//----- CIS処理 -----//
//▼配送先を取得
$query =  tep_db_query("
	SELECT
		IFNULL(`m00`.`phone1`,`m00`.`hphone1`) AS `mphone`,
		`m01`.`adrpost`,
		`m01`.`adr1`,
		`m01`.`adr2`,
		`m01`.`adr3`,
		`m01`.`adr4`
	FROM      `".TABLE_MEM00000."` `m00`
	LEFT JOIN `".TABLE_MEM00001."` `m01` ON `m01`.`memberid` = `m00`.`memberid`
	WHERE `m00`.`memberid` = '".tep_db_input($user_id)."'
");

if($f = tep_db_fetch_array($query)){
	$po = explode('-',$f['adrpost']);
}

//▼登録内容
$zip_a    = ($p_ssip == 'a')? $po[0] : $_POST['o_zip_a'];
$zip_b    = ($p_ssip == 'a')? $po[1] : $_POST['o_zip_b'];
$ss_zip   = ($p_ssip == 'a')? $f['adrpost'] : $_POST['o_zip_a'].'-'.$_POST['o_zip_b'];
$ss_pref  = ($p_ssip == 'a')? $f['adr1']    : $_POST['o_pref'];
$ss_city  = ($p_ssip == 'a')? $f['adr2']    : $_POST['o_city'];
$ss_area  = ($p_ssip == 'a')? $f['adr3']    : $_POST['o_area'];
$ss_strt  = ($p_ssip == 'a')? $f['adr4']    : $_POST['o_strt'];
$ss_phone = ($p_ssip == 'a')? $f['mphone']  : $_POST['o_phone'];
$ss_name  = ($p_ssip == 'a')? $_COOKIE['user_name'] : $_POST['o_name'];

//▼登録用配列
$ssip_ar = array(
	'otheradrpost' => $ss_zip,
	'otheradr1'    => $ss_pref,
	'otheradr2'    => $ss_city,
	'otheradr3'    => $ss_area,
	'otheradr4'    => $ss_strt,
	'otherphone'   => $ss_phone,
	'othername1'   => $ss_name
);

//▼配送先登録
$wp_set = "`memberid` = '".tep_db_input($user_id)."'";		//検索設定
tep_db_perform(TABLE_MEM00001,$ssip_ar,'update',$wp_set);	//登録実行


/*----- GTW設定 -----*/
//▼配送先の登録
$shipping_ar = array(
	'position_id'               => $position_id,
	'user_id'                   => $user_id,
	'user_o_shipping_zip_a'     => $zip_a,
	'user_o_shipping_zip_b'     => $zip_b,
	'user_o_shipping_pref'      => $ss_pref,
	'user_o_shipping_city'      => $ss_city,
	'user_o_shipping_area'      => $ss_area,
	'user_o_shipping_name'      => $ss_name,
	'user_o_shipping_tel'       => $ss_phone,
	'user_o_shipping_condition' => '1',
	'date_create'               => 'now()',
	'state'                     => '1'
);

//▼データ確認
$db_table_sip = TABLE_USER_O_SHIPPING;
$query =  tep_db_query("
	SELECT
		`user_o_shipping_id` AS `sip_id`
	FROM  `".$db_table_sip."`
	WHERE `user_id` = '".tep_db_input($user_id)."'
	AND   `state`   = '1'
");

//▼DB更新
if($s = tep_db_fetch_array($query)){
	//▼更新配列
	$del_ar    = array('date_update'=>'now()','state'=>'y');
	
	//▼検索設定
	$w_set_sip = "`user_o_shipping_id` = '".tep_db_input($s['sip_id'])."' AND `state`='1'";
	tep_db_perform($db_table_sip,$del_ar,'update',$w_set_sip);
}

//▼DB登録
tep_db_perform($db_table_sip,$shipping_ar);

?>