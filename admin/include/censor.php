<?php
/******************************************************************************************************
**  企业+ 7.0 - 企业+社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业+授权协议,查看或使用企业+的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
$alertinfo = $alert_info[$module][$action];
if(!empty($_POST))
{
	if ($module=='shopstore')
	{
		$agencyflag =1;
		$module='agency';
	}
	$sql = "SELECT moderate FROM {$tablepre}$module WHERE id='$id'";
	$moderate = $db->fetchOneBySql($sql);
	/*if(!empty($usergroup))
	{
		if(in_array(1,$usergroup))
		{
			$level = 1;
		}
		else
		{
			foreach($usergroup as $key=> $value)
			{
				$sql = "SELECT level FROM {$tablepre}permission WHERE act='censor' AND module='$module' AND groupid='$value'";
				$level = $db->fetchOneBySql($sql);
			}
		}
	}
	if($level == 0)
	{
		$level=1;
	}
	if(isset($level) && $level>=2)
	{
		$variable = $module.'sys';
		$variable = $$variable;
		$censorlevel = $variable['censorlevel'];
		$level = censorlevel($censorlevel,$level)-1;
	}*/
	if($module=='agency' && $censortype=='v')
	{
		$invitecode = random(6,1);
		$sql = "insert into {$tablepre}invitecode(code,fromid,dateline,uploader) values('$invitecode','$id','".time()."','".$user['uid']."')";
		$db->query($sql);

		$sql="update {$tablepre}agency set vtype='$moderate' where id='$id'";
		$db->query($sql);
	}
	else
	{
		if($moderate>1 || $moderate==0)
		{
			$sql = "select * from {$tablepre}{$module} where id='$id'";
			$moduleinfo = $db->fetchSingleAssocBySql($sql);
			
			$variable = $module.'sys';
			$variable = $$variable;
			$censorlevel = $variable['censorlevel'];
			$level = $censorlevel+2-$moderate;
			if($censorlevel==1)
			{
				$level = 0;
			}
			if($moderate>1)
			{
				$newmoderate=$moderate-1;
			}
			else
			{
				$newmoderate = 1;
			}
			$sql = "UPDATE {$tablepre}$module SET moderate='$newmoderate' WHERE id='$id'";
			$db->query($sql);
			$sql = "insert into {$tablepre}censorlogs(title,linkmodule,linkmoduleid,level,content,isagree,media_id,dateline,moderate,uploader) ";
			$sql .= "values('".addslashes($moduleinfo['title'])."','$module','$id','".$level."','','1','','".time()."','1','".$user['uid']."')";
			$db->query($sql);
			$logid = $db->insertId();
			
			notifytocensor($module,$id,0,$logid);
			
			/*$sql = "SELECT title,uploader FROM {$tablepre}$module WHERE id ='$id'";
			$info = $db->fetchSingleAssocBySql($sql);
			$result = searchcensoruser($module,$info['title'],$moderate,'censored',$info['uploader']);
			if($agencyflag ==1)
			{

			}*/
			
			if($newmoderate==1 && $module=='news')//同步到发现
			{
				if(@in_array($module,$system_key))
				{
					$sql ="SELECT * from {$tablepre}$module where id=$id";
					$sqlarr=$db->fetchSingleAssocBySql($sql);
					$title=$sqlarr['title'];
					$photoarr=explode(",",$sqlarr['photo']);
					$photo=$photoarr[0];
					if($module=='news')
					{
						$sql ="SELECT summary,content from {$tablepre}{$module}content where id=$id";
						$sqlarr=$db->fetchSingleAssocBySql($sql);
					}
					if(!empty($sqlarr['summary']))
					{
						$content=csubstr(strip_tags($sqlarr['summary']),0,100);
					}
					else
					{
						$content=csubstr(strip_tags($sqlarr['content']),0,100);
					}
				}
				else
				{
					$sql ="SELECT title,photo,content from {$tablepre}$module where id=$id";
					$sqlarr=$db->fetchSingleAssocBySql($sql);
					$title=$sqlarr['title'];
					$photo=$sqlarr['photo'];
					$content=csubstr(strip_tags($sqlarr['content']),0,100);
				}
			}
			$rdb->delRow($module,$id);
			
		}
		else
		{
			exitandreturn(0,$module,$action);
		}
	}
	exitandreturn(1,$module,$action);
}
else
{
	if($id)
	{
		$header = '<th colspan=4 style="text-align:center;">项目</th>';
		$data = '';
		if ($module=='shopstore')
		{
			$sql = "SELECT * FROM {$tablepre}agency WHERE 1 {$attasql} AND id='$id'";
		}
		else
		{
			$sql = "SELECT * FROM {$tablepre}$module WHERE 1 {$attasql} AND id='$id'";
		}

		$data = $db->fetchSingleAssocBySql($sql);
		if($module == 'news')
		{
			$data['content'] = $db->fetchOneBySql("SELECT content FROM {$tablepre}newscontent WHERE id='$id'");
		}
		if($module == 'agency' || $module=='shopstore')
		{
			$code = '';
			if($data['areacode'])
			{
				$code = codetoarea($data['areacode']);
			}
			$data['address'] = $code.$data['address'];
		}
		
		$lang = '';
		$variable = $module.'sys';
		$variable = $$variable;
		$chars = explode(',',$variable['char_setting']);
		$name = explode(',',$variable['name_setting']);
		$type = explode(',',$variable['type_setting']);
		$add =  explode(',',$variable['add_setting']);
		$required = explode(',',$variable['required_setting']);
		$view = explode(',',$variable['view_setting']);
		$chararray = explode(',',$variable['char_setting']);
		$censorlevel = $variable['censorlevel'];
		$thislevel = $data['moderate'] - $censorlevel;
		if($censorlevel==1)
		{
			$thislevel = 0;
		}
		$header = '<tr style="white-space:nowrap"><td colspan=4 style="text-align:center;"><span class="editable">'.$data['title'].'</span></tr>';
		$table = '';
		$attachslist = '';
		$array = explode(',',$string);
		$i = 0;
		if($module=='agency' && $censortype=='v')
		{
			$sql = "select distinct orderid from {$tablepre}revenue where uid='$data[uid]' and identity='agency'";
			$orders = $db->fetchColBySql($sql);
			$orderscount = count($orders);
			$sql = "select visitor from {$tablepre}members where uid='$data[uid]'";
			$yingxiangli = $db->fetchOneBySql($sql);

			$array[] = 'orderscount';
			$data['orderscount'] = $orderscount;
			$array[] = 'yingxiangli';
			$data['yingxiangli'] = $yingxiangli;
		}
		foreach($chararray as $key=> $value)
		{
			
			if($view[$key]==0)
			{
				continue;
			}
			if($value == 'classid')
			{
				$sql = "SELECT title FROM {$tablepre}{$module}class WHERE id='$data[classid]'";
				$data[$value] = $db->fetchOneBySql($sql);
			}
			if($module=='news')
			{
				if($type[$key]==6)
				{
					$attachslist .=  '<tr><td style="width:20%">'.$name[$key].'</td><td colspan="3"><span class="editable">'.showitem($module,$data[$value],$value,$type[$key]).'</span></td>';
					
				}
			}
			if($agenttype=='site')
			{
				if($value=='content')
				{
					$data['content'] = str_replace('\\&quot;',"",$data['content']);
					$data['content'] = str_replace("\\\\","",$data['content']);
					$data['content'] = stripslashes($data['content']);
					$table .=  '<tr><td colspan=4 style="text-align:center;white-space:normal;"><span class="editable">'.$data['content'].'</span></tr>';
				}
				else if($i==0)
				{
					$table .=  '<tr><td style="width:20%">'.$name[$key].'</td><td style="width:30%;overflow:hidden;"><span class="editable">'.showitem($module,$data[$value],$value,$type[$key]).'</span></td>';
					$i =1;
				}
				else if($i==1)
				{
					$table .=  '<td style="width:20%">'.$name[$key].'</td><td style="width:30%;overflow:hidden;"><span class="editable">'.showitem($module,$data[$value],$value,$type[$key]).'</span></td></tr>';
					$i=0;
				}
			}
			else
			{
				if($value=='content')
				{
					$data['content'] = str_replace('\\&quot;',"",$data['content']);
					$data['content'] = str_replace("\\\\","",$data['content']);
					$data['content'] = stripslashes($data['content']);
					$table .=  '<tr><td colspan=2 style="text-align:center;white-space:normal;"><span class="editable">'.$data['content'].'</span></tr>';
				}
				else
				{
					$table .=  '<tr><td style="width:20%">'.$name[$key].'</td><td style="width:30%;overflow:hidden;"><span class="editable">'.showitem($module,$data[$value],$value,$type[$key]).'</span></td></tr>';
				}
			}
		}
		
		eval ("\$previewnews .= \"" . $tpl->get("previewnews",$template). "\";");
		eval ("\$content = \"" . $tpl->get($action,$template). "\";");
 		exitandreturn($content,$module,$action);
	}
	else
	{
		$data = $moderatesql = '';
		// $usergroup = array(10);
		//$usergroup = array(15,16);
		
		if(in_array(1,$usergroup))
		{
			$moderatesql = ' AND moderate!=-1';
		}
		else
		{
			if(!empty($usergroup))
			{
				$levelarray = array();
				foreach($usergroup as $key=> $value)
				{
					$sql = "SELECT level FROM {$tablepre}permission WHERE act='censor' AND module='$module' AND groupid='$value'";
					$level = $db->fetchOneBySql($sql);
					if(!in_array($level,$levelarray))
					{
						$levelarray[] = $level;
					}
				}
			}
			$variable = $module.'sys';
			$variable = $$variable;
			$censorlevel = $variable['censorlevel'];
			if($levelarray)
			{
				$moderatesql = " AND (";
				$prefix = '';
				foreach($levelarray as $_level)
				{
					//$level = censorlevel($censorlevel,$level);
					if($_level==0)
					{
						$moderate=2;
					}
					else
					{
						$moderate = $censorlevel+2-$_level;
					}
					$moderatesql .= $prefix." moderate = '$moderate'";
					$prefix = ' or ';
				}
				$moderatesql .= ")";
			}
		}
		if($classid)
		{
			$sql = "SELECT * FROM {$tablepre}$module WHERE 1 AND moderate!=1  {$moderatesql} AND classid='$classid' ORDER BY $orderby $ascdesc LIMIT $pagestart,$limit";
		}
		else
		{
			$sql = "SELECT * FROM {$tablepre}$module WHERE 1  AND moderate!=1 {$moderatesql} ORDER BY $orderby $ascdesc LIMIT $pagestart,$limit";
		}
		$data = $db->fetchAssocArrBySql($sql);
	}
}