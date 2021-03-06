<?php
require('includes/application_top.php');


if($_COOKIE['user_id']){
	$user_id        = $_COOKIE['user_id'];
	$position_id    = $_COOKIE['position_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}

$form_action_to = 'order_edit.php';


//▼情報取得
$user_query =  tep_db_query("
	SELECT 
		`u`.`memberid`,
		`u`.`login_id`,
		`u`.`email`,
		`u`.`bctype`,
		IFNULL(`u`.`name1`,`u`.`name2`)                 AS `name`,
		DATE_FORMAT(`u`.`inputdate`,'%Y-%m-%d')         AS `d_done`,
		`p`.`position_id`                               AS `pid`,
		`p`.`position_inviter`                          AS `invit`,
		`p`.`position_my_invite_code`                   AS `my_code`,
		`p`.`position_condition`                        AS `p_condition`,
		`t`.`lv`
	FROM      `".TABLE_MEM00000."`   `u`
	LEFT JOIN `".TABLE_POSITION."`   `p` ON `p`.`memberid` = `u`.`memberid`
	LEFT JOIN `".TABLE_MEM01000."`   `t` ON `t`.`memberid` = `u`.`memberid`
	WHERE `p`.`state` = '1'
	AND   `u`.`memberid` = '".tep_db_input($user_id)."'
	AND   ((`t`.`memberid` = '".tep_db_input($user_id)."')OR(`t`.`memberid` IS NULL))
");

if($b = tep_db_fetch_array($user_query)){
	
	$fs_id = $b['login_id'];
	$p_id  = $b['pid'];
	$psiv.= '<li>'.$b['my_code'].'<span class="spc10_l">(ポジション番号：'.$b['pid'].')</span></li>';
	$my_code = $b['my_code'];
	$rank    = $b['lv'];
	$mail    = $b['email'];
	$bctype  = $b['bctype'];
	/*
	$mail
	*/
}


//▼現在ステータス
$m_rank = zRankList();

$query_ps = tep_db_query("
	SELECT
		`m_rank_name`
	FROM `".TABLE_M_RANK."`
	WHERE `state` = '1'
	AND   `m_rank_bctype` = '".tep_db_input($bctype)."'
");

if($c = tep_db_fetch_array($query_ps)){
	$rank  = $c['m_rank_name'];
	$point = '-';
}else{
	$rank  = '-';
	$point = '-';
}


//----- 注文情報 -----//
$base_cur = zGetSysSetting('sys_base_currency_unit');

//▼注文
$query_order =  tep_db_query("
	SELECT
		`user_order_id`         AS `id`,
		`user_order_sort`       AS `sort`,
		`user_order_condition`  AS `condition`,
		DATE_FORMAT(`user_order_date_limit`,'%Y-%m-%d')       AS `limit`,
		DATE_FORMAT(`user_order_date_application`,'%Y-%m-%d') AS `appli`
	FROM      `".TABLE_USER_ORDER."`
	WHERE `state`       = '1'
	AND   `user_id` = '".tep_db_input($user_id)."'
	ORDER BY `user_order_id` ASC
");

while($od = tep_db_fetch_array($query_order)){
	$od_ar[$od['id']]['order'] = $od;
	$for_get_od.= (($for_get_od)? ",'":"'").$od['id']."'";
}

//▼請求
$query_charge =  tep_db_query("
	SELECT
		`oc`.`user_order_id`           AS `id`,
		`oc`.`user_o_charge_c_amount`  AS `c_amount`,
		`oc`.`user_o_charge_condition` AS `condition`,
		IFNULL(`mc`.`m_currency_name`,'".$base_cur."') AS `c_name`
	FROM `".TABLE_USER_O_CHARGE."` AS `oc`
	LEFT JOIN `".TABLE_M_CURRENCY."` AS `mc` ON `mc`.`m_currency_id` = `oc`.`currency_id`
	WHERE `oc`.`state` = '1'
	AND   `oc`.`position_id` = '".tep_db_input($position_id)."'
	AND   `oc`.`user_order_id` IN (".$for_get_od.")
	AND   ((`mc`.`state` = '1')OR(`mc`.`state` IS NULL))
	ORDER BY `oc`.`currency_id`
");

while($oc = tep_db_fetch_array($query_charge)){
	$od_ar[$oc['id']]['charge'][] = $oc;
}


//表示の成形
foreach($od_ar AS $k => $ddd){
	
	$od = $ddd['order'];
	$ch = $ddd['charge'];
	
	$condition = ($od['condition'] == 'a')? '確認済':'<span class="alert">入金待</span>';
	
	//▼編集確認
	$edit_err = false;
	
	if($od['condition'] == 'a'){$edit_err = true;}
	
	
	$pay_a = '';
	foreach($ch AS $ccc){
		//▼編集許可
		if($ccc['condition'] == 'a'){$edit_err = true;}
		
		$pay_a.= '<p style="margin:0;">'.$ccc['c_amount'].'<span class="spc10_l">'.$ccc['c_name'].'</span></p>';
	}
	
	if($edit_err == false){
		$edit = '<form action="'.$form_action_to.'" method="POST">';
		$edit.= '<input type="hidden" name="uorder_id" value="'.tep_db_input($od['id']).'">';
		$edit.= '<input type="submit" class="btn" value="編集・削除">';
		$edit.= '</form>';
	}else{
		$edit = '-';
	}
	
	$order_in.= '<tr><td>'.$od['id'].'</td><td>'.$od['appli'].'</td><td>'.$pay_a.'</td><td>'.$condition.'</td><td>'.$edit.'</td></tr>';

}

$oder_list = '<table class="list_table">';
$oder_list.= '<tr><th>番号</th><th>注文日</th><th>金額</th><th>状況</th><th>操作</th></tr>';
$oder_list.= $order_in;
$oder_list.= '</table>';



//----- ビジネス会員用 -----//
//▼紹介用コード
$linkurl = HTTP_SERVER.'new_regisitration.php?fivcd='.$my_code;

//ライン
//▼暫定で変更　cis-home2.sakura.ne.jp
if(mb_strpos($_SERVER["SERVER_NAME"], 'rfcsystem.com') === FALSE){
	
	//開発用
	$line_in = '<span class="alert">本番はここにラインボタン</span>';
}else{
	//本番用
	$line_in = '<div class="line-it-button" style="display: none;" data-lang="ja" data-type="share-a" data-url="'.$linkurl.'"></div>';
}


//▼紹介情報
$invite_in.= '<div class="area1">';
$invite_in.= '<h2>あなたの会員番号</h2>';
$invite_in.= '<p class="u_info">'.$fs_id.'</p>';
$invite_in.= '</div>';

$invite_in.= '<div class="area1">';
$invite_in.= '<h2>コーディネーターコード</h2>';
$invite_in.= '<ul class="u_info">'.$psiv.'</ul>';
$invite_in.= '<div class="spc20">';
$invite_in.= '<div id="qrcode"></div>';
$invite_in.= '</div>';
$invite_in.= '</div>';

$invite_in.= '<div class="area1">';
$invite_in.= '<div class="spc20">';
$invite_in.= '<input type="text"    class="u_info" style="width:100%;" id="urlData" value="'.$linkurl.'" readonly><br>';
$invite_in.= '<button type="button" class="btn btn-success onClip spc10" data-clipboard-target="#urlData">この紹介用URLをコピー</button>';
$invite_in.= '</div>';
$invite_in.= '<div class="spc20">';
$invite_in.= $line_in;
$invite_in.= '<script src="https://d.line-scdn.net/r/web/social-plugin/js/thirdparty/loader.min.js" async="async" defer="defer"></script>';
$invite_in.= '</div>';
$invite_in.= '</div>';



//▼アカウント情報
$home_in = '<div class="area1">';
$home_in.= '<h2>あなたの現在ランク</h2>';
$home_in.= '<p class="u_info">'.$rank.'</p>';
$home_in.= '</div>';

$home_in.= '<div class="area1">';
$home_in.= '<h2>現在の保有ポイント</h2>';
$home_in.= '<p class="u_info">'.$point.'</p>';
$home_in.= '</div>';

$home_in0 = '<div class="area1">';
$home_in0.= '<h2>登録メールアドレス</h2>';
$home_in0.= '<p class="u_info">'.$mail.'</p>';
$home_in0.= '</div>';

$home_in0.= '<div class="area1">';
$home_in0.= '<h2>ご注文履歴</h2>';
$home_in0.= $oder_list;
$home_in0.= '</div>';



//----- 表示設定 -----//
//▼紹介情報
if($bctype == '21'){
	
	//▼表示タイトル
	$top_nav = '<h2>アカウント情報</h2>';
	
	//▼表示内容
	//アカウント情報
	$cont_index = '<div>';
	$cont_index.= $home_in0;
	$cont_index.= '</div>';
	
}else{
	
	//▼表示切替Navi
	$nc = 6;
	$icon1 = '<i class="fa fa-handshake-o" aria-hidden="true"></i>';
	$icon2 = '<i class="fa fa-home" aria-hidden="true"></i>';
	$top_nav_in = '<li class="col-xs-'.$nc.' col-md-'.$nc.' col-md-'.$nc.' col-lg-'.$nc.' activate tpnav" id="Invite">'.$icon1.'<br><span>紹介情報</span></li>';
	$top_nav_in.= '<li class="col-xs-'.$nc.' col-md-'.$nc.' col-md-'.$nc.' col-lg-'.$nc.' tpnav" id="Home">'.$icon2.'<br><span>アカウント状況</span></li>';

	$top_nav = '<ul class="row my_nav">';
	$top_nav.= $top_nav_in;
	$top_nav.= '</ul>';
	
	
	//▼表示内容
	//紹介情報
	$cont_index = '<div id="tbInvite" class="tops onShow">';
	$cont_index.= $invite_in;
	$cont_index.= '</div>';
	
	//アカウント情報
	$cont_index.= '<div id="tbHome" class="tops">';
	$cont_index.= $home_in;
	$cont_index.= $home_in0;
	$cont_index.= '</div>';
	
}


//▼登録注意
$ust = zUserStatusCheck($user_id);

if(!zCheckUserEmail($user_id)){
	$grace_alert.= '<div class="gr_alert"><p>メールアドレスが重複しています。メールアドレスを変更してください。</p></div>';
}

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
	<script src="../js/qr/jquery.qrcode.min.js"></script>
	<script src="../js/clip/clipboard.min.js"></script>
	<style>
		.u_info{border:1px solid #E4E4E4; border-radius:5px; padding:7px 10px; font-size:16px; font-weight:800; color:#099;}
		.list_table{width:100%; max-width:600px;}
		.area1 h2{font-size:18px;}
		
		.gr_alert{width:100%; border-radius:10px; border:3px solid #F00; background:#FFC; margin:20px 0;
			display: flex;
			justify-content: center;
			align-items: center;
		}
		.gr_alert p{text-align:center;padding:20px 5px;margin:0;}
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
						<div class="part">
							<?php echo $top_nav;?>
							<?php echo $grace_alert;?>
							
							<div style="overflow:hidden;">
								<?php echo $cont_index;?>
							</div>
						</div>
						<?php echo $red_form;?>
					</div>
				</div>
			</div>
		</div>
		<script src="../js/MyHelper.js" charset="UTF-8"></script>
		
		<div id="footer">
			<?php require('inc_user_footer.php');?>
		</div>
	</div>
	<script>
		$('#qrcode').qrcode({width: 196, height: 196, text:'<?php echo $linkurl;?>'});
		$('.onQR').on('click',function(){
			$('#Pop').toggleClass('isOpen');
		});
	</script>
	<script>
	$(function () {
		var clipboard = new Clipboard('.onClip');
		
		clipboard.on('success',function(e){
			e.clearSelection();
			alert('コピーしました');
		});
		clipboard.on('error', function(e) {
			alert('コピーに失敗しました');
		});
	});
	
	$('.tpnav').on('click',function(){
		if(!$(this).hasClass('activate')){
			$('.tpnav').toggleClass('activate');
			$('.tops').toggleClass('onShow');
		}
	});
	</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
