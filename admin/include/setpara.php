<?php
/******************************************************************************************************
**  企业Plus 7.0 - 企业Plus社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业Plus授权协议,查看或使用企业Plus的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: set template para
** Author.......: Winston Dang
** Version......: 5.0.0
** Last changed.: 2015/12/11 14:31:39
***************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');

include_once $site_engine_root.'mobile/ajax/settings.php';
include_once 'language/templates.php';

$sql = "select * from {$tablepre}templates where id='$id'";
$tplinfo = $db->fetchSingleAssocBySql($sql);
$sql = "select * from {$tablepre}operator where id='$tplinfo[operatorid]'";
$operatorinfo = $db->fetchSingleAssocBySql($sql);

$tpltype = $tplinfo['templatetype'];
function showeditfield($type,$name,$value)
{
	global $uploaddir;

	$text = '';
	if($type=='text')
	{
		$text = '<input type="text" name="'.$name.'" value="'.$value.'" class="form-control" setname="'.$name.'"/>';
	}
	else if($type == 'file')
	{
		if(empty($value))
		{
			$text = '<div class="inputfile"><input type="hidden" name="'.$name.'" value="'.$value.'" setname="'.$name.'"></div>';
		}
		else
		{
			$text = '<div class="inputfile active"><div class="txt">'.$value.'</div><input type="hidden" name="'.$name.'" value="'.$value.'" setname="'.$name.'"></div>';
		}
	}
	else if($type=='image')
	{
		$addfile = '<input type="hidden" name="'.$name.'"  id="add_'.$name.'" value="'.$value.'">';
		$addtc = '';
		if($name=='background')
		{
			$addtc = ' tcss="background-image" ';
		}
		if(preg_match('/http/',$value))
		{
			$text = '<div style="margin-top:5px;"><img setname="'.$name.'" src="'.$value.'" style="width:116px;"'.$addtc.'></div>'.$addfile;
		}
		else
		{
			if(!empty($value))
			{
				if(substr($value,0,1)=='/')
				{
					$img = $value;
				}
				else
				{
					$img = $uploaddir.$value;
				}
				$text = '<div style="margin-top:5px;"><img setname="'.$name.'" src="'.$img.'" style="width:116px;"'.$addtc.'></div>'.$addfile;
			}
			else
			{
				$text = $addfile;
			}
		}
	}
	else if(substr($type,0,8)=='fontsize')
	{
		$tmp = explode("||",$type);
		$text = '<input type="text" name="'.$name.'" value="'.$value.'" size="5" tcss="font-size" setname="'.$tmp[1].'"/>px';
	}
	else if(substr($type,0,5)=='color')
	{
		$tmp = explode("||",$type);
		$text = '<input type="text" name="'.$name.'" value="'.$value.'" class="form-control color" tcss="color" setname="'.$tmp[1].'"/>';
	}
	else if($type=='yesorno')
	{
		$text = '<select name="'.$name.'"  id="'.$name.'"  style="width:100%;">';
		if(intval($value)==1)
		{
			$text .= '<option value="1" SELECTED>是</option>';
			$text .= '<option value="0">否</option>';
		}
		else
		{
			$text .= '<option value="1">是</option>';
			$text .= '<option value="0" SELECTED>否</option>';
		}
		$text .= '</select>';
	}
	return $text;
}
$tpltype = $tplinfo['templatetype'];



if(!$tplinfo['basictpl'])
{
	$pages = $templates[$tpltype]['default'];
	$tplname = 'default_'.$tpltype;
}
else
{
	$pages = $templates[$tpltype][$tplinfo['basictpl']];
	$tplname = $tplinfo['basictpl'].'_'.$tpltype;
}
if($tplinfo['paras'])
{
	$paras = json_decode($tplinfo['paras'],true);
}
$tplname = 'operatoradmin/'.$tplname;
$content = $operatorinfo['content'];
$setparaleft = '<ul>';
foreach($pages as $k => $_page)
{
	$editchars = $_page['edit_char'];
	$html = '';
	$data = array();
	foreach($editchars as $key => $value)
	{
		$charval = '';
		if($paras && isset($paras[$k][$key]))
		{
			$charval = $paras[$k][$key];
		}
		else
		{
			if(!$tplinfo['basictpl'])
			{
				$_tpl = 'default';
			}
			else
			{
				$_tpl = $tplinfo['basictpl'];
			}
			$charval = $templates[$tpltype][$_tpl][$k]['default_para'][$key];
		}
		
		if($key=='title'&&!$charval)
		{
			$charval = $operatorinfo['title'];
		}
		if($value=='image' && substr($charval,0,1) != '/' && substr($charval,0,4) != 'http')
		{
			$charval = $uploaddir.$charval;
		}
		$data[$key] = $charval;
		//echo $key.'='.$charval.'<br/>';

		$html .= '<div class="form-group">';
		$html .= '	<div>'.$tl[$key].'</div>';
		$html .= showeditfield($value,$key,$charval);
		$html .= '</div>';
	}
	$setparaleft .= '<li index="$k">';
	eval ("\$setparaleft .= \"" . $tpl->get($tplname,'mobile'). "\";");
	$setparaleft .= '</li>';
	eval ("\$setpararight .= \"" . $tpl->get('setpararight','admin'). "\";");
}
$setparaleft .= '</ul>';
?>