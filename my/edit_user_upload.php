<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id = $_COOKIE['user_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}


//-----------------全体設定-----------------
//▼リンク先
$form_action_to = basename($_SERVER['PHP_SELF']);

//▼申し込み後
$form_action_next = 'index.php';

//▼必須属性
$must = '<span style="font-size:12pt; font-weight:400; color:#DD0000; vertical-align:super;">*</span>';

//▼使用不可
$disabled = 'disabled';

//▼Navigation
if($_COOKIE['bctype'] == '21'){
	//▼愛用者はTOPへ
	tep_redirect('index.php', '', 'SSL');
}
$my_nav = zGetMyNavigation($NavUserInfoJP,$form_action_to);


//-----------------証明書登録アップロード-----------------
//▼自動追加要素
$certif_user_id = '<input type="hidden" name="user_id" value="'.$user_id.'">';


//▼各証明書を設定
$input_certif_ident = '<form id="IdentForm">';
$input_certif_ident.= '<div class="form-group">';
$input_certif_ident.= '<input type="hidden" name="certif_type" value="a">';
$input_certif_ident.= $certif_user_id;
//$input_certif_ident.= '<label for="UpIdent">身分証明書'.$must.'</label>';
$input_certif_ident.= '<label for="UpIdent">登記簿謄本'.$must.'</label>';
$input_certif_ident.= '<input type="file" id="UpIdent" name="upfile" class="upfile" value="">';
$input_certif_ident.= '</div>';
$input_certif_ident.= '</form>';

$input_certif_addr = '<form id="AddrForm">';
$input_certif_addr.= '<div class="form-group">';
$input_certif_addr.= '<input type="hidden" name="certif_type" value="b">';
$input_certif_addr.= $certif_user_id;
$input_certif_addr.= '<label for="UpAddr">店舗外観画像'.$must.'</label>';
$input_certif_addr.= '<input type="file"  id="UpAddr" name="upfile"  class="upfile" value="">';
$input_certif_addr.= '</div>';
$input_certif_addr.= '</form>';

$input_certif_photo = '<form id="PhotoForm">';
$input_certif_photo.= '<div class="form-group">';
$input_certif_photo.= '<input type="hidden" name="certif_type" value="c">';
$input_certif_photo.= $certif_user_id;
$input_certif_photo.= '<label for="UpPhoto">店舗内観画像'.$must.'</label>';
$input_certif_photo.= '<input type="file" id="UpPhoto" name="upfile" class="upfile" value="">';
$input_certif_photo.= '<div class="form-group">';
$input_certif_photo.= '</form>';


//▼サンプル表示
$nig_append = '<p class="button_ano" onClick="zShowReference();">必要な証明書をみる</p>';



//▼登録済み証明書情報
$user_certif_query = tep_db_query("
	SELECT 
		`user_certification_type`       AS `type`,
		`user_certification_file_org`   AS `f_org`,
		`user_certification_file_name` AS `f_name`,
		`user_certification_condition`  AS `condition`,
		DATE_FORMAT(`user_certification_date_application`,'%Y-%m-%d') AS `appli`
	FROM `".TABLE_USER_CERTIFICATION."` 
	WHERE `state`   = '1' 
	AND   `memberid` = '".tep_db_input($user_id)."' 
	ORDER BY `user_certification_type`,`user_certification_date_application` ASC
");

while($certif = tep_db_fetch_array($user_certif_query)){

	//▼表示設定
	//$certif_list_in.= '<tr><td>'.$certif_list[$certif['type']].'</td><td>'.$certif['f_org'].'</td><td>'.$certif['appli'].'</td><td>'.$cetif_condition[$certif['condition']].'</td></tr>';
	
	$thumnail  = '<img src="../uploads/identification/6000_b_1517493830.jpg">';
	
	//個人
	//$info      = '<p>'.$certif_list[$certif['type']].'</p><p>'.$certif['appli'].'</p>';
	//法人
	$info      = '<p>'.$certif_corporate[$certif['type']].'</p><p>'.$certif['appli'].'</p>';
	
	$condition = ($cetif_condition[$certif['condition']])? $cetif_condition[$certif['condition']]:'-';
	
	//$certif_list_in.= '<tr><td class="thumnail">'.$thumnail.'</td><td>'.$info.'</td><td>'.$condition.'</td></tr>';
	$certif_list_in.= '<tr><td class="thumnail">'.$thumnail.'</td><td>'.$info.'</td></tr>';
}

$certif_list = '<table>';
$certif_list.= $certif_list_in;
$certif_list.= '</table>';



//-----------------表示フォーム-----------------
//▼申込ボタン
$input_button = '<button id="ActButton" class="input_button" name="act_send" '.$disabled.' onClick="formSend(\'ApplicForm\',\'wc\');">上の内容で登録する</button>';
$input_button.= '<button class="input_button" name="act_clear" OnClick="location.reload();">クリア</button>';
$input_button.= $data5_skip;


//▼自動入力要素
$input_auto = '<input type="hidden" name="act" value="process">';
$input_auto.= '<input type="hidden" id="UOwn" name="user_own" value="'.$user_walle_card['own'].'">';
$input_auto.= '<input type="hidden" name="user_id" value="'.$user_id.'">';

//▼身分証サンプル
$certif_append = '<button type="button" class="btn btn-info" style="width:100%;" id="ShowReference">必要な証明書の説明をみる</button>';

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
	
	<script type="text/javascript">
		function zShowReference(){
			window.open('../<?php echo DIR_WS_UPLOADS;?>/manu/certif_ref.pdf','subwin','width=800,height=600,scrollbars=yes');
			return false;
		}
	</script>
	<style>
		.form_area .form_el input{
			cursor:pointer; background:#F4F4F4;
			padding:5px; border-radius:5px;
		}
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
					
					<div class="content_outer">
						<div class ="form_area">
							<h3>証明書アップロード</h3>
							<ul class="form_table">
								<li>
									<div class="form_el">
										<?php echo $input_certif_ident;?>
									</div>
								</li>
								<li>
									<div class="form_el">
										<?php echo $input_certif_addr;?>
									</div>
								</li>
								<li>
									<div class="form_el">
										<?php echo $input_certif_photo;?>
									</div>
								</li>
							</ul>
						</div>
					</div>
					<div class="append_area">
						<?php echo $certif_append;?>
						<div id="CertRef" class="ref_area">
							<img src="../<?php echo DIR_WS_UPLOADS;?>/manu/certif_ref.jpg">
						</div>
					</div>
					<div class="spc20">
						<div class="certif_up_list">
						<h4>提出済写真一覧</h4>
						<?php echo $certif_list;?>
						</div>
					</div>

					<div style="clear:both;"></div>
				</div>
				<script>
					/*---------------- initial setting ----------------*/
					var maxM = 5;							//Max size
					var UpLimit = 1024 * 1024 * maxM;		//upload volume limit
					
					
					//----------------関数定義----------------
					//▼フォームのポスト送信
					function zSendPost(formData,zUrl){
					
						//▼POSTでアップロード
						$.ajax({
							url  : zUrl,
							type : "POST",
							contentType : false,
							processData : false,
							dataType    : "text",
							data : formData
						})
						.done(function(response){
							alert(response);
							location.reload();
						})
						.fail(function(jqXHR, textStatus, errorThrown){
							alert("ファイルのアップロードに失敗しました");
						});
					
					}
					
					
					//▼フォーム内容を登録
					function formSend(Form){
						
						//▼フォームデータを取得
						var formData = new FormData($('#'+Form).get(0));
						
						//▼ファイルアップロード
						zSendPost(formData,"xml_file_up.php");
					}
					
					
					//▼アップロードファイルのチェック
					function zUpFileCheck(FF){
							
						var res = false;
						
						if(!((FF.type == "image/jpeg")||(FF.type == "image/pjpeg"))){
							res = "登録できる画像はjpg・jpeg形式のみです";
							
						}else if(FF.size > UpLimit){
							res = "登録できるファイルは"+maxM+"M以下です";
						}
						
						return res;
					}
					
					
					/*----- user action -----*/
					//▼身分証
					$('#UpIdent').on('change',function(){
					
						var Fi = $(this).val();
						
						if(Fi != "" ){
							var f = $('#UpIdent').prop('files')[0];	//選択されたファイル要素を取得
							var UpCh = zUpFileCheck(f);					//ファイルタイプチェック
							
							//▼アップロード実行
							if(UpCh == false){
								formSend('IdentForm');
								
							}else{
								alert(UpCh);
							}
						}
						
					});
					
					
					//▼住所証明書
					$('#UpAddr').on('change',function(){
					
						var Fi = $(this).val();
						
						if(Fi != "" ){
							var f = $('#UpAddr').prop('files')[0];		//選択されたファイル要素を取得
							var UpCh = zUpFileCheck(f);					//ファイルタイプチェック
							
							//▼アップロード実行
							if(UpCh == false){
								formSend('AddrForm');
							}else{
								alert(UpCh);
							}
						}
					
					});
					
					
					//▼身分証を持っている写真
					$('#UpPhoto').on('change',function(){
					
						var Fi = $(this).val();
						
						if(Fi != "" ){
							var f = $('#UpPhoto').prop('files')[0];	//選択されたファイル要素を取得
							var UpCh = zUpFileCheck(f);					//ファイルタイプチェック
							
							//▼アップロード実行
							if(UpCh == false){
								formSend('PhotoForm');
							}else{
								alert(UpCh);
							}
						}
					
					});
					
					/*----- ユーザーアクション -----*/
					$('#ShowReference').on('click',function(){
						$('#CertRef').slideToggle(1000);
					});
				</script>
				
			</div>
		</div>
	</div>
	
	<div id="footer">
		<?php require('inc_user_footer.php'); ?>
	</div>
</div>
<script src="../js/MyHelper.js" charset="UTF-8"></script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
