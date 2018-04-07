<?php
/******************************************************************************************************
**  企业+ 3.0 - 企业+社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2015. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业+' 微信号
**  请详细阅读企业+授权协议,查看或使用企业+的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: admin file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
header("Content-Type: text/html; charset=UTF-8");
$admincp = 1;
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
require_once '../mobile/ajax/header.php';
require_once "../mobile/ajax/validate.php";
require_once '../mobile/ajax/emoji.php';
require_once './include/settings.php';
require_once 'include/language.php';
require_once 'include/global.php';
require_once 'include/alertinfo.php';
require_once 'include/function.php';
require_once $site_engine_root."mobile/lib/admin.php";
require_once $site_engine_root."mobile/lib/wechat.php";
require_once $site_engine_root."mobile/lib/error.php";
require_once $site_engine_root."mobile/lib/function.php";
require_once $site_engine_root."mobile/lib/shopfunctions.php";

$sql = "select uid from {$tablepre}kfaccount where moderate=1";
$allkfuids = $db->fetchColBySql($sql);

$options = options(0);
$weObj = new Wechat($options);
$listsql = '';
//printarray($replacemodule[$module]);
if(is_array($replacemodule[$module]))
{
	$sourcemodule = $module;
	$listsql = $replacemodule[$module][1];
	$module = $replacemodule[$module][0];
}
else
{
	$sourcemodule = $module;
}
if(!$user['uid'])
{
	@header("Location:login.php");exit;
}
else if(min($usergroup)>=1000)
{
	@header("Location:/mobile/user.php");exit;
}

/*****************************************************
** 参数处理
*****************************************************/
$template 	= str_replace("/","",dirname($_SERVER['SCRIPT_NAME']));
$action 	= isset($_GET['action'])? $_GET['action']:(isset($_POST['action'])? $_POST['action']:'');
$parentid 	= isset($_GET['parentid'])? $_GET['parentid']:(isset($_POST['parentid'])? $_POST['parentid']:'');
$classid 	= isset($_GET['classid'])? $_GET['classid']:(isset($_POST['classid'])? $_POST['classid']:'');
$page 		= isset($_GET['page'])? $_GET['page']:(isset($_POST['page'])? $_POST['page']:'0');
$type 		= isset($_GET['type'])? $_GET['type']:(isset($_POST['type'])? $_POST['type']:'');
$uid 		= isset($_GET['uid'])? $_GET['uid']:(isset($_POST['uid'])? $_POST['uid']:'');
$groupid 	= $_GET['groupid'] ? $_GET['groupid']:'';
$where	 	= $_GET['where'] ? $_GET['where']:'';
$wherevalue 	= $_GET['wherevalue'] ? $_GET['wherevalue']:'';
$search		= $_GET['search'] ? $_GET['search']:'';
$orderby 	= $_GET['orderby'] ? $_GET['orderby']:'id';
$limit	 	= intval($_GET['limit']) ? intval($_GET['limit']):'30';
$filter	 	= intval($_GET['filter']) ? intval($_GET['filter']):'';
$pagestart 	= $page ? $limit*($page-1):0;
if($groupid){$groupidsql= " AND groupid='$groupid'";}
if(!empty($where)&&$wherevalue){$attasql .= " AND ".$where." ='".$wherevalue."' ";}
if($domain == 'qiyeplus.qiyeplus.com')
{
	// if($module!='help' && $module!='work')
	// {
	// 	$title = $l[$module].$l[$action];
	// 	$content = $title;
	// 	$sql = "SELECT id FROM {$tablepre}help WHERE modul='$module' AND actio='$action' AND type='common'";
	// 	$selectid = $db->fetchOneBySql($sql);
	// 	if(!$selectid)
	// 	{
	// 		$sql = "INSERT INTO {$tablepre}help(title,content,modul,actio,type,moderate,dateline,photo) VALUES('$title','$content','$module','$action','common',1,'$time','/data/upload/boka.jpg')";
	// 		$db->query($sql);
	// 		$content .= '视频';
	// 		$sql = "INSERT INTO {$tablepre}help(title,content,modul,actio,type,url,moderate,dateline,photo) VALUES('$title','$content','$module','$action','video','/data/help/common.mp4',1,'$time','/data/upload/boka.jpg')";
	// 		$db->query($sql);
	// 	}
	// }
}
/*****************************************************
** 安全检查
*****************************************************/
if(!empty($_GET))
{
	foreach($_GET as $key=> $value)
	{
		if($key == 'openid' || $keyword || $next_openid)
		{
			continue;
		}
		if(mb_strlen($value,'utf8')>20 && $key!='jsoncallback')//jsonp
		{
			exit('ni');
		}
	}
}
if((is_string($type) && strlen($type)>20) || strlen($action)>20 || strlen($module)>32 || strlen($parentid)>16 ||  strlen($pagestart)>16 || strlen($limit)>16 || strlen($ascdesc)>16 || strlen($where)>16 || strlen($wherevalue)>16 || strlen($next_openid)>30 || strlen($openid)>30)
{
	exit('ni');
}
/*****************************************************
 ** 菜单处理
 *****************************************************/
//商城部分
if($supplier_mode==1)
{
	$adminarray['shop']['distribution']=array_delete_value($adminarray['shop']['distribution'],'shopstore');
}
if($distribution_mode==1)
{
	$adminarray['shop']['distribution']=array_delete_value($adminarray['shop']['distribution'],'agency');
	$adminarray['shop']['finance']=array_delete_value($adminarray['shop']['finance'],'agency');
	$adminarray['shop']['shopsetting']=array_delete_value($adminarray['shop']['shopsetting'],'agency');
}
$newarray = array();
/*if($module=='site')
{
	$module = 'channel';
}
*/
if(!empty($usergroup) && min($usergroup)< 100)
{
	$newarray = showtables();
}
else
{
	$newarray = array();
}

$system_key = array_keys($system_preview);
if((in_array($module,$system_key) && $action!='category')|| $action=='share' || $action=='message')
{
	$ascdesc = isset($_GET['ascdesc'])? $_GET['ascdesc']:(isset($_POST['ascdesc'])? $_POST['ascdesc']:'DESC');
}
else
{
	$ascdesc = isset($_GET['ascdesc'])? $_GET['ascdesc']:(isset($_POST['ascdesc'])? $_POST['ascdesc']:'');
}
if(file_exists($site_engine_root.$userdir.'plugins/admin/include/settings.php'))
{
	require_once $site_engine_root.$userdir.'plugins/admin/include/settings.php';
}
if(file_exists($site_engine_root.$userdir.'plugins/admin/include/language.php'))
{
	require_once $site_engine_root.$userdir.'plugins/admin/include/language.php';
}
if($action)
{

	if(!in_array(1,$usergroup))
	{
		if($action=='password' && $module=='members')
		{
			$cando = 1;
		}
		else if($action=='menu' || $action=='omenu')
		{
			$cando = 1;
		}
		else if(in_array($module,$system_key))
		{
			$permission = checkadmin($module,$action);
			$syspermission = explode(",",$system_preview[$module]);
			if(in_array($action,$permission) || (!in_array($action,$syspermission) && in_array('edit',$permission)))
			{
				$cando = 1;
			}
			if($action=='list' && !empty($permission))
			{
				$cando = 1;
			}
			/*
			if(!empty($permission) || $action == 'pushit')
			{
				if($action =='stat' || $action == 'pushit' || $action == 'priority' || in_array($action,$permission) || (($action=='add' || $action=='list' ||$action=='view' || $action=='delete')  && in_array('edit',$permission)))
				{
					$cando = 1;
				}
			}*/
		}
		else
		{
			if(@in_array($module,$membersarray))
			{
				$cando = checkadmin('members',$action);
			}
			else if(@in_array($module,$weixinarray))
			{
				$cando = checkadmin('weixin',$module);
			}
			else if(@in_array($module,$apparray))
			{
				$cando = checkadmin('app',$module);
			}
			else if(@in_array($module,$weiboarray))
			{
				$cando = checkadmin('weibo',$module);
			}
			else if(@in_array($module,$operatorarray))
			{
				$cando = checkadmin('operations',$module);
			}
			else if(@in_array($module,$marketingarray))
			{
				$cando = checkadmin('marketing',$module);
			}
			else if(@in_array($module,$toolsarray))
			{
				$cando = checkadmin('tools',$action);
			}
			else
			{
				$cando = checkadmin($module,$action);
			}
		}
	}
	else
	{
		$cando = 1;
	}

	if($cando>=1 || !empty($cando))
	{
		if(file_exists('include/language/'.$action.'.php'))
		{
			require_once 'include/language/'.$action.'.php';
		}
		if(file_exists('include/settings/'.$action.'.php'))
		{
			require_once 'include/settings/'.$action.'.php';
		}
		if(file_exists('include/language/'.$module.'.php'))
		{
			require_once 'include/language/'.$module.'.php';
		}
		if(file_exists('include/settings/'.$module.'.php'))
		{
			require_once 'include/settings/'.$module.'.php';
		}
		if($module=='finance')
		{
			if(file_exists($site_engine_root.$userdir.'plugins/admin/include/finance.php'))
			{
				require_once $site_engine_root.$userdir.'plugins/admin/include/finance.php';
			}
			else
			{
				require_once 'include/finance.php';
			}
		}
		else if ($module=='distribution')
		{
			$moduletype=$action;
			if(file_exists($site_engine_root.$userdir.'plugins/admin/include/agency.php'))
			{
				require_once $site_engine_root.$userdir.'plugins/admin/include/agency.php';
			}
			else
			{
				require_once 'include/agency.php';
			}
		}
		else if($module=='performance')
		{
			require_once 'include/'.$module.'.php';
		}
		else if(file_exists('include/'.$action.'.php'))
		{
			require_once 'include/'.$action.'.php';
		}
		else if(file_exists('include/'.$module.'.php'))
		{
			require_once 'include/'.$module.'.php';
		}
		else
		{
			$main_dir = dirname(__FILE__ ? __FILE__ : gender('SCRIPT_FILENAME'));
			if(file_exists($site_engine_root.$userdir.'plugins/admin/include/'.$module.'.php'))
			{
				require_once $site_engine_root.$userdir.'plugins/admin/include/'.$module.'.php';
			}
			else if(file_exists($site_engine_root.$userdir.'plugins/admin/include/'.$action.'.php'))
			{
				require_once $site_engine_root.$userdir.'plugins/admin/include/'.$action.'.php';
			}
			require_once 'include/others.php';
		}

	}
	else
	{
		exit('no permission');
	}
}
/*****************************************************
** 数据处理开始
*****************************************************/
if($module == 'codemaker')
{
	$count =1;
}
if(!isset($count) && $action!='setting' && $sql && $action !='stat')
{
	if(preg_match('/select/i',$sql) && !strpos($sql,':'))
	{
		$count = $db->fetchOneBySql(countsql($sql));
	}
}
$info = '';
require_once 'include/handle.php';
?>