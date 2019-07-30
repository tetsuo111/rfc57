<?php 
require('includes/application_top.php');

if($_COOKIE['user_id']){
	$user_id        = $_COOKIE['user_id'];
	$user_email     = $_COOKIE['user_email'];
	$head_user_name = $_COOKIE['user_name'].'様';
}else{
	//$head_user_name = 'ゲスト様';
	tep_redirect('../logout.php', '', 'SSL');
}

//フィルタリングのための区分の取得
$query = tep_db_query("SELECT * FROM mem00000 INNER JOIN typ01004 on typ01004.id = mem00000.bctype WHERE memberid='$user_id';");

$a = tep_db_fetch_array($query);
$bctype = $a['bctype'];

//▼とび先設定
$form_action_to = basename($_SERVER['PHP_SELF']);

//▼基準通貨
$cur     = zGetSysSetting('sys_base_currency');
$cur_uni = zGetSysSetting('sys_base_currency_unit');


/*-------- 全体設定 --------*/
//▼とび先設定
$form_action_to = basename($_SERVER['PHP_SELF']);
$cont_set       = '?doc_id='.$_GET['doc_id'];

//▼値取得
$doc_id     = $_POST['doc_id'];



/*-------- 全体設定 --------*/
//▼初期設定
$query =  tep_db_query("
	SELECT
		`a_doc_id`          AS `id`,
		`a_doc_file_name`   AS `name`,
		`a_doc_file_org`    AS `org`,
		`a_doc_instruction` AS `instruct`,
		DATE_FORMAT(`a_doc_date_from`,'%Y-%m-%d') AS `from`,
		DATE_FORMAT(`a_doc_date_to`  ,'%Y-%m-%d') AS `to`
	FROM  `".TABLE_A_DOC."`
	INNER JOIN tag00000 ON tag00000.article_id = ".TABLE_A_DOC.".a_doc_id
	WHERE `state` = '1' AND article_type=2 AND bctype='".$bctype."'
	AND  DATE_FORMAT(NOW(),'%Y-%m-%d') BETWEEN
		DATE_FORMAT(IFNULL(`a_doc_date_from`,NOW() - INTERVAL 1 DAY),'%Y-%m-%d')
		AND DATE_FORMAT(IFNULL(`a_doc_date_to`,NOW() + INTERVAL 2 YEAR),'%Y-%m-%d')
	ORDER BY `a_doc_id` ASC
");

//▼
while($a = tep_db_fetch_array($query)) {
	
	$operation = '<form action="" method="POST">';
	$operation.= '<input type="hidden" name="doc_id" value="'.$a['id'].'">';
	$operation.= '<input type="submit" name="act_dl" value="この資料をダウンロード" class="btn">';
	$operation.= '</form>';
	
	$list_in.= '<tr>';
	$list_in.= '<td>'.$a['org'].'</td>';
	$list_in.= '<td nowrap>'.$a['instruct'].'</td>';
	$list_in.= '<td>'.$a['from'].'</td>';
	$list_in.= '<td>'.(($a['to'])? $a['to']:'-').'</td>';
	$list_in.= '<td>'.$operation.'</td>';
	$list_in.= '</tr>';
	
	if($doc_id == $a['id']){
		$fname = $a['name'];
		$forg  = $a['org'];
	}
}


if($list_in){
	$doc_list = '';
	$doc_list = '<div class="table-responsive">';
 	$doc_list .= '<table class="list_table">';
	$doc_list.= '<tr><th>資料名</th><th>説明</th><th>掲載日</th><th>終了日</th><th>操作</th></tr>';
	$doc_list.= $list_in;
	$doc_list.= '</table>';
	$doc_list .= '</div>';
	
}else{
	$doc_list = '<p>ダウンロードできる資料はありません</p>';

}


echo '';


/*-------- ダウンロード処理 --------*/
if(($_POST['act_dl'])AND($doc_id)){
	
	//▼ダウンロード用ヘッダーの指定
	$fpath = '../'.DIR_WS_UPLOADS_DOCS.$fname;
	
	header('Content-Type: application/pdf');
	header('Content-Length: '.filesize($fpath));
	header('Content-disposition: attachment; filename="'.$forg.'"');

	//▼ダウンロードの実行
	readfile($fpath);
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type"         content="text/html; charset=<?php echo CHARSET; ?>">
	<meta http-equiv="Content-Style-Type"   content="text/css">
	<meta http-equiv="Content-Script-Type"  content="text/javascript">
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
	<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css">
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="../js/bootstrap/css/font-awesome.min.css" />
	<link rel="stylesheet" type="text/css" href="../css/my.css"       media="all">
	
	<script src="../js/jquery-3.2.1.min.js"            charset="UTF-8"></script>
	<script src="../js/bootstrap/js/bootstrap.min.js" charset="UTF-8"></script>
	<style>
		.list_table{width:100%;}
		.bl{color:#00F; font-weight:800;}
		.rd{color:#F00; font-weight:800;}
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
					
					<h2>Document</h2>
					<div>
						<?php echo $doc_list;?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div id="footer">
		<?php require('inc_user_footer.php');?>
	</div>
	
	<script src="../js/MyHelper.js" charset="UTF-8"></script>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
