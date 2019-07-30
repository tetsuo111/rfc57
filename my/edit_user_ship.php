<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id        = $_COOKIE['user_id'];
	$user_email     = $_COOKIE['user_email'];
	$head_user_name = $_COOKIE['user_name'].'様';
	$position_id    = $_COOKIE['position_id'];
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}


//▼とび先設定
$form_action_to = basename($_SERVER['PHP_SELF']);
$link_to        = 'order_regular.php';


//----- 商品一覧 -----//
//▼アクティブ確認
if($user_id){
	
	//▼住所確認
	$query_a =  tep_db_query("
		SELECT
			`memberid`
		FROM  `".TABLE_MEM00001."`
		WHERE `memberid` = '".tep_db_input($user_id)."'
	");

	if(!tep_db_num_rows($query_a)){$err_addr = true;}
	
}else{
	
	$err_pos = true;
}

//▼データ取得
$zip_a    = $_POST['o_zip_a'];
$zip_b    = $_POST['o_zip_b'];
$ss_zip   = $_POST['o_zip_a'].'-'.$_POST['o_zip_b'];
$ss_pref  = $_POST['o_pref'];
$ss_city  = $_POST['o_city'];
$ss_area  = $_POST['o_area'];
$ss_strt  = $_POST['o_strt'];
$ss_phone = $_POST['o_phone'];
$ss_name  = $_POST['o_name'];


if(($_POST['act'] == 'process') && $_POST['act_send']){
	
	//▼データ更新
	require('../util/inc_cart_prc_shipping.php');
	
	$end = 'end';
	
}else if($_POST['act'] == 'process'){
	
	//▼エラーチェック
	if($_POST['act_back']){
		//戻る対策
		$err = true;
		
	}else{
		//通常登録
		$err = false;
	}
	
	if(!$zip_a || !$zip_b){$err = true; $err_text.= '<p class="alert">郵便番号を入力してください</p>';}
	if(!$ss_pref) {$err = true; $err_text.= '<p class="alert">都道府県を入力してください</p>';}
	if(!$ss_city) {$err = true; $err_text.= '<p class="alert">市区を入力してください</p>';}
	if(!$ss_area) {$err = true; $err_text.= '<p class="alert">町村 番地を入力してください</p>';}
	if(!$ss_phone){$err = true; $err_text.= '<p class="alert">宛名を入力してください</p>';}
	if(!$ss_name) {$err = true; $err_text.= '<p class="alert">電話番号を入力してください</p>';}
	
	
	if($err == false){
		
		$read = 'readonly';
		
		//▼登録ボタン
		$input_button = '<input type="submit" name="act_send" class="btn"    value="この内容で登録する">';
		$input_button.= '<input type="submit" name="act_back" class="btn btn_cancel spc10_l" value="戻る">';
		
	}else{
		
		//▼番号検索
		$search_zip   = '<button type="button" class="btn" id="aFromZip" disabled>郵便番号から検索</button>';
		
		//▼登録ボタン
		$dis          = 'disabled';
		$input_button = '<input type="submit" class="btn" value="確認画面" id="Act" '.$dis.'>';
		$input_button.= '<a href="'.$link_to.'"><button type="button" class="btn btn_cancel spc10_l">定期購入に戻る</button></a>';
	}

}else{
	
	//▼番号検索
	$search_zip   = '<button type="button" class="btn" id="aFromZip" disabled>郵便番号から検索</button>';
	
	//▼登録ボタン
	$dis          = 'disabled';
	$input_button = '<input type="submit" class="btn" value="確認画面" id="Act" '.$dis.'>';
	$input_button.= '<a href="'.$link_to.'"><button type="button" class="btn btn_cancel spc10_l">定期購入に戻る</button></a>';
}


/*======================================
ユーザー情報取得
======================================*/
//▼ユーザー情報伝達
require ('inc_user_announce.php');


if($err_pos){
	
	//▼ポジションエラー
	$order_form = '<p class="alert">ログイン情報が正しくありません</br>';
	$order_form.= '一度ログアウトし再度ログインしてください</p>';
	
}else if($err_addr){
	
	//▼アドレス
	$order_form = '<p class="alert">住所情報が登録されていません</br>';
	$order_form.= '注文の前に住所を登録してください</p>';
	$order_form.= '<a href="'.$link_address.'">住所情報を登録する</a>';
	
}else{
	
	//----- 表示設定 -----//
	if($end == 'end'){
		
		//▼終了処理
		$order_form = '<p>配送先を変更しました</p>';
		$order_form.= '<a href="'.$link_to.'">定期購入に戻る</a>';
		
	}else{
		
		//▼配送先を取得
		$query =  tep_db_query("
			SELECT
				`adrpost`      AS `post1`,
				CONCAT(`adr1`,`adr2`,`adr3`,'　',`adr4`) AS `addr1`,
				`phone`        AS `phone1`,
				`otheradrpost` AS `post2`,
				`otheradr1`    AS `oadr1`,
				`otheradr2`    AS `oadr2`,
				`otheradr3`    AS `oadr3`,
				`otheradr4`    AS `oadr4`,
				`otherphone`   AS `phone2`,
				`othername1`   AS `name2`
			FROM  `".TABLE_MEM00001."`
			WHERE `memberid` = '".tep_db_input($user_id)."'
		");

		$f = tep_db_fetch_array($query);
		
		//▼登録住所
		$ssip_in1.= '<h4>登録住所</h4>';
		$ssip_in1.= '<div class="p_area">';
		$ssip_in1.= '<ul>';
		$ssip_in1.= '<li>'.$f['post1'].'</li>';
		$ssip_in1.= '<li>'.$f['addr1'].'</li>';
		$ssip_in1.= '<li>'.$_COOKIE['user_name'].'</li>';
		$ssip_in1.= '<li>'.$tel['phone1'].'</li>';
		$ssip_in1.= '</ul>';
		$ssip_in1.= '</div>';
		
		if(!$_POST['act']){
			//▼配送先
			$zip = explode('-',$f['post2']);
			
			$zip_a    = $zip[0];
			$zip_b    = $zip[1];
			$ss_pref  = $f['oadr1'];
			$ss_city  = $f['oadr2'];
			$ss_area  = $f['oadr3'];
			$ss_strt  = $f['oadr4'];
			$ss_name  = $f['name2'];
			$ss_phone = $f['phone2'];
		}
		
		$ssip_in2 = '<div style="margin-top:20px;">';
		$ssip_in2.= '<h4>配送先</h4>';
		$ssip_in2.= $err_text;
		$ssip_in2.= '<ul class="ship_input">';
		$ssip_in2.= '<li style="overflow:hidden;">';
		$ssip_in2.= '<label>郵便番号'.I_MUST.'</label>';
		$ssip_in2.= '<div class="form-inline zip_area" style="width:100%;">';
		$ssip_in2.= '<input type="text" id="Za" class="form-control adZip iShip fl_l" style="width:4em;" name="o_zip_a" maxlength="3" value="'.$zip_a.'" '.$read.'>';
		$ssip_in2.= '<span class="fl_l"> - </span>';
		$ssip_in2.= '<input type="text" id="Zb" class="form-control adZip iShip fl_l spc10_r" style="width:5em;" name="o_zip_b" maxlength="4" value="'.$zip_b.'" size="5" maxlength="4" '.$read.'>';
		$ssip_in2.= $search_zip;
		$ssip_in2.= '</div>';
		$ssip_in2.= '</li>';

		$ssip_in2.= '<li class="add_in">';
		$ssip_in2.= '<label>都道府県'.I_MUST.'</label>';
		$ssip_in2.= '<input type="text" id="Apref"  class="form-control iShip" name="o_pref" value="'.$ss_pref.'" required '.$read.'>';
		$ssip_in2.= '</li>';

		$ssip_in2.= '<li class="add_in">';
		$ssip_in2.= '<label>市区'.I_MUST.'</label>';
		$ssip_in2.= '<input type="text" id="Acity"  class="form-control iShip" name="o_city" value="'.$ss_city.'" required '.$read.'>';
		$ssip_in2.= '</li>';
		
		$ssip_in2.= '<li class="add_in">';
		$ssip_in2.= '<label>町村 番地'.I_MUST.'</label>';
		$ssip_in2.= '<input type="text" id="Aarea"  class="form-control iShip" name="o_area" value="'.$ss_area.'" required '.$read.'>';
		$ssip_in2.= '</li>';

		$ssip_in2.= '<li class="add_in">';
		$ssip_in2.= '<label>建物名</label>';
		$ssip_in2.= '<input type="text" id="Astrt"  class="form-control iShip" name="o_strt" value="'.$ss_strt.'" '.$read.'>';
		$ssip_in2.= '</li>';

		$ssip_in2.= '<li class="add_in">';
		$ssip_in2.= '<label>宛名'.I_MUST.'</label>';
		$ssip_in2.= '<input type="text" id="Aname"  class="form-control iShip" name="o_name" value="'.$ss_name.'" required '.$read.'>';
		$ssip_in2.= '</li>';
		
		$ssip_in2.= '<li class="add_in">';
		$ssip_in2.= '<label>電話番号'.I_MUST.'</label>';
		$ssip_in2.= '<input type="tel" id="Aphone"  class="form-control iShip" name="o_phone" value="'.$ss_phone.'"required pattern="[0-9-]+" '.$read.'>';
		$ssip_in2.= '</li>';
		
		$ssip_in2.= '</ul>';
		
		$ssip_in2.= '</div>';
		
		
		//----- 表示フォーム -----//
		$order_form = '<div class="spc20">';
		$order_form.= '<form action="'.$form_action_to.'" method="POST" id="sipForm">';
		$order_form.= '<input type="hidden" name="act" value="process">';

		$order_form.= $ssip_in1;
		$order_form.= $ssip_in2;

		$order_form.= '<div style="margin:50px 0; text-align:center;">';
		$order_form.= $input_button;
		$order_form.= '</div>';
		$order_form.= '</form>';
		$order_form.= '</div>';
	}
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type"         content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type"   content="text/css">
	<meta http-equiv="Content-Script-Type"  content="text/javascript">
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
	<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css">
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="../css/my.css"       media="all">
	
	<script src="../js/jquery-3.2.1.min.js"            charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js"   charset="UTF-8"></script>
	<script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>

	<style>
		.notable td{border:none; padding:2px 5px;}
		.notable td p{margin:0;padding:0;}
		
		.p_area{width:100%; border:1px solid #E4E4E4; padding:10px; overflow:hidden; border-radius:10px;}
		
		
		.add_in{margin-top:10px;}
		
		.btn_cancel{background:#D4D4D4;}
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
					
					<h2 style="overflow:hidden;">Deliver Address</h2>
					<div class="form_max">
						<?php echo $order_form;?>
					</div>
				</div>
			</div>
		</div>
	</div>
		
	<div id="footer">
		<?php require('inc_user_footer.php');?>
	</div>
</div>
<script src="../js/MyHelper.js" charset="UTF-8"></script>
<?php require('../util/inc_order_ship.php');?>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
