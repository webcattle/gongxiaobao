<?php
/******************************************************************************************************
 **  企业+ 5.0 - 企业+社交网络营销中心管理系统
 **  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
 **  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
 **  请详细阅读企业+授权协议,查看或使用企业+的任何部分意味着完全同意
 **  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
 *******************************************************************************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
if(empty($do))
{
	$sql = "select * from {$tablepre}operatorseats where module='operator' and moduleid='$id'";
	$data = $db->fetchSingleAssocBySql($sql);
	
	eval ("\$content .= \"" . $tpl->get("selectseat",$template). "\";");
	echo $content;
	exit;
}
else if($do=='getlayout')
{
	$ret = array();
	$sql = "select * from {$tablepre}operatorseats where module='operator' and moduleid='$id'";
	$data = $db->fetchSingleAssocBySql($sql);
	foreach($data as $key=>$value)
	{
		if($key=='blankseats')
		{
			$value = json_decode($value);
		}
		$ret[$key] = $value;
	}
	if($data)
	{
		for($i=1;$i<= $data['rows']; $i++)
		{
			for($j=1;$j<=$data['cols'];$j++)
			{
				$temp = array($i,$j);
				if(in_array($temp,$ret['blankseats']))
				{
					$tempdata[$i][$j] = array();
				}
				$sql = "select id,title,company,depart,position,phone,seat,layoutpos from {$tablepre}register where layoutpos='".implode(",",$temp)."' and module='$module' and moduleid='$id'";
				$tempdata[$i][$j] = $db->fetchSingleAssocBySql($sql);
			}
		}
		$ret['data'] = $tempdata;
	}
	echo jsondata($ret);
	exit;
}
else if($do=='savelayout')
{
	$blankseats = json_encode($_POST['blankseats']);
	$operatorid = intval($id);
	if(!$operatorid)
	{
		$ret['flag']=0;
		$ret['error'] = '参数错误';
		echo jsondata($ret);
		exit;
	}
	$sql = "select id from {$tablepre}operatorseats where module='$module' and moduleid='$operatorid'";
	$haveid = $db->fetchOneBySql($sql);
	$rows = intval($rows);
	$cols = intval($cols);
	if($haveid)
	{
		$sql = "update {$tablepre}operatorseats set rows='$rows',cols='$cols',blankseats='$blankseats' where id='$haveid'";
	}
	else
	{
		$sql = "insert into {$tablepre}operatorseats(module,moduleid,rows,cols,blankseats) values('$module','$operatorid','$rows','$cols','$blankseats')";
	}
	$db->query($sql);
	$ret['flag']=1;
	$ret['error'] = '提交成功';
	echo jsondata($ret);
	exit;
}
else if($do=='getseats')
{
	$sql = "select id,title,company,depart,seat,position,phone from {$tablepre}register where seat!='' and module='$module' and moduleid='$id'";
	$data = $db->fetchAssocArrBySql($sql);
	echo jsondata($data);
	exit;
}
else if($do=='addtoseat')
{
	$sql = "select id,title,company,depart,position,phone from {$tablepre}register where module='$module' and moduleid='$id'";
	$data = $db->fetchAssocArrBySql($sql);
	echo jsondata($data);
	exit;
}
else if($do=='saveseat')
{
	$id = intval($_GET['id']);
	if(!$id)
	{
		$ret['flag']=0;
		$ret['error'] = '参数错误';
		echo jsondata($ret);
		exit;
	}
	foreach($_POST as $key=>$value)
	{
		if($key=='noseat')
		{
			continue;
		}
		$regid = $key;
		$layoutpos = $value['layoutpos'];
		$seat = $value['seat'];
		$sql = "update {$tablepre}register set seat='$seat',layoutpos='$layoutpos' where id='$regid'";
		$db->query($sql);
	}
	if($_POST['noseat'])
	{
		$noseats = $_POST['noseat'];
		$sql = "update {$tablepre}register set seat='',layoutpos='' where id in ($noseats)";
		$db->query($sql);
	}
	$ret['flag']=1;
	$ret['error'] = '提交成功';
	echo jsondata($ret);
	exit;
}