<?php
// define the database table names used in the project
/*====================
�l�b�g���[�N���
====================*/
define('TABLE_POSITION'     , 'position');			//�|�W�V�����@�@�@�@���o�^���ݒ�
define('TABLE_P_UNI_LEVEL'  , 'p_uni_level');		//���j���x���̈ʒu�@������ݒ�

define('TABLE_P_UNI_SCORE'  , 'p_uni_score');		//�|�W�V�����̐��с@���Љ���閈�ɍX�V
define('TABLE_P_UNI_STATUS' , 'p_uni_status');		//�|�W�V�����̏�ԁ@������ݒ��͐��тɂ���čX�V


/*====================
���[�U�[���
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
��o����
====================*/
define('TABLE_USER_IDENTIFICATION'       , 'user_identification');			//�g���؏��
define('TABLE_USER_ADDRESS_CERTIFICATION', 'user_address_certification');	//�Z���ؖ���
define('TABLE_USER_CERTIFICATION'        , 'user_certification');			//�e��ؖ���


/*====================
�Ǘ���
====================*/
define('TABLE_A_DOC'       , 'a_doc');			//���[�U�[����
define('TABLE_A_NOTICE'    , 'a_notice');		//���m�点
define('TABLE_A_EVENT'     , 'a_event');		//�C�x���g
define('TABLE_A_QANDA'     , 'a_qanda');		//QandA
define('TABLE_A_QANDA_TAG' , 'a_qanda_tag');	//QandATAG


/*====================
�}�X�^�[
====================*/
define('TABLE_M_RANK'          , 'm_rank');					//�����N
define('TABLE_M_POINT'         , 'm_point');				//�|�C���g
define('TABLE_M_ITEM'          , 'm_item');					//��������
define('TABLE_M_DETAIL'        , 'm_detail');				//��p�ڍ׍���

define('TABLE_M_PLAN'          , 'm_plan');					//�w���v������
define('TABLE_M_PLAN'          , 'm_plan');					//�w���v������
define('TABLE_M_PLAN_ITEM'     , 'm_plan_item');			//�w���v�����i��
define('TABLE_M_PLAN_POINT'    , 'm_plan_point');			//�w���v�����|�C���g
define('TABLE_M_PLAN_ADD'      , 'm_plan_add');				//�w���A��


define('TABLE_M_CURRENCY'      , 'm_currency');				//�ʉݓo�^
define('TABLE_M_CURRENCY_NOW'  , 'm_currency_now');			//�ʉ݃��[�g

define('TABLE_M_CULC'          , 'm_culc');					//�萔���v�Z
define('TABLE_M_COST'          , 'm_cost');					//�萔��

define('TABLE_M_ORDER_SETTING' , 'm_order_setting');		//�I�[�_�[�ݒ�
define('TABLE_M_POINT_SETTING' , 'm_point_setting');		//�|�C���g�ݒ�

define('TABLE_M_PAYMENT'       , 'm_payment');				//�x�����@
define('TABLE_M_PAYMENT_FEE'   , 'm_payment_fee');			//�x���萔��


/*====================
VIEW�e�[�u��
====================*/
define('VIEW_PLAN'          , 'view_plan');				//�v�����\���p
define('VIEW_ORDER'         , 'view_order');			//�����\���p
define('VIEW_CHARGE'        , 'view_charge');			//�����\���p


/*====================
�T�C�g�S�̊Ǘ�
====================*/
define('TABLE_MASTER'      , 'master');			//�T�C�g�S�̊Ǘ���
define('TABLE_MASTER_BANK' , 'master_bank');	//�U�����s����

define('TABLE_FS_SETTING'  , 'fs_setting');		//Flagship�S�̂̐ݒ�

define('TABLE_CIS_FD'      , 'cis_fd');			//CSV�捞���̔��l


/*====================
�V�X�e���ݒ�
====================*/
//���V�X�e���ݒ�
define('TABLE_ZSYS_SETTING' , 'zsys_setting');


/*====================
CIS�p������
====================*/
define('TABLE_MEM00000'    , 'mem00000');		//�l���
define('TABLE_MEM00001'    , 'mem00001');		//�l�Z��
define('TABLE_MEM00002'    , 'mem00002');		//��s����
define('TABLE_MEM01000'    , 'mem01000');		//�l�^�C�g��

define('TABLE_MEM02002'    , 'mem02002');		//������

/*====================
CIS�p�������
====================*/
define('TABLE_ODR00000'    , 'odr00000');		//�����}�X�^�[
define('TABLE_ODR00001'    , 'odr00001');		//�ڋq�������


/*====================
CIS�p���i���
====================*/
define('TABLE_ITEM00000'   , 'item00000');		//���i�}�X�^�[
define('TABLE_ASITEM00000' , 'asitem00000');	//�I�����i


/*====================
���̑�
====================*/
define('TABLE_SESSIONS'    , 'sessions');
define('TABLE_WHOS_ONLINE' , 'whos_online');
?>
