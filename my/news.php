<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id = $_COOKIE['user_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}

//フィルタリングのためのm_rank_idの取得
$query = tep_db_query("SELECT m_rank_id FROM mem00000 INNER JOIN m_rank on m_rank.m_rank_bctype = mem00000.bctype WHERE memberid='$user_id' and m_rank.state=1;");



$a = tep_db_fetch_array($query);
$m_rank_id = $a['m_rank_id'];

/*----- リスト表示 -----*/
//▼初期設定

//news00001とjoinして会員区分ごとの出し分け、配信日、終了日によるフィルタ
$today = date('Y-m-d',time());



$query_str = "
	SELECT
		`news00000`.`news00000_id` AS `id`,
		`a_notice_subject` AS `subject`,
		`a_notice_text`  AS `text`,
		`a_notice_image` AS `image`,
		`a_notice_new_flag` AS `new`,
		`a_notice_date_send` AS `publish`,
		`a_notice_date_close` AS `close`
	FROM  `news00000` INNER JOIN news00001 ON news00000.news00000_id = news00001.news00000_id
	WHERE `news00000`.`state` = '1' and `news00001`.`state` = '1' and `a_notice_condition` = 'a' and news00001.m_rank_id = $m_rank_id
	 and a_notice_date_send <= '$today' and a_notice_date_close >= '$today'   
	ORDER BY `a_notice_date_send` desc
";

//echo $query_str;
$query =  tep_db_query($query_str);


//▼表示設定
while($a = tep_db_fetch_array($query)) {


	$list_in .= '<dt class="el1"><div>'.($a['new']==1?'<img src="../images/new.gif">':'').'<a data-toggle="collapse" data-target="#news'.$a['id'].'" href="#news'.$a['id'].'">'.$a['subject'].'</a>&nbsp;&nbsp;-&nbsp;&nbsp;'.$a['publish'].'</div></dt>';
	$list_in .= '<dd class="el1" >'.($a['image']!=''?'<img style="width:100px" src="../uploads/images/'.$a['image'].'">':'').'<div id="news'.$a['id'].'" class="collapse">'.nl2br($a['text']).'</div></dd>';
}

$input_list = '<dl class="qa_area">';
$input_list .= $list_in;
$input_list .= '</dl>';


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
		
		.qa_area .el1{
			display: flex;
			display: -webkit-flex;
			align-items        :flex-start;
			-webkit-align-items:flex-start;
			justify-content        :flex-start;
			-webkit-justify-content:flex-start;
			margin-bottom:10px;
		}
		
		.qa_area dt{border-top:1px solid #E4E4E4;padding-top:20px;}
		.qa_area dd{padding-bottom:20px;}
		.qa_area .el1 div{margin-left:10px;}
		
		i.q:before{content : "Q";background-color : #ff9e9e;}
		i.a:before{content : "A";background-color : #ffce9e;}
		i.q:before,i.a:before{
			display : block;
			width   : 24px;
			height  : 24px;
			border-radius:5px;
			text-align:center;
			text-decoration:none;
			font-size:14px;
			font-weight:400;
			padding:2px 0;
			font-family: arial, sans-serif;
			color:#FFF;
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
				
				<div id="left2" class="col-xs-12 col-md-12 col-md-8 col-lg-10">
					<div class="inner">
						<div class="part">
						
							<div class="area1">
								<h2>新着ニュース</h2>
								<?php echo $input_list;?>
							</div>
							
						</div>
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
	</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
