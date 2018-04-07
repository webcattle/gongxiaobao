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
foreach($data as $key=> $value)
{
	if($module == 'members' || $action=='members')
	{
		$title = $value['linkman']? $value['linkman']:$value['username'];
		$photo = $value['avatar'];
		$threedays = threedays($value['dateline'],$value['dateline'],$value['appid'],1);
		$url = "";
	}
	else
	{
		$title = $value['title'];
		if($value['uid'] && empty($title))
		{
			$sql = "SELECT linkman FROM {$tablepre}members WHERE uid='$value[uid]'";
			$title = $db->fetchOneBySql($sql);
			$photo = avatar($value['uid']);
		}
		else
		{
			$photo = $value['photo'];
		}
	}
	$dateline = date("Y-m-d",$value['dateline']);
	eval ("\$info .= \"" . $tpl->get("item",$template). "\";");
}