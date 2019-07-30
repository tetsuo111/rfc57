<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


/*-------- 全体設定 --------*/
//▼とび先設定
$form_action_to = basename($_SERVER['PHP_SELF']);
$e_master_id    = $_GET['e_master_id'];

//▼IDの取得
if($e_master_id){
	$cont_set    = "?e_master_id=".$e_master_id;
	//$pass_type = 'password';
}


//----- リスト取得 -----//
//▼登録済みデータ
$query = tep_db_query("
	SELECT
		`master_id`,
		`master_permission` AS `perm`,
		`master_password`   AS `pass`,
		`master_name`       AS `name`,
		`master_email`      AS `mail`,
		DATE_FORMAT(`date_create`,'%Y-%m-%d') AS `dcr`
	FROM `".TABLE_MASTER."`
	WHERE    `state` = '1'
	AND      `master_permission` != 's'
	ORDER BY `master_id` ASC
");

//▼データ取得
while($a = tep_db_fetch_array($query)){
	
	//▼権限
	if($a['perm'] == "a"){
		$permission = "管理者";
		
	}else if($a['perm'] == "o"){
		$permission = "オペレータ";
	}

	if($e_master_id == $a['master_id']){
		$dt = $a;
	}
	
	//▼操作
	$edit = '<a href="'.$form_action_to.'?e_master_id='.$a['master_id'].'"><button type="button">編集する</button></a>';
	
	$input_edit.= '<tr>';
	$input_edit.= '<td>'.$a['master_id'].'</td>';
	$input_edit.= '<td>'.$a['name'].'</td>';
	$input_edit.= '<td>'.$a['mail'].'</td>';
	$input_edit.= '<td>'.$permission.'</td>';
	$input_edit.= '<td>'.$a['dcr'].'</td>';
	$input_edit.= '<td>'.$edit.'</td>';
	$input_edit.= '</tr>';
}


//▼データの取得
$p_perm = ($_POST["act"])? $_POST['master_permission'] : $dt['perm'];
$p_name = ($_POST["act"])? $_POST['master_name']       : $dt['name'];
$p_mail = ($_POST["act"])? $_POST['master_email']      : $dt['mail'];
$p_pass = $_POST['master_password'];
$c_pass = $dt['pass'];


//----- データ処理 -----//
if(($_POST["act"] == "process")AND($_POST["act_send"])){
	
	//▼暗号化
	$crypted_password = tep_encrypt_password($p_pass);
	
	//▼登録するユーザー情報
	$data_array = array(
		'master_permission' => $p_perm,
		'master_name'       => $p_name,
		'master_email'      => $p_mail,
		'master_password'   => $crypted_password,
		'date_create'       => 'now()',
		'state'             => '1'
	);
	
	
	//▼登録設定
	$db_table = TABLE_MASTER;
	$ai_id    = 'master_ai_id';
	$tb_id    = 'master_id';
	
	//▼DB登録
	if($e_master_id){
		
		//更新登録
		zDBUpdate($db_table,$data_array,$e_master_id);
		
	}else{
		//新規登録
		zDBNewUniqueID($db_table,$data_array,$ai_id,$tb_id);
	}

	//▼終了処理
	$end = 'end';
	
}else if($_POST["act"] == "process"){
		
		//STEP2　確認画面
		//▼エラーチェック
		$err = false;
		
		//▼戻るボタン対策
		if($_POST['act_back']){$err = true;}
		
		if(!$p_name) {$err = true; $err_text.= '<p class="alert">氏名を入力してください</p>';}
		
		if(!$p_mail) {
			$err = true; $err_text.= '<p class="alert">ログイン用メールアドレスを入力してください</p>';
		}else{
			
			//▼自分は除外
			$exist_mail = ($e_master_id)? "AND `master_id` != '".$e_master_id."'":"";
			
			//アドレス重複確認　新規の場合
			$query = tep_db_query("
				SELECT
					`master_id`
				FROM `".TABLE_MASTER."`
				WHERE `state` = '1'
				AND `master_email`  = '".tep_db_input($p_mail)."'
				".$exist_mail);
			if(tep_db_num_rows($query)){$err = true; $err_text.= '<p class="alert">登録できないメールアドレスです</p>';}
		}

		if(!$p_pass && !$c_pass) {$err = true; $err_text.= '<p class="alert">パスワードを入力してください</p>';}
		if(!$p_perm) {$err = true; $err_text.= '<p class="alert">管理者権限を選択してください</p>';}
		
		//▼エラーがない場合
		if($err == false){

			//▼自動入力要素
			$input_auto_in = '<input type="hidden" name="master_name"       value="'.$p_name.'">';
			$input_auto_in.= '<input type="hidden" name="master_email"      value="'.$p_mail.'">';
			$input_auto_in.= '<input type="hidden" name="master_password"   value="'.$p_pass.'">';
			$input_auto_in.= '<input type="hidden" name="master_permission" value="'.$p_perm.'">';
			
			
			if($p_perm == "a"){
				$master_permission = "管理者";
			}else if($p_perm == "o"){
				$master_permission = "オペレータ";
			}
			
			$input_master_name       = $p_name;
			$input_master_email      = $p_mail;
			$input_master_password   = $p_pass;
			$input_master_permission = $master_permission;

			//▼入力ボタン
			$input_button = '<input type="submit" name="act_send" class="button_input"         value="この内容で登録する">';
			$input_button.= '<input type="submit" name="act_back" class="spa10_l button_back"  value="戻る">';
			
		}else{
			
			$input_master_name     = '<input type="text" name="master_name"     value="'.$p_name.'">';
			$input_master_email    = '<input type="text" name="master_email"    value="'.$p_mail.'">';
			$input_master_password = '<input type="text" name="master_password" value="'.$p_pass.'">';
			
			
			if($p_perm == "a"){
				$checked_a = "checked";
			}elseif($p_name == "o"){
				$checked_o = "checked";
			}
			
			$input_master_permission = '<input type="radio" name="master_permission" '.$checked_a.' value="a">管理者　';
			$input_master_permission.= '<input type="radio" name="master_permission" '.$checked_o.' value="o">オペレータ';
			
			
			//▼入力ボタン
			$input_button = '<input class="button_input" type="submit" value="確認画面">';
			$input_button.= '<a href="'.$form_action_to.'" style="text-decoration:none;"><input type="button" value="クリア"></a>';
		}
		
}else{
		
		//▼STEP1　初期設定
		$input_master_name     = '<input type="text"  name="master_name"     value="'.$p_name.'">';
		$input_master_email    = '<input type="text"  name="master_email"    value="'.$p_mail.'">';
		$input_master_password = '<input type="text"  name="master_password" value="'.$p_pass.'">';
		
		//▼権限
		$checked_a = ($p_perm == 'a')? 'checked':'';
		$checked_o = ($p_perm == 'o')? 'checked':'';
		$input_master_permission = '<input type="radio" name="master_permission" '.$checked_a.' value="a">管理者　';
		$input_master_permission.= '<input type="radio" name="master_permission" '.$checked_o.' value="o">オペレータ';
		
		//▼入力ボタン
		$input_button = '<input type="submit" class="button_input" value="確認画面">';
		$input_button.= '<a href="'.$form_action_to.'" style="text-decoration:none;"><input type="button" value="クリア"></a>';

}


//----- 表示フォーム -----//
if($end == 'end'){
	
	$input_form = '<p>登録しました</p>';
	$input_form.= '<a href="'.$form_action_to.'">オペレータの登録を続ける</a>';
	$input_list = '';
	
}else{


	//▼登録フォーム
	$input_auto = '<input type="hidden" name="act" value="process">';
	$input_auto.= $input_auto_in; 
	
	
	$alm_pass = ($e_master_id)? '<p class="alert">変更する場合は入力してください</p>':'';
	
	$input_form_in2 ='<tr><th style="width:130px;">オペレータ氏名 (必須)</th><td>'.$input_master_name.'</td></tr>';
	$input_form_in2.='<tr><th>メールアドレス (必須)</th><td>'.$input_master_email.'</td></tr>';
	$input_form_in2.='<tr><th>パスワード (必須)</th><td>'.$input_master_password.$alm_pass.'</td></tr>';
	$input_form_in2.='<tr><th>権限 (必須)</th><td>'.$input_master_permission.'</td></tr>';
	
	
	$input_form ='<form action="'.$form_action_to.$cont_set.'" method="POST">';
	$input_form.= $input_auto;
	$input_form.='<table class="input_form">';
	$input_form.= $input_form_in2;
	$input_form.='</table>';
	$input_form.='<div class="spc20">';
	$input_form.= $input_button;
	$input_form.='</div>';
	$input_form.='</form>';
	
	
	
	//▼登録済みリスト
	$input_head = '<th>番号</th>';
	$input_head.= '<th>オペレータ氏名</th>';
	$input_head.= '<th>メールアドレス</th>';
	$input_head.= '<th>権限</th>';
	$input_head.= '<th>登録日</th>';
	$input_head.= '<th>操作</th>';
	
	$input_list = '<h2>登録済オペレータ</h2>';
	$input_list.= '<div class="spc20">';
	$input_list.= '<table class="input_list">';
	$input_list.= '<tr>'.$input_head.'</tr>';
	$input_list.= $input_edit;
	$input_list.= '</table>';
	$input_list.= '</div>';
}

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
	<link rel="stylesheet" type="text/css" href="../css/cssreset.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/common.css"   media="all">
	<link rel="stylesheet" type="text/css" href="../css/master.css"   media="all">

	<script src="../js/jquery-3.2.1.min.js"          charset="UTF-8"></script>
	<script src="../js/jquery-migrate-1.4.1.min.js" charset="UTF-8"></script>
	
	<style>
		.input_list{width:100%;}
		.input_form input[type="text"]{padding:5px;}
		.input_form input[type="passward"]{padding:5px;}
	</style>

</head>
<body id="body">
<div id="wrapper">
	
	<div id="header">
		<?php require('inc_master_header.php');?>
	</div>
	<div id="head_line">
		<?php require('inc_master_head_line.php');?>
	</div>
	
	<div id="content">
		<div class="content_outer">
			<div id="left1">
				<div class="inner">
					<?php require('inc_master_left.php'); ?>
				</div>
			</div>
		
			<div id="left2">
				<div class="inner">
				
					<div class="master_menu">
						<?php require('inc_master_menu.php');?>
					</div>
					
					<div class="spc20">
						<h2>企業別オペレータ登録</h2>
						<div>
							<?php echo $err_text;?>
							<?php echo $input_form;?>
						</div>
					</div>
					
					<div class="spc50">
						<?php echo $input_list;?>
					</div>
				</div>
			</div>

			<div class="float_clear"></div>
		</div>
	</div>
	
	<div id="footer">
		<?php require('inc_master_footer.php'); ?>
	</div>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
