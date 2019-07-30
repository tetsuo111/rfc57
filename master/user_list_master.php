<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


//▼とび先設定
$form_action_to = basename($_SERVER['PHP_SELF']);
$form_use_proxy = 'user_proxy_master.php';


//▼代理ログイン削除
if($_COOKIE['memberid'] || $_COOKIE['user_id']){
	//▼ユーザーログイン情報削除
	tep_cookie_del('memberid');
	tep_cookie_del('user_id');
	tep_cookie_del('user_name');
	tep_cookie_del('user_email');
	tep_cookie_del('position_id');
	tep_cookie_del('bctype');
}

if($_POST['act']=='del_user'){
    $memberid = $_POST['memberid'];
    $query = tep_db_query("DELETE FROM mem00000 WHERE memberid=".$memberid);
    $query = tep_db_query("DELETE FROM mem00001 WHERE memberid=".$memberid);
    $query = tep_db_query("DELETE FROM mem00002 WHERE memberid=".$memberid);
    $query = tep_db_query("DELETE FROM `position` WHERE memberid=".$memberid);
    $query = tep_db_query("DELETE FROM `user_info` WHERE memberid=".$memberid);
    $query = tep_db_query("DELETE FROM `user_o_cart` WHERE user_id=".$memberid);
    $query = tep_db_query("DELETE FROM `user_o_charge` WHERE user_id=".$memberid);
    $query = tep_db_query("DELETE FROM `user_identification` WHERE memberid=".$memberid);
    $query = tep_db_query("DELETE FROM `user_certification` WHERE memberid=".$memberid);
    $query = tep_db_query("DELETE FROM `user_o_detail` WHERE user_id=".$memberid);
    $query = tep_db_query("DELETE FROM `user_o_shipping` WHERE user_id=".$memberid);
    $query = tep_db_query("DELETE FROM `user_o_order` WHERE user_id=".$memberid);
    $query = tep_db_query("DELETE FROM `user_address_certification` WHERE memberid=".$memberid);
    $query = tep_db_query("DELETE FROM `user_wc_status` WHERE memberid=".$memberid);

}


//▼ランクリスト
$query_r = tep_db_query("
	SELECT
		`m_rank_id`     AS `id`,
		`m_rank_name`   AS `name`,
		`m_rank_bctype` AS `bc`
	FROM  `".TABLE_M_RANK."`
	WHERE `state` = '1'
	ORDER BY `m_rank_order`
");

while($r = tep_db_fetch_array($query_r)){
	$rank_ar[$r['bc']] = $r['name'];
}


//▼顧客リスト
$pos_query = tep_db_query("
	SELECT 
		`p`.`position_id`,
		`ui`.`name1`,
		`ui`.`name2`
	FROM      `".TABLE_POSITION."`  AS `p`
	LEFT JOIN `".TABLE_MEM00000."`  AS `ui`  ON  `ui`.`memberid` = `p`.`memberid`
	WHERE `p`.`state` = '1'
");

while($b = tep_db_fetch_array($pos_query)){
	
	if(!$b['name1'] && !$b['name2']){
		$bn = '';
	}else{
		$bn = ($b['name2'])? $b['name2'] : $b['name1'];
	}
	
	$u_list[$b['position_id']] = $bn;
}

//▼情報取得
$user_query =  tep_db_query("
	SELECT 
		`u`.`memberid`,
		`u`.`name1`                                     AS `name`,
		`u`.`email`,
		`u`.`bctype`,
		DATE_FORMAT(`u`.`inputdate`,'%Y-%m-%d')         AS `d_done`,
		`ws`.`user_wc_status_info`                      AS `info`,
		`ws`.`user_wc_status_info_address`              AS `addr`,
		`ws`.`user_wc_status_identification`            AS `ident`,
		`ws`.`user_wc_status_address_certification`     AS `ad_certif`,
		`ws`.`user_wc_status_certification`             AS `certir`,
		`ws`.`user_wc_status_buy`                       AS `buy`,
		`p`.`position_id`                               AS `position_id`,
		`p`.`position_inviter`                          AS `invit`,
		`p`.`position_my_invite_code`                   AS `iv_code`,
		`p`.`position_condition`                        AS `p_condition`
	FROM      `".TABLE_MEM00000."`       AS `u`
	LEFT JOIN `".TABLE_USER_WC_STATUS."` AS `ws`  ON  `ws`.`memberid` = `u`.`memberid`
	LEFT JOIN `".TABLE_POSITION."`       AS `p`   ON   `p`.`memberid` = `u`.`memberid`
	WHERE `ws`.`state` = '1'
	AND   `p`.`state` = '1'
	ORDER BY `u`.`memberid` DESC
");


$st_ar = array('info','addr','ident','ad_certif','certir','buy');

if(tep_db_num_rows($user_query)){
	
	while($a = tep_db_fetch_array($user_query)){
		
		$t_cl = array();
		$p_condition = ($a['p_condition'])? '<span class="ac">Active</span>': '<span class="inac">Inactive</span>';

		
		$memberid  = $a['memberid'];
		$user_name = $a['name'];
		
		//▼代理ログイン
		$st = 'style="font-size:10px; padding:2px 5px;"';
		$dairi = '<input type="button" value="代理でログイン" '.$st.' onClick="zUserProxy(\''.$memberid.'\',\''.$user_name.'\');">';

		
		//▼表示の設定
		
		foreach($st_ar AS $v){
			
			if($a[$v] == 'a'){
				$cl = 'ok';
				$tx = '承認済';
			}else if($a[$v] == 'u'){
				$cl = 'up';
				$tx = '更新済';
			}else if($a[$v] == 'n'){
				$cl = 'ng';
				$tx = '再提出';
			}else if($a[$v] == '1'){
				$cl = 'on';
				$tx = '提出済';
			}else{
				$cl = 'no';
				$tx = '未提出';
			}
			
			$t_cl[$v]['cl'] = $cl;
			$t_cl[$v]['tx'] = $tx;
		}
		
		//▼クラス設定
		$s0 = $st_ar[0];
		$s1 = $st_ar[1];
		$s2 = $st_ar[2];
		$s3 = $st_ar[3];
		$s4 = $st_ar[4];
		$s5 = $st_ar[5];
		
		$mid = $a['memberid'];
		$rank_in = zSelectListSet($rank_ar,$a['bctype'],'rank','-','mRank'.$mid,'','','class="mRank" id-data="'.$mid.'"');
		$rank_in.= '<button type="button" class="cRank" id="cRank'.$mid.'" onclick="lzChangeRank('.$mid.')" disabled>ランクを変更</button>';

		if(isset($_GET['change_inviter'])){
			//紹介者変更モード
			$p_invite    = zSelectListSet($u_list,$a['invit'],'inviter','不明','inviter'.$mid,'','','class="inviter" id-data="'.$mid.'"');
			$p_invite .= '<button type="button" class="cInviter" id="cInviter'.$mid.'" onclick="lzChangeInviter('.$mid.')" disabled>紹介者を変更</button>';
		}else{
			//通常モード
			$p_invite    = ($u_list[$a['invit']])? $u_list[$a['invit']] : '不明';
		}

		//▼表示フォーム
		$list_in.= '<tr>';
		$list_in.= '<td>'.$a['memberid'].'</td>';
		$list_in.= '<td>'.$a['iv_code'].'</td>';
		$list_in.= '<td>'.$a['name'].'</td>';
		$list_in.= '<td>'.$dairi.'</td>';
		//$list_in.= '<td>'.$a['position_id'].'</td>';
		$list_in.= '<td>'.$p_invite.'</td>';
		//$list_in.= '<td>'.$p_condition.'</td>';
		$list_in.= '<td>'.$rank_in.'</td>';
		$list_in.= '<td>'.$a['email'].'</td>';
		$list_in.= '<td>'.(($a['d_done'])? $a['d_done']:'-').'</td>';
		$list_in.= '<td class="'.$t_cl[$s0]['cl'].'">'.$t_cl[$s0]['tx'].'</td>';
		$list_in.= '<td class="'.$t_cl[$s1]['cl'].'">'.$t_cl[$s1]['tx'].'</td>';
		$list_in.= '<td class="'.$t_cl[$s2]['cl'].'">'.$t_cl[$s2]['tx'].'</td>';
		$list_in.= '<td class="'.$t_cl[$s3]['cl'].'">'.$t_cl[$s3]['tx'].'</td>';
		$list_in.= '<td class="'.$t_cl[$s4]['cl'].'">'.$t_cl[$s4]['tx'].'</td>';
		$list_in.= '<td class="'.$t_cl[$s5]['cl'].'">'.$t_cl[$s5]['tx'].'</td>';
        if($_GET['change_inviter']=='enable_delete') {
            $list_in.= '<td><form action="user_list_master.php?change_inviter=enable_delete" method="post"><input type="hidden" name="act" value="del_user"><input type="hidden" name="memberid" value="'.$a['memberid'].'"><input type="submit" name="submit" value="削除" onclick=\'return confirm("本当に削除しますか？");\' /></form></td>';
        }
		$list_in.= '</tr>';
	}

}

//▼表示リスト
$list_head = '<th>会員ID</th>';
$list_head.= '<th>紹介番号</th>';
$list_head.= '<th>顧客名</th>';
$list_head.= '<th>代理ログイン</th>';
//$list_head.= '<th>ポジション<br>番号</th>';
$list_head.= '<th>紹介者</th>';
//$list_head.= '<th>状況</th>';
$list_head.= '<th>ランク</th>';
$list_head.= '<th>メールアドレス</th>';
$list_head.= '<th>初回ログイン</th>';
$list_head.= '<th>個人<br>情報</th>';
$list_head.= '<th>住所<br>情報</th>';
//$list_head.= '<th>身分証<br>情報</th>';
//$list_head.= '<th>住所<br>証明書</th>';
//$list_head.= '<th>写真</th>';
$list_head.= '<th>登記簿謄本</th>';
$list_head.= '<th>店舗外観画像</th>';
$list_head.= '<th>店舗内観画像</th>';
$list_head.= '<th>初回<br>注文</th>';
if($_GET['change_inviter']=='enable_delete') {
    $list_head .= '<th>削除</th>';
}



$input_list = '<table class="input_list">'  ;
$input_list.= '<tr>'.$list_head.'</tr>';
$input_list.= $list_in;
$input_list.= '</table>' ;
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
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
	<style>
		.input_list{width:100%; font-size:11px;}
		.input_list th{line-height:110%;}
		
		.ok{background:#77F; color:#FFF;}
		.up{background:#FF0;}
		.ng{background:#FFA500;}
		.on{background:#98FB98;}
		.no{background:#F44; color:#FFF;}
		
		.ac  {color:#00F;font-weight:800;}
		.inac{color:#F00;font-weight:800;}
		
		.cRank,.cInviter{font-size:10px;text-align:center; padding:0 5px; margin-top:3px;}

		/*--- Pop ---*/
		#Pop{width:100%; height:100%; background:rgba(60,60,60,0.4); display:none; position:fixed; top:0; left:0; z-index:2;}
		#Pop #offDairi{position:absolute; top:0; bottom:0; left:0; right:0; margin:auto;height:50px; width:250px; color:#F00;font-weight:800;}
		
		#Pop.onPop{display:block;}
	</style>

</head>
<body id="body">
<div id="wrapper">
	<div id="Pop">
		<button type="button" id="offDairi">代理ログイン状態を解除する</button>
	</div>
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
				
					<div class="admin_menu">
						<?php require('inc_master_menu.php');?>
					</div>
					
					<h2>顧客一覧</h2>
					<div>
						<div>
							<?php echo $input_list;?>
						</div>
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
<script src="../js/MyHelper.js"></script>
<script>
	function zUserProxy(ID,Na){
		window.open().location.href='<?php echo $form_use_proxy;?>?memberid='+ID+'&zed=proxy';
		$('#Pop').toggleClass('onPop');
	}
	
	$('#offDairi').on("click", function(){
		location.reload();
	});
	
	function lzChangeRank(A){
		aa = $('#mRank'+A).val();
		
		Cat   = new jSendPostDataAj('xml_change_rank.php');
		sData = {top : 'crank',mid : A,bct : aa}
		
		Obj = Cat.sendPost(sData);
		Obj.done(function(res){
			if(res == 'ok'){
				alert('変更しました');
			}else if(res != 'ok'){
				alert('ランクの変更に失敗しました');
			}
			
		}).fail(function(){
			alert('ファイルにアクセスできません');
		});
	}
	
	$('.mRank').on('change',function(){
		aa = $(this).attr('id-data');
		$('#cRank'+aa).prop('disabled',false);
	});


	function lzChangeInviter(A){
		aa = $('#inviter'+A).val();

		Cat   = new jSendPostDataAj('xml_change_rank.php');
		sData = {top : 'cinviter',mid : A,bct : aa}

		Obj = Cat.sendPost(sData);
		Obj.done(function(res){
			if(res == 'ok'){
				alert('変更しました');
			}else if(res != 'ok'){
				alert('ランクの変更に失敗しました');
			}

		}).fail(function(){
			alert('ファイルにアクセスできません');
		});
	}

	$('.inviter').on('change',function(){
		aa = $(this).attr('id-data');
		$('#cInviter'+aa).prop('disabled',false);
	});


</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
