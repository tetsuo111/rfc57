<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id  = $_COOKIE['user_id'];
	$memberid = $_COOKIE['user_id'];
	$head_user_name = $_COOKIE['user_name'];
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}


/*----- 初期設定 -----*/
//▼登録ボタン用
$disabled = 'disabled';

//▼表示処理
$target_a = '';
$target_b = '';

$form_action_to   = basename($_SERVER['PHP_SELF']);
//$form_action_next = 'edit_user_upload.php';
$form_action_next = 'order.php';


//▼Navigation
if($_COOKIE['bctype'] == '21'){
	//▼ユーザーは写真を削除
	unset($NavUserInfoJP['edit_user_upload']);
}
$my_nav = zGetMyNavigation($NavUserInfoJP,$form_action_to);


//-------------必要情報読込-------------
//▼登録情報取得
$user_address_zip_a     = $_POST['user_address_zip_a'];
$user_address_zip_b     = $_POST['user_address_zip_b'];
$user_address_pref      = $_POST['user_address_pref'];
$user_address_city      = $_POST['user_address_city'];
$user_address_area      = $_POST['user_address_area'];
$user_address_strt      = $_POST['user_address_strt'];

$user_address_pref_roma = $_POST['user_address_pref_roma'];
$user_address_city_roma = $_POST['user_address_city_roma'];
$user_address_area_roma = $_POST['user_address_area_roma'];
$user_address_strt_roma = $_POST['user_address_strt_roma'];
$user_address_country_code = $_POST['user_address_country_code'];



/*------------------ データ処理 ------------------*/
if(($_POST['act'] == 'process')AND(!empty($_POST['act_send']))){
	
	//▼登録情報
	$sql_data_array = array(
		'memberid' => $memberid,
		'adrpost'  => $user_address_zip_a.'-'.$user_address_zip_b,
		'adr1'     => $user_address_pref,
		'adr2'     => $user_address_city,
		'adr3'     => $user_address_area,
		'adr4'     => $user_address_strt
	);
	
	//▼テーブル指定
	$db_table = TABLE_MEM00001;
	
	//▼DB登録
	if(zCheckUserRegMem($db_table,$user_id)){
		
		//更新登録
		$w_set = "`memberid`='".tep_db_input($memberid)."'";
		tep_db_perform($db_table,$sql_data_array,'update',$w_set);
		$end   = '<script>alert("登録しました");</script>';
		
	}else{
		//新規登録
		tep_db_perform($db_table,$sql_data_array);
		$end = '<script>alert("登録しました");location.href="'.$form_action_next.'";</script>';
	}
	
	
	/*----- ステータス更新 -----*/
	$st = zUserStatusCheck($user_id);
	
	//更新の時は何もしない
	if($st['addr'] != 'u'){
		
		//n　＞　u更新に変更
		if($st['addr'] == 'n'){
				$res = 'u';
		}else{
			$res = '1';
		}
		
		//▼ステータス更新
		zUserWCStatusUpdate('user_wc_status_info_address',$res,$user_id);
	}
	
	//▼終了処理
	echo $end;

}else if($_POST['act'] == 'process'){
	
	
	/*-------- エラーチェック --------*/
	$err = false;
	
	
	/*----- 住所 -----*/
	if((!$user_address_zip_a) || (!$user_address_zip_b)){$err = true; $err_zip = true;}	//郵便番号
	//if(!$user_address_country_code) { $err = true; $err_c_code = true;}					//国別コード
	if(!$user_address_pref)         { $err = true; $err_pref  = true; }					//都道府県
	if(!$user_address_city)         { $err = true; $err_city  = true; }					//市区町村
	if(!$user_address_area)         { $err = true; $err_area  = true; }					//番地
	
	
	/*-------- 表示設定 --------*/
	if(($err == false)AND(empty($_POST['act_cancel']))){
	
		//▼エラーなし　＞　確認画面
		$form_select = 'process';
		
	}else{
	
		if($err_zip       == true) { $edit_err_zip       = '<span class="err"> 未記入</span>'; }
		if($err_pref      == true) { $edit_err_pref      = '<span class="err"> 未記入</span>'; }
		if($err_city      == true) { $edit_err_city      = '<span class="err"> 未記入</span>'; }
		if($err_area      == true) { $edit_err_area      = '<span class="err"> 未記入</span>'; }
		if($err_pref_roma == true) { $edit_err_pref_roma = '<span class="err"> 未記入</span>'; }
		if($err_city_roma == true) { $edit_err_city_roma = '<span class="err"> 未記入</span>'; }
		if($err_area_roma == true) { $edit_err_area_roma = '<span class="err"> 未記入</span>'; }
		if($err_c_code    == true) { $edit_err_c_code    = '<span class="err"> 未記入</span>'; }
	}
	
} else {


	/*-------- 初期設定 --------*/
	//▼住所
	$user_address_query = tep_db_query("
		SELECT 
			`memberid`,
			`adrpost`,
			`adr1`,
			`adr2`,
			`adr3`,
			`adr4`
		FROM `".TABLE_MEM00001."` 
		WHERE `memberid` = '".tep_db_input($memberid)."' 
	");
	
	if($ua = tep_db_fetch_array($user_address_query)){
		$po = explode('-',$ua['adrpost']);
		
		$user_address_zip_a        = $po[0];
		$user_address_zip_b        = $po[1];
		$user_address_pref         = $ua['adr1'];
		$user_address_city         = $ua['adr2'];
		$user_address_area         = $ua['adr3'];
		$user_address_strt         = $ua['adr4'];
		//$user_address_pref_roma    = $ua['user_address_pref_roma'];
		//$user_address_city_roma    = $ua['user_address_city_roma'];
		//$user_address_area_roma    = $ua['user_address_area_roma'];
		//$user_address_strt_roma    = $ua['user_address_strt_roma'];
		//$user_address_country_code = $ua['user_address_country_code'];
		
	}else{
		//$user_address_country_code = '81';
		
	}
	
	//▼表示処理
	$target_b = ($ua)? '': 'class="tglTargetB"';
}



//------------------表示フォーム------------------
$col_zip     = 'col-xs';
$col_zip_btn = '';

if($form_select == 'process'){
	
	/*----- 自動登録項目 -----*/
	$input_auto = '<input type="hidden" name="act" value="process">';		//登録フォーム
	$user_form_ele_text = $edit_err_text;
	
	
	/*----- 住所 -----*/
	$keep = 'readonly';
	
	$zip_a    = '<input type="text" id="Za"     class="form-control keep" style="width:4em;" name="user_address_zip_a" maxlength="3" value="'.$user_address_zip_a.'" '.$keep.'>';
	$zip_b    = '<input type="text" id="Zb"     class="form-control keep" style="width:5em;" name="user_address_zip_b" maxlength="4" value="'.$user_address_zip_b.'" '.$keep.'>';

	$addr_1   = '<input type="text" id="Apref"  class="form-control keep" name="user_address_pref"      value="'.$user_address_pref.'" '.$keep.'>';
	$addr_2   = '<input type="text" id="Acity"  class="form-control keep" name="user_address_city"      value="'.$user_address_city.'" '.$keep.'>';
	$addr_3   = '<input type="text" id="Aarea"  class="form-control keep" name="user_address_area"      value="'.$user_address_area.'" '.$keep.'>';
	$addr_4   = '<input type="text" id="Astrt"  class="form-control keep" name="user_address_strt"      value="'.$user_address_strt.'" '.$keep.'>';

	$addr_1_2 = '<input type="text" id="AprefK" class="form-control keep" name="user_address_pref_roma" value="'.$user_address_pref_roma.'" '.$keep.'>';
	$addr_2_2 = '<input type="text" id="AcityK" class="form-control keep" name="user_address_city_roma" value="'.$user_address_city_roma.'" '.$keep.'>';
	$addr_3_2 = '<input type="text" id="AareaK" class="form-control keep" name="user_address_area_roma" value="'.$user_address_area_roma.'" '.$keep.'>';
	$addr_4_2 = '<input type="text" id="AstrtK" class="form-control keep" name="user_address_strt_roma" value="'.$user_address_strt_roma.'" '.$keep.'>';

	
	//▼国別コード
	$user_form_country_code = '<input type="text" class="form-control keep" style="width:5em;" name="user_address_country_code" value="'.$user_address_country_code.'" '.$keep.'>';
	
	//▼登録ボタン
	$user_form_submit = '<input type="submit" class="btn form_submit"         name="act_send"   value="登録する">';
	$user_form_submit.= '<input type="submit" class="btn form_cancel spc10_l" name="act_cancel" value="キャンセル">';

}else{

	/*----- フォーム設定 -----*/
	$user_form_ele_text = $edit_err_text;
	
	//▼登録ボタン　＞　既存の場合は変更
	$input_auto = '<input type="hidden" name="act" value="process">';			//登録フォーム
	$serch_zip  = '<button type="button" class="btn name_se" id="iOnToggleB">入力を続ける</button>';
	$user_form_submit = '<input type="submit" class="btn form_submit" value="入力内容を確認する" id="Act" '.$disabled.'>';
	
	
	/*----- 住所登録 -----*/
	//▼電話番号
	$zip_a    = '<input type="text" id="Za"     class="form-control" style="width:4em;" name="user_address_zip_a" maxlength="3" value="'.$user_address_zip_a.'">';
	$zip_b    = '<input type="text" id="Zb"     class="form-control" style="width:5em;" name="user_address_zip_b" maxlength="4" value="'.$user_address_zip_b.'" size="5" maxlength="4" onKeyUp="AjaxZip3.zip2addr(\'user_address_zip_a\',\'user_address_zip_b\',\'user_address_pref\',\'user_address_city\',\'user_address_area\')" >';

	$addr_1   = '<input type="text" id="Apref"  class="form-control" name="user_address_pref"      value="'.$user_address_pref.'" style="width:100px;" required>';
	$addr_2   = '<input type="text" id="Acity"  class="form-control" name="user_address_city"      value="'.$user_address_city.'" required>';
	$addr_3   = '<input type="text" id="Aarea"  class="form-control" name="user_address_area"      value="'.$user_address_area.'" required>';
	$addr_4   = '<input type="text" id="Astrt"  class="form-control" name="user_address_strt"      value="'.$user_address_strt.'">';

	$addr_1_2 = '<input type="text" id="AprefK" class="form-control" name="user_address_pref_roma" value="'.$user_address_pref_roma.'">';
	$addr_2_2 = '<input type="text" id="AcityK" class="form-control" name="user_address_city_roma" value="'.$user_address_city_roma.'">';
	$addr_3_2 = '<input type="text" id="AareaK" class="form-control" name="user_address_area_roma" value="'.$user_address_area_roma.'">';
	$addr_4_2 = '<input type="text" id="AstrtK" class="form-control" name="user_address_strt_roma" value="'.$user_address_strt_roma.'">';
	
	
	//▼国別コード
	$user_form_country_code =  '<input type="text" id="cCode" class="form-control" style="width:5em;" name="user_address_country_code" value="'.$user_address_country_code.'" readonly>';

}


/*----- 表示内容 -----*/
//▼必須
$must = '<span style="font-size:12pt; font-weight:400; color:#DD0000; vertical-align:super;">*</span>';

//▼氏名
$user_form_zip = '<div class="form-inline zip_area">';
$user_form_zip.= '<span>'.$zip_a.'</span><span>-</span><span>'.$zip_b.'</span><span>'.$serch_zip.'</span>';
$user_form_zip.= '</div>';

$user_form_addr_1   = '<div class="form-group">'.$addr_1.'</div>';
$user_form_addr_2   = '<div class="form-group">'.$addr_2.'</div>';
$user_form_addr_3   = '<div class="form-group">'.$addr_3.'</div>';
$user_form_addr_4   = '<div class="form-group">'.$addr_4.'</div>';

$user_form_addr_1_2 = '<div class="form-group">'.$addr_1_2.'</div>';
$user_form_addr_2_2 = '<div class="form-group">'.$addr_2_2.'</div>';
$user_form_addr_3_2 = '<div class="form-group">'.$addr_3_2.'</div>';
$user_form_addr_4_2 = '<div class="form-group">'.$addr_4_2.'</div>';

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
	</script>

	<style>
		.tglTargetB .form_el{display:none;}
		.form_submit{max-width:180px; width:100%;}
		
		.zip_area span{margin-right:10px;float:left;}
		
		.input_text.keep  {background:#E4E4E4;}
		.input_text_f.keep{background:#E4E4E4;}
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
			
			<div id="left2" class="col-xs-12 col-sm-12 col-md-8 col-lg-10">
				<div class="inner">
						
					<div>
						<?php echo $my_nav;?>
					</div>
						
					<div class="area1">
						<form name="AddressForm" action="<?php echo $form_action_to;?>" method="POST" class="form-horizontal">
							<?php echo $input_auto;?>
							
							<div class="form_group form_area">
								<h3>お住まいについて教えてください</h3>
								<ul class="form_table">
									<li>
										<div class="form_el row">
											<h4>郵便番号<?php echo $must;?></h4>
											<?php echo $user_form_zip; ?>
										</div>
									</li>
									<li <?php echo $target_b;?>>
										<div class="form_el">
											<h4>都道府県<?php echo $must;?></h4>
											<?php echo $user_form_addr_1; ?>
										</div>
									</li>
									<li <?php echo $target_b;?>>
										<div class="form_el">
											<h4>市区<?php echo $must;?></h4>
											<?php echo $user_form_addr_2; ?>
										</div>
									</li>
									<li <?php echo $target_b;?>>
										<div class="form_el">
											<h4>町村 番地<?php echo $must;?></h4>
											<?php echo $user_form_addr_3; ?>
										</div>
									</li>
									<li <?php echo $target_b;?>>
										<div class="form_el">
											<h4>建物名</h4>
											<?php echo $user_form_addr_4; ?>
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
	</div>
	
	<div id="footer">
		<?php require('inc_user_footer.php'); ?>
	</div>
</div>

<script src="../js/MyHelper.js" charset="UTF-8"></script>
<script>
	
	var FlagB = false;
	
	/*----- 住所登録 -----*/
	$('#iOnToggleB').on('click',function(){
	
		//-----------値を取得-----------
		//▼郵便番号
		var Za = $('#Za').val();
		var Zb = $('#Zb').val();
		
		var zipCode = "";
		zipCode = Za+Zb;
		
		if(zipCode != ""){
		
			//-----------データを登録-----------
			$.ajax({
				url: 'zip_post.php',
				type:'POST',
				dataType: 'json',
				data: {
					top : "ziproma",
					zip : zipCode
				}
			})
			
			//▼成功したとき
			.done(function (response) {
				var res = response.result;
				
				//▼結果処理
				if(res != 'ok'){
					alert("該当する住所がありません");
					
				}else{
					
					//▼値を格納
					var Char = response.ZipData.Char;
					var Kana = response.ZipData.Kana;
					var Roma = response.ZipData.Roma;
					
					if(Char[0] == ""){
						alert("該当する住所がありません");
						
					}else{
						
						//▼漢字
						$('#Apref').val(Char[0]);
						$('#Acity').val(Char[1]);
						$('#Aarea').val(Char[2]);
						
						alert("自動入力しました");
					}
				}
				
				//▼入力項目を開く
				if(!FlagB){
					$('.tglTargetB .form_el').slideToggle(800);
					FlagB = true;
				}
				
				//▼ボタンの開放
				$('#Act').prop('disabled',jCheckFormVal());
			})

			//▼失敗したとき
			.fail(function (data, textStatus, errorThrown) {
				alert("データの登録に失敗しました");
			});
		
		}else{
		
			alert("郵便番号を入力してください");
		}
		
	});
	
	
	//▼住所のコピー
	$('#Aarea').on('change',function(){
		var aa = $('#Aarea').val();
		$('#AareaR').val(aa);
	});
</script>

<script>
	function jCheckFormVal(){
	
		var FA = 0;
		
		/*----- 入力確認 -----*/
		var AA = jIsValue('Za');			//郵便番号A
		var AB = jIsValue('Zb');			//郵便番号B
		//var AC = jIsValue('cCode');		//国コード
		var AC = 1;							//国コード
		var AD = jIsValue('Apref');			//都道府県漢字
		var AE = jIsValue('Acity');			//市区町村漢字
		var AF = jIsValue('Aarea');			//番地漢字
		//var AG = jIsValue('AprefR');		//都道府県ローマ字
		//var AH = jIsValue('AcityR');		//市区町村ローマ字
		//var AI = jIsValue('AareaR');		//番地ローマ字
		var AG = 1;							//都道府県カナ字
		var AH = 1;							//市区町村カナ字
		var AI = 1;							//番地カナ字

		FA = AA * AB * AC * AD * AE * AF * AG * AH * AI;
		
		if(FA > 0){return false;} else {return true;}
	}
	
	/*----- ユーザーアクション -----*/
	$('#AstrtR').on('change',function (){
		var a = $('#AstrtR').val();
		t = zChangeChr0(a)
		$('#AstrtR').val(t);
	});
	
	
	$('form[name="AddressForm"]').on('change keyup',function(){$('#Act').prop('disabled',jCheckFormVal());});
</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
