<?php
/******************************************************************************************************
**  企业Plus 7.0 - 企业Plus社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业Plus授权协议,查看或使用企业Plus的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: global file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
require_once $site_engine_root."mobile/ajax/validate.php";

if (!check_ip($systemlimit['adminip']))
{
	exit('未允许的IP访问！');
}
/*****************************************************
** 版本处理
*****************************************************/
if($accesstoken && !empty($siteinfo))
{
	$db_name = $main_dbname;
	$db = new DATABASE();
	$sql = "SELECT * FROM {$tablepre}websiteinfo WHERE siteid='$siteinfo[id]'";
	$info = $db->fetchAssocArrBySql($sql);
	if(!empty($info))
	{
		foreach($info as $key=> $value)
		{
			if($value['value']==1)
			{
				$sitearray[$value['module']] = 1;
			}
		}
	}
	$db_name = $siteinfo['sitedomain'];
	$db = new DATABASE();
	if(!empty($adminarray))
	{
		foreach($adminarray as $key=> $value)
		{
			foreach($value as $k=> $v)
			{
				if($sitearray[$key.'_'.$k]!=1)
				{
					unset($adminarray[$key][$k]);
				}
			}
		}
	}
	if($siteinfo['service_type_info']=='dingyue')
	{
		unset($adminarray['operator']['hongbao']);
	}
	unset($array);
	unset($sql);
}
if($domain == 'weixin.qiyeplus.com')
{
	$adminarray['tools'] = array_merge($adminarray['tools'],array('database'=>'build'));
	$adminarray['tools'] = array_merge($adminarray['tools'],array('industry'=>'industry'));
}
if($weixin_type=='dingyue' || $weixin_type=='')
{
	unset($adminarray['operator']['hongbao']);
	unset($adminarray['operator']['qyhongbao']);
	unset($adminarray['operator']['eventhongbao']);
	unset($adminarray['operator']['sharehongbao']);
	unset($adminarray['operator']['qrcode']);
	$member['view_char'] = str_replace('monthpush,','',$member['view_char']);
}
if($usehongbao!=1)
{
	unset($adminarray['operator']['hongbao']);
}
?>