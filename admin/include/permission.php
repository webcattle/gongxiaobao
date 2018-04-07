<?php
/******************************************************************************************************
**  企业+ 7.0 - 企业+社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业+授权协议,查看或使用企业+的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: permission file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
if(!empty($_POST))
{
	$groupid = intval($_POST['groupid']);
	if($groupid < 1000)
	{
		// printarray($_POST);exit;
		$sql = "DELETE FROM {$tablepre}permission WHERE groupid = '$groupid'";
		$db->query($sql);

		foreach($_POST as $key=> $value)
		{
		 	if($key=='groupid')
			{
				continue;
			}
			if($value==1)
			{
				if(preg_match('/_/',$key))
				{
					$array = explode('_',$key);
					$module = $array[0];
					$act = $array[1];
				}
				$sql = "INSERT INTO {$tablepre}permission(groupid,module,act,dateline,uploader,moderate) VALUES('$groupid','$module','$act','$time','$user[linkman]',1)";
				$db->query($sql);
			}
			else
			{
				if(preg_match('/_/',$key))
				{
					$array = explode('_',$key);
					$module = $array[0];
					$act = 'censor';
					$level = str_replace($act,'',$value);
				}
				$sql = "INSERT INTO {$tablepre}permission(groupid,module,act,level,dateline,uploader,moderate) VALUES('$groupid','$module','$act','$level','$time','$user[linkman]',1)";

				$db->query($sql);
			}
		}
	}
	else
	{
		$sql = "DELETE FROM {$tablepre}permission WHERE groupid = '$groupid'";
		$db->query($sql);
		foreach($_POST as $key=> $value)
		{
		 	if($key=='groupid')
			{
				continue;
			}
			if($value==1)
			{
				$sql = "INSERT INTO {$tablepre}permission(groupid,module,act,dateline,uploader,moderate) VALUES('$groupid','$key','$act','$time','$user[linkman]',1)";
				$db->query($sql);
			}
		}
	}
	echo 1;
}
else {
	/*****************************************************
	 ** 后台用户权限
	 *****************************************************/
	if ($id == 1)
	{
		$content .= '<tr><td colspan="2">系统管理组拥有最高权限，不用设置</td></tr>';
		
		eval ("\$content = \"" . $tpl->get("permission",$template). "\";");
		echo $content;
	}
	else if($id < 1000)
	{
		$sql = "SELECT * FROM {$tablepre}permission WHERE groupid='$id'";
		$data = $db->fetchAssocArrBySql($sql);
		if(!empty($data))
		{
			foreach($data as $key=> $value)
			{
				$string = $value['module'].'_'.$value['act'];
				if($value['level'])
				{
					$$string = $value['level'];
				}
				else
				{
					$$string = 1;
				}
			}
		}
		$censorstring = '';
		foreach($system_key as $k => $v)
		{
			if(preg_match('/class/',$v) || in_array($v,array_keys($shopa))|| $v=='meeting' || $v=='groups' || $v == 'usergroup'||$v=='words')
			{
				continue;
			}
			if(in_array($v,$nopermitmodules))
			{
				continue;
			}
			
			$check = '';
			//分类权限
			if(in_array($v.'class',$system_key))
			{
				$string  = $v.'_category';
				if($$string==1)
				{
					$check  ='<label style="font-weight:normal"><input name="'.$v.'_category" type="checkbox" value=1 checked>&nbsp;类别</label>&nbsp;';
				}
				else
				{
					$check  ='<label style="font-weight:normal"><input name="'.$v.'_category" type="checkbox" value=1>&nbsp;类别</label>&nbsp;';
				}
			}
			//增加权限、编辑权限
			if(!in_array($v,$noaddmodules))
			{
				$string  = $v.'_add';
				if(isset($$string)&& $$string==1)
				{
					$check .='<label style="font-weight:normal"><input name="'.$v.'_add" type="checkbox" value=1 checked>增加</label>&nbsp;';
				}
				else
				{
					$check .='<label style="font-weight:normal"><input name="'.$v.'_add" type="checkbox" value=1>增加</label>&nbsp;';
				}
				$string  = $v.'_edit';
				if(isset($$string)&& $$string==1)
				{
					$check .='<label style="font-weight:normal"><input name="'.$v.'_edit" type="checkbox" value=1 checked>编辑</label>&nbsp;';
				}
				else
				{
					$check .='<label style="font-weight:normal"><input name="'.$v.'_edit" type="checkbox" value=1>编辑</label>&nbsp;';
				}
				$string  = $v.'_delete';
				if(isset($$string)&& $$string==1)
				{
					$check .='<label style="font-weight:normal"><input name="'.$v.'_delete" type="checkbox" value=1 checked>删除</label>&nbsp;';
				}
				else
				{
					$check .='<label style="font-weight:normal"><input name="'.$v.'_delete" type="checkbox" value=1>删除</label>&nbsp;';
				}
			}
			//审批权限
			$variable = $v.'sys';
			$variable = $$variable;
			if($variable['censorlevel'])
			{
				$sql = "SELECT level FROM {$tablepre}permission WHERE module='$key' AND act='censor'";
				$level = $db->fetchOneBySql($sql);
				$censorlevel = $variable['censorlevel'];
				if(intval($censorlevel) > 1)
				{

					for($i=1;$i<= $censorlevel;$i++)
					{
						$string  = $v.'_censor';
						$name = $v.'censor';
						$_title = $i.'审';
						if($i==$censorlevel)
						{
							$_title = '终审';
						}
						if(isset($$string) && $$string==$i)
						{
							$check .='<label style="font-weight:normal"><input name="'.$string.'" type="checkbox" value="censor'.$i.'" checked>'.$_title.'</label>&nbsp; ';
						}
						else
						{
							$check .='<label style="font-weight:normal"><input name="'.$string.'" type="checkbox" value="censor'.$i.'">'.$_title.'</label>&nbsp; ';
						}
					}
					// echo $newscensor;
				}
			}
			if($system_preview[$v])
			{
				$previews = explode(",",$system_preview[$v]);
				$haveset = array('category','add','edit','list','censor');
				foreach($previews as $_preview)
				{
					if(!in_array($_preview,$haveset))
					{
						$string  = $v.'_'.$_preview;
						if($$string==1)
						{
							$check  .='<label style="font-weight:normal"><input name="'.$v.'_'.$_preview.'" type="checkbox" value=1 checked>&nbsp;'.$l[$_preview].'</label>&nbsp;';
						}
						else
						{
							$check  .='<label style="font-weight:normal"><input name="'.$v.'_'.$_preview.'" type="checkbox" value=1>&nbsp;'.$l[$_preview].'</label>&nbsp;';
						}
					}
				}
			}
			if($check)
			{
				if($variable['name'])
				{
					$l[$v] = $variable['name'];
				}
				$content .= '<tr><td style="width:20%;"><i class="fa fa-'.$v.'"></i>&nbsp;'.$l[$v].'</td><td>'.$check.'</td></tr>';
			}
		}

		foreach($shopa as $key => $value)
		{
			$check='';
			foreach($value as $k=>$v)
			{
				$string  = $key.'_'.$v;
				if($$string==1)
				{
				 	$check .='<label style="font-weight:normal"><input name="'.$key.'_'.$v.'" type="checkbox" value=1 checked>'.$l[$v].'</label>&nbsp;';
				}
				else
				{
				 	$check .='<label style="font-weight:normal"><input name="'.$key.'_'.$v.'" type="checkbox" value=1>'.$l[$v].'</label>&nbsp;';
				}
			}
			$content .= '<tr><td style="width:20%;"><i class="fa fa-'.$key.'"></i>&nbsp;'.$l[$key].'</td><td>'.$check.'</td></tr>';
		}
		eval ("\$content = \"" . $tpl->get("permission",$template). "\";");
		echo $content;
	}
	/*****************************************************
	** 前台用户权限
	*****************************************************/
	else
	{
		$sql = "SELECT * FROM {$tablepre}permission WHERE groupid='$id'";
		$data = $db->fetchAssocArrBySql($sql);
		if(!empty($data))
		{
			foreach($data as $key=> $value)
			{
				$string = $value['module'];
				$$string = 1;
			}
		}
		$censorstring = '';
		foreach ($frontuserarray as $key=> $value)
		{
			$check = $string = '';
			$string  = $value;
			if($$string==1)
			{
			 	$check .='<label style="font-weight:normal"><input name="'.$value.'" type="checkbox" value=1 checked>'.$l[$value].'</label>&nbsp;';
			}
			else
			{
			 	$check .='<label style="font-weight:normal"><input name="'.$value.'" type="checkbox" value=1>'.$l[$value].'</label>&nbsp;';
			}
			$content .= '<tr><td style="width:20%;"><i class="fa fa-'.$key.'"></i>&nbsp;'.$l[$value].'</td><td>'.$check.'</td></tr>';
		}
		eval ("\$content = \"" . $tpl->get("permission",$template). "\";");
		echo $content;
	}
}
exit;;