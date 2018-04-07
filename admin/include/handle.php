<?php
/******************************************************************************************************
**  企业+ 5.0 - 企业+社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业+授权协议,查看或使用企业+的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: handle file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
if($module == 'members')
{
	$sql = "SELECT id FROM {$tablepre}kfaccount WHERE uid='$uid'";
	$kfid = $db->fetchOneBySql($sql);
}
// 代码生成器模块判断
$variable = $module.'sys';
$modulesystem = $variable = $$variable;
$chararray = explode(',',$variable['char_setting']);
$listarray = explode(',',$variable['list_setting']);
$typearray = explode(',',$variable['type_setting']);

if($action=='stat'&&$module!='agency')
{
 	eval ("\$content = \"" . $tpl->get($action,$template). "\";");
 	echo $content;
}
else if($action =='setting' ||  $action == 'eventhongbao')
{
	$module = ($module=='channel')? 'site':$module;
	$header = '<th colspan=2 style="text-align:center;">项目</th>';
	$info = buildsettingform($module,$id);
	eval ("\$content = \"" . $tpl->get($action,$template). "\";");
 	exitandreturn($content,$module,$action);
}
else if($action == 'add')
{
	if($module=='sense')
	{
		eval ("\$content = \"" . $tpl->get('add_sense',$template). "\";");
	}
	else
	{
		if($module == 'devices')
		{
			$chararray = array('quantity','apply_reason','comment','poi_id');
		}
		$header = '<th colspan=4 style="text-align:center;">项目</th>';
		if(in_array('content',$chararray))
		{
			$editor_color=file_get_contents($site_engine_root.'data/config/editor_color.js');
			eval ("\$editor = \"" . $tpl->get('editor',$template). "\";");
		}
		$chararray = array_filter($chararray);
		if(empty($chararray))
		{
			$variable = $module.'sys';
			$variable = $$variable;
			$chararray = explode(',',$variable['char_setting']);
		}
		$info = buildeditbycodemaker($module,0);
		eval ("\$ajaxtemplates = \"" . $tpl->get('ajaxtemplates',$template). "\";");
		eval ("\$content = \"" . $tpl->get($action,$template). "\";");
	}
 	exitandreturn($content,$module,$action);
}
else if($action == 'edit')
{
	$id = $_GET['id'] ? $_GET['id'] : $_GET['uid'];
	$header = '<th colspan=4 style="text-align:center;">项目</th>';
	if($module=='sense')
	{
		eval ("\$content = \"" . $tpl->get('editsense',$template). "\";");
	}
	else
	{
		if($module=='settings')
		{
			$chararray = array('content');
		}
		if(in_array('content',$chararray))
		{
			$editor_color=file_get_contents($site_engine_root.'data/config/editor_color.js');
		 	eval ("\$editor = \"" . $tpl->get('editor',$template). "\";");
		}
		if(empty($chararray))
		{
			$chararray = array('title','photo','listing','shares','visitor','subscribes','stat');
		}
	 	//$info = buildeditform($module,$id,$chararray,$dataarray);
	 	$info = buildeditbycodemaker($module,$id);
		eval ("\$ajaxtemplates = \"" . $tpl->get('ajaxtemplates',$template). "\";");
	 	eval ("\$content = \"" . $tpl->get($action,$template). "\";");
	}
 	exitandreturn($content,$module,$action);
}
else if($action == 'orders')
{
	$header = '<th colspan=4 style="text-align:center;">订单</th>';
	eval ("\$content = \"" . $tpl->get($action,$template). "\";");
 	exitandreturn($content,$module,$action);
}
else if ($action == 'agency' || $action=='shopstore')
{
	if($module=='distribution')
	{
		eval ("\$content = \"" . $tpl->get('agency',$template). "\";");
	}
	else
	{
		eval ("\$content = \"" . $tpl->get('finance_'.$action,$template). "\";");
	}
 	exitandreturn($content,$module,$action);
}
else if($action == 'moneyflow' || $action=='finance')
{
	eval ("\$content = \"" . $tpl->get('finance_'.$action,$template). "\";");
	exitandreturn($content,$module,$action);
}
// 有自己的模版
else if(in_array($action,$selftemplatearray))
{
	eval ("\$content = \"" . $tpl->get($action,$template). "\";");
 	exitandreturn($content,$module,$action);
}
else if($action == 'menu')
{
	exit;
}
else if($action == 'dataselect')
{
	eval ("\$content = \"" . $tpl->get('moduleselect',$template). "\";");
 	exitandreturn($content,$module,$action);
}
else
{

	if(empty($a[$module]['censor']))
	{
		$a[$module]['censor']		= array('censor','edit','delete');
	}
	if($module=='codemaker')
	{
		$variable = array();
		$variable['char_setting'] = 'title,name,count';
		$variable['list_setting'] = '1,1,1';
		$variable['type_setting'] = '1,1,1';
	}
	else if($module=='crowdfund' && $action=='myusers')
	{
		$variable = 'crowdfundusersys';
		$variable = $$variable;
		$_module = 'crowdfunduser';
	}
	else if($module=='unsubscribe')
	{
		$variable = 'memberssys';
		$variable = $$variable;
		$_module = 'members';
	}
	else
	{
		$variable = $module.'sys';
		$variable = $$variable;
		$_module = $module;
	}
	// printarray($variable);
	$chararray = explode(',',$variable['char_setting']);
	$listarray = explode(',',$variable['list_setting']);
	$typearray = explode(',',$variable['type_setting']);
	$namearray = explode(',',$variable['name_setting']);

	$censorlevel = $variable['censorlevel'] ? $variable['censorlevel'] :0;

	if($action == 'groups')
	{
		$chararray = array_merge($chararray,array('usergroup'));
		$typearray = array_merge($typearray,array('1'));
		$listarray = array_merge($listarray,array('1'));
	}
	$fieldsarray = array();

	/*if($addtional[$module][$action])
	{
		$chararray = array_merge($chararray,$addtional[$module][$action]);
		foreach($addtional[$module][$action] as $k=>$v)
		{
			$newlistarray[] = 1;
		}
		$listarray = array_merge($listarray,$newlistarray);
	}
	*/
	$sql = "SELECT skey,value,fields FROM {$tablepre}fieldslist WHERE module='$_module'";
	$modulefields = $db->fetchAssocArrBySql($sql);
	if(!empty($modulefields))
	{
		foreach($modulefields as $k=> $v)
		{
			if(!empty($v['value']))
			{
				$fieldsarray[$v['fields']][$v['skey']] = $v['value'];
			}
		}
	}

	if(empty($char))
	{
		if(preg_match('/class/',$module))
		{
			//$char = array('title','photo','listing','shares','visitor','subscribes','stat');
			if(empty($a[$module]['category']))
			{
				$a[$module]['category']		= array('edit','delete','up','down');
			}
		}
		if(!empty($listarray) && count($listarray)>=2)
		{
			$name = array();
			$type = array();
			foreach($listarray as $key=> $value)
			{
				if($value==1)
				{
					$char[] = $chararray[$key];
					$name[] = $namearray[$key];
				}
			}
		}
		else
		{
			$char = $commonlist[$action];
		}

		if(empty($char))
		{
			$variable = $module.'sys';
			$variable = $$variable;
			$chararray = explode(',',$variable['char_setting']);

			if(!empty($chararray))
			{
			 	$char = $chararray;
			 	$char = array_delete_value($char,'id');
			}
		}
	}
	else
	{
		$name = array();
	}

	if(empty($char))
	{
		echo 'empty char array, pls modify char.php ';
		// $char = array('linkman','dateline');
	}

	if($meetingid)
	{
		$sql = "SELECT censorpush FROM {$tablepre}meeting WHERE id='$meetingid'";
		$censorpush = $db->fetchOneBySql($sql);
		if($censorpush==1)
		{
			$char = array_merge($char,array('censor_push'));
		}
		$char = array_delete_value($char,'reply');
		$char = array_delete_value($char,'replyer');
		$char = array_delete_value($char,'replydateline');
	}

	//搜索

	/*****************************************************
	** 组织字段
	*****************************************************/
	if(preg_match('/class/',$module))
	{
		$isclass = 1;
		$_module = str_replace('class','',$module);
	}
	/*****************************************************
	** 动作位置
	*****************************************************/
	if($action == 'search' && ($search=='bd' || $search=='wb'|| $search=='wx'))
	{
		$a[$module][$action] = array();
	}
	if($variable['show']==1 && !$a[$module][$action])
	{
		$a[$module][$action] = array('edit','delete');
	}
	if(!empty($a[$module][$action]))
	{
		$newchar = $newname = $newtype = array();
		foreach($char as $key=> $value)
		{
			if($key==0)
			{
				$newchar[] = $value;
				$newname[] = $name[$key];

				$newchar[] = 'action';
				$newname[] = '操作';

				if($addtional[$module][$action])
				{
					foreach($addtional[$module][$action] as $k => $v)
					{
						$newchar[] = $k;
						$newname[] = $v;
					}
					//$listarray = array_merge($listarray,$newlistarray);
				}
				if($action=='category' && $module!='channel')
				{
					$newchar[] = 'listing';
					$newname[] = '列表';
				}
			}
			else
			{
				$newchar[] = $value;
				$newname[] = $name[$key];
			}
		}
		if($action=='category')
		{
			$newchar[] = 'stat';
			$newname[] = '统计';
		}
		$char = $newchar;
		$name = $newname;
	}
	else
	{
		$newchar = $newname = $newtype = array();
		foreach($char as $key=> $value)
		{
			if($key==0)
			{
				$newchar[] = $value;
				$newname[] = $name[$key];

				if($addtional[$module][$action])
				{
					foreach($addtional[$module][$action] as $k => $v)
					{
						$newchar[] = $k;
						$newname[] = $v;
					}
					//$listarray = array_merge($listarray,$newlistarray);
				}
				if($action=='category' && $module!='channel')
				{
					$newchar[] = 'listing';
					$newname[] = '列表';
				}
			}
			else
			{
				$newchar[] = $value;
				$newname[] = $name[$key];
			}
		}
		if($action=='category')
		{
			$newchar[] = 'stat';
			$newname[] = '统计';
		}
		$char = $newchar;
		$name = $newname;
	}

	foreach($char as $key=> $value)
	{
		/*****************************************************
		** 不能点击的表头字段 10/29/2014 12:21:14 AM
		*****************************************************/
		if($name[$key])
		{
			$_title = $name[$key];
		}
		else
		{
			$_title = $l[$value];
		}

		if(!in_array($value,$noclickarray))
		{
			$header .= '<th style="text-align:center;white-space:nowrap;" class="sort up" row="'.$value.'">'.$_title.'<i class="icon"></i></th>';
		}
		else
		{
			$header .= '<th style="text-align:center;white-space:nowrap;">'.$_title.'</th>';
		}

	}
	$charcount = count($char);
	$footer =  '<td style="white-space:nowrap;text-align:left;" colspan='.$charcount.'>共'.$count.'项';
	if($summoney)
	{
		$footer .= '，合计'.$summoney.'元';
	}
	$footer .='</td>';
	/*****************************************************
	** 数据循环
	*****************************************************/
	if(!empty($data))
	{
		if($newui==1)
		{
			require_once 'mobile.php';
		}
		else
		{
			require_once 'site.php';

		}
	}
	else
	{
		$charcount = count($char);
		if($module=='usergroup')
		{
			$info =  '<td style="white-space:nowrap;text-align:center;" colspan='.$charcount.'><i class="fa fa-users"></i>&nbsp;<a href="javascript:;" class="adduser">添加用户</a></td>';
		}
		else
		{
			$info =  '<td style="white-space:nowrap;text-align:center;" colspan='.$charcount.'>没有符合条件的信息</td>';
		}
	}
	if($module!='recyclebin')
	{
		$pageinfo = page($page,$count,$limit,5);
	}

	if(file_exists('templates/'.$module.'_'.$action.'.html'))
	{
		eval ("\$content = \"" . $tpl->get($module.'_'.$action,$template). "\";");
	}
	else
	{
		eval ("\$content = \"" . $tpl->get("list",$template). "\";");
	}
 	exitandreturn($content,$module,$action);
}