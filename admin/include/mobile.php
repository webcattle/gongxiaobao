<?php
/******************************************************************************************************
**  ��ҵPlus 7.0 - ��ҵPlus�罻����Ӫ�����Ĺ���ϵͳ
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  ���������ȷ�����������޹�˾ www.qiyeplus.com    *����֧�� ���ע'��ҵPlus' ΢�ź�
**  ����ϸ�Ķ���ҵPlus��ȨЭ��,�鿴��ʹ����ҵPlus���κβ�����ζ����ȫͬ��
**  Э���е�ȫ������,��֧�ֹ��������ҵ,�Ͻ�һ��Υ��Э�����Ȩ��Ϊ.
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