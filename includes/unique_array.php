<?php 
//---------------ナビゲーション---------------
//▼基本情報
$NavUserInfoJP = array(
	'edit_user_info'           => array('val'=>'基本情報'    ,'icon'=>'fa fa-user'),
	'edit_user_address'        => array('val'=>'住所情報'    ,'icon'=>'fa fa-address-book'),
	//'edit_user_identification' => array('val'=>'身分証情報'  ,'icon'=>'fa fa-id-card'),
	'edit_user_upload'         => array('val'=>'アップロード','icon'=>'fa fa-picture-o')
);

$NavUserInfoEN = array(
	'edit_user_info'           => array('val'=>'Basic'    ,'icon'=>'fa fa-user'),
	'edit_user_address'        => array('val'=>'Address'    ,'icon'=>'fa fa-address-book'),
	//'edit_user_identification' => array('val'=>'Identification'  ,'icon'=>'fa fa-id-card'),
	'edit_user_upload'         => array('val'=>'Upload','icon'=>'fa fa-picture-o')
);


//---------------マスター用---------------
//▼税区分
$TaxType = array(
	'0'=>'内税',
	'1'=>'外税',
	'2'=>'税なし');

//---------------登録用---------------
//▼登録区分
$RegTypeArray = array('1'=>'個人','2'=>'法人');


//▼証明書種類
$certif_list      = array('a'=>'身分証明書','b'=>'住所証明書'  ,'c'=>'持っている写真');
$certif_corporate = array('a'=>'登記簿謄本','b'=>'店舗外観画像','c'=>'店舗内観画像');

//▼承認状況
$cetif_condition = array('1'=>'承認待','a'=>'OK','n'=>'NG','u'=>'更新');

//▼カード状況
$WCOwnArray = array(
	'a' => '既存',
	'b' => '新規'
);

//▼カードステータス
$WCStatusArray = array(
	'reg'          => 'user_wc_status_reg',
	'info'         => 'user_wc_status_info',
	'info_addr'    => 'user_wc_status_info_address',
	'ident'        => 'user_wc_status_identification',
	'addr'         => 'user_wc_status_address_certification',
	'certif'       => 'user_wc_status_certification',
	'buy'          => 'user_wc_status_buy',
	'step_w'       => 'user_wc_status_step_wallet',
	'step_c'       => 'user_wc_status_step_card',
	'd_app_agent'  => 'user_wc_status_date_approved_agent',
	'd_app_nig'    => 'user_wc_status_date_approved_nig',
	'd_app_anx'    => 'user_wc_status_date_approved_anx',
	'd_rec_nig'    => 'user_wc_status_date_receive_nig',
	'd_send_nid'   => 'user_wc_status_date_send_nig',
	'd_rec_agent'  => 'user_wc_status_date_receive_agent',
	'd_send_agent' => 'user_wc_status_date_send_agent',
	'd_rec_user'   => 'user_wc_status_date_receive_user'
);


//▼住所証明書入力用
$AddressCertifArray = array(
	'a' => '公共料金',
	'b' => '銀行残高証明',
	'c' => 'クレジットカード請求書',
	'd' => '住民票'
);

//▼住所証明書登録用
$AddressCertifArrayCSV = array(
	'a' => 'Public Unility Charge',
	'b' => 'Bank Statement',
	'c' => 'Credit Card Statement',
	'd' => 'Resident card'
);

//▼性別
$UserSexArray = array(
	'm' => '男性',
	'w' => '女性'
);

//▼お知らせ
$NoticeTargetArray = array(
	't' => 'トップ',
	'u' => 'ユーザー',
	'a' => '代理店'
);

//▼注文種類
$OrderSortArray = array(
	'a' => '初回',
	'b' => '追加'
);

//▼注文種類
$OrderType = array(
	'a' => '初回注文',
	'b' => '追加注文',
	'c' => '定期購入'
);

//▼配送先
$SipArray = array(
	'a'=>'登録住所',
	'b'=>'配送先'
);

//▼定期購入コース
$DeliverArray = array(
	'1'=> '毎月',
	'2'=> '2ヶ月に1回',
	'3'=> '3ヵ月に1回'
);

/*----- 計算用 -----*/
//▼計算処理
$OperatorArray = array(
	'a'=> '＋',
	'b'=> '－',
	'c'=> '×',
	'd'=> '÷'
);

//▼端数処理
$BrakeArray = array(
	'c' => '切上',
	'f' => '切捨',
	'r' => '四捨五入'
);

//▼計算単位
$CulcPoint = array(
	'a' => '商品単位で計算',
	'b' => '商品合計で計算'
);

//▼上限管理
$tmp = range(1,15);
foreach($tmp AS $v){$LimitNum[$v] = $v;}

/*----- Event -----*/
$TimeArray = array(
	'00:00'=>'00:00',
	'00:30'=>'00:30',
	'01:00'=>'01:00',
	'01:30'=>'01:30',
	'02:00'=>'02:00',
	'02:30'=>'02:30',
	'03:00'=>'03:00',
	'03:30'=>'03:30',
	'04:00'=>'04:00',
	'04:30'=>'04:30',
	'05:00'=>'05:00',
	'05:30'=>'05:30',
	'06:00'=>'06:00',
	'06:30'=>'06:30',
	'07:00'=>'07:00',
	'07:30'=>'07:30',
	'08:00'=>'08:00',
	'08:30'=>'08:30',
	'09:00'=>'09:00',
	'09:30'=>'09:30',
	'10:00'=>'10:00',
	'10:30'=>'10:30',
	'11:00'=>'11:00',
	'11:30'=>'11:30',
	'12:00'=>'12:00',
	'12:30'=>'12:30',
	'13:00'=>'13:00',
	'13:30'=>'13:30',
	'14:00'=>'14:00',
	'14:30'=>'14:30',
	'15:00'=>'15:00',
	'15:30'=>'15:30',
	'16:00'=>'16:00',
	'16:30'=>'16:30',
	'17:00'=>'17:00',
	'17:30'=>'17:30',
	'18:00'=>'18:00',
	'18:30'=>'18:30',
	'19:00'=>'19:00',
	'19:30'=>'19:30',
	'20:00'=>'20:00',
	'20:30'=>'20:30',
	'21:00'=>'21:00',
	'21:30'=>'21:30',
	'22:00'=>'22:00',
	'22:30'=>'22:30',
	'23:00'=>'23:00',
	'23:30'=>'23:30',
	'23:00'=>'23:00',
	'23:30'=>'23:30'
);

$AreaArray = array(
	'北海道' => '北海道',
	'東北'   => '東北',
	'関東'   => '関東',
	'中部'   => '中部',
	'近畿'   => '近畿',
	'中国'   => '中国',
	'四国'   => '四国',
	'九州'   => '九州'
);


//▼お知らせ
$NoticeTargetArray = array(
	't' => 'トップ',
	'u' => 'ユーザー'
);
?>