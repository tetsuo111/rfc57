<?php
/*=====================
DB登録
=====================*/

/*----- DB新規登録 -----*/
function zDBNewUniqueID($db_table,$array,$ai_id,$table_id){
	
	//データベースに書き込む
	tep_db_perform($db_table,$array);
	
	//データベースから追加した最新のidを取得する
	$new_id = tep_db_insert_id();
	
	//書き込む内容を用意する
	$array2 = array($table_id => $new_id);
	
	//データベースに書き込む
	tep_db_perform($db_table, $array2, 'update', "`".$ai_id."` = '".tep_db_input($new_id)."'");
	
	//追加したIDの結果を返す
	return $new_id;
}


/*----- DB更新登録 -----*/
function zDBUpdate($db_table,$data_array,$db_id){

	//---------初期設定---------
	//▼共通入力要素
	$par_increment_id = $db_table."_ai_id";
	$par_id           = $db_table."_id";
	$par_create       = "date_create";
	$par_update       = "date_update";
	$par_status       = "state";
	
	
	//▼変更データの取得
	$query = tep_db_query("
		SELECT
			*
		FROM  `".$db_table."`
		WHERE `".$par_status."` = '1'
		AND   `".$par_id."` = '".tep_db_input($db_id)."'
	");
	
	//---------データの登録---------
	//▼登録の判断
	if($tmp_ar = tep_db_fetch_array($query)){

//        echo '<pre>';
//        var_dump($tmp_ar);
//        echo '</pre>';
		//▼不要データの削除
		unset($tmp_ar[$par_increment_id]);
		unset($tmp_ar[$par_update]);
		$sql_data_array = [];
		
		//▼取得したデータの書き換え　＞ null　対策
		foreach($tmp_ar AS $key => $value){
			if(isset($data_array[$key])){
				$sql_data_array[$key] = $data_array[$key];
			}else{
				$sql_data_array[$key] = (is_null($value))? 'null':$value;
			}
		}
		
		//▼共通データの追加
		$sql_data_array[$par_create]  = "now()";
		
		
		//▼データを無効化
		$sql_old_array = array($par_status => 'y', $par_update => 'now()');
		tep_db_perform($db_table, $sql_old_array, 'update', "`".$par_id."` = '".tep_db_input($db_id)."' AND `".$par_status."` = '1'");

//        var_dump($sql_data_array);
		//▼新しいデータを追加する
		tep_db_perform($db_table, $sql_data_array);
	
	}else{
		
		//▼エラー処理
		return false;
	}
}


//▼登録確認
function zCheckUserReg($db_table,$memberid){
	
	$table_id = $db_table.'_id';
	
	if($memberid){
		$a_query = tep_db_query("
			SELECT 
				`".$table_id."`
			FROM `".$db_table."` 
			WHERE `state` = '1' 
			AND   `memberid` = '".tep_db_input($memberid)."' 
		");
		
		if($a = tep_db_fetch_array($a_query)){
			$res = $a[$table_id];
			
		}else{
			$res = false;
		}
		
	}else{
		$res = false;
	}
	
	return $res;
}


//▼メールの重複確認
function zCheckUserEmail($user_id){
	
	//ユーザーのメールアドレス
	if($mail = zGetUserEmail($user_id)){
		
		$email_query = tep_db_query("
			SELECT
				`memberid`
			FROM `".TABLE_MEM00000."`
			WHERE `email` = '".tep_db_input($mail)."'
		");

		if(tep_db_num_rows($email_query) == 1){
			$res = true;
		}else{
			$res = false;
		}
		
	}else{
		
		//無効なユーザー
		$res = false;
	}
	
	return $res;
}


function zCheckUserRegMem($db_table,$memberid){
	
	if($memberid){
		$a_query = tep_db_query("
			SELECT 
				`memberid`
			FROM `".$db_table."` 
			WHERE `memberid` = '".tep_db_input($memberid)."'
		");
		
		if($a = tep_db_fetch_array($a_query)){
			$res = $a['memberid'];
			
		}else{
			$res = false;
		}
		
	}else{
		$res = false;
	}
	
	return $res;
}


//▼ユーザーのメールアドレス
function zGetUserEmail($memberid){

	//▼送信先メールアドレス
	$user_query = tep_db_query("
		SELECT
			`email`
		FROM  `".TABLE_MEM00000."`
		WHERE `memberid` = '".tep_db_input($memberid)."'
	");

	if($u = tep_db_fetch_array($user_query)){
		$res = $u['email'];
	}else{
		$res = false;
	}
	return $res;
}


//▼QandAカテゴリー
function zQAtag(){
	$query =  tep_db_query("
		SELECT
			`a_qanda_tag_id`     AS `id`,
			`a_qanda_tag_name`   AS `name`,
			`a_qanda_tag_order`  AS `order`
		FROM  `".TABLE_A_QANDA_TAG."`
		WHERE    `state` = '1' 
		ORDER BY `a_qanda_tag_order` ASC
	");

	while($a = tep_db_fetch_array($query)) {
		$res_ar[$a['id']] = $a;
	}
	
	return $res_ar;
}


/*=====================
ステータス管理
=====================*/
//▼ユーザーステータス新規
function zUserWCStatusNew($param,$val,$memberid){

	//ステータス情報を追加
	$data_array = array(
		'memberid'     => $memberid,
		$param        => '1',
		'date_create' => 'now()',
		'state'       => '1'
	);
	
	zDBNewUniqueID(TABLE_USER_WC_STATUS,$data_array,'user_wc_status_ai_id','user_wc_status_id');
}


//▼ユーザーステータス更新
function zUserWCStatusUpdate($param,$val,$memberid){

	//登録用配列
	$data_array = array($param => $val,'date_update'=>'now()');

	//データ更新
	tep_db_perform(TABLE_USER_WC_STATUS, $data_array, 'update', "`state`='1' AND `memberid` = '".tep_db_input($memberid)."'");
}


//▼ユーザーステータス確認
function zUserStatusCheck($memberid){

	$query = tep_db_query("
		SELECT
			`memberid`,
			`user_wc_status_reg`                   AS `reg`,
			`user_wc_status_info`                  AS `info`,
			`user_wc_status_info_address`          AS `addr`,
			`user_wc_status_identification`        AS `ident`,
			`user_wc_status_address_certification` AS `ad_certif`,
			`user_wc_status_certification`         AS `certir`,
			`user_wc_status_buy`                   AS `buy`,
			`user_wc_status_step_wallet`           AS `step_w`,
			`user_wc_status_step_card`             AS `step_c`,
			`user_wc_status_date_wallet_open`      AS `d_w_open`,
			`user_wc_status_date_agree_ap`         AS `d_agree_ap`,
			`user_wc_status_date_agree_fs`         AS `d_agree_fs`,
			`user_wc_status_date_issue_aplogin`    AS `d_aplogin`,
			`user_wc_status_date_fs_approve`       AS `d_fs_aprv`,
			`user_wc_status_date_fs_send_to_ap`    AS `d_fs_send_ap`,
			`user_wc_status_date_fs_back_from_ap`  AS `d_fs_back`,
			`user_wc_status_date_ap_approve`       AS `d_ap_aprv`,
			`user_wc_status_date_fs_receive`       AS `d_fs_recv`,
			`user_wc_status_date_fs_send_to_user`  AS `d_fs_send_us`,
			`user_wc_status_date_us_receive`       AS `d_us_recv`
		FROM  `".TABLE_USER_WC_STATUS."`
		WHERE `memberid` = '".tep_db_input($memberid)."'
		AND   `state` = '1'
	");
	
	$res  = false;
	
	if($d = tep_db_fetch_array($query)){ $res = $d; }
	
	return $res;
}


//▼PositionActive確認
function zPositionActiveCheck($pos_id){

	$query = tep_db_query("
		SELECT
			`position_condition` AS `cond`
		FROM  `".TABLE_POSITION."`
		WHERE `position_id` = '".tep_db_input($pos_id)."'
		AND   `state` = '1'
	");
	
	$res  = false;
	
	if($d = tep_db_fetch_array($query)){
		
		$res = ($d['cond'] == 'a')? true:false;
	}
	
	return $res;
}


/*=====================
データ作成
=====================*/
//▼選択リストを作成する
function zSelectListSet($in_array,$data_in,$name,$text,$option_id ='',$option_function ='',$any_option ='',$select_option=''){

	$function = "";
	$id = "";
    $option_s = '';

	//▼関数オプション
	if(!empty($option_function)){$function = 'OnChange="'.$option_function.';"';}
	if(!empty($option_id))      {$id = 'id="'.$option_id.'"';}
	
	foreach($in_array as $key => $value){
		if(isset($data_in)  && ($data_in == $key)){
			$selected = 'selected';
		}else{
			$selected = '';
		}

		$option_s.= '<option value="'.$key.'" '.$selected.' '.$any_option.'>'.$value.'</option>';
	}
	
	//▼表示成形
	$select_s = '<select '.$id.' name="'.$name.'" size="1" style="vertical-align: top; margin-right: 10px;" '.$function.' '.$select_option.'>';
	$select_s.= ($text)? '<option value="">'.$text.'</option>':'';
	$select_s.= $option_s;
	$select_s.= '</select>';
	
	return $select_s;
}


//▼ラジオ選択
function zRadioSet($in_array,$data_in,$name,$option=''){

    $radio = '';
	foreach($in_array AS $k => $v){
		$che_r = ($k == $data_in)? 'checked':'';
		$cl_r  = ($radio)? 'class="spc10_l i_radio"':'class="i_radio"';
		$radio.= '<input type="radio" name="'.$name.'" value="'.$k.'" '.$cl_r.' '.$che_r.' '.$option.'>'.$v;
	}
	
	return $radio;
}


//▼チェックボックス
function zCheckboxSet($in_array,$data_in,$name,$option=''){

    $checkl = '';
	foreach($in_array AS $k => $v){
		$che_r  = ($k == $data_in)? 'checked':'';
		$cl_r   = 'class="i_radio"';
		$checkl.= (($checkl)? '<br>':'').'<input type="checkbox" name="'.$name.'" value="'.$k.'" '.$cl_r.' '.$che_r.' '.$option.'> '.$v;
	}
	
	return $checkl;
}


//▼ナビ
function zGetMyNavigation($nav_array,$page){
	
	$p_id = str_replace('.php','',$page);
	
	$nc = floor(12 / count($nav_array));
	
	foreach($nav_array AS $key => $nd){
		
		//▼スタイル
		$act = ($key == $p_id)? 'activate':'';
		$bdr = ($nav_in)? 'bdr':'';
		
		//▼表示内容
		$val  = $nd['val'];
		$icon = ($nd['icon'])? '<i class="'.$nd['icon'].'" aria-hidden="true"></i>':'';
		
		//▼リンク処理
		$link   = $key.'.php';
		$nav_in.= '<a href="'.$key.'.php"><li class="col-xs-'.$nc.' col-md-'.$nc.' col-md-'.$nc.' col-lg-'.$nc.' active '.$act.' '.$bdr.'">'.$icon.'<br>'.$val.'</li></a>';
	}
	
	$nav = '<ul class="row my_nav">';
	$nav.= $nav_in;
	$nav.= '</ul>';
	
	return $nav;
}


//▼文字列をJSON形式に変換
function zToJSText($array){
	
	//値が0、空白の要素を削除
	$result = array_filter($array, function($v){
		if(!empty($v)){
			
			//▼変数が配列かを判断する
			if(is_array($v)){
				
				//変数を削除
				foreach($v AS $a => $b){if(!empty($b)){	$tt[$a] = $b;}}
				return $tt;
				
			}else{
				return $v;
			}
		}
	});
	
	//文字列を変更
	$tmp = json_encode($result);
	
	return ($tmp)? $tmp : false;
}


//▼文字列を配列に変更
function zJSToArry($text){
	
	return ($text)? json_decode($text,true) : false;
}


//▼ランダムパスワード作成
function makeRandStr($length = 8){
	
	//▼配列を作成
	$str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
	
	$r_str = null;
	
	//▼指定した配列の中文字をランダムに結合
	for($i=0; $i< $length; $i++) {
		$r_str.= $str[mt_rand(0, count($str) - 1)];
	}

	return $r_str;
}


/*=====================
マスターデータ
=====================*/
//▼通貨一覧
function zCurrencyList(){

	//▼基準通貨
	$cur_ar[0] = zGetSysSetting('sys_base_currency_unit');

	//▼設定通貨
	$query_currency = tep_db_query("
		SELECT
			`m_currency_id`       AS `id`,
			`m_currency_name`     AS `name`
		FROM  `".TABLE_M_CURRENCY."`
		WHERE `state` = '1'
		ORDER BY `m_currency_id` ASC
	");
	
	while($c = tep_db_fetch_array($query_currency)) {
		$cur_ar[$c['id']] = $c['name'];
	}
	
	return $cur_ar; 
}


//▼レート一覧
function zRateList(){
	
	//▼通貨
	$query = tep_db_query("
		SELECT
			`cr`.`m_currency_id`       AS `id`,
			`cr`.`m_currency_name`     AS `name`,
			`cn`.`m_currency_now_rate` AS `rate`
		FROM `".TABLE_M_CURRENCY."` AS `cr`
		LEFT JOIN `".TABLE_M_CURRENCY_NOW."` AS `cn` ON `cn`.`m_currency_id` = `cr`.`m_currency_id`
		WHERE `cr`.`state` = '1'
		AND   `cn`.`state` = '1'
		ORDER BY `cr`.`m_currency_id` ASC
	");

	//▼」初期設定
	$res[0] = array(
		'id'   =>0,
		'name' =>zGetSysSetting('sys_base_currency_unit'),
		'rate' =>1
	);

	if(tep_db_num_rows($query)){
		while($a = tep_db_fetch_array($query)) {
			$res[$a['id']] = $a;
		}
	}
	
	return $res;
}


//▼ランク一覧
function zRankList($type=''){

	$query =  tep_db_query("
		SELECT
			`m_rank_id`     AS `id`,
			`m_rank_name`   AS `name`,
			`m_rank_order`  AS `odr`,
			`m_rank_bctype` AS `bc`
		FROM  `".TABLE_M_RANK."`
		WHERE `state` = '1'
		ORDER BY `m_rank_id` ASC
	");

	while($a = tep_db_fetch_array($query)) {
		$ar[$a['id']]        = $a['name'];	//ID用
		$ar_odr[$a['odr']]   = $a['name'];	//ランク用
		
		$ar_bcid[$a['bc']]   = $a['id'];		//会員区分bcid
		$ar_bcodr[$a['bc']]  = $a['odr'];	//区分ランク
		$ar_bcname[$a['bc']] = $a['name'];		//会員区分bcid
	}
	
	switch ($type) {
		case 'order':
			$res = $ar_odr;
			break;
		case 'bcid':
			$res = $ar_bcid;
			break;
		case 'bcorder':
			$res = $ar_bcodr;
			break;
		case 'bcname':
			$res = $ar_bcname;
			break;
		default:
			$res = $ar;
	}
	
	return $res;
}

//▼ポイント一覧
function zPointList(){

	$query =  tep_db_query("
		SELECT
			`m_point_id`   AS `id`,
			`m_point_name` AS `name`
		FROM  `".TABLE_M_POINT."`
		WHERE `state` = '1'
		ORDER BY `m_point_id` ASC
	");

	while($a = tep_db_fetch_array($query)) {
		$ar[$a['id']] = $a['name'];
	}
	
	return $ar;
}


//▼商品詳細取得
function zPlanItemPoint($plan_id){
	
	if($plan_id){
		//品目
		$query =  tep_db_query("
			SELECT
				`m_item_id`       AS `id`,
				`m_plan_item_num` AS `num`
			FROM  `".TABLE_M_PLAN_ITEM."`
			WHERE    `state` = '1'
			AND      `m_plan_id` = '".tep_db_input($plan_id)."'
			ORDER BY `m_plan_item_ai_id` ASC
		");

		while($m = tep_db_fetch_array($query)) {
			$it_ar[$m['id']] = array('id'=>$m['id'],'num'=>$m['num']);
		}
		
		//ポイント
		$query =  tep_db_query("
			SELECT
				`m_point_id`          AS `id`,
				`m_plan_point_amount` AS `amt`
			FROM  `".TABLE_M_PLAN_POINT."`
			WHERE    `state` = '1'
			AND      `m_plan_id` = '".tep_db_input($plan_id)."'
			ORDER BY `m_plan_point_ai_id` ASC
		");
		
		while($m = tep_db_fetch_array($query)) {
			$po_ar[$m['id']] = array('id'=>$m['id'],'amt'=>$m['amt']);
		}
		
		//結果の設定
		if($it_ar || $po_ar){
			$ar = array('item' => $it_ar,'point'=> $po_ar);
		}
		
	}else{
		$ar = false;
	}
	
	return $ar;
}

//▼支払方法一覧
function zPaymentList($code=''){
	
	$query =  tep_db_query("
		SELECT
			`m_payment_id`   AS `id`,
			`m_payment_code` AS `code`,
			`m_payment_name` AS `name`
		FROM  `".TABLE_M_PAYMENT."`
		WHERE `state` = '1'
		ORDER BY `m_payment_code` ASC
	");
	
	//$ar[0] = '銀行振込';
	while($a = tep_db_fetch_array($query)) {
		$ar[$a['id']]          = $a['name'];
		$ar_code[$a['code']]   = $a['name'];
		$ar_codeid[$a['code']] = $a['id'];
		$ar_idcode[$a['id']]   = $a['code'];
	}
	
	switch ($code) {
		case 'code':
			$res = $ar_code;
			break;
		case 'codeid':
			$res = $ar_codeid;
			break;
		case 'idcode':
			$res = $ar_idcode;
			break;
		default:
			$res = $ar;
	}
	
	return $res;
}


//▼会社内訳一覧
function zDetailListSys(){

	$query =  tep_db_query("
		SELECT
			`m_detail_sys_name` AS `sys_name`,
			`m_detail_name`     AS `name`
		FROM  `".TABLE_M_DETAIL."`
		WHERE `state` = '1'
		ORDER BY `m_detail_id` ASC
	");
	
	while($a = tep_db_fetch_array($query)) {
		$ar[$a['sys_name']] = $a['name'];
	}
	
	return $ar;
}


//▼費用内訳一覧
function zItemList(){

	$query =  tep_db_query("
		SELECT
			`m_item_id`     AS `id`,
			`m_item_name`   AS `name`
		FROM  `".TABLE_M_ITEM."`
		WHERE `state` = '1'
		ORDER BY `m_item_id` ASC
	");
	
	while($a = tep_db_fetch_array($query)) {
		$ar[$a['id']] = $a['name'];
	}
	
	return $ar;
}

//▼費用計算
function zCulcList(){

	$query =  tep_db_query("
		SELECT
			`m_culc_id`     AS `id`,
			`m_culc_name`   AS `name`
		FROM  `".TABLE_M_CULC."`
		WHERE `state` = '1'
		ORDER BY `m_culc_id` ASC
	");
	
	while($a = tep_db_fetch_array($query)) {
		$ar[$a['id']] = $a['name'];
	}
	
	return $ar;
}


//▼商品名一覧
function zPlanListAr(){
	
	$query =  tep_db_query("
		SELECT
			`m_plan_id`        AS `id`,
			`m_plan_name`      AS `name`,
			`m_plan_rank_id`   AS `rank_id`
		FROM  `".TABLE_M_PLAN."`
		WHERE `state` = '1'
		ORDER  BY `m_plan_id` ASC
	");
	
	while($a = tep_db_fetch_array($query)) {
		$ar[$a['id']] = $a;
	}
	
	return $ar;
}


//▼uni比率
function zRankUniRateList(){

	$query =  tep_db_query("
		SELECT
			`m_rank_id`    AS `id`,
			`m_rank_r_uni` AS `uni`
		FROM  `".TABLE_M_RANK."`
		WHERE `state` = '1'
		ORDER BY `m_rank_id` ASC
	");

	while($a = tep_db_fetch_array($query)) {
		$ar[$a['id']] = $a['uni'];
	}
	
	return $ar;
}


//▼選択リストを作成する
function zSelectCart($in_array,$data_in,$name,$any_option ='',$select_option=''){

	//▼関数オプション
	foreach($in_array as $key => $value){
		if(isset($data_in)AND($data_in == $key)){
			$selected = 'selected';
		}else{
			$selected = '';
		}
		$option_s.= '<option value="'.$key.'" '.$selected.' '.$any_option.'>'.$value.'</option>';
	}

	$select_s = '<select '.$id.' name="'.$name.'" size="1" style="vertical-align: top; margin-right: 10px;" '.$function.' '.$select_option.'>';
	$select_s.= $option_s;
	$select_s.= '</select>';
	
	return $select_s;
}


/*=====================
システム設定
=====================*/

/*-------- システムパラメータ取得 --------*/
function zGetSysSetting($parm){

	//▼登録用データ
	$query = tep_db_query("
		SELECT
			`zsys_setting_value` AS `value`
		FROM `".TABLE_ZSYS_SETTING."`
		WHERE `state` = '1'
		AND `zsys_setting_paramater` = '".tep_db_input($parm)."'
	");
	
	//▼データ取得
	if($d = tep_db_fetch_array($query)){
		return mb_convert_kana($d['value'],'as');
	}else{
		return false;
	}
}

/*-------- システムパラメータ全取得 --------*/
function zGetSysSettingAll(){

	//▼登録用データ
	$query = tep_db_query("
		SELECT
			`zsys_setting_paramater` AS `paramater`,
			`zsys_setting_value` AS `value`
		FROM `".TABLE_ZSYS_SETTING."`
		WHERE `state` = '1'
	");
	
	//▼データ取得
	while($ds = tep_db_fetch_array($query)){
		
		$array[$ds['paramater']]= mb_convert_kana($ds['value'],'as');
	}
	
	return $array;
}


//▼入金銀行口座
function zGetMasterBank(){
	//▼銀行情報
	$query =  tep_db_query("
		SELECT 
			`master_bank_name`         AS `bank_name`,
			`master_bank_branch`       AS `bank_branch`,
			`master_bank_type`         AS `bank_type`,
			`master_bank_number`       AS `bank_number`,
			`master_bank_account_name` AS `bank_account_name`
		FROM `".TABLE_MASTER_BANK."`
		WHERE `state` = '1' 
	");

	if ($b = tep_db_fetch_array($query)) {
		$res = $b;
	}else{
		$res = false;
	}
	
	return $res;
}


/*=====================
費用計算関連1
=====================*/
function mSetPlanSetting($i_ar,$d_ar,$plan_id,$d_i_ar,$d_dt_ar,$input_control,$input = '',$d_pay_ar = ''){
	
	foreach($i_ar AS $k => $v){
		
		//▼各値の値
		if($plan_id){$v_item = ($d_i_ar[$k])? $d_i_ar[$k] : '0';}
		
		//▼費用詳細の有無を確認
		$dt_h  = '';
		$dt_in = '';
		
		if($v['i_detail']){
			
			//▼配列に変換
			$tmp_ar = zJSToArry($v['i_detail']);
			$dt_ht  = '';
			$dt_int = '';
			
			foreach($tmp_ar AS $kd => $vd){
				
				$t_name = $d_ar[$kd]['name'];							//表示名を取得
				$t_sys  = $d_ar[$kd]['sys_name'];						//システム名を取得
				$t_val  = ($d_dt_ar[$k])? $d_dt_ar[$k][$t_sys]:'0';		//データの登録があれば
			
				$dt_ht .= '<li>'.$t_name.'</li>';
				
				if($input == 'text'){
					$dt_int.= '<li><p>'.(($t_val)? zCheckNum($t_val):'-').'</p></li>';
				}else{
					$dt_int.= '<li><input type="text" name="idetail['.$k.']['.$t_sys.']" value="'.$t_val.'" '.$input_control.'></li>';
				}
				
			}
			
			//▼表示
			$dt_h  = '<ul class="dt_in">'.$dt_ht.'</ul>'; 
			$dt_in = '<ul class="dt_in">'.$dt_int.'</ul>';
			
		}else{
			$dt_h  = '-';
			$dt_in = '';
		}
		
		
		/*----- 費用項目計算 -----*/
		if($input == 'text'){
			//▼テキスト表示
			$in = '<p>'.(($v_item)? zCheckNum($v_item):'-').'</p>';
		}else{
			
			//▼入力表示
			if($v['i_cid']){
				
				//▼計算用クラスを定義
				$cul = new CulcItemAmount;
				
				$cul->culcid  = $v['i_cid'];		//計算ID
				$target = $cul->GetItemIDs();		//計算対象項目
				
				
				//▼金額設定
				foreach($target AS $kr => $vtarget){
					if(isset($vtarget)){
						
						//▼IDから数値を取得
						if($vtarget === '0'){
							$i_amt = array_sum($d_i_ar);
						}else{
							$i_amt = $d_i_ar[$kr];
						}
						//▼値を格納
						if($kr == 'target1'){
							$cul->amount1 = $i_amt;
						}else if($kr == 'target2'){
							$cul->amount2 = $i_amt;
						}
					}
				}
				
				//▼金額計算
				$aaa = $cul->zCulcItemAmount();

				//▼選択処理
				if($d_pay_ar[$k]){
					$checked_p = 'checked';
				}else{
					$checked_p = '';
				}
				$in = '<input type="checkbox" name="itemid['.$k.']" value="culc" '.$checked_p.'><span class="spc10_l">'.zCheckNum($aaa).'</span>';
				$in.= '<p class="alert">表示は参考金額です</p>';
				
			}else{
				//固定入力
				$in = '<input class="input_text" type="text" name="itemid['.$k.']" value="'.$v_item.'" '.$input_control.'>';
			}
		}
		
		$form_res.= '<tr><th>'.$v['name'].'</th><td>'.$in.'</td><td style="border-right:none;">'.$dt_h.'</td><td style="border-left:none;">'.$dt_in.'</td></tr>';
	}
	
	return $form_res;
}

//▼計算IDを取得
function zGetCulcIDFromItem($id){
	
	//計算情報
	$query =  tep_db_query("
		SELECT 
			`m_item_culc_id`
		FROM `".TABLE_M_ITEM."`
		WHERE `state` = '1'
		AND `m_item_id` = '".tep_db_input($id)."'
	");

	if ($b = tep_db_fetch_array($query)) {
		$res = $b['m_item_culc_id'];
	}else{
		$res = false;
	}
	
	return $res;
}


//▼金額計算式
class CulcItemAmount{
	
	//▼変数設定
	public $amount1 = 0;
	public $amount2 = 0;
	public $culcid  = '';
	private $target = '';
	

	//▼対象の費用IDを取得
	function GetItemIDs(){
		
		$id = $this->culcid;
		
		$query_id =  tep_db_query("
			SELECT 
				CAST(`m_culc_target_id1` AS CHAR) AS `target1`,
				CAST(`m_culc_target_id2` AS CHAR) AS `target2`
			FROM `".TABLE_M_CULC."`
			WHERE `state` = '1' 
			AND   `m_culc_id` = '".tep_db_input($id)."'
		");
		
		if($b = tep_db_fetch_array($query_id)) {
			$res = $b;
		}else{
			$res = false;
		}
		
		$this->target = $res;
		return $res;
	}
	
	function zSetTarget($array){
		
		//▼金額設定
		foreach($this->target AS $kr => $vtarget){
			
			if(isset($vtarget)){
				
				//▼IDから数値を取得
				if($vtarget === '0'){
					$i_amt = array_sum($array);
					
				}else{
					$i_amt = $array[$kr];
				}
				
				
				
				//▼値を格納
				if($kr == 'target1'){
					$this->amount1 = $i_amt;
				}else if($kr == 'target2'){
					$this->amount2 = $i_amt;
				}
			}
		}
	}
	
	//▼内部計算
	private function zClulcInner($num1,$num2,$o){
		switch($o){
			case 'a':
				$amt = $num1 + $num2; 
				break;
			case 'b':
				$amt = $num1 - $num2;
				break;
			case 'c':
				$amt = $num1 * $num2;
				break;
			case 'd':
				$amt = $num1 / $num2;
				break;
			default:
				$amt = false;
		}
		
		return $amt;
	}
	
	//▼端数処理
	private function zCulcBreak($num,$b){

		switch($b){
			case 'f':
				$amt = floor($num); 
				break;
			case 'c':
				$amt = ceil($num);
				break;
			case 'r':
				$amt = round($num);
				break;
			default:
				$amt = false;
		}
		
		return $amt;
	}
	
	//▼費用計算
	function zCulcItemAmount(){
		
		//▼費用の設定
		$id   = $this->culcid;
		$num1 = $this->amount1;
		$num2 = $this->amount2;
		
		//▼計算情報
		$query =  tep_db_query("
			SELECT 
				`m_culc_operator_in`  AS `in`,
				`m_culc_operator_out` AS `out`,
				`m_culc_number`       AS `number`,
				`m_culc_treat_broken` AS `break`,
				`m_culc_max_value`    AS `max`,
				`m_culc_min_value`    AS `min`
			FROM `".TABLE_M_CULC."`
			WHERE `state` = '1' 
			AND   `m_culc_id` = '".tep_db_input($id)."'
		");

		if ($b = tep_db_fetch_array($query)) {
			
			//▼内部計算
			if(($num1)AND($num2)AND($b['in'])){
				$amt_in = $this->zClulcInner($num1,$num2,$b['in']);
			}else{
				$amt_in = $num1;
			}

			//▼値の計算
			if($b['number']){
				
				//▼数値を計算
				$amt_out = $this->zClulcInner($amt_in,$b['number'],$b['out']);			//外部計算
				if($c = $this->zCulcBreak($amt_out,$b['break'])){$amt_out = $c;}		//端数処理
				
				//▼値の範囲の確認
				if(($b['max'] > 0)AND($amt_out > $b['max'])){$amt_out = $b['max'];}		//最大
				if(($b['min'] > 0)AND($amt_out < $b['min'])){$amt_out = $b['min'];}		//最小
				
				$res = $amt_out;
			}else{
				$res = false;
			}
			
		}else{
			$res = false;
		}
		
		return $res;
	}
}


/*=====================
費用計算関連2
=====================*/
//▼手数料計算
class ClucFeeAmount{

	//▼変数設定
	private $range_from   = '';
	private $range_to     = '';
	private $target_id1   = '';
	private $operator_in  = '';
	private $target_id2   = '';
	private $operator_out = '';
	private $number       = 0;
	private $treat_broken = '';
	private $max_value    = 0;
	private $min_value    = 0;
	
	//▼金額設定用
	public  $amount1 = 0;
	public  $amount2 = 0;
	
	//▼結果取得用用
	private $id      = '';
	private $name    = '';
	private $fee     = 0;
	
	
	//▼初期設定　＞　宣言時に呼び出し
	function __construct($culc_id)	{
		
		$query = tep_db_query("
			SELECT 
				`m_culc_id`           AS `id`,
				`m_culc_name`         AS `name`,
				`m_culc_target_id1`   AS `tgtid1`,
				`m_culc_operator_in`  AS `o_in`,
				`m_culc_target_id2`   AS `tgtid2`,
				`m_culc_operator_out` AS `o_out`,
				`m_culc_number`       AS `num`,
				`m_culc_treat_broken` AS `broken`,
				`m_culc_max_value`    AS `v_max`,
				`m_culc_min_value`    AS `v_min`,
				`m_culc_range_from`   AS `r_from`,
				`m_culc_range_to`     AS `r_to`
			FROM  `".TABLE_M_CULC."`
			WHERE `state` = '1'
			AND   `m_culc_id` = '".tep_db_input($culc_id)."'
		");
		
		if($a = tep_db_fetch_array($query)){
			//▼変数設定
			$this->target_id1   = $a['tgtid1'];
			$this->operator_in  = $a['o_in'];
			$this->target_id2   = $a['tgtid2'];
			$this->operator_out = $a['o_out'];
			$this->number       = $a['num'];
			$this->treat_broken = $a['broken'];
			$this->max_value    = $a['v_max'];
			$this->min_value    = $a['v_min'];
			$this->range_from   = $a['r_from'];
			$this->range_to     = $a['r_to'];
			
			//▼結果取得用
			$this->id   = $culc_id;
			$this->name = $a['name'];
			
			//計算結果を格納
			$res = true;
		}else{
			//計算終了
			$res = false;
		}
		return $res;
	}
	
	//▼結果のIDを外部に取得
	public function pGetItemId(){
		
		$res = array(
			'target1_id' =>$this->target_id1,
			'target2_id' =>$this->target_id2
		);
		
		return $res;
	}
	
	
	//▼内部計算
	private function pCulcInner(){
		
		//▼初期設定
		$o    = $this->operator_in;
		$amt1 = $this->amount1;
		$amt2 = $this->amount2;
		
		//▼内部計算
		switch($o){
			case 'a':
				$amt = $amt1 + $amt2; 
				break;
			case 'b':
				$amt = $amt1 - $amt2;
				break;
			case 'c':
				$amt = $amt1 * $amt2;
				break;
			case 'd':
				$amt = ($amt2 == 0)? 0 : $amt1 / $amt2;
				break;
			default:
				//何もなければ設定値1
				$amt = $amt1;
		}
		
		//▼範囲確認　＞結果は金額かfalse
		$res = $this->pCheckRange($amt);
		
		return $res;
	}
	
	
	//▼範囲確認
	private function pCheckRange($amt){
		$rfrom = $this->range_from;
		$rto   = $this->range_to;
		$amt   = $amt;
		
		//▼最大最小の設定で分岐
		if($rfrom && $rto){
			//開始値と終了値を判定
			$res = (($rfrom <= $amt)AND($amt < $rto))? $amt :false;
			
		}else if($rfrom){
			//開始値を判定
			$res = ($rfrom <= $amt)? $amt :false;
			
		}else if($rto){
			//終了値を判定
			$res = ($amt < $rto)? $amt :false;
			
		}else{
			//値があるかだけを確認 ＞ 0を検出
			$res = ($amt)? $amt :false;
		}
		
		//write_log($string,"w");
		
		return $res;
	}
	
	
	//▼外部計算
	private function pClulcOuter($a_in){
		
		//▼初期設定
		$o    = $this->operator_out;	//計算演算子
		$amt1 = $a_in;					//内部演算結果
		$amt2 = $this->number;			//外部演算数値
		
		//▼内部計算
		switch($o){
			case 'a':
				//全体金額設定を考慮
				$amt = ($this->target_id1 == 0)? $amt2: $amt1 + $amt2;
				break;
			case 'b':
				$amt = $amt1 - $amt2;
				break;
			case 'c':
				$amt = $amt1 * $amt2;
				break;
			case 'd':
				$amt = ($amt2 == 0)? 0:$amt1 / $amt2;
				break;
			default:
				$amt = 0;
		}
		
		//▼端数処理
		$amt = $this->pCulcBreak($amt);
		
		//▼最大・最小判定　＞　最大・最小の設定があれば判定
		$max = $this->max_value;
		$min = $this->min_value;
		if($max && $max < $amt){$amt = $max;}
		if($min && $amt < $min){$amt = $min;}
		
		return $amt;
	}
	
	
	//▼端数処理
	private function pCulcBreak($num){
		
		//▼初期設定
		$b = $this->treat_broken;
		
		switch($b){
			case 'f':
				$amt = floor($num); 
				break;
			case 'c':
				$amt = ceil($num);
				break;
			case 'r':
				$amt = round($num);
				break;
			default:
				$amt = $num;
		}
		return $amt;
	}
	
	
	//----- 計算開始 -----//
	public function pStartCulc(){
		
		//▼内部計算
		if($amt_in = $this->pCulcInner()){
			//▼外部計算
			$res = $this->pClulcOuter($amt_in);
			
		}else{
			$res = 0;
		}
		
		$this->fee = $res;
		
		return $res;
	}
	
	public function pGetCulcResult(){
		return array(
			'id'   => $this->id,
			'name' => $this->name,
			'amt'  => $this->fee
		);
	}
}


//▼会員種類別商品情報
function zGetTypePlanData($type,$sort,$c_plan_ar){
	
	//▼リスト取得
	$rank_bcodr_ar = zRankList('bcorder');		//ランク一覧　会員区分＞順番
	$rank_bcid_ar  = zRankList('bcid');			//ランク一覧　会員区分＞ID
	
	
	//▼検索条件
	$query_rank = tep_db_query("
		SELECT
			`p`.`m_plan_id`,
			`p`.`m_plan_name`,
			`r`.`m_rank_id`    AS `r_id`,
			`r`.`m_rank_order` AS `order`
		FROM `".TABLE_M_PLAN."` `p`
		LEFT JOIN `".TABLE_M_RANK."` `r` ON `r`.`m_rank_id` = `p`.`m_plan_rank_id`
		WHERE `r`.`state`    = '1'
		AND   `p`.`state`    = '1'
		AND   `r`.`m_rank_order` <= ".tep_db_input($rank_bcodr_ar[$type])."
		AND   `p`.`m_plan_sort` = '".tep_db_input($sort)."'
	");
	
	if(tep_db_num_rows($query_rank)){
		
		//▼データIDを取得
		while($c = tep_db_fetch_array($query_rank)){
			$for_get_ovpl.= (($for_get_ovpl)? ",'":"'").$c['m_plan_id']."'";
		}
		
		//▼以上ランク設定
		$over_in = "OR(`m_plan_id` IN(".$for_get_ovpl."))";
	}
	
	//▼検索指定
	$rank_search ="AND (";
	$rank_search.= "((`m_plan_limited_id` = '".tep_db_input($rank_bcid_ar[$type])."')AND(`m_plan_rank_id` = 0))";	//限定ランク
	$rank_search.= $over_in;													//以上ランク
	$rank_search.= "OR((`m_plan_limited_id` = 0)AND(`m_plan_rank_id` = 0))";								//指定なし
	$rank_search.= ")";
	
	//▼対象商品一覧
	//限定ランク
	//以上ランク
	//ランクなし
	//定期購入
	$query =  tep_db_query("
		SELECT
			`m_plan_id`            AS `id`,
			`m_plan_name`          AS `name`,
			`m_plan_o_limit_piece` AS `ol_piece`
		FROM       `".TABLE_M_PLAN."`
		WHERE `state` = '1'
		AND   `m_plan_sort` = '".tep_db_input($sort)."'
		".$rank_search."
		ORDER  BY `m_plan_id` ASC
	");
	
	while($a = tep_db_fetch_array($query)) {
		//▼対象商品
		$m_plan_ar[$a['id']] = $a;
		
		//▼取得用
		$for_get_plan.= (($for_get_plan)? ",'":"'").$a['id']."'";
	}
	
	//▼合計金額
	$query = tep_db_query("
		SELECT
			`pi`.`m_plan_id` AS `id`,
			SUM(`mi`.`m_item_fixamount` * `pi`.`m_plan_item_num`) AS `sum`
		FROM      `".TABLE_M_PLAN_ITEM."` `pi`
		LEFT JOIN `".TABLE_M_ITEM."`      `mi` ON `mi`.`m_item_id` = `pi`.`m_item_id`
		WHERE `pi`.`state` = '1'
		AND   `mi`.`state` = '1'
		AND   `pi`.`m_plan_id` IN(".$for_get_plan.")
		GROUP BY `pi`.`m_plan_id`
	");
	
	while($a = tep_db_fetch_array($query)) {
		$m_plan_ar[$a['id']]['sum']   = $a['sum'];							//単価
		$m_plan_ar[$a['id']]['num']   = $c_plan_ar[$a['id']];				//商品個数
		$m_plan_ar[$a['id']]['total'] = $a['sum'] * $c_plan_ar[$a['id']];	//注文合計
	}
	
	//▼ポイント
	$query = tep_db_query("
		SELECT
			`pp`.`m_plan_id`           AS `id`,
			`pp`.`m_point_id`          AS `pointid`,
			`pp`.`m_plan_point_amount` AS `amt`,
			`mp`.`m_point_name`        AS `name`
		FROM      `".TABLE_M_PLAN_POINT."` `pp`
		LEFT JOIN `".TABLE_M_POINT."`      `mp` ON `mp`.`m_point_id` = `pp`.`m_point_id`
		WHERE `pp`.`state` = '1'
		AND   `mp`.`state` = '1'
		AND   `pp`.`m_plan_id` IN(".$for_get_plan.")
		GROUP BY `pp`.`m_plan_id`
	");
	
	while($a = tep_db_fetch_array($query)) {
		//▼ポイント登録
		$m_plan_ar[$a['id']]['point'][$a['pointid']] = array(
			'name'  => $a['name'],
			'amt'   => $a['amt'],
			'total' => $a['amt'] * $c_plan_ar[$a['id']]
		);
	}
	
	//▼取得結果を戻す
	return $m_plan_ar;
}


/*=====================
画像フィルアップロード
=====================*/
/*-------- ファイルのエラーチェック --------*/
function z_check_upfile($f_name,$f_tmp,$f_size){
	
	$err  = 0;
	$text = "ok";

	//▼添付ファイルのパス
	$path = $f_tmp;
	
	//▼ファイル種類の獲得
	$mime = shell_exec('file -bi '.escapeshellcmd($path));
	$mime = trim($mime);
	$mime = preg_replace("/ [^ ]*/", "", $mime);
	$mime = str_replace(";", "", $mime);
	
	if((preg_match("/\/*(jpg)$/", $mime))OR(preg_match("/\/*(jpeg)$/", $mime))OR(preg_match("/\/*(jp_)$/", $mime))){
		
		//▼ファイル種類エラー
		if(!(
				(substr(strrchr($f_name, '.'), 1) == "jpg")OR(substr(strrchr($f_name, '.'), 1) == "jpeg")
				OR(substr(strrchr($f_name, '.'), 1) == "JPG")OR(substr(strrchr($f_name, '.'), 1) == "JPEG")
			)){
			$err = 1;
			$text = '登録できるファイルは「.jpg」「.jpeg」のみです';
		}
	
	}else if($f_size > 1024*1024*10){
		
		//▼ファイル容量エラー
		$err = 1;
		$text = '登録できるファイルは5MB以下です';
	}
	
	return array('err'=>$err,'text'=>$text);
}


/*-------- 画像のリサイズと保存 --------*/
function z_resize_jpg_img($foldar,$org,$f_name,$f_tmp,$f_size,$memberid,$certif_t){
	
	//-----------ファイル準備-----------
	//▼拡張子
	$extension = substr(strrchr($f_name, '.'), 1);
	
	//▼取得した拡張子を小文字にする
	$extension = mb_strtolower($extension);
	
	//▼アップロードファイル名
	//ユーザーid + 証明書種類
	$upfile_name = $memberid.'_'.$certif_t.'_'.time().'.'.$extension;

	//▼操作ファイル
	$file_path = $f_tmp;
	
	//▼ファイル出力先
	$out_file_path = '../'.$foldar.$upfile_name;

	//▼元画像
	$up_org = '../'.$org;
	
	//▼サイズ上限
	$size_limit = 50*1024;
	$max_w = 1500;		//横上限
	$max_h = 1500;		//縦上限
	
	
	
	//-----------リサイズ初期設定-----------
	//▼画像オブジェクト作成
	$img = ImageCreateFromJPEG($file_path);
	
	//▼サイズ取得
	$width  = ImageSX($img);
	$height = ImageSY($img);
	
	
	//▼サイズ上限を設定
	$new_width  = $width;
	$new_height = $height;

	if($width > $height){
		
		if($width > $max_w){
			$new_width  = $max_w;
			$new_height = round($height * $max_w / $width);
		}
		
	}else{
		if($height > $max_h){
			$new_width  = round($width * $max_h / $height);
			$new_height = $max_h;
		}
	}
	
	
	//-----------リサイズ実行-----------
	//▼リサイズ用画像リソース
	$new_img = ImageCreateTrueColor($new_width, $new_height); //イメージリソースに黒い色を付ける

	
	//▼イメージリソースに大元の画像をコピーして画像のファイルの要素を作る
	ImageCopyResampled($new_img,$img,0,0,0,0,$new_width,$new_height,$width,$height);
	ImageJPEG($new_img, $out_file_path);	// JPEGファイルをidにアップロードする
	$size = filesize($out_file_path);
	
	
	//▼orgに保存する
	if(move_uploaded_file($f_tmp, $up_org.$upfile_name)){
		
		$result = $upfile_name;
		
	}else{
		$result = false;
	}
	
	return $result;
}


/*=====================
CSVダウンロード
=====================*/

/*-------- CSV形式でダウンロード --------*/
class DataCsvDL{

	//▼登録用初期設定
	public $csv_header  = '';
	public $csv_data    = '';
	public $dl_filename = '';
	
	function CsvDLRun(){
	
		//▼ファイル保存用一時フォルダ
		$stock = "../stock/";
		
		//----------ファイル準備----------
		//▼作業フォルダ
		$dr_tmp = "ul".$_COOKIE['admin_company_id']."_".date("ymdhis");
		mkdir($stock.$dr_tmp);
		
		//▼作業ディレクトリ
		$workdir = $stock.$dr_tmp."/";

		//▼一時出力ファイル名
		$fname = "ul".$_COOKIE['admin_company_id']."_".date("ymdhis").".csv";
		$fpath = $workdir.$fname;
		
		//▼出力用のファイルを準備
		$file = fopen($fpath, 'w');
		
		
		//----------書き込み----------
		//▼BOMの書き込み
		fwrite($file, "\xef\xbb\xbf");
		
		//▼CSVのヘッダーの書き込み
		fputcsv($file,$this->csv_header);
		
		//▼ダウンロードファイル名
		$name_dl = $this->dl_filename;
		//$name_dl = mb_convert_encoding($name_dl, "SJIS", "utf-8");

		//▼データを書き込み
		foreach($this->csv_data AS $data){
			fputcsv($file,$data);
		}
		
		
		//----------ダウンロード----------
		//▼ダウンロード用ヘッダーの指定
		header('Content-Type: text/csv');
		header('Content-Length: '.filesize($fpath));
		header('Content-disposition: attachment; filename="'.$name_dl.'"');

		//▼ダウンロードの実行
		readfile($fpath);
		
		//▼ファイルを閉じる
		fclose($file);
		
		//----------終了処理----------
		//▼ディレクトリの中身を取得
		$handle = opendir($workdir);

		//▼ディレクトリの中のファイルの削除
		while(($entry = readdir($handle)) !== false){
			if(($entry != ".")AND($entry != "..")){
				unlink($workdir.$entry);
			}
		}

		//▼ディレクトリとの連携を切る
		closedir($handle);

		//▼ディレクトリの削除処理
		rmdir($workdir);
		
		exit;
	}
}


/*-------- zip形式でダウンロード --------*/
class DataCsvDLZip{

	//▼登録用配列
	public $csv_array = array();
	
	//▼フォルダ構成用
	public $root_fold_name = "";
	public $fold_array = array();
	
	
	function CsvDLZipRun(){
	
		//▼ファイル保存用一時フォルダ
		$stock = "../stock/";
		
		//▼作業フォルダ
		$dr_tmp = "ul".$_COOKIE['admin_company_id']."_".date("ymdhis");
		mkdir($stock.$dr_tmp);
		
		//▼作業ディレクトリ
		$workdir = $stock.$dr_tmp."/";


		//----------zip準備----------
		//▼zipオブジェクトを作成
		$zip = new ZipArchive;
		
		//▼zipファイル名
		$zipname = "zp".$_COOKIE['admin_company_id']."_".date("ymdhis").".zip";
		
		//▼zipファイル作成
		$zip->open($workdir.$zipname, ZipArchive::CREATE | ZIPARCHIVE::OVERWRITE);
	
		//▼CSVファイルの保存
		foreach($this->csv_array AS $k => $csv){
			
			//----------csv準備----------
			//▼一時出力ファイル名
			$fname = "ul".$_COOKIE['admin_company_id']."_".date("ymdhis").$k.".csv";
			$fpath = $workdir.$fname;
			
			//▼出力用のファイルを準備
			$file = fopen($fpath, 'w');
			
			//----------CSVファイル作成----------
			//▼BOMの書き込み
			fwrite($file, "\xef\xbb\xbf");
			
			//▼CSVのヘッダーの書き込み
			fputcsv($file,$csv["csv_header"]);
			
			//▼ダウンロードファイル名
			//$name_dl = mb_convert_encoding($name_dl, "SJIS", "utf-8");
			
			//▼データの列ごとに処理
			foreach($csv['csv_data'] AS $row){
				//▼データを書き込み
				fputcsv($file,$row);
			}
			
			//▼ファイルを閉じる
			fclose($file);
			
			//▼ファイル名をエンコード
			$csv_name = mb_convert_encoding($csv["dl_filename"], "SJIS");
			
			//----------zipへ保存----------
			//▼zipfileへ保存
			//dl_filename:展開ファイル名　file:csvフィル本体
			$zip->addFile($fpath,$csv_name);
		}
		
		
		
		//------------------写真の登録------------------
		//▼フォルダの登録
		if(!empty($this->fold_array)){
			
			//▼元のフォルダを作成する
			$zip->addEmptyDir ($this->root_fold_name);
			
			foreach($this->fold_array AS $k => $f_data){
				
				//▼格納用のフォルダ
				$local_dir = "./".$this->root_fold_name."/".$k."/";
				
				//▼格納用のフォルダを追加
				$zip->addEmptyDir ($local_dir);
				
				foreach($f_data AS $up_name){
				
					//▼フォルダにファイルを追加
					$zip->addFile("../".DIR_WS_UPLOADS_IDENTIFICATION.$up_name,$local_dir.$up_name);
				}
			}
			
		}

		
		//▼zipファイルを閉じる
		$zip->close();
		
		//▼dlファイル名
		$name_dl = date("Ymd")."_FI_Data".".zip";
		

		//----------ダウンロード----------
		//▼ダウンロード用ヘッダーの指定
		header('Content-Type: application/force-download;');
		header('Content-Length: '.filesize($workdir.$zipname));
		header('Content-disposition: attachment; filename="'.$name_dl.'"');

		//▼ダウンロードの実行
		readfile($workdir.$zipname);

		
		//----------終了処理----------
		//▼ディレクトリの中身をすべて削除
		if ($handle = opendir($workdir)) {
			
			//▼ディレクトリの中のファイルを読む
			while (($item = readdir($handle)) !== false) {
				
				if ($item != "." && $item != "..") {
					
					if (is_dir($workdir.$item)) {
						//▼ディレクト削除
						rmdir($workdir.$item);
					} else {
						//▼ファイル削除
						unlink($workdir.$item);
					}
				}
			}
			
			closedir($handle);
			
			//▼ディレクトリ自体を削除
			rmdir($workdir);
		}
	}
}


/*=====================
その他関数
=====================*/
//▼スペース削除
function kill_space($str){
	$str = preg_replace('/(\s|　)/','',$str);
	return $str;
}

//▼数字の表記
function zCheckNum($num){
	$bb =  strlen(str_replace(".","",strrchr($num,'.'))); 
	return number_format($num,$bb);
}


//▼null設定
function zSetNull($str){
	$bb = ($str == '')? 'null': $str;
	return $bb;
}


//▼PHPで別ファイルにポスト送信する
function nPostData($url,$post_data,$out=''){
	
	
	/*----- エラーチェック -----*/
	$err = false;
	
	if(!(($url)AND($post_data))){
		$err = true;
	}
	
	/*----- 送信設定 -----*/
	if($err == false){
		if($out == 'out'){
			$ful_url = $url;
		}else{
			$ful_url = HTTP_SERVER.trim(dirname($_SERVER["SCRIPT_NAME"]),'/').'/'.$url;
		}
		
		//▼初期化
		$chu = curl_init($ful_url);
		
		//▼転送時のオプションを設定
		curl_setopt($chu,CURLOPT_POST,true);				//POST送信の有効化
		curl_setopt($chu,CURLOPT_RETURNTRANSFER,true);		//curl_exec()の返り値を文字列で返す
		curl_setopt($chu,CURLOPT_POSTFIELDS,$post_data);	//POST送信するデータ本体
		
		//▼送信実行
		$aa = curl_exec($chu);
		
		//セッションを終了
		curl_close($chu);

	}else{
		$aa = false;
	}
	
	//cURL セッションを初期化
	return $aa;
}

//▼消費税計算
function zCulcTax($amount,$rate,$brake=''){
		
		$t = $amount * $rate / 100;

		switch ($i) {
			case 'c':
				$res = ceil($t);
				break;
			case 'f':
				$res = floor($t);
				break;
			case 'r':
				$res = round($t);
				break;
			default:
				$res = floor($t);
		}
	
	return $res;
}



/*-------- エラーログ --------*/
function write_log($string,$mode){
	
	//▼保存先
	$filepath =  "./log/".basename($_SERVER['PHP_SELF'],'.php').".log";
	
	//▼追記モードで開く
	$fp = fopen($filepath,$mode); 

	//▼ログ追加
	fwrite($fp, $string);
	fclose($fp);
}


//▼ポップアップ
class mkPop{

	public $subject;
	public $popcontens;

	function getPop(){
		$pop_in = '<div id="popcontain"></div>';
		$pop_in.= '<div id="popup">';
		$pop_in.= '<div class="popup_inner">';
		$pop_in.= '<div class="subject_area">';
		$pop_in.= '<p>'.$this->subject.':<span id="Subject"></span></p>';
		$pop_in.= '</div>';
		$pop_in.= '<div>';
		$pop_in.= $this->popcontens;
		$pop_in.= '</div>';
		$pop_in.= '<p id="PopClose" class="close">×</p>';
		$pop_in.= '</div>';
		$pop_in.= '</div>';
		
		return $pop_in;
	}
}

//▼設定ファイルを作成
function write_setting($filepath,$cot,$mode){
	
	//▼内容追加
	$string = "<?php"."\n";
	$string.= $cot."\n";
	$string.= "?>";
	
	//▼ファイルを開く
	$fp = fopen($filepath,$mode); 

	//▼内容を追加
	fwrite($fp, $string);
	fclose($fp);
}

function sync_rank_typ01004(){
    $query = tep_db_query("SELECT * FROM m_rank WHERE state=1");

    tep_db_trancate('typ01004');

    while($a = tep_db_fetch_array($query)){
        $data = [
            'id' => $a['m_rank_bctype'],
            'tag' => $a['m_rank_name'],
            'margin' => 0
         ];
        tep_db_perform('typ01004',$data,'insert');

    }
}

function zCheckboxSet2($in_array,$data_in,$name,$option=''){

    $checkl = '';
    foreach($in_array AS $k => $v){
        $che_r = '';
        foreach($data_in as $di){
            if($k == $di){
                $che_r  = 'checked';
            }
        }
        $cl_r   = 'class="i_radio"';
        $checkl.= (($checkl)? '<br>':'').'<input type="checkbox" name="'.$name.'" value="'.$k.'" '.$cl_r.' '.$che_r.' '.$option.'> '.$v;
    }

    return $checkl;
}


?>