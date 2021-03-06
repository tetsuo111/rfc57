<?php 
require('includes/application_top.php');

if(($_COOKIE['master_id']) && ($_COOKIE['master_permission']) && (empty($_COOKIE['admin_id']))){
	$master_id = $_COOKIE['master_id'];
	$head_master_name = $_COOKIE['master_name'].'様';
}else{
	//$head_master_name = 'ゲスト様';
	tep_redirect('logout.php', '', 'SSL');
}


//▼住所を市区町村と番地に分割
function zz_change_city_addr($str){

	//▼検索用
	//$count_ar = array('0','1','2','3','4','5','6','7','8','9');
	$t_addr =  mb_convert_kana($str,'as');

	$res = false;
	
	for($p=0;$p < 10; $p++){
		
		//数字の出現位置を検出
		if(($t_pos = mb_strpos($t_addr,$p)) !== false){

			//最初に数字が出る個所を取得
			if($pos){
				if(($t_pos !== false)AND($t_pos < $pos)){
					$pos = $t_pos;
				}
			}else{
				$pos = $t_pos;
			}
		}
	}
	
	if($pos){
		//▼市区町村　＞開始位置 文字数
		$ap  = mb_substr($t_addr,0,$pos);
		
		//▼番地以降
		$bp1 = mb_substr($t_addr,$pos);
		
		//▼ビル名などがあるかを検出
		if(mb_strpos($bp1,'@') !== false){
			$tbp = explode('@',$bp1);
			$bp = $tbp[0];
			$cp = $tbp[1];
		}else{
			$bp = $bp1;
			$cp = '';
		}
		
		$res = array($ap,$bp,$cp);
	}
	
	return $res;
}

/*============================
　全体設定
============================*/
//飛び先設定
$form_action_to  = basename($_SERVER['PHP_SELF']);

$data_num = 16;

/*
	0	CISID		position
	1	CIS紹介者ID	position
	2	氏名		user_info
	3	ふりがな	user_info
	4	生年月日	user_info
	5	ＴＥＬ		user_info
	6	ＦＡＸ		user_info
	7	携帯番号	user_info
	8	E-mail		user_info
	9	郵便番号	user_address
	10	都道府県	user_address
	11	住所		user_address
	12	申請日		user_order
	13	ポジ数		user_order
	14	金額		user_order
	15	備考		cis_fd
*/


/*-------- リスト取得 --------*/
//▼登録ユーザーリスト
$query_fs = tep_db_query("
	SELECT
		`fs`.`fs_id`,
		`p`.`position_id` AS `p_id`
	FROM      `".TABLE_FS_SETTING."` AS `fs`
	LEFT JOIN `".TABLE_POSITION."`   AS `p` ON `p`.`fs_id` = `fs`.`fs_id`
	WHERE `fs`.`state` = '1'
	AND    `p`.`state` = '1'
	ORDER BY `fs`.`fs_setting_id` ASC
");

while($fs = tep_db_fetch_array($query_fs)){
	$list_fs_id[$fs['fs_id']] = $fs['p_id'];
}


/*-------- DB登録 --------*/
//▼設定
$cis_new = $_POST['s_cis_new'];

if($_POST['act'] == 'process'){
	
	//CSVデータ読込
	
	//▼エラーチェック　＞　キャンセル対応
	$err = false;
	
	//ロケールを設定する
	setlocale(LC_ALL, 'ja_JP.UTF-8');
	$file = $_FILES['user_csv']['tmp_name'];
	
	//ファイルの内容を取得する
	$data = file_get_contents($file);
	if($data == false){
		$err = true;
		$err_text = '<span class="input_alert">ファイルが読み込めません</span>';
	}

	if($err == false){

		//ファイルのコードをUTF-8に変換する
		$data = mb_convert_encoding($data, 'UTF-8', 'SJIS-win');

		//tempファイルを作成する
		$temp = tmpfile();

		//ファイル操作
		//エンコードした内容を添付ファイルに書き込む
		fwrite($temp, $data);
		rewind($temp);

		//ファイル内容を取得する
		$i_c    = 0;	//データエラーの数
		$i_d    = 0;	//フォーマットエラーの数
		$i_fget = 0;	//現在のデータ行数
		
		
		while (($d2 = fgetcsv($temp)) !== FALSE) {
			
			//▼最初の一行を無視する
			if($i_fget != 0){
				
				$err_c = false;
				
				//データの数を数える
				if(count($d2) != $data_num){
					
					$err_c    = true;
					$err_text = '<span class="input_alert">ファイルのフォーマットが正しくありません</span>';
					$i_d++;
				}else{
					
					/*
						0：CISID
						1：CIS紹介者ID
					*/
					/*----- エラーチェック -----*/
					//▼FSID確認
					if($cis_new == 'a'){
						
						//▼CIS番号新規発行
						if($f = cCreateNewCisNum()){
							
							//登録データ
							$cis_num_ar[$i_fget] = $f;
							$fs_id = cNewCisToFs($f);
							
						}else{
							$err_c      = true;
							$err_cis_tx = '<br>CISIDを発行できません';
						}
					
					}else{
					
						//▼既存のIDを確認する
						if($fs_id = cCisToFs($d2[0])){
							
							if($list_fs_id[$fs_id]){
								$err_c    = true;
								$err_cis_tx = '<br>すでに登録されているCISIDです';
							}else{
								$cis_num_ar[$i_fget] = $d2[0];
							}
							
						}else{
							$err_c      = true;
							$err_cis_tx = '<br>CISIDデータ不正です';
						}
						
					}
					
					
					/*----- DB登録 -----*/
					if($err_c == false){
						
						//▼登録用配列へ格納
						$data_ar[$i_fget]   = $d2;			//データ番号
						$cis_fs_ar[$i_fget] = $fs_id;		//FS番号
						$list_fs_id[$fs_id] = 'a';			//検索用データ
						
					}else{
						
						//▼エラー表示用
						$err_cis_ar[$i_fget] = $err_cis_tx;
						$err_ar[$i_fget]     = $d2;
						$i_c++;
					}
				}
			}
			
			$i_fget++;
		}
		
		
		/*----- 紹介者ID確認 -----*/
		//入力されているか
		//登録されているIDの中にあるか
		//登録しようとしているIDの中にあるか
		if(($i_c == 0)AND($i_d == 0)AND($i_fget >1)){
			
			foreach($data_ar AS $kd3 => $vd3){
				
				$err_inv = false;
				$inv = '';
				
				//▼紹介者IDの確認
				if($vd3[1]){
					
					//FSIDに変換
					if($inv = cCisToFs($vd3[1])){
						
						//該当するFSIDが存在する
						if($list_fs_id[$inv]){
							$inv_fs_ar[$kd3]  = $inv;	//紹介者FSID
							
						}else{
							$err_inv_ar[$kd3] = '<br>不正な紹介者IDです<br>'.$inv;
							$err_inv = true;
						}
						
					}else{
						
						//不正な文字列
						$err_inv_ar[$kd3] = '<br>不正な文字列が登録されています<br>'.$inv;
						$err_inv = true;
					}
					
				}else{
					
					//紹介者IDがない場合はすべて本部に付ける
					//$err_inv_ar[$kd3] = '<br>紹介者のCISIDがありません<br>';
					//$err_inv = true;
					$inv_fs_ar[$kd3][1] = 'gld99999999';
				}
				
				//▼不正な登録　＞エラー数を追加
				if($err_inv){
					$err_ar[$kd3] = $vd3;
					$i_c++;
				}
			}
		}
		
		
		/*----- DB登録 -----*/
		//エラーがなくデータがある場合
		if(($i_c == 0)AND($i_d == 0)AND($i_fget >1)){
			
			$err_text = '<span class="input_alert">顧客データを登録しました</span>';
			
			//▼データ登録
			foreach($data_ar AS $kd2 => $vd2){
				
				/*----- FS登録 -----*/
				//▼FSID取得
				$fs_id = $cis_fs_ar[$kd2];
				
				$fs_array = array(
					'date_create' => 'now()',
					'state'       => '1'
				);
				
				//▼FS登録用
				$ai_fs = zDBNewUniqueID(TABLE_FS_SETTING,$fs_array,'fs_setting_ai_id','fs_setting_id');	//追加したID
				
				
				/*----- User登録 -----*/
				//▼パスワード
				$pass_word = '12345';								//CSV登録限定
				$crpt_pass = tep_encrypt_password($pass_word);		//DB登録用
				
				/*
					8E-mail
				*/
				$user_email = ($vd2[8])? $vd2[8]:'null';
				
				//▼ユーザー情報
				$user_ar = array(
					'user_permission' => 'u',
					'fs_id'           => $fs_id,
					'user_email'      => $user_email,
					'user_password'   => $crpt_pass,
					'date_create'     => 'now()',
					'state'           => '1'
				);
				
				//▼DB登録
				$user_id  = zDBNewUniqueID(TABLE_USER,$user_ar,'user_ai_id','user_id');
				
				
				//▼ユーザーステータスレコードを追加
				$wc_st_ar = array(
					'user_id'     => $user_id,
					'date_create' => 'now()',
					'state'       => '1'
				);
				
				//▼ユーザーステータスを事前に作成
				zDBNewUniqueID(TABLE_USER_WC_STATUS,$wc_st_ar,'user_wc_status_ai_id','user_wc_status_id');
				
				
				/*----- FS更新 -----*/
				//▼FSID更新
				$fs_up_array = array(
					'user_id' => $user_id,
					'fs_id'   => $fs_id
				);
				
				$w_fs_set = "`fs_setting_ai_id`='".tep_db_input($ai_fs)."' AND `state`='1'";
				tep_db_perform(TABLE_FS_SETTING,$fs_up_array,'update',$w_fs_set);
				
				
				/*----- Position登録 -----*/
				/*
					0	CISID
					1	CIS紹介者ID
				*/
				
				//▼暫定確保
				$p_inviter = 0;
				
				//▼登録データ
				$position_ar = array(
					'position_permission'     => 'a',
					'user_id'                 => $user_id,
					'fs_id'                   => $fs_id,
					'cis_id'                  => (trim($cis_num_ar[$kd2])),
					'position_my_invite_code' => '0',
					'position_inviter'        => $p_inviter,
					'position_date_regi'      => 'now()',		//ポジション登録日
					'position_stock'          => 'c',			//c csv登録者
					'date_create'             => 'now()',
					'state'                   => '1'
				);
				
				//▼ポジション
				$p_id    = zDBNewUniqueID(TABLE_POSITION,$position_ar,'position_ai_id','position_id');
				
				//▼紹介コード作成
				$my_code = MY_CODE_PREFIX.((strlen($p_id) < 6)? sprintf('%06d', $p_id):$p_id);
				
				//▼DB登録
				$ps_up_array = array('position_my_invite_code'=>$my_code);						//登録データ
				$w_ps_set    = "`position_id`='".tep_db_input($p_id)."' AND `state`='1'";		//検索条件
				tep_db_perform(TABLE_POSITION,$ps_up_array,'update',$w_ps_set);					//データ更新
				
				
				//▼紹介者登録用
				/*
					0	CIS自身ID
					1	CIS紹介者ID
					$inv_fs_ar[$i_fget]  = $inv_fs_id;	//紹介者FSID
				*/
				//▼自身ポジション　＞　紹介者FSID変換
				$list_invite_ar[$p_id] = $inv_fs_ar[$kd2];	//position_id　＞ 紹介者FSID
				
				//▼FSID　＞　ポジション変換
				$list_pid_ar[$fs_id]   = $p_id;				//FSID　　　　＞ position_id
				
				
				/*----- ユーザー情報 -----*/
				/*
					▼基本情報
					2	氏名
					3	ふりがな
					4	生年月日
					5	ＴＥＬ
					6	ＦＡＸ
					7	携帯番号
				*/
				
				$un = ($vd2[2])? explode(' ',str_replace('　',' ',trim($vd2[2]))):'';
				$uk = ($vd2[3])? explode(' ',str_replace('　',' ',trim($vd2[3]))):'';
				
				$user_name  = ($un)? $un[0]:'null';
				$user_name2 = ($un)? $un[1]:'null';
				$user_kana  = ($uk)? $uk[0]:'null';
				$user_kana2 = ($uk)? $uk[1]:'null';
				
				$user_birth = $vd2[4];
				$user_tel_t = $vd2[5];
				$user_tel_m = $vd2[6];
				$user_fax   = $vd2[7];
				
				//▼ユーザー情報
				$uinfo_array = array(
					'user_id'         => $user_id,
					'user_name'       => $user_name,
					'user_name2'      => $user_name2,
					'user_name_kana'  => $user_kana,
					'user_name_kana2' => $user_kana2,
					'user_borthday'   => $user_birth,
					'user_tel_t'      => $user_tel_t,
					'user_tel_m'      => $user_tel_m,
					'user_fax'        => $user_fax,
					'date_create'     => 'now()',
					'state'           => '1'
				);
				
				
				/*----- 住所情報 -----*/
				//新規登録
				zDBNewUniqueID(TABLE_USER_INFO,$uinfo_array,'user_info_ai_id','user_info_id');
				/*
					▼住所
					9	郵便番号
					10	都道府県
					11	住所
				*/
				
				//郵便番号
				if($vd2[9]){
					$z = explode('-',trim($vd2[9]));
					$zip_a = $z[0];
					$zip_b = $z[1];
				}
				
				$pref = ($vd2[10])? $vd2[10]:'null';
				
				if($vd2[11]){
					$addr = zz_change_city_addr($vd2[11]);
					$city = $addr[0];
					$area = $addr[1];
					$strt = $addr[2];
				}
				
				
				//▼登録情報
				$uaddr_array = array(
					'user_id'                   => $user_id,
					'user_address_zip_a'        => $zip_a,
					'user_address_zip_b'        => $zip_b,
					'user_address_pref'         => $pref,
					'user_address_city'         => (($city)? $city : 'null'),
					'user_address_area'         => (($area)? $area : 'null'),
					'user_address_strt'         => (($strt)? $strt : 'null'),
					'user_address_country_code' => '81',
					'date_create'               => 'now()',
					'state'                     => '1'
				);
				
				
				//新規登録
				zDBNewUniqueID(TABLE_USER_ADDRESS,$uaddr_array,'user_address_ai_id','user_address_id');
				
				
				/*----- 注文情報 -----*/
				/*
					▼注文情報
					12	申請日
					13	ポジ数
					14	金額
				*/
				
				//▼注文配列に変換
				$d_application = $vd2[12];
				$o_num         = $vd2[13];
				$o_amount      = str_replace(',','',trim($vd2[14]));
				
				//▼商品情報
				$plan[1]  = array(
					'planid' => '1',
					'num'    => $o_num,
					'unit'   => ($o_amount / $o_num),
					'pay'    => 0,
					'sum'    => $o_amount
				);
				
				$item  = array('1' => $o_amount);	//費用項目
				$point = array('1' => $o_amount);	//ポイント
				
				
				//▼登録データ　＞　初回入金
				$order_ar = array(
					'position_id'                 => $p_id,
					'user_id'                     => $user_id,
					'user_order_user_rank_id'     => 1,
					'user_order_sort'             => 'a',
					'user_order_pay_currency_id'  => 0,
					'user_order_date_application' => $d_application,
					'user_order_amount'           => $o_amount,
					'user_order_pay_rate'         => '1',
					'user_order_pay_rate_amount'  => $o_amount,
					'user_order_pay_wey'          => 'a',
					'user_order_date_limit'       => $d_application,
					'user_order_plan'             => zToJSText($plan),
					'user_order_item'             => zToJSText($item),
					'user_order_point'            => zToJSText($point),
					'user_order_condition'        => '1',
					'date_create'                 => 'now()',
					'state'                       => '1'
				);
				
				//▼DB登録
				zDBNewUniqueID(TABLE_USER_ORDER,$order_ar,'user_order_ai_id','user_order_id');
				
				
				/*----- CIS追加情報 -----*/
				//▼紹介者情報登録用
				if($vd2[15]){
					
					//▼登録内容
					$rmks = str_replace("$","\n",trim($vd2[15],'$'));	//改行対策
					
					$memo_query = '';				//初期化
					$memo_query = array(
						'user_id'        => $user_id,
						'fs_id'          => $fs_id,
						'cis_fd_remarks' => $rmks,
						'date_create'    => 'now()',
						'state'          => '1'
					);
					
					//新規登録
					zDBNewUniqueID(TABLE_CIS_FD,$memo_query,'cis_fd_ai_id','cis_fd_id');
				}
				
			}
			
			
			/*----- 紹介者ID追加 -----*/
			foreach($list_invite_ar AS $kpid => $vfs){
				
				/*
					$kpid：自身position
					$vfs ：紹介者fsid
					
					$list_invite_ar[$p_id] = $fs_id;	//position_id　＞ 紹介者FSID
					$list_pid_ar[$fs_id]   = $p_id;	//▼FSID　＞　ポジション変換
				*/
				
				//▼紹介者ポジションID
				$p_inviter = $list_pid_ar[$vfs];
				
				//▼更新情報
				$query_inv = array('position_inviter' => $p_inviter);
				
				$wp_set = "`position_id` = '".tep_db_input($kpid)."' AND `state` = '1'";
				tep_db_perform(TABLE_POSITION,$query_inv,'update',$wp_set);
			}
			
			
		}else if($i_c > 0){
			
			$err_text.= '<span class="input_alert">エラーがあります</span>';
			
			
			/*----- エラー表示 -----*/
			foreach($err_ar AS $ke => $ve){
				
				/*--- エラー表示 ---*/
				$er0 = false;
				$err0_tx = '';
				$err1_tx = '';
				
				if($err_cis_ar[$ke]){
					$er0 = true;
					$err0_tx = '<span class="dt_ng">'.$err_cis_ar[$ke].'</span>';
				}
				
				if($err_inv_ar[$ke]){
					$er0 = true;
					$err1_tx = '<span class="dt_ng">'.$err_inv_ar[$ke].'</span>';
				}
				
				//▼表示
				$cl0 = ($er0)? 'class="ng"':'';
				
				
				/*--- 内容表示 ---*/
				//▼表示用
				$a_td = 'CIS自身：'.$ve[0].$err0_tx.'<br>';
				$a_td.= 'CIS紹介：'.$ve[1].$err1_tx;
				
				//▼表示用
				$b_td = $ve[2].'<br>';
				$b_td.= $ve[3].'<br>';
				$b_td.= $ve[4].'<br>';
				$b_td.= $ve[5].'<br>';
				$b_td.= $ve[6].'<br>';
				$b_td.= $ve[7].'<br>';
				$b_td.= $ve[8];
				
				
				//▼表示用
				$c_td = $ve[9].'<br>';
				$c_td.= $ve[10].'<br>';
				$c_td.= $ve[11];
				
				
				//▼表示用
				$d_td = '申請日：'.$ve[12].'<br>';
				$d_td.= '個数：'.$ve[13].'<br>';
				$d_td.= '合計：'.$ve[14];
				
				//▼紹介者情報登録用
				$e_td = '';
				if($ve[15]){$e_td = $ve[15];}
				
				
				/*----- 表示項目 -----*/
				$err_tb_in.= '<tr>';
				$err_tb_in.= '<td>'.$ke.'</td>';	//番号
				$err_tb_in.= '<td '.$cl0.'>'.$a_td.'</td>';	//ポジション
				$err_tb_in.= '<td>'.$b_td.'</td>';	//ユーザー
				$err_tb_in.= '<td>'.$c_td.'</td>';	//住所
				$err_tb_in.= '<td>'.$d_td.'</td>';	//注文
				$err_tb_in.= '<td>'.$e_td.'</td>';	//備考
				$err_tb_in.= '</tr>';
			}
			
			//▼表示項目
			$err_head = '<tr>';
			$err_head.= '<th>番号</th>';
			$err_head.= '<th>ポジション</th>';
			$err_head.= '<th>ユーザー</th>';
			$err_head.= '<th>住所</th>';
			$err_head.= '<th>注文</th>';
			$err_head.= '<th>備考</th>';
			$err_head.= '</tr>';
			
			//▼表示処理
			$err_table = '<table class="list_table">';
			$err_table.= $err_head;
			$err_table.= $err_tb_in;
			$err_table.= '</table>';
		}
	}
}


$ch_a = '';
$ch_b = '';
if($cis_new == 'a'){$ch_a = 'checked';}
if($cis_new == 'b'){$ch_b = 'checked';}

$input_form = '<form action="'.$form_action_to.'" name="input_form" method="POST" enctype="multipart/form-data">';
$input_form.= '<input type="hidden" name="act"    value="process">';

$input_form.= '<div style="margin:20px 0;">';
$input_form.= '<p>CISID発行</p>';
$input_form.= '<input type="radio"   name="s_cis_new" value="a" required '.$ch_a.'><span class="spc10_l">新しく発行する</span><br>';
$input_form.= '<input type="radio"   name="s_cis_new" value="b" required '.$ch_b.'><span class="spc10_l">新しく発行しない</span>';
$input_form.= '</div>';

$input_form.= '<input type="file"   name="user_csv" value="" accept=".csv">';
$input_form.= '<div style="margin-top:20px;"><input type="submit" name="act_send" value="データを登録する"></div>';
$input_form.= '</form>';

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
	<link rel="stylesheet" type="text/css" href="../css/common.css" media="all">
	<link rel="stylesheet" type="text/css" href="../css/master.css" media="all">
	
	<style>
		.csv_area input[type="radio"] {vertical-align:middle;}
		.dt_ng {color:#FF0;font-weight:800;}
		.ng {background:#F99;}
		input[type="submit"] {cursor:pointer;}
		
		.list_table{width:100%;}
		.list_table td{vertical-align:top;}
	</style>
	<script src="../js/jquery-3.2.1.min.js" charset="UTF-8"></script>
	<script src="../js/d3/d3.v3.min.js"     charset="UTF-8"></script>
</head>
<body>
<div id="wrapper">
	
	<div id="header">
		<?php require('inc_master_header.php');?>
	</div>
	<div id="head_line">
		<?php require('inc_master_head_line.php');?>
	</div>

	<div id="content">
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
				
				<h2>CSV取込</h2>
				<div class="part">
					<div class="spc20">
						<div class="csv_area">
							<?php echo $input_form;?>
						</div>
						<?php echo $err_text;?>
					</div>
					<div class="spc20">
						<a href="">クリア</a>
						<?php echo $err_table;?>
					</div>
				</div>
			</div>
		</div>
		
		<div class="clear_float"></div>
	</div>
	
	<div id="footer">
		<?php require('inc_master_footer.php'); ?>
	</div>
</div>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>