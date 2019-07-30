<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission'])){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


/*----- リスト取得 -----*/
//▼ランクリスト
$rank_ar = zRankList();


/*----- 検索設定 -----*/
if((!empty($_POST['act_search']))OR(!empty($_POST['act_dl']))){

	//▼送信状況
	if(($s_send_r)OR($s_send_w)){
		
		if($s_send == '1'){
			//新規のみ
			$search_send = "AND `us`.`user_wc_status_anx_send` IS NULL";
		
		}else if($s_send == 'a'){
			//送信済
			$search_send = "AND `us`.`user_wc_status_anx_send` = 'a'";
			
		}else if($s_send == 'b'){
			//要再送
			$search_send = "AND `us`.`user_wc_status_anx_back` = 'a'";
		}
		
	}else{
		$search_send = '';
	}
	
	
	//▼期間
	$start_date = $_POST['start'];
	$end_date   = $_POST['end'];
	
	if((!empty($start_date))AND(!empty($end_date))){
		
		$input_user_term  = "AND DATE_FORMAT(`us`.`user_wc_status_date_approved_nig`,'%Y-%m-%d')  BETWEEN '".$start_date."' AND '".$end_date."'"; 
	
	}else if((empty($start_date))AND(empty($end_date))){
		$input_user_term  = '';
		
	}else{
		
		if(!empty($start_date)){
			$input_user_term  = "AND DATE_FORMAT(`us`.`user_wc_status_date_approved_nig` ,'%Y-%m-%d')  >= '".$start_date."'"; 
		}
		
		if(!empty($end_date)){
			$input_user_term  = "AND DATE_FORMAT(`us`.`user_wc_status_date_approved_nig` ,'%Y-%m-%d')  <= '".$end_date."'"; 
		}
	}
	
	//▼値の確保
	if($c_type == "type_r"){
		$start_date_r = $_POST['start'];
		$end_date_r   = $_POST['end'];
		
	}else if($c_type == "type_w"){
		$start_date_w = $_POST['start'];
		$end_date_w   = $_POST['end'];
	}
	
	
	//▼ステータス
	if($s_state){
		
		if($s_state == 'a'){
			//新規
			$search_state = "AND u.`user_permission` != 'x'";
			
		}else if($s_state == 'b'){
			//差替
			$search_state = "AND u.`user_permission`  = 'x'";
		}
		
	}else{
		$search_state = "";
	}
}


/*----- 顧客情報取得 -----*/
//▼情報取得
$user_query =  tep_db_query("
	SELECT 
		`u`.`user_id`,
		`u`.`fs_id`,
		`u`.`user_email`, 
		DATE_FORMAT(`u`.`user_date_done`,'%Y-%m-%d')    AS `d_done`,
		CONCAT(`ui`.`user_name`,'　',`ui`.`user_name2`) AS `name`,
		`ws`.`user_wc_status_info`                      AS `info`,
		`ws`.`user_wc_status_info_address`              AS `addr`,
		`ws`.`user_wc_status_identification`            AS `ident`,
		`ws`.`user_wc_status_address_certification`     AS `ad_certif`,
		`ws`.`user_wc_status_certification`             AS `certir`,
		`ws`.`user_wc_status_buy`                       AS `buy`,
		`p`.`position_id`                               AS `position_id`,
		`p`.`position_condition`                        AS `p_condition`
	FROM      `".TABLE_USER."`           AS `u`
	LEFT JOIN `".TABLE_USER_INFO."`      AS `ui`  ON  `ui`.`user_id` = `u`.`user_id`
	LEFT JOIN `".TABLE_USER_WC_STATUS."` AS `ws`  ON  `ws`.`user_id` = `u`.`user_id`
	LEFT JOIN `".TABLE_POSITION."`       AS `p`   ON   `p`.`user_id` = `u`.`user_id`
	WHERE `u`.`state` = '1'
	AND   `u`.`user_permission` = 'u'
	AND   `ui`.`state` = '1'
	AND   `ws`.`state` = '1'
	AND   `p`.`state` = '1'
	AND   `p`.`position_condition` = 'a'
	AND  `ws`.`user_wc_status_info`                  IN('1','a')
	AND  `ws`.`user_wc_status_info_address`          IN('1','a')
	AND  `ws`.`user_wc_status_identification`        IN('1','a')
	AND  `ws`.`user_wc_status_address_certification` IN('1','a')
	AND  `ws`.`user_wc_status_certification`         IN('1','a')
	AND  `ws`.`user_wc_status_buy`                   IN('1','a')
	ORDER BY `u`.`user_id` DESC
");

$st_ar = array('info','addr','ident','ad_certif','certir','buy');

while($a = tep_db_fetch_array($user_query)){
	
	$t_cl = '';
	$p_condition = ($a['p_condition'])? '<span class="ac">Active</span>': '<span class="inac">Inactive</span>';
	
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
	
	//▼表示フォーム
	$list_in.= '<tr>';
	$list_in.= '<td>'.$a['user_id'].'</td>';
	$list_in.= '<td>'.$a['name'].'</td>';
	$list_in.= '<td>'.$a['fs_id'].'</td>';
	$list_in.= '<td>'.$a['position_id'].'</td>';
	$list_in.= '<td>'.$p_condition.'</td>';
	$list_in.= '<td>'.(($a['rank_id'])? $rank_ar[$a['rank_id']]:'-').'</td>';
	$list_in.= '<td>'.$a['user_email'].'</td>';
	$list_in.= '<td>'.(($a['d_done'])? $a['d_done']:'-').'</td>';
	$list_in.= '<td class="'.$t_cl[$s0]['cl'].'">'.$t_cl[$s0]['tx'].'</td>';
	$list_in.= '<td class="'.$t_cl[$s1]['cl'].'">'.$t_cl[$s1]['tx'].'</td>';
	$list_in.= '<td class="'.$t_cl[$s2]['cl'].'">'.$t_cl[$s2]['tx'].'</td>';
	$list_in.= '<td class="'.$t_cl[$s3]['cl'].'">'.$t_cl[$s3]['tx'].'</td>';
	$list_in.= '<td class="'.$t_cl[$s4]['cl'].'">'.$t_cl[$s4]['tx'].'</td>';
	$list_in.= '<td class="'.$t_cl[$s5]['cl'].'">'.$t_cl[$s5]['tx'].'</td>';
	$list_in.= '</tr>';
}

//▼表示リスト
$list_head = '<th>顧客<br>番号</th>';
$list_head.= '<th>顧客名</th>';
$list_head.= '<th>FSID</th>';
$list_head.= '<th>ポジション<br>番号</th>';
$list_head.= '<th>状況</th>';
$list_head.= '<th>ランク</th>';
$list_head.= '<th>メールアドレス</th>';
$list_head.= '<th>初回ログイン</th>';
$list_head.= '<th>個人<br>情報</th>';
$list_head.= '<th>住所<br>情報</th>';
$list_head.= '<th>身分証<br>情報</th>';
$list_head.= '<th>住所<br>証明書</th>';
$list_head.= '<th>写真</th>';
$list_head.= '<th>初回<br>注文</th>';


$input_list = '<table class="input_list">'  ;
$input_list.= '<tr>'.$list_head.'</tr>';
$input_list.= $list_in;
$input_list.= '</table>' ;


/*----- CSVダウンロード -----*/
if(($_POST['act'] == 'process')AND(!empty($_POST['act_dl']))){

	/*-------------- csv項目 --------------*/
	//▼赤カード
	$head_r = array(
		"Name of File",						//個別ファイル名
		"Date of Registration ",			//申込日　
		"User Account Email Address",		//E-mail
		"User Last Name",					//姓（漢字）
		"User FIrst Name",					//名（漢字）
		"User First Name",					//名（ローマ字）
		"User Last Name",					//姓（ローマ字）
		"Postal Address - City",			//県（ローマ字）
		"Postal Address - Country ISO",		//国コード（JPN）
		"Postal Address - Line 1",			//区・市（ローマ字）
		"Postal Address - Line 2",			//以下（ローマ字）
		"Postal Address - Line 3",			//入力不要
		"Postal Address - Region",			//入力不要
		"Postal Address - ZIP code",		//郵便番号（ハイフンなし）
		"Date of Birth (YYYY-MM-DD)",		//誕生日（例:1980-01-12）
		"Mobile Country Code",				//国コード（日本の場合は+81）
		"Phone Number",						//電話番号（ハイフン無）
		"Gender",							//姓（男性=M,女性=F）
		"ID No",							//ID番号
		"Date of issue（DD-MM-YYYY）",		//証明書発行日（日-月-年）
		"Date of Expire（DD-MM-YYYY）",		//証明書有効期限（日-月-年）
		"Address Certificate",				//住所証明書
		"AC Date of issue（DD-MM-YYYY）",	//住所証明書発行日
		"≪NIG USE ONLY ≫VISA Card No",	//NIG用追加項目
		"≪NIG USE ONLY≫VISA Card PIN No",	//NIG用追加項目
		"Source of fund (Salary/Saving/Inheritance/Other)",		//NIG用追加項目
		"Please specify(if Other)",			//NIG用追加項目
		"Annual Income (0-400000/400001-800000/800001-1200000/over 1200000)",	//NIG用追加項目
		"Nature of Avtivity(Buy Crpypto for lawful personal use/Sell Crypto for lawful personal use/ Buy and Sell Crpypto for lawful personal use/Other)",	//NIG用追加項目
		"Please specify (if other)"			//NIG用追加項目
	);

	//▼登録用データ
	if(!empty($user_array)){
		
		foreach($user_array AS $row){
		
			//▼性別対応
			if(!empty($row['sex'])){
				$sex = ($row['sex'] == 'm')? 'M':'F';
			}
			
			//▼都道府県用
			$search_pref = array(' ','-to','-TO','-fu','-FU','-KEN','-ken');
			$replac_pref = array('-',''   ,''   ,''   ,''   ,''    ,''    );
			
			$pref = '';
			$pref = str_replace($search_pref,$replac_pref,$row['pref_roma']);
			
			
			//▼市区町村用
			$search_city = array(' ',',','-ku-','-KU-','-shi-','-SHI-');
			$replac_city = array('-','-','-ku@','-KU-@','-shi@','-SHI@','');
			
			//▼市区判定用
			$city = '';
			$city = str_replace($search_city,$replac_city,$row['city_roma']);
			
			//▼配列へ格納
			$city_ar = '';
			$city_ar = explode('@',$city);
			
			//▼表示処理
			$count_c = count($city_ar);
			
			//▼市区
			$line1 = '';
			for($i=0;$i<($count_c -1);$i++){
				$line1.= (empty($line1))? $city_ar[$i] : ','.$city_ar[$i] ;
			}
			
			//▼町名等
			$line2 = '';
			$line2 = $city_ar[$count_c - 1];
			
			//▼番地
			if(!empty($row['area_roma'])){$line2.= ','.$row['area_roma'];}
			
			//▼ビル名等
			if(!empty($row['strt_roma'])){$line2.= ','.$row['strt_roma'];}
			
			
			//▼CSVデータ登録用
			//$c_code
			//$c_code = $row['country_code'];
			$c_code = "JPN";
			
			//▼住所証明書タイプ
			$ac_type = (!empty($row['ac_type']))? $AddressCertifArrayCSV[$row['ac_type']] : '';
			
			$data[] = array(
				'',
				$row['appli'],
				$row['email'],
				$row['name'],
				$row['name2'],
				$row['kana2'],
				$row['kana'],
				$pref,
				$c_code,
				$line1,
				$line2,
				'',
				'',
				$row['zip_a'].$row['zip_b'],
				$row['borth'],
				'＋81',
				$row['tel'],
				$sex,
				$row['id_num'],
				$row['id_issue'],
				$row['id_expire'],
				$ac_type,
				$row['ac_issue'],
				'',
				'',
				'',
				'',
				'',
				'',
				''
			);
		}
	}

	//------------CSVダウンロード------------
	//▼登録用配列
	if($c_type == "type_r"){
		
		//▼CSVzipダウンロードオブジェクト
		$zobj = new DataCsvDLZip();
		
		//▼登録データ設定
		$data_head = $head_r;
		$fname     = "_Daily_FI.csv";
		
		$array[] = array("csv_header"=>$data_head,"csv_data"=>$data,"dl_filename"=>date('Ymd').$fname);
		
		//▼配列を格納
		$zobj->csv_array = $array;
		
		//▼ファイルを格納
		$zobj->root_fold_name = "photos";
		$zobj->fold_array = $certif;
		
		//▼作成＆ダウンロード
		$zobj->CsvDLZipRun();
	
	}

}



/*-------- 検索フォーム --------*/
//▼ボタン設定
$input_button = '<input type="submit" name="act_dl" value="表示している内容をダウンロード" disabled>';
$input_button.= '<span class="spc10_l"><a href="'.$form_action_to.'">クリア</a></span>';


//▼検索設定
$dl_form = '<form action="'.$form_action_to.'" method="POST">';
$dl_form.= '<input type="hidden" name="act" value="process">';
$dl_form.= '<input type="hidden" name="c_type" value="type_r">';
$dl_form.= '<div class="search_area">';
$dl_form.= '承認日：<input class="input_text" type="text" style="width:100px;" name="start" value="'.$start_date_r.'" id="date1" readonly="readonly">～';
$dl_form.=         '<input class="input_text" type="text" style="width:100px;" name="end"   value="'.$end_date_r.'"   id="date2" readonly="readonly">';
$dl_form.= zSelectListSet($ArrayStateMaster ,$_POST['s_state'],'s_state','▼ステータス','','');
$dl_form.= zSelectListSet($ArrayAnxSend     ,$s_send_r        ,'s_send' ,'▼状況の選択','','');
$dl_form.= '<span class="spc10_l"><input type="submit" name="act_search" value="この内容で検索" disabled></span>';
$dl_form.= '</div>';
$dl_form.= '<div class="spc20">';
$dl_form.= $input_button;
$dl_form.= '</div>';
$dl_form.= '</form>';


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
		.input_list{width:100%;}
		.input_list th{line-height:110%;}
		
		.ok{background:#77F; color:#FFF;}
		.up{background:#FF0;}
		.ng{background:#FFA500;}
		.on{background:#98FB98;}
		.no{background:#F44; color:#FFF;}
		
		.ac  {color:#00F;font-weight:800;}
		.inac{color:#F00;font-weight:800;}
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
				
					<div class="admin_menu">
						<?php require('inc_master_menu.php');?>
					</div>
					
					<h2>顧客一覧</h2>
					<div>
						<div>
							<?php echo $dl_form;?>
						</div>
						<div class="spc20">
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
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
