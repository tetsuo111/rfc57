<?php
use Carbon\Carbon;

require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id = $_COOKIE['user_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}

//フィルタリングのための区分の取得
$query = tep_db_query("SELECT * FROM mem00000 INNER JOIN typ01004 on typ01004.id = mem00000.bctype WHERE memberid='$user_id';");

$a = tep_db_fetch_array($query);
$bctype = $a['bctype'];

$today = date('Y-m-d',time());

/*----- リスト表示 -----*/
//▼初期設定
$query_str = "
	SELECT
		`a`.`a_event_id`       AS `id`,
		`a`.`a_event_name`        AS `name`,
		`a`.`a_event_owner`        AS `owner`,
		`a`.`a_event_area`        AS `area`,
		`a`.`a_event_place`        AS `place`,
		`a`.`a_event_number`        AS `number`,
		`a`.`a_event_url`        AS `url`,
		`a`.`a_event_explain`        AS `explain`,
		`a`.`a_event_date`        AS `date`,
		`a`.`a_event_time_open`        AS `open`,
		`a`.`a_event_time_start`        AS `start`,
		`a`.`a_event_time_end`        AS `end`
	FROM      `".TABLE_A_EVENT."`     `a` 
	INNER JOIN tag00000 ON tag00000.article_id = `a`.`a_event_id`
	WHERE `a`.`state` = '1' AND article_type=3 AND bctype='".$bctype."'
	AND a_event_date_send <= '$today' and a_event_date_close >= '$today'
	ORDER BY `a`.`a_event_id`
";

$query =  tep_db_query($query_str);


//▼データ取得


//▼
	$list_in = '';
	
	while($a = tep_db_fetch_array($query)){
		$list_in .= '<div class="table-responsive"><table class="table table-bordered">';
		$list_in.= '<tr style="background-color:#c2d8a9 "><td colspan="2" width="66%"><strong>'.$a['name'].'</strong></td><td>主催：'.$a['owner'].'</td></tr>';
		$list_in.= '<tr><td width="33%">エリア：'.$a['area'].'</td><td>会場：'.$a['place'].'</td><td>定員：'.$a['number'].'名</td></tr>';

		$list_in.= '<tr><td>開催日：'.Carbon::parse($a['date'])->format('Y年m月d日').'</td><td>開場：'.preg_replace('/:00$/','',$a['open']).'</td><td>開催時間：'.preg_replace('/:00$/','',$a['start']).'~'.preg_replace('/:00$/','',$a['end']).'</td></tr>';
//		$list_in.= '<tr><td colspan="3"><a href="#" id="explain_toggle">'.substr($a['explain'],0,10).'...</a><div id="explain" style="display:none">'.nl2br($a['explain']);
		$list_in.= '<tr><td colspan="3"><a href="#" class="explain_toggle" event_id="'.$a['id'].'">▼詳細▼</a><div id="explain'.$a['id'].'" style="display:none">'.nl2br($a['explain']);
		if($a['url']){
			$list_in .= '<br><br><a href="'.$a['url'].'">詳細はこちら</a>';
		}
		$list_in .= '</div></td></tr>';
		$list_in .= '</table></div>';
	}



	
//	$input_list.= '<h4>'.$dt[0]['tname'].'</h4>';
	$input_list.= '<dl class="qa_area">';
	$input_list.= $list_in;
	$input_list.= '</dl>';


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
								<h2>イベント情報</h2>
								<div class="spc20">
									<?php echo $input_list;?>
								</div>
								
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

	$('.explain_toggle').click(function(){
		$('#explain' + $(this).attr('event_id')).toggle();
	});

	</script>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
