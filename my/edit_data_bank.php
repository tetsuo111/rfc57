<?php



require('includes/application_top.php');
require('includes/database.php');


if($_COOKIE['user_id']){
	$user_id = $_COOKIE['user_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}

//▼ユーザー情報伝達
require ('inc_user_announce.php');



// 更新APIモード
if ($_POST) {
	
//	$sql_data_array = $_POST;
//	tep_db_perform(TABLE_MEM00000, $sql_data_array, 'update', " `memberid` = '".tep_db_input($user_id)."'");

	$in = $_POST;
	$in['memberid'] = $user_id;
	update('mem00002', 'bankcode bankkana branchcode branchkana actype acnumber kigou bangou ackana', 'memberid', $in);
	echo json_encode(array('status'=>200));
	exit;
}

$in['memberid'] = $user_id;
$mem00002 = query("SELECT * FROM mem00002 WHERE memberid = :memberid", $in);
if (empty($mem00002)) insert('mem00002', 'memberid', $in);


$user_query = tep_db_query("
	SELECT `email`
	FROM `".TABLE_MEM00000."` 
	WHERE `memberid` = '".tep_db_input($user_id)."' 
");
$user = tep_db_fetch_array($user_query);
$now_email  = $user['email'];
$new_email  = $_POST['new_email'];

if (isset($_POST['act']) && ($_POST['act'] == 'edit')) {
	$err = false;
	$err_empty = false;

	if(empty($new_email)){
		$err = true; $err_empty = true;
	}else{
		////登録済メールアドレスチェック
		$new_email = mb_convert_kana($new_email, "as");        //全角英数字、スペースを半角に  //http://php.net/manual/ja/function.mb-convert-kana.php
		$new_email = preg_replace('/(\s|　)/','',$new_email);  //スペース削除
		$email_query = tep_db_query("
			SELECT u.`memberid`
			FROM `".TABLE_MEM00000."` u 
			WHERE u.`email` = '".tep_db_input($new_email)."'
		");
		if(!tep_db_num_rows($email_query) ) { //該当なし
		}else{//既存のemail
			$err = true; $err_email = true;
		}
	}
}elseif($_POST['act'] == 'no_err'){
	
	$sql_data_array = array('email' => $new_email);
	tep_db_perform(TABLE_MEM00000, $sql_data_array, 'update', " `memberid` = '".tep_db_input($user_id)."'");
	tep_cookie_set('email',$new_email);    //$_COOKIE['email']
	/*
	//メール送信
	$email = $user_email;
	$reset_url = HTTP_SERVER.'/pass_reset.php?uid='.$user_id.'&email='.$user_email;
	Email_Pass_Reset($EmailHead,$EmailFoot,$email,$reset_url);
	*/
	$announce = '<div class="announce">';
	$announce.= 'メールアドレスを変更しました。<br>';
	$announce.= '</div>';
	$echo_flag = 'announce';
}

//err text
if($err_empty == true) { $edit_err_text  = '<p class="alert">未入力の項目があります。</p>'; }
if($err_email == true) { $edit_err_text .= '<p class="alert">既に登録のあるメールアドレスです。</p>'; }

if(($_POST['act'] == 'edit') && ($err == false)){
	$item .= '<input type="hidden" name="new_email" value="'.$new_email.'">';             //希望確認
	
	$edit_form  = '<form name="repass" action="edit_data_email.php" method="post">';
	$edit_form .= '<input type="hidden" name="act" value="no_err">';
	$edit_form .= $item;
	$edit_form_ele_text = $edit_err_text;
	$edit_form_ele_1 = '<input class="form-control" type="text" value="'.$now_email.'" disabled="disabled">';             //現在
	$edit_form_ele_2 = '<input class="form-control" type="text" value="'.$new_email.'" disabled="disabled">';             //希望
	$edit_form_ele_submit = '<input type="submit"  class="btn" value="変更する">';
	$edit_form_end  = '</form>';
	
}else{
	$edit_form  = '<form name="repass" action="edit_data_email.php" method="post">';
	$edit_form .= '<input type="hidden" name="act" value="edit">';
	$edit_form_ele_text = $edit_err_text;
	$edit_form_ele_1 = '<input class="form-control" type="text" name="now_email" value="'.$now_email.'" disabled="disabled">';  //現在
	$edit_form_ele_2 = '<input class="form-control" type="text" name="new_email" value="'.$new_email.'">';                      //希望
	$edit_form_ele_submit = '<input type="submit" class="btn" value="確認">';
	$edit_form_end  = '</form>';
}



i
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

	// 動作確認中---木下

	$(document).ready(function(){
	
		var api = 'http://52.192.193.100/api/bankcode.php';
		var bankinitial   = $('#bankinitial');
		var bankname      = $('#bankname');
		var branchinitial = $('#branchinitial');
		var branchname    = $('#branchname');
		

		if (bankinitial) {

			bankinitial.append($('<option>').html('（先頭ｶﾅ）').val(''));
			bankname.append($('<option>').html('（銀行名）').val(''));
			branchinitial.append($('<option>').html('（先頭ｶﾅ）').val(''));
			branchname.append($('<option>').html('（支店名）').val(''));

			$.getJSON(api, {}, function(data) {
				$.each(data, function(i, item) {
					bankinitial.append($('<option>').html(data[i].initial).val(data[i].initial));
				});
			});

			bankinitial.on('change', function(){

				branchinitial.children().remove();
				branchinitial.append($('<option>').html('（先頭ｶﾅ）').val(''));
				branchname.children().remove();
				branchname.append($('<option>').html('（支店名）').val(''));

				$.getJSON(api,  {bankinitial:bankinitial.val()}, function(data) {
					bankname.children().remove();
					bankname.append($('<option>').html('（銀行名）').val(''));
					$.each(data, function(i, item) {
						bankname.append($('<option>').html(data[i].name).val(data[i].name));
					});
				});
			});

			bankname.on('change', function(){
				$.getJSON(api,  {bankname:bankname.val()}, function(data) {
					branchinitial.children().remove();
					branchinitial.append($('<option>').html('（先頭ｶﾅ）').val(''));
					branchname.children().remove();
					branchname.append($('<option>').html('（支店名）').val(''));
					$.each(data, function(i, item) {
						branchinitial.append($('<option>').html(data[i].initial).val(data[i].initial));
					});
				});
			});

			branchinitial.on('change', function(){
				$.getJSON(api,  {bankname:bankname.val(), branchinitial:branchinitial.val()}, function(data) {
					branchname.children().remove();
					branchname.append($('<option>').html('（支店名）').val(''));
					$.each(data, function(i, item) {
						branchname.append($('<option>').html(data[i].name).val(data[i].name));
					});
				});
			});


		};
	});
	</script>
	<script>
		function update() {
			
			var error = [];
			
			
			if (error.length) {
				alert(error.join("\n"));
				return false;
			}
			
			alert('更新します');
			$.post( "edit_data_bank.php", $("#bank-edit").serialize() , function( data ) {
				alert('更新しました');
			});
			return true;
		}
	</script>


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
			
			<div id="left2" class="col-xs-12 col-sm-10 col-md-6 col-lg-4">
				<div class="inner">

					<div class="area1">

							<div class="form_group form_area">
								<h3>報酬受取口座</h3>
								<form id="bank-edit">

								<input type="hidden" id="bankcode" name="bankcode" value="">
								<input type="hidden" id="bankkana" name="bankkana" value="">
								<input type="hidden" id="branchcode" name="branchcode" value="">
								<input type="hidden" id="branchkana" name="branchkana" value="">

								<ul class="form_table">
									<li>
										<div class="form_el row">
											<h4>金融機関種別</h4>
											<div>
												<select class="form-control" id="banktype" name="banktype" onchange="
													if ($('#banktype').val()==1) {
														$('.bank').show();
														$('.post').hide();
													} else {
														$('.bank').hide();
														$('.post').show();
													}
												">
													<option value="1">銀行</option>
													<option value="2">ゆうちょ</option>
												</select>
											</div>
										</div>
									</li>

									<li>
										<div class="form_el row bank">
											<h4>金融機関</h4>
											<div><select class="form-control" id="bankinitial" name="bankinitial"></select></div>
											<div><select class="form-control" id="bankname" name="bankname"></select></div>
										</div>
									</li>
									<li>
										<div class="form_el row bank">
											<h4>支店</h4>
											<div><select class="form-control" id="branchinitial" name="branchinitial"></select></div>
											<div><select class="form-control" id="branchname" name="branchname"></select></div>
										</div>
									</li>
									<li>
										<div class="form_el row bank">
											<h4>口座種別</h4>
											<div>
												<select class="form-control" id="actype" name="actype">
													<option value="1">普通</option>
													<option value="2">当座</option>
													<option value="4">貯蓄</option>
												</select>
											</div>
										</div>
									</li>
									<li>
										<div class="form_el row">
											<h4>口座番号</h4>
											<div><input class="form-control" type="text" id="acnumber" name="acnumber" value="<?php echo $mem00002['acnumber']; ?>"></div>
										</div>
									</li>

									<li>
										<div class="form_el row post" style="display:none">
											<h4>記号</h4>
											<div><input class="form-control" type="text" id="acnumber" name="kigou" value="<?php echo $mem00002['kigou']; ?>"></div>
										</div>
									</li>

									<li>
										<div class="form_el row post" style="display:none">
											<h4>番号</h4>	
											<div><input class="form-control" type="text" id="acnumber" name="bangou" value="<?php echo $mem00002['bangou']; ?>"></div>
										</div>
									</li>

									<li>
										<div class="form_el row">
											<h4>口座人名義（カナ）</h4>
											<div><input class="form-control" type="text" id="ackana" name="ackana" value="<?php echo $mem00002['ackana']; ?>"></div>
										</div>
									</li>
								</ul>

							</div>
							<div class="submit_area spc20">
								<input class="btn btn-default" value="確認" type="button" onclick="update()">
							</div>
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
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>