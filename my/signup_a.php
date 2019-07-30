<?php 
require('includes/application_top.php');

////////////////////////////////////////
// アジアンペイ系列専用
////////////////////////////////////////
$to_pay_page = 'AsianPay';

/*
$Amount1  = intval(get_master_parameters('amount1'));  //初期費用カードなし
$Amount2  = intval(get_master_parameters('amount2'));  //初期費用カードあり
$Amount3  = intval(get_master_parameters('amount3'));  //2口目以降
$id_min   = intval(get_master_parameters('id_min')); //最小
$pass_min = intval(get_master_parameters('pass_min')); //最小
$pass_max = intval(get_master_parameters('pass_max')); //最大
$Fee1     = intval('6000');
*/

////デフォルト固定
$root_check_iou = 'checked';

function rateD_Y($unit,$dol){
	$yen = $dol * $unit;
	return $yen;
}


////////////////////////////////////////
$asianpay_flag        = $_POST['data4_1'];   //アジアンペイ希望

$inv_id = $_GET['inv_id'];//invitation_tableのID

if(empty($inv_id)){ 
	//tep_redirect('/', '', 'SSL');
	
}else{
	$set_inv_query = tep_db_query("
		SELECT `signup_form_id`
		FROM `".TABLE_SIGNUP_FORM."` 
		WHERE `state` = '1' 
		AND `inv_id` = '".tep_db_input($inv_id)."' 
	");
	
	if (tep_db_num_rows($set_inv_query)) {
		//既に登録されている
		tep_redirect('/p_full.php', '', 'SSL'); 
	}
	
	$inv_query = tep_db_query("
		SELECT 
			`from_name`,
			`to_name`,
			`to_email`,
			`to_text`,
			`to_pay`,
			`inv_p_id2` 
		FROM `".TABLE_INVITATION."` 
		WHERE `state` = '1' 
		AND `inv_id` = '".tep_db_input($inv_id)."' 
	");
	
	if (tep_db_num_rows($inv_query)) {
		$inv_set = tep_db_fetch_array($inv_query);
		$data0_1 = $inv_set['from_name'];       //差出人
		$data0_2 = $inv_set['to_name'];         //被招待者
		$data0_3 = $inv_set['to_email'];        //被招待者メール
		$data0_4 =  nl2br($inv_set['to_text']); //メッセージ
		$data1_1 = $data0_3;                    //メールアドレス //エラーチェック済み

		////系のチェック
		$to_pay  = $inv_set['to_pay'];
		if($to_pay != $to_pay_page){ tep_redirect('/', '', 'SSL');  }
		
		$data5_4_2_yen = rateD_Y(120,'5');
		
		//メール開封フラグ
		$sql_data_array = array( 
			'date_update' => 'now()',
			'onaccess'    => '1'
		);
		tep_db_perform(TABLE_INVITATION, $sql_data_array, 'update', " `inv_id` = '".tep_db_input($inv_id)."' ");
		
		//$inv_set['inv_p_id2'];        //配置先希望p_id
		if(get_p_lr($inv_set['inv_p_id2']) == 'full'){
			tep_redirect('/p_full.php', '', 'SSL');
		}
	}else{
		//該当データなし
		tep_redirect('/', '', 'SSL'); 
	}
}

////////////////////////////////////////
//err_css
$data1_1_c = '';
$data1_2_c = '';
$data1_3_c = '';
$data2_1_c = '';
$data2_2_c = '';
$data2_3_c = '';
$data2_4_c = '';
$data2_5_c = '';
$data2_6_c = '';
$data2_7_c = '';
$data3_1_c = '';
$data3_2_c = '';
$data3_3_c = '';
$data3_4_c = '';
$data3_5_c = '';
$data4_1_c = '';
$data4_2_c = '';
$data5_1_c = '';
$data5_2_c = '';
$data5_3_c = '';
$data5_4_c = '';
$data5_5_c = '';
$agree1_c  = '';
$agree2_c  = '';
$err_css   = 'style="background:#FFF0F0;"';
$set_err   = '';
////////////////////////////////////////

////////$_POST;
if($_POST['act'] == 'back'){
	$data1_1   = $_POST['back_data1_1'];   //メールアドレス //エラーチェック済み
	$data1_2   = $_POST['back_data1_2'];   //
	$data1_3   = $_POST['back_data1_3'];   //
	$data2_1   = $_POST['back_data2_1'];   //
	$data2_1_2 = $_POST['back_data2_1_2']; //
	$data2_2   = $_POST['back_data2_2'];   //
	$data2_2_2 = $_POST['back_data2_2_2']; //
	$data2_3   = $_POST['back_data2_3'];   //
	$data2_3_2 = $_POST['back_data2_3_2'];   //
	$data2_3_3 = $_POST['back_data2_3_3'];   //
	$data2_4   = $_POST['back_data2_4'];   //
	$data2_5   = $_POST['back_data2_5'];   //
	$data2_6   = $_POST['back_data2_6'];   //
	$data2_7   = $_POST['back_data2_7'];   //
	$data3_1   = $_POST['back_data3_1'];   //
	$data3_2   = $_POST['back_data3_2'];   //
	$data3_3   = $_POST['back_data3_3'];   //
	$data3_3r  = $_POST['back_data3_3r'];   //
	$data3_4   = $_POST['back_data3_4'];   //
	$data3_4r  = $_POST['back_data3_4r'];   //
	$data3_5   = $_POST['back_data3_5'];   //
	$data3_5r  = $_POST['back_data3_5r'];   //
	$data3_6   = $_POST['back_data3_6'];   //
	$data3_6r  = $_POST['back_data3_6r'];   //
	$data3_7   = $_POST['back_data3_7'];   //
	$data3_7r  = $_POST['back_data3_7r'];   //
	$data4_1   = $_POST['back_data4_1'];   //
	$data4_2   = $_POST['back_data4_2'];   //
	$data5_1   = $_POST['back_data5_1'];   //
	$data5_2   = $_POST['back_data5_2'];   //
	$data5_3   = $_POST['back_data5_3'];   //
	$data5_4   = $_POST['back_data5_4'];   //
	$data5_5   = $_POST['back_data5_5'];   //total
	$agree1    = $_POST['back_agree1'];    //
	$agree2    = $_POST['back_agree2'];    //
}elseif($_POST['act'] == 'success'){
	//リロード対策
	if($_POST['ticket'] === $_COOKIE['ticket']){
		tep_cookie_del('ticket');
	}else{
		tep_cookie_del('ticket');
		tep_redirect('/', '', 'SSL');
	}

	//signup_form
	$sql_data_array = array( 
		'inv_id'      => $_POST['inv_id'],
		'data1_1'     => $_POST['data1_1'],
		'data1_2'     => $_POST['data1_2'],
		'data1_3'     => $_POST['data1_3'],
		'data2_1'     => $_POST['data2_1'],
		'data2_1_2'   => $_POST['data2_1_2'],
		'data2_2'     => $_POST['data2_2'],
		'data2_2_2'   => $_POST['data2_2_2'],
		'data2_3'     => $_POST['data2_3'],
		'data2_3_2'   => $_POST['data2_3_2'],
		'data2_3_3'   => $_POST['data2_3_3'],
		'data2_4'     => $_POST['data2_4'],
		'data2_5'     => $_POST['data2_5'],
		'data2_6'     => $_POST['data2_6'],
		'data2_7'     => $_POST['data2_7'],
		'data3_1'     => $_POST['data3_1'],
		'data3_2'     => $_POST['data3_2'],
		'data3_3'     => $_POST['data3_3'],
		'data3_3r'    => $_POST['data3_3r'],
		'data3_4'     => $_POST['data3_4'],
		'data3_4r'    => $_POST['data3_4r'],
		'data3_5'     => $_POST['data3_5'],
		'data3_5r'    => $_POST['data3_5r'],
		'data3_6'     => $_POST['data3_6'],
		'data3_6r'    => $_POST['data3_6r'],
		'data3_7'     => $_POST['data3_7'],
		'data3_7r'    => $_POST['data3_7r'],
		'data4_1'     => $_POST['data4_1'],
		'data4_2'     => $_POST['data4_2'],
		'data5_1'     => $_POST['data5_1'],
		'data5_2'     => $_POST['data5_2'],
		'data5_3'     => $_POST['data5_3'],
		'data5_4'     => $_POST['data5_4'],
		'data5_5'     => $_POST['data5_5'],
		'agree1'      => $_POST['agree1'],
		'agree2'      => $_POST['agree2'],
		
		'd_num'          => $_POST['d_num'],
		//'data4_2_check'  => $_POST['data4_2_check'],
		'root_iou'       => $_POST['root_iou'],
		
		'date_create' => 'now()',
		'state'       => '1'
	);
	tep_db_perform(TABLE_SIGNUP_FORM, $sql_data_array);
	$signup_form_id = tep_db_insert_id();
	
	$inv_id           = $_POST['inv_id'];    //inv_id
	$user_email       = $_POST['data1_1'];   //メールアドレス //エラーチェック済み
	$user_p_name_id   = $_POST['data1_2'];   //希望ユーザーID
	$first_password   = $_POST['data1_3'];   //希望パスワード
	$crypted_password = tep_encrypt_password($first_password);

	$user_name            = $_POST['data2_1'];   //氏
	$user_name2           = $_POST['data2_1_2']; //名
	$user_name_kana       = $_POST['data2_2'];   //シ
	$user_name_kana2      = $_POST['data2_2_2']; //メイ
	$user_name_en         = $_POST['data2_3'];   //英語名
	$user_name_en2        = $_POST['data2_3_2'];   //英語名
	//$user_name_en3        = $_POST['data2_3_3'];   //英語名
	$user_tel_t           = $_POST['data2_4'];   //固定電話
	//$user_tel_m           = $_POST['data2_5'];   //携帯電話
	$user_borthday        = $_POST['borth_y'].'-'.$_POST['borth_m'].'-'.$_POST['borth_d'];//borth
	$user_sex             = $_POST['user_sex'];  //user_sex
	$user_address_zip     = $_POST['data3_2'];   //郵便番号
	$user_address_country = $_POST['data3_1'];   //国名
	$user_address_pref    = $_POST['data3_3'];   //都道府県
	$user_address_pref_r  = $_POST['data3_3r'];   //都道府県
	$user_address_city    = $_POST['data3_4'];   //住所1
	$user_address_city_r  = $_POST['data3_4r'];   //住所1
	$user_address_area    = $_POST['data3_5'];   //住所2
	$user_address_area_r  = $_POST['data3_5r'];   //住所2
	$user_address_strt    = $_POST['data3_6'];   //住所3
	$user_address_strt_r  = $_POST['data3_6r'];   //住所3
	$user_address_strt2   = $_POST['data3_7'];   //住所4
	$user_address_strt2_r = $_POST['data3_7r'];   //住所4
	$order_d_num          = $_POST['d_num'];     //注文デポジット数
	$order_root_iou       = $_POST['root_iou'];  //ルート（カード）と紐づく
	
	$asianpay_flag        = $_POST['data4_1'];   //アジアンペイ希望
	$asianpay             = $_POST['data4_2'];   //アジアンペイ口座ID
	
	$date_expected        = $_POST['data5_3'];   //入金予定日
	$user_permission = 'u';//個人
	$user_name_full  = $user_name.' '.$user_name2;

	//ユーザー登録
	$sql_data_array = array( 
		'user_permission'      => $user_permission,
		'user_email'           => $user_email,
		'user_password'        => $crypted_password,
		'user_name'            => $user_name,
		'user_name2'           => $user_name2,
		'user_name_kana'       => $user_name_kana,
		'user_name_kana2'      => $user_name_kana2,
		'user_name_en'         => $user_name_en,
		'user_name_en2'        => $user_name_en2,
		'user_tel_t'           => $user_tel_t,
		'user_tel_m'           => $user_tel_m,
		'user_address_zip'     => $user_address_zip,
		'user_address_country' => $user_address_country,
		'user_address_pref'    => $user_address_pref,
		'user_address_pref_r'  => $user_address_pref_r,
		'user_address_city'    => $user_address_city,
		'user_address_city_r'  => $user_address_city_r,
		'user_address_area'    => $user_address_area,
		'user_address_area_r'  => $user_address_area_r,
		'user_address_strt'    => $user_address_strt,
		'user_address_strt_r'  => $user_address_strt_r,
		'user_address_strt2'   => $user_address_strt2,
		'user_address_strt2_r' => $user_address_strt2_r,
		'user_borthday'        => $user_borthday,
		'user_sex'             => $user_sex,
		'date_create'          => 'now()',
		'state'                => '1'
	);
	tep_db_perform(TABLE_USER, $sql_data_array);
	$user_id = tep_db_insert_id();
	
	$sql_data_array = array( 'user_id' => $user_id );
	tep_db_perform(TABLE_USER, $sql_data_array, 'update', " `user_auto_id` = '".tep_db_input($user_id)."'");

	//空ポジション作成
	//$user_id    //獲得ユーザー
	//$inv_p_id   //紹介ポジション
	//$inv_p_id2  //希望直上ポジション
	$inv2_query = tep_db_query("
		SELECT 
			`inv_p_id`,
			`inv_p_id2`
		FROM `".TABLE_INVITATION."` 
		WHERE `state` = '1' 
		AND `inv_id` = '".tep_db_input($inv_id)."' 
	");
	$inv2_set = tep_db_fetch_array($inv2_query);
	$inv_p_id  = $inv2_set['inv_p_id'];
	$inv_p_id2 = $inv2_set['inv_p_id2'];

	//ルートポジション
	$root_position_id = get_new_position($user_id, $inv_p_id, $inv_p_id2);
	
	//金額算出
	$ocean_price = ($asianpay_flag == '1')? 50000 : 0; 
	$coin_amount    = $ocean_price + 57750 + 6000 + ($order_d_num * 57750);  //入金予定額
	//$coin_amount    = 57750 + 6000 + ($order_d_num * 57750);  //入金予定額
	$coin_scheduled = 50000 * ($order_d_num + 1);              //デポジット予定額  100000 * ($order_d_num+1)

	$set_memo        = '新規入会：p_id='.$root_position_id;
	$set_memo       .= ($order_root_iou == IOU_S)? ','.IOU_L.'権' : '';

	if($asianpay_flag == '1'){
		$set_memo       .= ',カード申請あり';  //申請あり
	}elseif($asianpay_flag == '2'){
		$set_memo       .= ',カード申請なし';  //申請なし
	}else{
		$set_memo       .= ',カード申請不明';  //申請不明
	}
	
	////申請テーブル
	$sql_data_array = array( 
		'r_coin_user_id'    => $user_id,
		'r_coin_amount'     => $coin_amount,     //申請コイン（入金予定額）
		'r_coin_scheduled'  => $coin_scheduled,  //デポジット予定量
		//'r_coin_pay_amount' => '',             //デポジット提供（入金確認額）
		'date_create'       => 'now()',          //申請日
		'date_expected'     => $date_expected,   //支払い予定日
		//'date_payment'      => '',             //提供日（確認日）
		'state'             => '1',              //1:未処理, a:提供, c:キャンセル
		'memo'              => $set_memo,
	);
	tep_db_perform(TABLE_REQUEST_COIN, $sql_data_array);
	$r_coin_id = tep_db_insert_id();
	
	///////////////////////////////////////////////////////////////////////////////
	////新規登録メール（管理者・アジアンペイ宛）
	$Set_sex = ($user_sex == 'm')? '男性' : '女性' ;  //user_sex
	$Set_agree1 = ($_POST['agree1'] == '1')? '承認します。' : '承認しません。';
	$Set_agree2 = ($_POST['agree2'] == '1')? '承認します。' : '承認しません。';
	$now_data = date("Y-m-d  H:i:s");
	
	$user_address_r  = $user_address_strt2_r . ', ';
	$user_address_r .= $user_address_strt_r . ', ';
	$user_address_r .= $user_address_area_r . ', ';
	$user_address_r .= $user_address_city_r . ', ';
	$user_address_r .= $user_address_pref_r . ', ';
	$user_address_r .= $user_address_zip . ', ';
	$user_address_r .= $user_address_country . ', ';
	
	$SetText  = "申込日 : ".$now_data."\n";
	$SetText .= "氏名(日本語) 姓 : ".$user_name."\n";
	$SetText .= "氏名(日本語) 名 : ".$user_name2."\n";
	$SetText .= "氏名(カナ) 姓 : ".$user_name_kana."\n";
	$SetText .= "氏名(カナ) 名 : ".$user_name_kana2."\n";
	$SetText .= "Family ： ".$user_name_en."\n";
	$SetText .= "First  ： ".$user_name_en2."\n";
	$SetText .= "生年月日 : ".$user_borthday."\n";
	$SetText .= "性別 : ".$Set_sex."\n";
	$SetText .= "Eメール : ".$user_email."\n";
	$SetText .= "ユーザ名(ID) : ".$user_p_name_id."\n";
	$SetText .= "パスワード : ".$first_password."\n";
	$SetText .= "郵便番号 : ".$user_address_zip."\n";
	$SetText .= "国 : ".$user_address_country."\n";
	$SetText .= "都道府県 : ". $user_address_pref."\n";
	$SetText .= "市区町村 : ". $user_address_city."\n";
	$SetText .= "町名 : ".     $user_address_area."\n";
	$SetText .= "番地 : ".     $user_address_strt."\n";
	$SetText .= "建物名 : ".   $user_address_strt2."\n";
	$SetText .= "ローマ字表記 : ".$user_address_r."\n";
	$SetText .= "固定電話 : ".$user_tel_t."\n";
	$SetText .= "携帯電話 : ".$user_tel_m."\n";
	$SetText .= "メモ : ".$set_memo."\n";
	$SetText .= "紹介者(p_id) ： ".$inv_p_id."\n";
	$SetText .= "直上者(p_id) ： ".$inv_p_id2."\n";
	$SetText .= "規約承認 : ".$Set_agree1."\n";
	$SetText .= "アフィリエイト規約承認 : ".$Set_agree2."\n";
	
	Email_Add_User($EmailHead,$EmailFoot,$user_email,$SetText,$to_pay);

	///////////////////////////////////////////////////////////////////////////////
	
	////請求メール
	$user_query = tep_db_query("
		SELECT 
			`user_name`,
			`user_name2`,
			`user_email`
		FROM `".TABLE_USER."` 
		WHERE `state` = '1' 
		AND  `user_id` = '".tep_db_input($user_id)."' 
	");
	$user = tep_db_fetch_array($user_query);
	$name       = $user['user_name'];
	$name2      = $user['user_name2'];
	$user_email = $user['user_email'];
	$user_name  = $name.'　'.$name2;

	$email        = $user_email;
	$to_name      = $user_name;
	$order_id     = $r_coin_id;
	$order_amount = $coin_amount;
	$order_limit  = $date_expected;
	Email_Order_Coin($EmailHead,$EmailFoot,$email,$to_name,$order_id,$order_amount,$order_limit,$to_pay,$user_p_name_id);
	
	//カードの有無は関係なく、価格は同じ
	$iou_price = 50000;       //カードの有無 あり(申請有り)：5万、なし(申請無し)：10万
	
	$right_iou        = 'right_'.IOU_S;
	$right_iou_create = 'right_'.IOU_S.'_create';
	get_request_iou($root_position_id, $iou_price);
	$sql_data_array = array( 
		'user_id'          => $user_id,
		$right_iou         => '1', 
		$right_iou_create  => 'now()',
		'state'            => '1'
	);
	tep_db_perform(TABLE_USER_RIGHT, $sql_data_array);

	//ユーザーセッティングを登録
	$sql_data_array = array( 
		'user_id'                  => $user_id,
		'to_pay'                   => $to_pay,
		'asianpay_account_number'  => $asianpay,
		'user_p_name_id'           => $user_p_name_id,
		'first_password'           => $first_password,
		'root_position_id'         => $root_position_id,
		'signup_form_id'           => $signup_form_id,
		'date_create'              => 'now()'
	);
	tep_db_perform(TABLE_USER_SETTING, $sql_data_array);
	
	
	//ログインクッキーを設定
	tep_cookie_set('user_id',$user_id);						//$_COOKIE['user_id']
	tep_cookie_set('user_permission',$user_permission);		//$_COOKIE['user_permission']
	tep_cookie_set('user_name',$user_name_full);			//$_COOKIE['user_name']
	
	//myページへリダイレクト
	tep_redirect('/'.IOU_L.'/my', '', 'SSL');
	
	
}elseif($_POST['act'] == 'process'){
	$data1_1   = $_POST['data1_1'];   //メールアドレス //エラーチェック済み
	$data1_2   = kill_space($_POST['data1_2']);   //
	$data1_3   = $_POST['data1_3'];   //
	$data2_1   = $_POST['data2_1'];   //
	$data2_1_2 = $_POST['data2_1_2']; //
	$data2_2   = $_POST['data2_2'];   //
	$data2_2_2 = $_POST['data2_2_2']; //
	$data2_3   = $_POST['data2_3'];   //
	$data2_3_2 = $_POST['data2_3_2'];   //
	$data2_3_3 = $_POST['data2_3_3'];   //
	$data2_4   = $_POST['data2_4'];   //
	$data2_5   = $_POST['data2_5'];   //
	$data2_6   = $_POST['borth_y'].'-'.$_POST['borth_m'].'-'.$_POST['borth_d'];//borth
	$data2_7   = $_POST['user_sex'];  //user_sex
	$data3_1   = $_POST['data3_1'];   //
	$data3_2   = $_POST['data3_2'];   //
	$data3_3   = $_POST['data3_3'];   //
	$data3_3r  = $_POST['data3_3r'];   //
	$data3_4   = $_POST['data3_4'];   //
	$data3_4r  = $_POST['data3_4r'];   //
	$data3_5   = $_POST['data3_5'];   //
	$data3_5r  = $_POST['data3_5r'];   //
	$data3_6   = $_POST['data3_6'];   //
	$data3_6r  = $_POST['data3_6r'];   //
	$data3_7   = $_POST['data3_7'];   //
	$data3_7r  = $_POST['data3_7r'];   //

	$data4_1   = $_POST['data4_1'];   //
	$data4_2   = $_POST['data4_2'];   //
	$data5_3   = $_POST['data5_3'];   //
	$agree1    = $_POST['agree1'];    //
	$agree2    = $_POST['agree2'];    //

	$d_num     = $_POST['d_num'];    //
	$root_iou =  $_POST['root_iou'];    //

////////////////////////////////////////////////////////////////////////////////
////エラーチェック
////////////////////////////////////////////////////////////////////////////////

	$root_check_iou = ($root_iou == IOU_S)? 'checked' : '';
	$root_check = (empty($root_iou))? $err_css : '';
	
	$data1_2   = get_convert_eisuji($data1_2);//半角英数字化
	$data1_2_c = (empty($data1_2))? $err_css : $data1_2_c;
	$data1_2_c = (get_check_eisuji($data1_2))? $err_css : $data1_2_c;//英数字チェック
	$data1_2_c = (get_check_nameid($data1_2))? $err_css : $data1_2_c;//ユニークチェック
	$data1_2_t = (get_check_nameid($data1_2))? '<span style="color:#FF0000;">既に使われているIDです。</span><br>' : '';//ユニークチェック

	$data1_3   = get_convert_eisuji($data1_3);//半角英数字化
	$data1_3_c = (empty($data1_3))? $err_css : $data1_3_c;
	$data1_3_c = (get_check_eisuji($data1_3))? $err_css : $data1_3_c;//英数字チェック
	$data1_3_c = (get_check_initial($data1_3))? $data1_3_c : $err_css;//頭文字大文字チェック //大文字の場合はtrue
	$data1_3_c = (mb_strlen($data1_3) < $pass_min)? $err_css : $data1_3_c;//文字数チェック PassMinより小さい
	$data1_3_c = (mb_strlen($data1_3) > $pass_max)? $err_css : $data1_3_c;//文字数チェック PassMaxより大きい
	
	$data2_1_c = ((empty($data2_1)) OR (empty($data2_1_2)))? $err_css : $data2_1_c;
	$data2_2   = get_convert_kana($data2_2);  //カナ
	$data2_2_2 = get_convert_kana($data2_2_2);//カナ
	$data2_2_c = ((empty($data2_2)) OR (empty($data2_2_2)))? $err_css : $data2_2_c;
	$data2_2_c = (get_check_kanji($data2_2))?   $err_css : $data2_2_c;    // 漢字を含まないチェック
	$data2_2_c = (get_check_kanji($data2_2_2))? $err_css : $data2_2_c;    // 漢字を含まないチェック

	$data2_3   = get_convert_eisuji($data2_3);  //英数字化
	$data2_3_2 = get_convert_eisuji($data2_3_2);//英数字化
	$data2_3_c = ((empty($data2_3)) OR (empty($data2_3_2)))? $err_css : $data2_3_c;
	$data2_3_c = (get_check_kanji($data2_3))?   $err_css : $data2_3_c;    // 漢字を含まないチェック
	$data2_3_c = (get_check_kanji($data2_3_2))? $err_css : $data2_3_c;    // 漢字を含まないチェック
	
	
	$data2_4   = get_convert_number($data2_4);//tel //数値以外削除
	$data2_5   = get_convert_number($data2_5);//tel //数値以外削除
	if(($data2_4) || ($data2_5)){
		if($data2_4){
			$data2_4_c = (get_check_suji($data2_4))? $err_css : $data2_4_c;
		}elseif($data2_5){
			$data2_5_c = (get_check_suji($data2_5))? $err_css : $data2_5_c;
		}
	}else{
		$data2_4_c = (empty($data2_4))? $err_css : $data2_4_c;
		$data2_4_c = (get_check_suji($data2_4))? $err_css : $data2_4_c;
		
		$data2_5_c = (empty($data2_5))? $err_css : $data2_5_c;
		$data2_5_c = (get_check_suji($data2_5))? $err_css : $data2_5_c;
	}
	
	$data2_6_c = (empty($data2_6))? $err_css : $data2_6_c;
	$data2_7_c = (empty($data2_7))? $err_css : $data2_7_c;

	$data3_1_c = (empty($data3_1))? $err_css : $data3_1_c;
	
	$data3_2   = get_convert_number($data3_2);//数字以外削除
	$data3_2_c = (empty($data3_2))? $err_css : $data3_2_c;
	$data3_2_c = (get_check_suji($data3_2))? $err_css : $data3_2_c;//数字チェック
	
	$data3_3_c  = (empty($data3_3) )? $err_css : $data3_3_c;
	$data3_3r_c = (empty($data3_3r))? $err_css : $data3_3r_c;
	$data3_4_c  = (empty($data3_4) )? $err_css : $data3_4_c;
	$data3_4r_c = (empty($data3_4r))? $err_css : $data3_4r_c;
	$data3_5_c  = (empty($data3_5) )? $err_css : $data3_5_c;
	$data3_5r_c = (empty($data3_5r))? $err_css : $data3_5r_c;
	$data3_6_c  = (empty($data3_6) )? $err_css : $data3_6_c;
	$data3_6r_c = (empty($data3_6r))? $err_css : $data3_6r_c;
	
	if( ($data3_7) OR ($data3_7r) ){
		$data3_7_c  = (empty($data3_7))? $err_css : $data3_7_c;
		$data3_7r_c = (empty($data3_7r))? $err_css : $data3_7r_c;
	}else{
		$data3_7_c  = $data3_7_c;
		$data3_7r_c = $data3_7r_c;
	}

	$data4_1_c = (empty($data4_1))? $err_css : $data4_1_c;
	if($data4_1 == '2'){ 
		$data4_2_c = $data4_2_c; 
		//if(empty($data4_2)){
		//	$data4_2_c = (empty($data4_2_check))? $err_css : $data4_2_c; 
		//}else{
		//	$data4_2_c = $data4_2_c; 
		//}
	}//※すでにアジアンペイ口座をお持ちの方のみ

	$data5_3_c = (empty($data5_3))? $err_css : $data5_3_c;
	$data5_3_c = ($data5_3 < date("Y-m-d"))? $err_css : $data5_3_c;

	$agree1_c = (empty($agree1))? $err_css : $agree1_c;
	$agree2_c = (empty($agree2))? $err_css : $agree2_c;
	
	if(
		empty($data1_1_c) && 
		empty($data1_2_c) && 
		empty($data1_3_c) && 
		empty($data2_1_c) && 
		empty($data2_2_c) && 
		empty($data2_3_c) && 
		empty($data2_4_c) && 
		empty($data2_5_c) && 
		empty($data2_6_c) && 
		empty($data2_7_c) && 
		empty($data3_1_c) && 
		empty($data3_2_c) && 
		empty($data3_3_c) && 
		empty($data3_3r_c) && 
		empty($data3_4_c) && 
		empty($data3_4r_c) && 
		empty($data3_5_c) && 
		empty($data3_5r_c) && 
		empty($data3_6_c) && 
		empty($data3_6r_c) && 
		empty($data3_7_c) && 
		empty($data3_7r_c) && 
		empty($data4_1_c) && 
		empty($data4_2_c) && 
		empty($data5_1_c) && 
		empty($data5_2_c) && 
		empty($data5_3_c) && 
		empty($data5_4_c) && 
		empty($data5_5_c) && 
		empty($root_check) && 
		empty($agree1_c) && 
		empty($agree2_c)
	){
		$set_err = 'NoErr';   //エラーなし
	}else{
		$set_err = 'Err'; //エラーあり
	}

}

////////

if($data4_1 == '1'){
	$checked_data4_1_1 = 'checked';
	$checked_data4_1_2 = '';
	$checked_data4_2_c = '';
}elseif($data4_1 == '2'){
	$checked_data4_1_1 = '';
	$checked_data4_1_2 = 'checked';
	/*
	if($data4_2_check){
		$checked_data4_2_c = 'checked';
	}else{
		$checked_data4_2_c = '';
	}
	*/
}

////////
if($data5_1 == '1'){
	$checked_data5_1_1 = 'checked';
	$checked_data5_1_2 = '';
	$sum1_amount = $Amount1;
}elseif($data5_1 == '2'){
	$checked_data5_1_1 = '';
	$checked_data5_1_2 = 'checked';
	$sum1_amount = $Amount2;
}

////////
if($data5_4 == '1'){
	$checked_data5_4_1 = 'checked';
	$checked_data5_4_2 = '';
	$checked_data5_4_3 = '';
}elseif($data5_4 == '2'){
	$checked_data5_4_1 = '';
	$checked_data5_4_2 = 'checked';
	$checked_data5_4_3 = '';
}elseif($data5_4 == '3'){
	$checked_data5_4_1 = '';
	$checked_data5_4_2 = '';
	$checked_data5_4_3 = 'checked';
}

////////
$agree1_checked = ($agree1 == '1')? 'checked' : '';
$agree2_checked = ($agree2 == '1')? 'checked' : '';

////////country
for($t=0;$t<count($country_array);$t++){
	$data3_1_selected = ($data3_1 == $country_array[$t])? 'selected' : '' ; 
	$country_option .= '<option value="'.$country_array[$t].'" '.$data3_1_selected.'>'.$country_array[$t].'</option>';
}
$country_select  = '<select name="data3_1">';
$country_select .= $country_option;
$country_select .= '</select>';

////////d_num
$order_num_option = '';
for($i=0;$i<=99;$i++){
	//$data5_2_selected = ($i == $data5_2)? 'selected' : '';
	$d_num_selected = ($i == $d_num)? 'selected' : '';
	$order_num_option .= '<option value="'.$i.'" '.$d_num_selected.'>'.$i.'</option>';
}
$order_num_select  = '<select id="d_num" name="d_num" onChange="orderSum()">';
$order_num_select .= $order_num_option;
$order_num_select .= '</select>';

if($set_err == 'NoErr'){
	$act_set = 'success';
	$button_text = '申込みをする';
	$announce  = '<div class="blue_box">';
	$announce .= 'この内容で申込みをします。よろしいですか？';
	$announce .= '</div>'."\n";
	$signFormCover = '<div class="signFormCover"></div>';
}elseif($set_err == 'Err'){
	$act_set = 'process';
	$button_text = '確認画面へ';
	$announce  = '<div class="red_box">';
	$announce .= '未記入または形式ミスがあります。';
	$announce .= '</div>'."\n";
	$signFormCover = '';
}else{
	$act_set = 'process';
	$button_text = '確認画面へ';
	$announce      = '';
	$signFormCover = '';
}

////////
if($asianpay_flag == '1'){
	$op_amount = ($asianpay_flag == '1')? 50000 : 0;
	$op1   = 1;
	$op1_a = number_format(35000);
	$op2   = 1;
	$op2_a = number_format(15000);
}else{
	$op_amount = 0;
	$op1   = 0;
	$op1_a = number_format(0);
	$op2   = 0;
	$op2_a = number_format(0);
}

	
$d_num_set0 = $d_num * 57750;
$d_num_set = number_format($d_num_set0);
$total_amout0 = 57750 + 6000 + $d_num_set0 + $op_amount;
$total_amout = number_format($total_amout0);

$ticket = md5(uniqid().mt_rand());//リロード対策
tep_cookie_set('ticket',$ticket );//リロード対策

//////////////////////////
//back_form
//////////////////////////
$back_inputs  = '<input type="hidden" name="act" value="back">'."\n";
//$back_inputs .= '<input type="hidden" name="inv_id" value="'.$inv_id.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data1_1"   value="'.$data1_1.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data1_2"   value="'.$data1_2.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data1_3"   value="'.$data1_3.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data2_1"   value="'.$data2_1.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data2_1_2" value="'.$data2_1_2.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data2_2"   value="'.$data2_2.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data2_2_2" value="'.$data2_2_2.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data2_3"   value="'.$data2_3.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data2_3_2" value="'.$data2_3_2.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data2_3_3" value="'.$data2_3_3.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data2_4"   value="'.$data2_4.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data2_5"   value="'.$data2_5.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data2_6"   value="'.$data2_6.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data2_7"   value="'.$data2_7.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data3_1"   value="'.$data3_1.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data3_2"   value="'.$data3_2.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data3_3"   value="'.$data3_3.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data3_3r"  value="'.$data3_3r.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data3_4"   value="'.$data3_4.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data3_4r"  value="'.$data3_4r.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data3_5"   value="'.$data3_5.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data3_5r"  value="'.$data3_5r.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data3_6"   value="'.$data3_6.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data3_6r"  value="'.$data3_6r.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data3_7"   value="'.$data3_7.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data3_7r"  value="'.$data3_7r.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data4_1"   value="'.$data4_1.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data4_2"   value="'.$data4_2.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data5_1"   value="'.$data5_1.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data5_2"   value="'.$data5_2.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data5_3"   value="'.$data5_3.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data5_4"   value="'.$data5_4.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_data5_5"   value="'.$data5_5.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_agree1"    value="'.$agree1.'">'."\n";
$back_inputs .= '<input type="hidden" name="back_agree2"    value="'.$agree2.'">'."\n";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<?php echo $favicon."\n"; ?>
	<title><?php echo $title;?></title>
	<meta name="description" content="">
	<meta name="keywords" content="">
	<meta name="robots" content="noindex,nofollow,noarchive">
	<meta name="format-detection" content="telephone=no">
	<meta name="format-detection" content="email=no">
	<link rel="stylesheet" type="text/css" href="css/cssreset.css" media="all">
	<link rel="stylesheet" type="text/css" href="css/plain.css" media="all">

	<link rel="stylesheet" href="../js/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="front/css/my.css" media="all">
	
	<link rel="stylesheet" href="../js/bootstrap/css/font-awesome.min.css" />
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
	<script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js" charset="UTF-8"></script>
	

	<link rel="stylesheet" href="../datepicker/css/jquery-ui.min.css" />
	<script src="../datepicker/js/jquery-1.11.0.min.js"></script>
	<script src="../datepicker/js/jquery.ui.core.min.js"></script>
	<script src="../datepicker/js/jquery.ui.datepicker.min.js"></script>
	<script src="../datepicker/js/jquery.ui.datepicker-ja.min.js"></script>
	<script type="text/javascript">
		$(function() {
			$('#date').datepicker({ dateFormat: 'yy-mm-dd', });
		});
	</script>

	<script>
	inpPassSet = 1;
	function pass(){
		if(inpPassSet == 1){
			document.getElementById('inpPass').type = 'text'; 
			inpPassSet = 2;
		}else{
			document.getElementById('inpPass').type = 'password';
			inpPassSet = 1;
		}
	}
	</script>
	<script>
	function orderSum(){
		root_p_amount = 57750;//ルート
		send_amount   = 6000;//送料
		
		check1 = document.getElementById("Radio1").checked;
		check2 = document.getElementById("Radio2").checked;
		if (check1 == true) {
			document.getElementById("op1").innerHTML   = '1';
			document.getElementById("op1_a").innerHTML = '35,000';
			document.getElementById("op2").innerHTML   = '1';
			document.getElementById("op2_a").innerHTML = '15,000';
			op_amount = 50000;
		}else if (check2 == true) {
			document.getElementById("op1").innerHTML   = '0';
			document.getElementById("op1_a").innerHTML = '0';
			document.getElementById("op2").innerHTML   = '0';
			document.getElementById("op2_a").innerHTML = '0';
			op_amount = 0;
		}

		set_num = document.signForm.d_num.selectedIndex;
		d_amount = set_num * 57750;//デポジット
		document.getElementById("d_amount").innerHTML = d_amount.toLocaleString();
		
		sum_amount = root_p_amount + send_amount + d_amount + op_amount;
		document.getElementById("sum_amount").innerHTML = sum_amount.toLocaleString();

	}
	</script>

	<script>
	//////////////////////////////
	////郵便番号検索
	//////////////////////////////
	function AjaxPostZip(zip){
		$.ajax({
			type: "POST",
			url: "zip_post.php",
			cache: false,
			data: "zip="+zip,
			success: function(msg){
					ZipJson = msg; //データ獲得
					var ZipObj = (new Function("return " + ZipJson))();
					document.getElementById("zip0").value = ZipObj[0][0];
					document.getElementById("zip1").value = ZipObj[0][1];
					document.getElementById("zip2").value = ZipObj[0][2];
					document.getElementById("zip0r").value = ZipObj[2][0];
					document.getElementById("zip1r").value = ZipObj[2][1];
					document.getElementById("zip2r").value = ZipObj[2][2];
			},
			error: function(xhr, textStatus, errorThrown){ }
		});
	}

	//////////////////////////////
	////番地サポート
	//////////////////////////////
	function CopyZip3($value){
		// 設定開始（チェックする項目を設定してください）
		if($value.match(/[^0-9-\s]+/)){
			window.alert('指定文字以外が入力されています');
			document.getElementById("zip3r").value = '';
		}else{
			document.getElementById("zip3r").value = $value;
		}
		
	}
	</script>
	<style> 
		.ex {font-size:8pt};
	</style>
</head>
<body>
<div id="wrapper">
	
	<div id="header">
		<?php require('inc_user_header.php');?>
	</div>
	
	<div d="contents" class="container">
		<div class="row">
			
			<div id="left1" class="col-md-4 col-lg-2">
				<div class="inner">
					<div class="u_menu_area">
						<?php require('inc_user_left.php'); ?>
					</div>
				</div>
			</div>
			
			<div id="left2" class="col-xs-12 col-md-12 col-md-8 col-lg-10">
				<div class="inner">
					<div class="part">
						<?php echo $warning; ?>
						<div class="area1">
							<h2>メインメニュー</h2>
							<div style="text-align:center;">
								<span class="link_set" OnClick="location.href='order.php'"><button type="button" class="btn btn-default"><i class="fa fa-money" aria-hidden="true"></i><p>コインを買う</p></button></span>
								<span class="link_set" OnClick="location.href='order_list.php'"><button type="button" class="btn btn-default"><i class="glyphicon glyphicon-list" aria-hidden="true"></i><p>ご注文一覧</p></button></span>
								<span class="link_set" OnClick="location.href='info_bank.php'"><button type="button" class="btn btn-default"><i class="glyphicon glyphicon-yen" aria-hidden="true"></i><p>ご入金先確認</p></button></span>
							</div>
						</div>
						
						<div class="area1">
							<h2>あなたのNIGウォレットID</h2>
							<p class="id_area"><?php echo $user_card_email;?></p>
							<?php echo $wc_change;?>
						</div>
						
						<div class="area1">
							<h2>お知らせ</h2>
							<div class="table-responsive">
								<?php echo $input_notice;?>
							</div>
						</div>
						
						<div class="area1">
							<h2>ご注文一覧【<?php echo ISSUER_IOU; ?>】 <p class="edit_link"><a href="order.php">追加注文</a></p></h2>
							<div class="table-responsive">
								<?php echo $order_list;?>
								<?php echo $order_wc_list;?>
							</div>
						</div>
						
						<div class="area1">
							<h2>ウォレット＆カード状況</h2>
							<div class="table-responsive">
								<?php echo $wc_status_in;?>
							</div>
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
	
	<div id="footer">
	</div>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>