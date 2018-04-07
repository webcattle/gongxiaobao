<?php
/******************************************************************************************************
**  企业+ 7.0 - 企业+社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业+授权协议,查看或使用企业+的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: agency file
** Author.......: Winstod Dang
** Version......: 5.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
$data = array();
if($module=='distribution')
{
	//$moduletype = $_GET['moduletype'] ? $_GET['moduletype']:'shopstore';//默认为商铺
	if( in_array(1,$usergroup))
	{
		$perm=$shopa[$moduletype];
	}
	else
	{
		$perm = checkadmin($moduletype);
	}
	if(!$perm)
	{
		exit("no preview");
	}
	$extsql = '';
	if($moduletype=='agency')
	{
		if($agencytype=='moderate')
		{
			$extsql = " and moderate=0 ";
		}
		else if($agencytype == 'vtype')
		{
			$extsql = " and vtype=2 ";
		}
		else if($agencytype=='priority')
		{
			$extsql = " and priority>0";
		}
		if($aguser)
		{
			$sql = "select uid from {$tablepre}members where linkman like '%".$aguser."%'";
			$suid = $db->fetchColBySql($sql);
			if($suid)
			{
				$extsql .= " and uid in (".implode(",",$suid).") ";
			}
		}
		if($agtitle)
		{
			$extsql .= " and title like '%".$agtitle."%' ";
		}
		if($agmobile)
		{
			$extsql .= " and phone like '%".$agmobile."%' ";
		}
	}
	$sql = "select * from {$tablepre}agency where module='$moduletype' {$extsql} order by priority desc,id desc LIMIT $pagestart,$limit";
	$data = $db->fetchAssocArrBySql($sql);
	$agencylist = '';
	foreach($data as $key => $value)
	{
		$operation = '<i class="fa fa-view"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$moduletype.'&id='.$value['id'].'\')" >查看</a>';
		if(in_array('censor',$perm))
		{
			if($value['moderate']==1)
			{
				if($moduletype=='agency' )
				{
					$operation .= '<i class="fa fa-cancel"></i><a href="javascript:;" class="cancel">取消</a>';
				}
				else
				{
					$operation .= '<i class="fa fa-cancel"></i><a href="javascript:;" class="cancel">关闭</a>';
				}
			}
			else
			{
				$operation .= '<i class="fa fa-censor"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=censor&module='.$moduletype.'&id='.$value['id'].'\')" >审批</a>';
			}
		}
		if($moduletype=='shopstore' && in_array('edit',$perm))
		{
			$operation .= '<i class="fa fa-edit"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=edit&module='.$moduletype.'&id='.$value['id'].'\')" >编辑</a>';
			
		}
		if ($moduletype=='shopstore' && in_array('supplierproduct',$perm))
		{
			$operation .= '<i class="fa fa-product"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=list&module=supplierproduct&id='.$value['id'].'\')" >商品管理</a>';
		}
		
		if ($moduletype == 'agency' && in_array('censorv',$perm))
		{
			if($value['vtype']=='1')
			{
				$operation .= '<i class="fa fa-cancelv"></i><a href="javascript:;" class="cancelv">取消加V</a>';
			}
			else if($value['vtype']=='2')
			{
				$operation .= '<i class="fa fa-censor"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=censor&module='.$moduletype.'&id='.$value['id'].'&censortype=v\')" >认证审批</a>';
			}
		}
		if ($moduletype == 'agency' && in_array('priority',$perm))
		{
			$operation .= '<i class="fa fa-priority"></i><a href="javascript:;" class="priority">置顶</a>';
		}
		
		$agencylist .= '<tr trid='.$value['id'].'  freeze="'.$value['isfreeze'].'"   trmodule="'.$moduletype.'" trpriority="'.$value['priority'].'" truid="'.$value['uid'].'">';
		$agencylist .= '<td>'.$value['id'].'.'.$value['title'].'</td>';
		$agencylist .= '<td>'.$operation.'</td>';
		$memberurl = "admin.php?action=view&module=members&uid=".$value['uid'];
		
		if($moduletype=='agency')
		{
			$agencylist .= '<td>'.$select['agency']['vtype'][$value['vtype']].'</td>';
			$avatar= avatar($value['uid']);
			$linkman = $db->fetchOneBySql("select linkman from {$tablepre}members where uid='$value[uid]'");
			$html = '<img src="'.$uploaddir.$avatar.'" style="width:30px;height:30px;">&nbsp;<a href="javascript:;"   title="'.strip_tags($linkman).'" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\')">'.$linkman.'</a>';
			$agencylist .= '<td>'.$html.'</td>';
			if($value['fromuser'])
			{
				$avatar= avatar($value['fromuser']);
				$fromuser = $db->fetchOneBySql("select linkman from {$tablepre}members where uid='$value[fromuser]'");
				$html = '<img src="'.$uploaddir.$avatar.'" style="width:30px;height:30px;">&nbsp;<a href="javascript:;"   title="'.strip_tags($fromuser).'" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['fromuser'].'\')">'.$fromuser.'</a>';
				$agencylist .= '<td>'.$html.'</td>';
			}
			else
			{
				$agencylist .= '<td>无</td>';
			}
			//$agencylist .= '<td><img src="'.$photo.'" width="80px"></td>';
		}
		if($moduletype=='shopstore')
		{
			$agencylist .= '<td>'.$value['express'].'</td>';
		}
		$agencylist .= '<td>'.$value['chiefleader'].'</td>';
		$agencylist .= '<td>'.$value['phone'].'</td>';
		$agencylist .= '<td>'.$moderatearray[$value['moderate']].'</td>';
		$agencylist .= '<td>'.date('Y-m-d H:i',$value['dateline']).'</td>';
		$agencylist .= '</tr>';
	}
	$sql = "select count(*) from {$tablepre}agency where module='$moduletype' $extsql";
	$count = $db->fetchOneBySql($sql);
	if($moduletype=='shopstore')
	{
		$charcount=7;
	}
	else
	{
		$charcount=8;
	}
	$footer =  '<td style="white-space:nowrap;text-align:left;" colspan='.$charcount.'>共'.$count.'项';
	$pageinfo = page($page,$count,$limit,5);
}
else
{
	require_once 'include/others.php';
}
?>