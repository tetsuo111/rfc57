<?php
// define the database table names used in the project
/*====================
ネットワーク情報
====================*/
define('TABLE_POSITION'     , 'position');			//ポジション　　　　＞登録時設定
define('TABLE_P_UNI_LEVEL'  , 'p_uni_level');		//ユニレベルの位置　＞初回設定

define('TABLE_P_UNI_SCORE'  , 'p_uni_score');		//ポジションの成績　＞紹介がある毎に更新
define('TABLE_P_UNI_STATUS' , 'p_uni_status');		//ポジションの状態　＞初回設定後は成績によって更新


/*====================
ユーザー情報
====================*/
define('TABLE_USER'           , 'user');
define('TABLE_USER_INFO'      , 'user_info');
define('TABLE_USER_ADDRESS'   , 'user_address');
define('TABLE_USER_WC_STATUS' , 'user_wc_status');

define('TABLE_USER_ORDER'     , 'user_order');
define('TABLE_USER_O_CART'    , 'user_o_cart');
define('TABLE_USER_O_CHARGE'  , 'user_o_charge');
define('TABLE_USER_O_DETAIL'  , 'user_o_detail');
define('TABLE_USER_O_SHIPPING', 'user_o_shipping');

/*====================
提出書類
====================*/
define('TABLE_USER_IDENTIFICATION'       , 'user_identification');			//身分証情報
define('TABLE_USER_ADDRESS_CERTIFICATION', 'user_address_certification');	//住所証明書
define('TABLE_USER_CERTIFICATION'        , 'user_certification');			//各種証明書


/*====================
管理者
====================*/
define('TABLE_A_DOC'       , 'a_doc');			//ユーザー資料
define('TABLE_A_NOTICE'    , 'a_notice');		//お知らせ
define('TABLE_A_EVENT'     , 'a_event');		//イベント
define('TABLE_A_QANDA'     , 'a_qanda');		//QandA
define('TABLE_A_QANDA_TAG' , 'a_qanda_tag');	//QandATAG


/*====================
マスター
====================*/
define('TABLE_M_RANK'          , 'm_rank');					//ランク
define('TABLE_M_POINT'         , 'm_point');				//ポイント
define('TABLE_M_ITEM'          , 'm_item');					//注文項目
define('TABLE_M_DETAIL'        , 'm_detail');				//費用詳細項目

define('TABLE_M_PLAN'          , 'm_plan');					//購入プラン名
define('TABLE_M_PLAN'          , 'm_plan');					//購入プラン名
define('TABLE_M_PLAN_ITEM'     , 'm_plan_item');			//購入プラン品目
define('TABLE_M_PLAN_POINT'    , 'm_plan_point');			//購入プランポイント
define('TABLE_M_PLAN_ADD'      , 'm_plan_add');				//購入連動


define('TABLE_M_CURRENCY'      , 'm_currency');				//通貨登録
define('TABLE_M_CURRENCY_NOW'  , 'm_currency_now');			//通貨レート

define('TABLE_M_CULC'          , 'm_culc');					//手数料計算
define('TABLE_M_COST'          , 'm_cost');					//手数料

define('TABLE_M_ORDER_SETTING' , 'm_order_setting');		//オーダー設定
define('TABLE_M_POINT_SETTING' , 'm_point_setting');		//ポイント設定

define('TABLE_M_PAYMENT'       , 'm_payment');				//支払方法
define('TABLE_M_PAYMENT_FEE'   , 'm_payment_fee');			//支払手数料


/*====================
VIEWテーブル
====================*/
define('VIEW_PLAN'          , 'view_plan');				//プラン表示用
define('VIEW_ORDER'         , 'view_order');			//注文表示用
define('VIEW_CHARGE'        , 'view_charge');			//請求表示用


/*====================
サイト全体管理
====================*/
define('TABLE_MASTER'      , 'master');			//サイト全体管理者
define('TABLE_MASTER_BANK' , 'master_bank');	//振込先銀行口座

define('TABLE_FS_SETTING'  , 'fs_setting');		//Flagship全体の設定

define('TABLE_CIS_FD'      , 'cis_fd');			//CSV取込時の備考


/*====================
システム設定
====================*/
//▼システム設定
define('TABLE_ZSYS_SETTING' , 'zsys_setting');


/*====================
CIS用会員情報
====================*/
define('TABLE_MEM00000'    , 'mem00000');		//個人情報
define('TABLE_MEM00001'    , 'mem00001');		//個人住所
define('TABLE_MEM00002'    , 'mem00002');		//銀行口座
define('TABLE_MEM01000'    , 'mem01000');		//個人タイトル

define('TABLE_MEM02002'    , 'mem02002');		//調整金

/*====================
CIS用注文情報
====================*/
define('TABLE_ODR00000'    , 'odr00000');		//注文マスター
define('TABLE_ODR00001'    , 'odr00001');		//顧客注文情報


/*====================
CIS用商品情報
====================*/
define('TABLE_ITEM00000'   , 'item00000');		//商品マスター
define('TABLE_ASITEM00000' , 'asitem00000');	//選択商品


/*====================
その他
====================*/
define('TABLE_SESSIONS'    , 'sessions');
define('TABLE_WHOS_ONLINE' , 'whos_online');
?>
