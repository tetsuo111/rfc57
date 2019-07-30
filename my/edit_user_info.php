<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id  = $_COOKIE['user_id'];
	$memberid = $_COOKIE['user_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}


//----- 初期設定 -----//
//▼とび先設定
$form_action_to   = basename($_SERVER['PHP_SELF']);
$form_action_next = 'edit_user_address.php';		//オーダーに変更
//$form_action_next   = 'order.php';					//オーダーに変更


//▼登録ボタン用
$disabled = 'disabled';

//▼表示処理
$target_a = '';
$target_b = '';

//▼Navigation
if($_COOKIE['bctype'] == '21'){
	//▼ユーザーは写真を削除
	unset($NavUserInfoJP['edit_user_upload']);
}
$my_nav = zGetMyNavigation($NavUserInfoJP,$form_action_to);

//▼ニックネーム許可パターン
$ng_pattern   = '[a-zA-Z0-9ぁ-んァ-ヶー一-龠]+$';
$ng_pattern_p = '[^a-zA-Z0-9ぁ-んァ-ヶー一-龠]+/u';


//-------------関数定義-------------//
//▼電話番号チェック
function tel_num_treat($tel_num){

	//▼半角にする
	$check_tel = mb_convert_kana($tel_num, "sa");
	
	//▼スペース削除
	$check_tel = str_replace(" ", "", $check_tel);
	
	//「-」を削除する 「-」入り希望のため削除
	$check_tel = str_replace("―", "", $check_tel);//全角ダッシュ
	$check_tel = str_replace("－", "", $check_tel);//全角マイナス
	$check_tel = str_replace("‐", "", $check_tel);//全角ハイフン
	$check_tel = str_replace("-" , "", $check_tel);//半角ハイフン
	$check_tel = str_replace("ー", "", $check_tel);//長音

	return $check_tel;
}


//-------------必要情報読込-------------
//▼登録情報取得
$regtype         = $_POST['regtype'];			//登録区分

$user_name       = $_POST['user_name'];
$user_name2      = $_POST['user_name2'];
$user_name_kana  = $_POST['user_name_kana'];
$user_name_kana2 = $_POST['user_name_kana2'];
$user_name_en    = $_POST['user_name_en'];
$user_name_en2   = $_POST['user_name_en2'];

$user_sex        = $_POST['user_sex'];
$user_tel_t      = tel_num_treat($_POST['user_tel_t']);
$user_tel_m      = tel_num_treat($_POST['user_tel_m']);
$user_tel_f      = tel_num_treat($_POST['user_tel_f']);

$user_birth_y    = str_pad($_POST['user_birth_y'], 4, "0", STR_PAD_LEFT);
$user_birth_m    = str_pad($_POST['user_birth_m'], 2, "0", STR_PAD_LEFT);
$user_birth_d    = str_pad($_POST['user_birth_d'], 2, "0", STR_PAD_LEFT);

$user_label      = $_POST['user_label'];

//法人対応
$cmp_name2       = $_POST['cmp_name2'];			//法人名
$cmp_name2_kana  = $_POST['cmp_name2_kana'];	//法人カナ
$cmp_hat         = $_POST['cmp_hat'];			//法人肩書


//▼編集禁止
$ust = zUserStatusCheck($user_id);
if($ust['info']){$no_edit = 'readonly';}


//------------------ データ処理 ------------------//
if(($_POST['act'] == 'process')AND(!empty($_POST['act_send']))){
	
	//▼生年月日
	$user_birth_y = str_pad($user_birth_y, 4, "0", STR_PAD_LEFT);
	$user_birth_m = str_pad($user_birth_m, 2, "0", STR_PAD_LEFT);
	$user_birth_d = str_pad($user_birth_d, 2, "0", STR_PAD_LEFT);
	$user_birth   = $user_birth_y.'-'.$user_birth_m.'-'.$user_birth_d;

	
	//▼ユーザー情報
	$sql_data_array = array(
		'regtype'   => $regtype,
		'memberid'  => $memberid,
		'name1'     => $user_name.' '.$user_name2,
		'name1kana' => $user_name_kana.' '.$user_name_kana2,
		'name2'     => (($cmp_name2)?      $cmp_name2     :'null'),
		'name2kana' => (($cmp_name2_kana)? $cmp_name2_kana:'null'),
		'memo2'     => (($cmp_hat)?        $cmp_hat       :'null'),
		'birthday'  => $user_birth,
		'sextype'   => $user_sex,
		'phone1'    => $user_tel_t,
		'intcount'  => (($user_label)? $user_label:'null')
	);
	
	//▼名前設定
	tep_cookie_set('user_name',$user_name.$user_name2);		//
	
	//▼テーブル指定
	$w_set = "`memberid`='".tep_db_input($memberid)."'";
	tep_db_perform(TABLE_MEM00000,$sql_data_array,'update',$w_set);
	
	
	//▼ローマ字用
	$sql_en_ar = array(
		'memberid'      => $memberid,
		'user_name_en'  => $user_name_en,
		'user_name_en2' => $user_name_en2,
		'date_create'   => 'now()',
		'state'         => '1'
	);
	
	
	$info_table = TABLE_USER_INFO;
	//▼登録確認
	$check_en = tep_db_query("
		SELECT
			`user_info_id` AS `id`
		FROM `".$info_table."`
		WHERE `memberid` = '".tep_db_input($memberid)."'
		AND   `state`    = '1'
	");
	
	if($a = tep_db_fetch_array($check_en)){
		//更新登録
		zDBUpdate($info_table,$sql_en_ar,$a['id']);
		
	}else{
		//新規登録
		zDBNewUniqueID($info_table,$sql_en_ar,'user_info_ai_id','user_info_id');
	}
	
	//----- ステータス更新 -----//
	$st = zUserStatusCheck($user_id);
	
	//更新の時は何もしない
	if($st['info'] != 'u'){
		
		//n　＞　u更新に変更
		if($st['info'] == 'n'){
			$res = 'u';
		}else{
			$res = '1';
		}
		//▼ステータス更新
		zUserWCStatusUpdate('user_wc_status_info',$res,$user_id);
	}
	
	
	//▼登録後設定
	if($st['info']){
		//更新登録
		$end = '<script>alert("登録しました");</script>';
		
	}else{
		//新規登録
		$end = '<script>alert("登録しました");location.href="'.$form_action_next.'";</script>';
	}
	
	echo $end;
	
	
}else if($_POST['act'] == 'process'){
	
	//-------- エラーチェック --------//
	$err = false;
	
	if(!$regtype){$err = true; $err_regtype = true;}
	
	//----- 法人情報 -----//
	if($regtype == 2){
		if(!$cmp_name2)     {$err = true; $err_cmp      = true;}
		if(!$cmp_name2_kana){$err = true; $err_cmp_kana = true;}
		if(!$cmp_hat)       {$err = true; $err_cmp_hat  = true;}
	}
	
	
	//----- 個人情報 -----//
	if((!$user_name)OR(!$user_name2)) { $err = true; $err_name = true; }
	
	//----- カナ確認 -----//
	//▼姓カナ
	if(!$user_name_kana) {
		$err = true; $err_name_k = true; $err_kana = '未記入';
	}else{
		$user_name_kana  = mb_convert_kana($user_name_kana,'KC');
	}
	
	if(!$user_name_kana2){
		$err = true; $err_name_k = true; $err_kana = '未記入';
	}else{
		$user_name_kana2 = mb_convert_kana($user_name_kana2,'KC');
	}
	
	
	//----- ローマ字確認 -----//
	//▼姓ローマ字
	if(!$user_name_en)   {
		$err = true; $err_name_e = true; $err_en = '未記入';
	}else{
		$user_name_en = mb_convert_kana($user_name_en,'as');
		if(!preg_match("/^[a-zA-Z0-9 ]+$/", $user_name_en)){$err = true; $err_name_e = true; $err_en = '半角英数字以外';}
	}
	
	//▼名ローマ字
	if(!$user_name_en2)   {
		$err = true; $err_name_e = true; $err_en = '未記入';
	}else{
		$user_name_en2 = mb_convert_kana($user_name_en2,'as');
		if(!preg_match("/^[a-zA-Z0-9 ]+$/", $user_name_en2)){$err = true; $err_name_e = true; $err_en = '半角英数字以外';}
	}
	
	
	if(!$user_sex)   { $err = true; $err_sex   = true;}
	if(!$user_tel_t) { $err = true; $err_tel_t = true;}
	if(($user_birth_y == 0000 ) || ($user_birth_m == 00) || ($user_birth_d == 00)){ 
		$err = true; $err_birth = true; 
	}
	
	//▼ニックネーム
	if($user_label){
		//指定した文字列以外が入っていたらNG
		if(preg_match("/".$ng_pattern_p."/", $user_label) !== false){
			$err = true; $err_label = true; 
		}
	}
	
	//-------- 表示設定 --------//
	if(($err == false)AND(empty($_POST['act_cancel']))){
	
		//▼エラーなし　＞　確認画面
		$form_select = 'process';
		
	}else{
		if($err_name      == true) { $edit_err_name     = '<span class="err"> 未記入</span>'; }
		if($err_name_k    == true) { $edit_err_name_k   = '<span class="err"> '.$err_kana.'</span>'; }
		if($err_name_e    == true) { $edit_err_name_e   = '<span class="err"> '.$err_en.'</span>'; }
		if($err_tel_t     == true) { $edit_err_tel_t    = '<span class="err"> 未記入</span>'; }
		if($err_birth     == true) { $edit_err_birth    = '<span class="err"> 未選択</span>'; }
		if($err_sex       == true) { $edit_err_sex      = '<span class="err"> 未選択</span>'; }
		
		if($err_regtype   == true) { $edit_err_regtype  = '<span class="err"> 未選択</span>'; }
		if($err_cmp       == true) { $edit_err_cmp      = '<span class="err"> 未記入</span>'; }
		if($err_cmp_kana  == true) { $edit_err_cmp_kana = '<span class="err"> 未記入</span>'; }
		if($err_cmp_hat   == true) { $edit_err_cmp_hat  = '<span class="err"> 未記入</span>'; }
	}

} else {


	//-------- 初期設定 --------//
	//▼氏名、生年月日
	$query_info = tep_db_query("
		SELECT
			`m0`.`regtype`,
			`m0`.`name1`     AS `name`,
			`m0`.`name1kana` AS `namekana`,
			`m0`.`name2`,
			`m0`.`name2kana`,
			`m0`.`memo2`,
			`m0`.`birthday`, 
			DATE_FORMAT(`m0`.`birthday`, '%Y') AS user_birth_y,
			DATE_FORMAT(`m0`.`birthday`, '%m') AS user_birth_m,
			DATE_FORMAT(`m0`.`birthday`, '%d') AS user_birth_d,
			`m0`.`sextype`,
			`m0`.`phone1`,
			`m0`.`intcount`,
			`ui`.`user_name_en`,
			`ui`.`user_name_en2`
		FROM      `".TABLE_MEM00000."`  AS `m0`
		LEFT JOIN `".TABLE_USER_INFO."` AS `ui` ON `ui`.`memberid` = `m0`.`memberid`
		WHERE `m0`.`memberid` = '".tep_db_input($memberid)."'
		AND   `ui`.`memberid` = '".tep_db_input($memberid)."'
		AND   `ui`.`state`    = '1'
	");
	
	
	if($us = tep_db_fetch_array($query_info)){
		$un_a = explode(' ',$us['name']);
		$un_b = explode(' ',$us['namekana']);
		
		$regtype         = $us['regtype'];			//登録区分
		
		$user_name       = $un_a[0];
		$user_name2      = $un_a[1];
		$user_name_kana  = $un_b[0];
		$user_name_kana2 = $un_b[1];
		$user_name_en    = $us['user_name_en'];
		$user_name_en2   = $us['user_name_en2'];
		$user_sex        = $us['sextype'];
		$user_tel_t      = $us['phone1'];
		$user_birth_y    = $us['user_birth_y'];
		$user_birth_m    = $us['user_birth_m'];
		$user_birth_d    = $us['user_birth_d'];
		$user_label      = $us['intcount'];
		
		//▼法人対応
		$cmp_name2       = $us['name2'];			//法人名
		$cmp_name2_kana  = $us['name2kana'];		//法人カナ
		$cmp_hat         = $us['memo2'];			//法人肩書
	}
	
	//▼表示処理
	$target_a = (!$user_name)?   'class="tglTargetA"':'';
}



//------------------表示フォーム------------------
//▼表示幅設定
$col_name     = 'col-xs-2  col-sm-2  col-md-1 col-lg-1';
$col_input    = 'col-xs-10 col-sm-10 col-md-4 col-lg-4';
$col_name_btn = 'col-xs-12 col-sm-12 col-md-2 col-lg-2';

$col_birth    = 'col-xs-4  col-sm-4  col-md-2 col-lg-2';

$cl_cmp   = ($regtype == 2)? 'class="cmp isOpen"':'class="cmp"';

if($form_select == 'process'){
	
	//▼自動登録項目
	$input_auto = '<input type="hidden" name="act"          value="process">';				//登録
	$input_auto.= '<input type="hidden" name="regtype"      value="'.$regtype.'">';			//登録区分
	$input_auto.= '<input type="hidden" name="user_birth_y" value="'.$user_birth_y.'">';	//年
	$input_auto.= '<input type="hidden" name="user_birth_m" value="'.$user_birth_m.'">';	//月
	$input_auto.= '<input type="hidden" name="user_birth_d" value="'.$user_birth_d.'">';	//日
	
	$user_form_ele_text = $edit_err_text;
	
	$keep = 'readonly';
	
	
	//登録区分
	$regtype_in = '<p>'.$RegTypeArray[$regtype].'</p>';
	
	//----- 法人情報登録 -----//
	$cmp_name2      = '<input class="form-control" type="text" name="cmp_name2"      value="'.$cmp_name2.'"      id="CmpName" '.$keep.'>';		//法人名漢字
	$cmp_name2_kana = '<input class="form-control" type="text" name="cmp_name2_kana" value="'.$cmp_name2_kana.'" id="CmpKana" '.$keep.'>';		//法人名カナ
	$cmp_hat        = '<input class="form-control w160" type="text" name="cmp_hat"   value="'.$cmp_hat.'"        id="CmpHat"  '.$keep.'>';		//肩書
	
	//----- 個人情報 -----//
	$kanji_1in = '<input class="form-control keep" type="text" name="user_name"       value="'.$user_name.'"       '.$keep.'>';		//name
	$kanji_2in = '<input class="form-control keep" type="text" name="user_name2"      value="'.$user_name2.'"      '.$keep.'>';		//name2
	
	$kana_1in  = '<input class="form-control keep" type="text" name="user_name_kana"  value="'.$user_name_kana.'"  '.$keep.'>';		//kana
	$kana_2in  = '<input class="form-control keep" type="text" name="user_name_kana2" value="'.$user_name_kana2.'" '.$keep.'>';		//kana2
	
	$roma_1in  = '<input class="form-control keep" type="text" name="user_name_en"    value="'.$user_name_en.'"    '.$keep.'>';		//roma
	$roma_2in  = '<input class="form-control keep" type="text" name="user_name_en2"   value="'.$user_name_en2.'"   '.$keep.'>';		//roma2
	
	
	$tel_in    = '<input class="form-control keep" type="tel" name="user_tel_t" value="'.$user_tel_t.'" '.$keep.'>';				//電話番号
	
	//▼表示名
	$label_in  = '<input class="form-control keep" type="text" name="user_label" value="'.$user_label.'" '.$keep.'>';				//表示名
	
	//▼性別
	$sex_in = '<input class="form-control keep" type="text" name="user_sex" value="'.$UserSexArray[$user_sex].'" '.$keep.'>';
	$sex_in.= '<input type="hidden" name="user_sex" value="'.$user_sex.'">';
	
	//▼誕生日
	$birth_1 = '<input class="form-control keep" type="text" value="'.$user_birth_y.'年" '.$keep.'>';			//年
	$birth_2 = '<input class="form-control keep" type="text" value="'.$user_birth_m.'月" '.$keep.'>';			//月
	$birth_3 = '<input class="form-control keep" type="text" value="'.$user_birth_d.'日" '.$keep.'>';			//日
	
	//▼登録ボタン
	$user_form_submit = '<input type="submit" class="btn form_submit"         name="act_send"   value="登録する">';
	$user_form_submit.= '<input type="submit" class="btn form_cancel spc10_l" name="act_cancel" value="キャンセル">';
	
	
}else{

	//----- フォーム設定 -----//
	$user_form_ele_text = $edit_err_text;
	
	//▼登録ボタン　＞　既存の場合は変更不可
	$input_auto = '<input type="hidden" name="act" value="process">';		//登録フォーム
	$user_form_submit = '<input type="submit" class="btn form_submit" value="入力内容を確認する" id="Act" '.$disabled.'>';
	
	//▼変更ボタン
	if(!$no_edit){
		$name_set   = '<div class="'.$col_name_btn.'" style="text-align:center;"><button type="button" class="btn name_se" id="iOnToggleA">入力を続ける</button></div>';					//名前設定
		$name_reset = '<div class="'.$col_name_btn.'" style="text-align:center;"><button type="button" class="btn name_se" onClick="resetName();">名前リセット</button></div>';				//名前リセット
	}
	
	//▼個人法人分類
	if($no_edit){
		
		$regtype_in     = '<p><input type="hidden" name="regtype" value="'.$regtype.'" '.$no_edit.'>'.$RegTypeArray[$regtype].'</p>';
		
	}else{
		
		foreach($RegTypeArray AS $k => $v){
			$clrg = ($regtype_in)? ' class="spc10_l"':'';
			$chrg = ($regtype == $k)? 'checked="checked"':'';
			$regtype_in.= '<span'.$clrg.'><input type="radio" name="regtype" value="'.$k.'" '.$chrg.'>'.$v.'</span>';
		}
	}
	
	
	//----- 法人情報登録 -----//
	$cmp_name2      = '<input class="form-control"      type="text" name="cmp_name2"      value="'.$cmp_name2.'"      '.$no_edit.' id="CmpName">';
	$cmp_name2_kana = '<input class="form-control"      type="text" name="cmp_name2_kana" value="'.$cmp_name2_kana.'" '.$no_edit.' id="CmpKana">';
	$cmp_hat        = '<input class="form-control w160" type="text" name="cmp_hat"        value="'.$cmp_hat.'"        '.$no_edit.' id="CmpHat">';
	
	
	//----- 個人情報登録 -----//
	$kanji_1in = '<input class="form-control" id="NameKanji1" type="text" name="user_name"       value="'.$user_name.'"       autocomplete="off" required '.$no_edit.'>';							//name
	$kanji_2in = '<input class="form-control" id="NameKanji2" type="text" name="user_name2"      value="'.$user_name2.'"      autocomplete="off" required '.$no_edit.'>';							//name2
	
	$kana_1in  = '<input class="form-control" id="NameKana1"  type="text" name="user_name_kana"  value="'.$user_name_kana.'"  autocomplete="off" required '.$no_edit.'>';							//kana
	$kana_2in  = '<input class="form-control" id="NameKana2"  type="text" name="user_name_kana2" value="'.$user_name_kana2.'" autocomplete="off" required '.$no_edit.'>';							//kana2

	$roma_1in  = '<input class="form-control" id="NameRoma1"  type="text" name="user_name_en"    value="'.$user_name_en.'"    autocomplete="off" pattern="^[0-9A-Za-z]+$" required '.$no_edit.'>';	//roma
	$roma_2in  = '<input class="form-control" id="NameRoma2"  type="text" name="user_name_en2"   value="'.$user_name_en2.'"   autocomplete="off" pattern="^[0-9A-Za-z]+$" required '.$no_edit.'>';	//roma2

	//▼性別
	$checked_sex_m = ($user_sex == 'm')? 'checked' : '';
	$checked_sex_w = ($user_sex == 'w')? 'checked' : '';
	$sex_in = '<label class="radio-inline"><input type="radio" name="user_sex" value="m" id="r1" '.$checked_sex_m.'>男性</label>';
	$sex_in.= '<label class="radio-inline"><input type="radio" name="user_sex" value="w" id="r2" '.$checked_sex_w.'>女性</label>　';
	
	//▼電話番号
	$tel_in   = '<input class="form-control" type="tel"  name="user_tel_t" value="'.$user_tel_t.'" id="Telt" pattern="^[0-9]+$" placeholder="01234567">';
	
	//▼表示名
	$label_in = '<input class="form-control" type="text" name="user_label" value="'.$user_label.'" pattern="'.$ng_pattern.'" maxlength="10">';
	
	
	//----- 生年月日 -----//
	if($no_edit){
		
		//▼編集不可の場合
		$birth_1 = '<input class="form-control keep" type="text" name="user_birth_y" value="'.$user_birth_y.'" '.$no_edit.'>';		//年
		$birth_2 = '<input class="form-control keep" type="text" name="user_birth_m" value="'.$user_birth_m.'" '.$no_edit.'>';		//月
		$birth_3 = '<input class="form-control keep" type="text" name="user_birth_d" value="'.$user_birth_d.'" '.$no_edit.'>';		//日
		
	}else{
		
		//▼年
		$birth_1 = '<select class="form-control selcet_birth" name="user_birth_y" id="birthY">';
		$birth_1.= '<option value="">年</option>';
		
		for ($y = 1920; $y < date('Y'); $y++){ 
			$selected_y = '';
			if($user_birth_y == $y){ $selected_y = 'selected';}
			$birth_1.= '<option value="'.$y.'" '.$selected_y.'>'.$y.'</option>'; 
		}
		
		$birth_1.= '</select>';
		
		
		//▼月
		$birth_2 = '<select class="form-control selcet_birth" name="user_birth_m" id="birthM">';
		$birth_2.= '<option value="">月</option>';
		
		for ($m = 1; $m <= 12; $m++){
			$selected_m = "";
			if($user_birth_m == $m){ $selected_m = 'selected'; }
			$birth_2.= '<option value="'.$m.'" '.$selected_m.'>'.str_pad($m, 2, "0", STR_PAD_LEFT).'</option>';
		}
		
		$birth_2.= '</select>';
		
		
		//▼生年月日　＞　日
		$birth_3 = '<select class="form-control selcet_birth" name="user_birth_d" id="birthD">';
		$birth_3.= '<option value="">日</option>';
		for ($d = 1; $d <= 31; $d++){
			$selected_d = "";
			if($user_birth_d == $d){ $selected_d = 'selected'; }
			$birth_3.= '<option value="'.$d.'" '.$selected_d.'>'.str_pad($d, 2, "0", STR_PAD_LEFT).'</option>';
		}
		$birth_3.= '</select>';
	}
}



//----- 表示内容 -----//
//▼必須
$must = '<span style="font-size:12pt; font-weight:400; color:#DD0000; vertical-align:super;">*</span>';

//▼氏名
$user_form_name_1 = '<div class="form-inline">';
$user_form_name_1.= '<div class="form-group"><div class="'.$col_name.'">姓:</div><div class="'.$col_input.'">'.$kanji_1in.'</div></div>';	//name
$user_form_name_1.= '<div class="form-group"><div class="'.$col_name.'">名:</div><div class="'.$col_input.'">'.$kanji_2in.'</div></div>';	//name2
$user_form_name_1.= '<div class="form-group">'.$name_set.'</div>';
$user_form_name_1.= '</div>';

$user_form_name_2 = '<div class="form-inline">';
$user_form_name_2.= '<div class="form-group"><div class="'.$col_name.'">姓:</div><div class="'.$col_input.'">'.$kana_1in.'</div></div>';	//kana
$user_form_name_2.= '<div class="form-group"><div class="'.$col_name.'">名:</div><div class="'.$col_input.'">'.$kana_2in.'</div></div>';	//kana2
$user_form_name_2.= '<div class="form-group">'.$name_reset.'</div>';
$user_form_name_2.= '</div>';

$user_form_name_3 = '<div class="form-inline">';
$user_form_name_3.= '<div class="form-group"><div class="'.$col_name.'">姓:</div><div class="'.$col_input.'">'.$roma_1in.'</div></div>';	//roma
$user_form_name_3.= '<div class="form-group"><div class="'.$col_name.'">名:</div><div class="'.$col_input.'">'.$roma_2in.'</div></div>';	//roma2
$user_form_name_3.= '</div>';

//$user_form_name_3 = $roma_1in;	//roma
//$user_form_name_3.= $roma_2in;	//roma2


//▼ニックネーム
$user_form_label = '<div class="tel">'.$label_in.'</div>';	//user_label


//▼電話、性別
$user_form_tel = '<div class="tel">'.$tel_in.'</div>';		//tel
$user_form_sex = '<div class="tel">'.$sex_in.'</div>';		//sex

//▼誕生日
$user_form_birth = '<div class="form-group">';
$user_form_birth.= '<div class="'.$col_birth.'">'.$birth_1.'</div>';
$user_form_birth.= '<div class="'.$col_birth.'">'.$birth_2.'</div>';
$user_form_birth.= '<div class="'.$col_birth.'">'.$birth_3.'</div>';
$user_form_birth.= '</div>';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type"         content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type"  content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<meta http-equiv="X-UA-Compatible"      content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php echo $favicon."\n"; ?>
	<title><?php echo $title;?></title>
	<meta name="description"       content="">
	<meta name="keywords"          content="">
	<meta name="robots"            content="noindex,nofollow,noarchive">
	<meta name="format-detection" content="telephone=no">
	<meta name="format-detection" content="email=no">
	<link rel="stylesheet" type="text/css" href="../css/cssreset.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/common.css"   media="all">
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="../css/my.css"       media="all">
	
	<script src="../js/jquery-3.2.1.min.js"            charset="UTF-8"></script>
	<script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>

	<script>
		function zChangeChr0(T){
			var res = T.charAt(0).toUpperCase() + T.slice(1);
			return res;
		}
		
		$(function(){

			//▼大文字小文字変換
			$('#NameRoma1').on('change',function (){
				var a = $('#NameRoma1').val();
				t = zChangeChr0(a)
				$('#NameRoma1').val(t);
			});
			
			$('#NameRoma2').on('change',function (){
				var a = $('#NameRoma2').val();
				t = zChangeChr0(a)
				$('#NameRoma2').val(t);
			});
		});
	</script>

	<style>
		.tglTargetA .form_el{display:none;}
		
		.form_submit{max-width:180px; width:100%;}
		
		.input_text.keep  {background:#E4E4E4;}
		.input_text_f.keep{background:#E4E4E4;}
		
		.tel{max-width:300px; margin:0 20px;}
		
		.w80{width:80px;}
		.w120{width:120px;}
		.w160{width:160px;}
		
		.cmp{display:none;}
		.cmp.isOpen{display:block;}
	</style>
</head>
<body>
<div id="wrapper">
	
	<div id="header">
		<?php require('inc_user_header.php');?>
	</div>
	
	<div class="container-fluid">
		<div id="content" class="row">
			
			<div id="left1" class="col-md-4 col-lg-2">
				<div class="inner">
					<div class="u_menu_area">
						<?php require('inc_user_left.php'); ?>
					</div>
				</div>
			</div>
			
			<div id="left2" class="col-xs-12 col-md-12 col-md-8 col-lg-10">
				<div class="inner">
					
					<div>
						<?php echo $my_nav;?>
					</div>
					
					<form name="UserForm" action="<?php echo $form_action_to;?>" method="POST" id="UserForm" class="form-horizontal">
						<?php echo $input_auto;?>
					
						<div class="form_group form_area">
							<h3>あなたについて教えてください</h3>
							<ul class="form_table">
								<li>
									<div class="form_el row">
										<h4>登録区分<?php echo $must;?></h4>
										<?php echo $regtype_in;?>
									</div>
								</li>
								
								<li <?php echo $cl_cmp;?>>
									<div class="form_el row">
										<h4>法人名（漢字）<?php echo $must;?></h4>
										<?php echo $cmp_name2;?>
									</div>
								</li>
								
								<li <?php echo $cl_cmp;?>>
									<div class="form_el row">
										<h4>法人名（カナ）<?php echo $must;?></h4>
										<?php echo $cmp_name2_kana;?>
									</div>
								</li>

								<li <?php echo $cl_cmp;?>>
									<div class="form_el row">
										<h4>肩書<?php echo $must;?></h4>
										<?php echo $cmp_hat;?>
									</div>
								</li>

								<li>
									<div class="form_el row">
										<h4>姓名（漢字）<?php echo $must;?></h4>
										<?php echo $user_form_name_1;?>
									</div>
								</li>
								<li <?php echo $target_a;?>>
									<div class="form_el row">
										<h4>姓名（カナ）<?php echo $must;?></h4>
										<?php echo $user_form_name_2;?>
									</div>
								</li>
								<li <?php echo $target_a;?>>
									<div class="form_el row">
										<h4>姓名（ローマ字）<?php echo $must;?></h4>
										<?php echo $user_form_name_3; ?>
									</div>
								</li>
								<li <?php echo $target_a;?>>
									<div class="form_el row">
										<h4>ニックネーム</h4>
										<?php echo $user_form_label; ?>
										<p class="alert">最大10文字、「#,$」などの記号不可</p>
									</div>
								</li>
								<li <?php echo $target_a;?>>
									<div class="form_el row">
										<h4>性別<?php echo $must;?></h4>
										<?php echo $user_form_sex; ?>
									</div>
								</li>
								<li <?php echo $target_a;?>>
									<div class="form_el row">
										<h4>連絡先電話番号<?php echo $must;?></h4>
										<?php echo $user_form_tel; ?>
										<p class="alert">「ハイフンなし入力」</p>
									</div>
								</li>
								<li <?php echo $target_a;?>>
									<div class="form_el row">
										<h4>生年月日<?php echo $must;?></h4>
										<?php echo $user_form_birth; ?>
									</div>
								</li>
							</ul>
						</div>
						
						<div class="submit_area spc20">
							<?php echo $user_form_submit;?>
						</div>
					</form>
				
				</div>
			</div>
		</div>
	</div>
	<script src="../js/MyHelper.js" charset="UTF-8"></script>
		
	<div id="footer">
		<?php require('inc_user_footer.php');?>
	</div>
</div>
<script src="../js/autoKana.js" charset="UTF-8"></script>
<script>
//▼変換定義
var Kanji1 = '#NameKanji1';
var Kana1  = '#NameKana1';
var Roma1  = '#NameRoma1';

var Kanji2 = '#NameKanji2';
var Kana2  = '#NameKana2';
var Roma2  = '#NameRoma2';

//▼入力リセット
function resetName(){
	$(Kanji1).val('');
	$(Kana1).val('');
	$(Roma1).val('');

	$(Kanji2).val('');
	$(Kana2).val('');
	$(Roma2).val('');
}

//▼漢字ひらがな変換
$(document).ready( function(){
	//▼名前(カタカナ)
	$.fn.autoKana( Kanji1, Kana1, { katakana : true });
	$.fn.autoKana( Kanji2, Kana2, { katakana : true });
});


//▼姓入力
$(Kanji1).on('change',function(){
	
	//▼初期化
	$(Roma1).val('');

	//▼結果取得
	var res =(function(){
		
		var Kn = $(Kana1).val();
		var aa = toRoman(Kn);
		
		//▼最初の一文字処理
		tmp = aa.substring(0,1).toUpperCase(); 
		aa = tmp + aa.substring(1,(aa.length + 1));
		
		return aa;
	}());
	
	$(Roma1).val(res);
});

//▼名入力
$(Kanji2).on('change',function(){

	//▼初期化
	$(Roma2).val('');

	//▼結果取得
	var res =(function(){
		
		var Kn = $(Kana2).val();
		var aa = toRoman(Kn);
		
		//▼最初の一文字処理
		tmp = aa.substring(0,1).toUpperCase(); 
		aa = tmp + aa.substring(1,(aa.length + 1));
		
		return aa;
	}());
	
	$(Roma2).val(res);
});

//▼ローマ字変換　式
var toRoman = (function () {
	var roman = {
		'１':'1', '２':'2', '３':'3', '４':'4', '５':'5', '６':'6', '７':'7', '８':'8', '９':'9', '０':'0',
		'！':'!', '”':'"', '＃':'#', '＄':'$', '％':'%', '＆':'&', '’':"'", '（':'(', '）':')', '＝':'=',
		'～':'~', '｜':'|', '＠':'@', '‘':'`', '＋':'+', '＊':'*', '；':";", '：':':', '＜':'<', '＞':'>',
		'、':',', '。':'.', '／':'/', '？':'?', '＿':'_', '・':'･', '「':'[', '」':']', '｛':'{', '｝':'}',
		'￥':'\\', '＾':'^',
		'ファ':'fa', 'フィ':'fi', 'フェ':'fe', 'フォ':'fo',
		'キャ':'kya', 'キュ':'kyu', 'キョ':'kyo',
		'シャ':'sha', 'シュ':'shu', 'ショ':'sho',
		'チャ':'tya', 'チュ':'tyu', 'チョ':'tyo',
		'ニャ':'nya', 'ニュ':'nyu', 'ニョ':'nyo',
		'ヒャ':'hya', 'ヒュ':'hyu', 'ヒョ':'hyo',
		'ミャ':'mya', 'ミュ':'myu', 'ミョ':'myo',
		'リャ':'rya', 'リュ':'ryu', 'リョ':'ryo',
		'フャ':'fya', 'フュ':'fyu', 'フョ':'fyo',
		'ピャ':'pya', 'ピュ':'pyu', 'ピョ':'pyo',
		'ビャ':'bya', 'ビュ':'byu', 'ビョ':'byo',
		'ヂャ':'dya', 'ヂュ':'dyu', 'ヂョ':'dyo',
		'ジャ':'ja',  'ジュ':'ju',  'ジョ':'jo',
		'ギャ':'gya', 'ギュ':'gyu', 'ギョ':'gyo',
		'パ':'pa', 'ピ':'pi', 'プ':'pu', 'ペ':'pe', 'ポ':'po',
		'バ':'ba', 'ビ':'bi', 'ブ':'bu', 'ベ':'be', 'ボ':'bo',
		'ダ':'da', 'ヂ':'di', 'ヅ':'du', 'デ':'de', 'ド':'do',
		'ザ':'za', 'ジ':'ji', 'ズ':'zu', 'ゼ':'ze', 'ゾ':'zo',
		'ガ':'ga', 'ギ':'gi', 'グ':'gu', 'ゲ':'ge', 'ゴ':'go',
		'ワ':'wa', 'ヰ':'wi', 'ウ':'wu', 'ヱ':'we', 'ヲ':'wo',
		'ラ':'ra', 'リ':'ri', 'ル':'ru', 'レ':'re', 'ロ':'ro',
		'ヤ':'ya',            'ユ':'yu',            'ヨ':'yo',
		'マ':'ma', 'ミ':'mi', 'ム':'mu', 'メ':'me', 'モ':'mo',
		'ハ':'ha', 'ヒ':'hi', 'フ':'fu', 'ヘ':'he', 'ホ':'ho',
		'ナ':'na', 'ニ':'ni', 'ヌ':'nu', 'ネ':'ne', 'ノ':'no',
		'タ':'ta', 'チ':'chi', 'ツ':'tsu', 'テ':'te', 'ト':'to',
		'サ':'sa', 'シ':'si', 'ス':'su', 'セ':'se', 'ソ':'so',
		'カ':'ka', 'キ':'ki', 'ク':'ku', 'ケ':'ke', 'コ':'ko',
		'ア':'a', 'イ':'i', 'ウ':'u', 'エ':'e', 'オ':'o',
		'ァ':'la', 'ィ':'li', 'ゥ':'lu', 'ェ':'le', 'ォ':'lo',
		'ガ':'ke', 'カ':'ka',
		'ン':'n',  'ー':'-', '　':' '
	};
	
	//gグローバルマッチ　⇒　全て一致させる
	//m複数行マッチさせる
	var reg_tu  = /っ([bcdfghijklmnopqrstuvwyz])/gm;
	var reg_xtu = /っ/gm;

	return function (str) {
	
		var pnt = 0;
		var max = str.length;
		var s, r;
		var txt = '';
		
		while( pnt <= max ) {
			//substring　文字列抜き出し(開始位置,終了位置)
			//開始位置から2文字取得　＞　なければ一文字で取得
			if( r = roman[ str.substring( pnt, pnt + 2 ) ] ) {
				txt += r;
				pnt += 2;
			} else {
				//一文字取得　＞　配列にあればそのまま変換
				s = str.substring( pnt, pnt + 1 );
				
				//なければそのまま文字列を残す　＞　「っ」が残る
				if(r = roman[s]){
					txt += r;
				}else{
					txt += s;
				}
				pnt += 1;
			}
		}
		
		//「っ」処理
		//▼通常処理
		txt = txt.replace( reg_tu, '$1$1' );
		
		//▼単体処理
		txt = txt.replace( reg_xtu, 'xtu' );

		return txt;
	};
}());


//----- ユーザーアクション -----//
var FlagA = false;

$('#iOnToggleA').on('click',function(){
	
	if(!FlagA){
		$('.tglTargetA .form_el').slideToggle(800);
		$('#iOnToggleA').prop('disabled',true);
		FlagA = true;
	}
});
</script>

<script>
	function jCheckFormVal(){
	
		var FI = 0;
		var FA = 0;
		
		//----- 入力確認 -----//
		var iA = jIsValue('NameKanji1');	//姓漢字
		var iB = jIsValue('NameKanji2');	//名漢字
		//var iC = jIsValue('NameRoma1');	//姓ローマ字
		//var iD = jIsValue('NameRoma2');	//名ローマ字
		var iC = 1;							//姓ローマ字
		var iD = 1;							//名ローマ字
		var iE = jIsValue('r1');			//性別1
		var iF = jIsValue('r2');			//性別2
		var iG = jIsValue('Telt');			//電話番号
		var iH = jIsValue('birthY');		//月
		var iI = jIsValue('birthM');		//月
		var iJ = jIsValue('birthD');		//日
		var iK = jIsValue('NameKana1');		//姓カナ
		var iL = jIsValue('NameKana2');		//名カナ
		
		var iM = jIsValueRadio('regtype');	//登録区分
		
		var iN = 1;
		var iO = 1;
		var iP = 1;
		
		jRG = $('input[type="radio"][name="regtype"]:checked').val();
		
		if(jRG == 2){
			iN = jIsValue('CmpName');
			iO = jIsValue('CmpKana');
			iP = jIsValue('CmpHat');
		}
		
		FI = iA * iB * iC * iD * iE * iF * iG * iH * iI * iJ *iK *iL *iM;
		
		if(FI > 0){
			return false;
		} else {
			return true;
		}
	}
	
	function ljResetCmp(){
		$('#CmpName').val('');
		$('#CmpKana').val('');
		$('#CmpHat').val('');
	}
	
	$('input[type="radio"][name="regtype"]').on('change',function(){
		
		vr  = $('input[type="radio"][name="regtype"]:checked').val();
		dis = 800;
		if(vr == 2){
			$('.cmp').slideDown(dis);
		}else{
			$('.cmp').slideUp(dis,ljResetCmp);
		}
	});
	
	//----- ユーザーアクション -----//
	$('form[name="UserForm"]').on('change keyup',function(){
		$('#Act').prop('disabled',jCheckFormVal());
	});
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
