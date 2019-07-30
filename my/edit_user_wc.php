<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id = $_COOKIE['user_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_workplace_name = 'ゲスト様';
	tep_redirect('../user_logout.php', '', 'SSL');
}


/*-------- 全体設定 --------*/
//▼リンク先
$form_action_to   = basename($_SERVER['PHP_SELF']);
$form_action_next = 'edit_user_info.php';


$disabled = 'disabled';


//▼ユーザーメールアドレス
$user_array = tep_db_query("
	SELECT
		`user_agent_id`,
		`user_email`
	FROM `".TABLE_USER."` 
	WHERE `state`   = '1' 
	AND   `user_id` = '".tep_db_input($user_id)."' 
");

$a = tep_db_fetch_array($user_array);
$jsonUser = json_encode($a);

//▼一元管理受付代理店
$home_agent = zGetHomeAgentNumber($a['user_agent_id']);

//▼一元管理用ユーザーid
$h_user_id  = nGetHomeUserID($user_id);


/*-------- ウォレット＆カード情報登録 --------*/
if($_POST['act'] == 'process'){

	/*----- エラーチェック -----*/
	$err = false;
	
	$mail = $_POST['user_wallet_card_email'];
	$own  = $_POST['user_wallet_card_own'];
	
	if(empty($mail))      {$err = true; $err_text = '<p class="err">会員IDを入力してください</p>';}
	if(empty($own))       {$err = true; $err_text = '<p class="err">カードの状況を選択してください</p>';}
	if(empty($home_agent)){$err = true; $err_text = '<p class="err">有効な代理店が設定されていません</p>';}
	
	
	/*----- DB登録 -----*/
	if($err == false){
		
		//▼登録状況確認
		$query_ch_wc = tep_db_query("
			SELECT
				*
			FROM `".TABLE_USER_WALLET_CARD."`
			WHERE `user_id` = '".tep_db_input($user_id)."'
			AND   `state` = '1'
		");
		
		$now = date('Y-m-d');
		
		
		/*--- ローカルDB登録 ---*/
		if($d_wc = tep_db_fetch_array($query_ch_wc)){
			
			//▼データ調整
			unset($d_wc['user_wallet_card_ai_id']);
			
			//▼値を追加
			$d_wc['user_wallet_card_own']   = $own;		//カード状況
			$d_wc['user_wallet_card_email'] = $mail;	//会員ID
			$d_wc['date_create']            = $now;
			
			//▼変数設定
			$sql_data_array = $d_wc;											//登録データ
			$old_array      = array('date_update'=>'now()','state' =>'y'); 
			$w_set          = "`user_id` = '".tep_db_input($user_id)."' AND `state` = '1'";
			
			//▼登録実行
			zDBUpdate(TABLE_USER_WALLET_CARD,$sql_data_array,$old_array,$w_set);
			
		}else{
			
			//▼登録用配列
			$sql_data_array = array(
				'user_id'                           => $user_id,
				'user_wallet_card_own'              => $own,
				'user_wallet_card_email'            => $mail,
				'user_wallet_card_condition'        => '1',
				'user_wallet_card_date_application' => $now,
				'date_create'                       => $now,
				'state'                             => '1'
			);

			//▼新規登録
			tep_db_perform(TABLE_USER_WALLET_CARD, $sql_data_array);
			
			$new = 'new';
		}
		
		
		/*--- HomeDB登録 ---*/
		//▼新規の場合　＞　HOMEDBに登録　　既存の場合　＞　prject登録のみ
		if(($own == 'a')OR($own == 'b')){
			
			//▼接続先url
			$url = nGetNigApiUrl('wc');
			
			//▼受付代理店を追加
			$sql_data_array['home_agent_number'] = $home_agent;
			
			
			//▼データ登録
			$res = nHomeSendPost($url,$sql_data_array);
			
			$end = ($res == 'ok')? 'end' : '';
		
		}else{
			$end = 'end';
		}
		
		
		//▼クッキーの設定
		if(empty($_COOKIE['h_user_id'])){
			if($h_user_id = nGetHomeUserID($user_id)){
				tep_cookie_set('h_user_id',$h_user_id);
			}
		}
		
		
		/*--- 終了処理 ---*/
		if($end == 'end'){
			
			//既存の場合　＞　ユーザートップへ
			if($own == 'a'){
				echo '<script>setTimeout(function(){alert("NIGアカウントの基本情報を引き継ぎます"); location.href="index.php";},600);</script>';
			}
			
			
			//新規かつ初めての登録の場合　＞　情報登録へ
			if(($own == 'b')AND($new == 'new')){
				echo '<script>setTimeout(function(){alert("登録しました"); location.href="'.$form_action_next.'";},600);</script>';
			}else{
				echo '<script>alert("登録しました");</script>';
			}
			
		}else{
			$err_text = '<p class="err">データの登録に失敗しました</p>';
		}
	}
}


/*-------- 初期設定 --------*/
//▼ユーザー情報伝達
require ('inc_user_announce.php');


/*-------- フォーム設定 --------*/
$w_c_place_text = "NIGウォレット＆カードの会員ID";


//▼登録メールアドレス
$in_wc_l = 'col-xs-12 col-sm-6 col-md-6 col-lg-6';

$in_wc.= '<p id="IdExp" class="notice"></p>';
$in_wc.= '<p><input type="email" id="WCMail" class="input_text" name="user_wallet_card_email" value="'.$user_card_email.'" placeholder="'.$w_c_place_text.'"></p>';
$in_wc.= '<p id="CopyMail" class="button_ano">基本情報で登録したメールアドレスを<br>ウォレットIDとして使う方は　クリック</p>';


//▼カードの所有
$col_l = 'col-xs-1 col-sm-1 col-md-1 col-lg-1';
$col_r = 'col-xs-11 col-sm-11 col-md-11 col-lg-11';

if($user_card_own){

	if($user_card_own == 'a'){
		
		//▼既存の場合
		$ch_a = 'checked';
		$data4_form_ele_1 = '<input type="radio" class="ownRadio '.$col_l.'" name="user_wallet_card_own" '.$ch_a.' value="a">';
		$data4_form_ele_1.= '<p class="'.$col_r.'">「NIGウォレット＆カード」を持っています。※申請中の方も含みます</p>';
		
	}else if($user_card_own == 'b'){
		
		//▼新規の場合
		$ch_b = 'checked';
		$data4_form_ele_3 = '<input type="radio" class="ownRadio '.$col_l.'" name="user_wallet_card_own" '.$ch_b.' value="b">';
		$data4_form_ele_3.= '<p class="'.$col_r.'">「NIGウォレット＆カード」を持っていません。</p>';
	}
	
}else{
	
	//▼新規の場合
	$data4_form_ele_1 = '<input type="radio" class="ownRadio '.$col_l.'" name="user_wallet_card_own" '.$ch_a.' value="a">';
	$data4_form_ele_1.= '<p class="'.$col_r.'">「NIGウォレット＆カード」を持っています。※申請中の方も含みます</p>';

	$data4_form_ele_3 = '<input type="radio" class="ownRadio '.$col_l.'" name="user_wallet_card_own" '.$ch_b.' value="b">';
	$data4_form_ele_3.= '<p class="'.$col_r.'">「NIGウォレット＆カード」を持っていません。</p>';
	
}


//▼申込ボタン
$data4_form_ele_submit = '<input type="button" class="btn form_submit" value="有効な会員IDかを確認する" '.$disabled.' id="ActCheck">';
$data4_form_ele_submit.= '<input type="submit" class="btn form_submit wcClose" value="この内容で登録する" id="Act">';
$data4_form_ele_submit.= '<input type="button" class="btn form_cancel spc10_l" value="リセット" onClick="location.href=\''.$form_action_to.'\'">';


//▼自動入力要素
$input_auto = '<input type="hidden" name="act"     value="process">';
$input_auto.= '<input type="hidden" name="user_wc" value="check">';

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
	
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="google" value="notranslate">
	
	<link rel="stylesheet" type="text/css" href="../css/cssreset.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/common.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/user.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/user_nig.css" media="all">
	
	<link rel="stylesheet" href="../js/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" href="../js/bootstrap/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="../js/keypad/jquery.keypad.css" media="all">
	
	<script src="../js/jquery-2.2.1.min.js" charset="UTF-8"></script>
	<script type="text/javascript" src="../js/keypad/jquery.plugin.min.js"></script> 
	<script type="text/javascript" src="../js/keypad/jquery.keypad.min.js"></script>
	<script type="text/javascript" src="../js/keypad/keylayout.js"></script>
	
	<style>
		.button_ano{max-width:300px;margin:0 auto;}
		#InWC{text-align:center; display:none;}
		.wcClose{display:none;}
		
	</style>
</head>
<body id="body">
<div id="wrapper">
	
	<div class="my_header">
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
					<div class="part">
						<?php echo $warning; ?>
						
						<div class="area1">
							<form action="<?php echo $form_action_to;?>" method="POST" id="WCForm">
								<?php echo $input_auto;?>
								<div class="form_area">
									<h3>NIGウォレット＆カードについて教えてください</h3>
									<ul class="form_table">
										<li id="wca">
											<div class="form_el row">
												<?php echo $data4_form_ele_1;?>
											</div>
										</li>
										<li id="wcb">
											<div class="form_el row">
												<?php echo $data4_form_ele_3;?>
											</div>
										</li>
									</ul>
									<div id="InWC">
										<?php echo $in_wc;?>
									</div>
								</div>
								<div class="submit_area spc20">
									<?php echo $err_text;?>
									<?php echo $data4_form_ele_submit;?>
								</div>
							</form>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<script src="../js/jUserHelper.js" charset="UTF-8"></script>
	
	<div id="footer">
		<?php require('inc_user_footer.php'); ?>
	</div>
</div>
<script>
	
	var bb = ['a','b'];
	var cc = ["お持ちのNIGウォレット＆カードの会員IDを入力してください。","NIGウォレット＆カードの会員IDとして登録するメールアドレスを入力してください。"];
	
	
	function zCheckOwnRadio(){
		var Flag = 0;
		var Own = '';
		
		//▼カード状況の選択
		Own = $('.ownRadio:checked').val();
		
		if((Own == "a")||(Own == "b")||(Own == "c")){
			
			for(var i =0; i< bb.length; i++){
				
				if(Own == bb[i]){
					$('#IdExp').html(cc[i]);
				}else{
					$('#wc'+bb[i]).slideUp(1000);
				}
			}
			
			$('#InWC').slideDown(1000);
			
			Flag = 1;
		}
		
		var aa = jIsValue('WCMail');
		
		Flag = Flag * aa;
		if(Flag > 0){
			$('#ActCheck').prop('disabled',false);
		}else{
			$('#ActCheck').prop('disabled',true);
		}
	}
	
	$('#WCForm').on('change click',function(){
		zCheckOwnRadio();
	});
	
	
	//▼アドレスコピー
	$('#CopyMail').on('click',function(){
		
		//▼値を取得
		var Ma = JSON.parse('<?php echo $jsonUser; ?>');

		$('#WCMail').val(Ma['user_email']);
	});
	
	
	/*----- データ送信 -----*/
	function jSendPost(formData){
		$.ajax({
			url  : "xml_http.php",
			type : "POST",
			contentType : false,
			processData : false,
			dataType    : "text",
			data : formData
		})
		
		.done(function(response){

			var Re    = JSON.parse(response);
			var state = Re['state'];
			var own   = Re['own'];
			
			if(state == 'ok'){
				
				if(own == 'a'){
					alert("会員IDの確認が取れました");
				}else{
					alert("登録できる会員IDです");
				}
				
				//▼ボタンの非表示
				$('#ActCheck').addClass('wcClose');
				
				//▼登録ボタンの表示
				$('#Act').removeClass('wcClose');
				
			}else if(state == 'ng'){
				
				if(own == 'a'){
					alert("会員IDの確認が取れません");
				}else{
					alert("この会員IDはすでに登録されています");
				}
			
			}else{
				alert("データが正しくありません");
			}
		})
		
		.fail(function(jqXHR, textStatus, errorThrown){
			alert("データの確認に失敗しました");
		});
	}
	
	
	$('#ActCheck').on('click',function(){
		//▼フォームデータを取得
		var formData = new FormData($('#WCForm').get(0));
		jSendPost(formData);
	});
	
	
	
	/*----- radioボタン初期化 -----*/
	var va;
	$(function(){
		 $('.ownRadio').on('click',function(){
			
			if(va == $(this).val()){
				
				$(this).prop('checked',false);

				for(var i =0; i< bb.length; i++){
					if(bb[i] != va){
						$('#wc'+bb[i]).slideToggle(1100);
					}
				}
				
				setTimeout(function(){
					
					$('#InWC').slideToggle(600,function(){$('#WCMail').val('');});
					//初期化
					va = 0;
				
				}, 300)
				
			}else{
				va = $(this).val();
			}
		 });
	});
	
	var yy = '<?php echo $user_card_own;?>';
	if(yy){zCheckOwnRadio();}
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
