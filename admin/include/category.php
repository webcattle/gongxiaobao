<?php
/******************************************************************************************************
**  企业Plus 7.0 - 企业Plus社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业Plus授权协议,查看或使用企业Plus的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
$module_system = $module;
$data = array();
if($parentid=='')
{
	$parentid = 0;
}
$pos=strpos($module,'class');
if($pos===false && $module!='channel')
{
	$_module = $module;
	$module .= 'class';
}
else
{
	$_module = str_replace("class","",$module);
}
if(in_array($_module,$system_key))
{
	$headbuttons .= buildHeadButton('增加',0,'admin.php?action=add&module='.$module.'&parentid='.$parentid);
	$orderby = $_GET['orderby'] ? $_GET['orderby']:'orderid';
	$ascdesc = $_GET['ascdesc'] ? $_GET['ascdesc']:'ASC';
	$data = $newdata = '';
	if($module=='channel')
	{
		if($id)
		{
			$sql = "SELECT * FROM ".$tablepre.$module." WHERE id='$id'";
		}
		else if(intval($parentid) || $parentid==0)
		{
			$sql = "SELECT * FROM ".$tablepre.$module." WHERE parentid='$parentid' and moderate=1  ORDER BY $orderby $ascdesc LIMIT $pagestart,$limit";
		}
	}
	else if($module == 'replytemplateclass')
	{
		$sql = "SELECT * FROM ".$tablepre.$module." WHERE parentid='$parentid' and moderate=1 and uid=1 ORDER BY  $orderby $ascdesc LIMIT $pagestart,$limit";
	}
	else
	{
		$sql = "SELECT * FROM ".$tablepre.$module." WHERE parentid='$parentid' and moderate=1 ORDER BY  $orderby $ascdesc LIMIT $pagestart,$limit";
	}
	$data = $db->fetchAssocArrBySql($sql);
	$newdata =array();
	$count = count($data);
}
else
{
	$pos=strpos($module,'class');
	if($pos===false)
	{
		$module .= 'class';
	}
	$module= $module=='kfaccount' ? $module : 'kfaccountclass';
	$headbuttons .= buildHeadButton('增加',0,'admin.php?action=add&module='.$module.'&parentid='.$parentid.'&appid='.$appid);
	$orderby = $_GET['orderby'] ? $_GET['orderby']:'orderid';
	$ascdesc = $_GET['ascdesc'] ? $_GET['ascdesc']:'ASC';
	$sql = "SELECT * FROM ".$tablepre.$module." WHERE parentid='$parentid' and moderate=1 ORDER BY $orderby $ascdesc LIMIT $pagestart,$limit";
	$data = $db->fetchAssocArrBySql($sql);
	$newdata =array();
}