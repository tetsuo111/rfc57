<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id = $_COOKIE['user_id'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}

/*----- リスト表示 -----*/
//▼初期設定

$list_in = '';


//▼表示設定
$dst = [];
recursion($user_id,$dst,0);

foreach($dst as $mem){
    $list_in .= $mem.'<br>';
}


//$input_list = '<table class="table table-bordered">';
$input_list = 'あなた<br>';
$input_list .= ''.$list_in.'';
//$input_list .= '</table>';



function recursion($chain,&$dst,$level){
    $query =  tep_db_query("
	SELECT
		`memberid` AS `id`,
		`chain`,
		`name1` AS `name`
	FROM  `mem00000`
	WHERE `chain` = '".$chain."' ORDER BY `inputdate` desc");

    while($a = tep_db_fetch_array($query)){
//        $dst[] = str_repeat('<td></td>', $level).'<td>'.$a['name'].'</td>';
        $dst[] = str_repeat('　  ', $level+1).'L'.$a['name'];
        recursion($a['id'],$dst,$level+1);
    }

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
								<h2>ご紹介実績</h2>
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
