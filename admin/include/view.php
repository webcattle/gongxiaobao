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
$alertinfo = $alert_info[$module][$action];
$variable = $module.'sys';
$variable = $$variable;
$chars = explode(',',$variable['char_setting']);
$name = explode(',',$variable['name_setting']);
$type = explode(',',$variable['type_setting']);
$add =  explode(',',$variable['add_setting']);
$required = explode(',',$variable['required_setting']);
$view = explode(',',$variable['view_setting']);
$data = array();
$module = $module!='weixin' ? $module : 'channel';
if((in_array($module,$system_key) && $module!='members' && $module!='push') || $module=='channel')
{
	$data = '';
	if($module=='news')
	{
		$sql = "SELECT * FROM {$tablepre}$module n LEFT JOIN {$tablepre}newscontent c ON n.id=c.id WHERE n.id='$id'";
	}
	else
	{
		$sql = "SELECT * FROM {$tablepre}$module WHERE 1 {$attasql} AND id='$id'";
	}
	$data = $db->fetchSingleAssocBySql($sql);
	// $data = parsemodule($data);
	$lang = '';
	//$string = 'content,classid,views,dateline,origin,vicetitle,photo,shares,visitor,subscribes';
	$header = '<tr style="white-space:nowrap"><td colspan=4 style="text-align:center;"><span class="editable">'.$data['title'].'</span></tr>';
	$table = '';
	$array = explode(',',$string);
	$i = 0;
	$variable = $module.'sys';
	$variable = $$variable;
	$chararray = array_unique(explode(',',$variable['char_setting']));
	$name = explode(',',$variable['name_setting']);
	$havecontent = 0;

	foreach($chararray as $key=> $value)
	{
		if($view[$key]==0)
		{
			continue;
		}
		if(in_array('classid',$chararray))
		{
			if($value == 'classid' && $module!='channel')
			{
				$sql = "SELECT title FROM {$tablepre}{$module}class WHERE id='$data[classid]'";
				$data[$value] = $db->fetchOneBySql($sql);
			}
		}
		if($type[$key]==10)
		{
			$havecontent = 1;
			continue;
		}
		if($agenttype=='site')
		{
			if($i==0)
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
				if($module=="news")
				{
					eval ("\$previewnews .= \"" . $tpl->get("previewnews",$template). "\";");
					$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;"><span class="editable">'.$previewnews.'</span></tr>';
				}
				else
				{
					$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;"><span class="editable">'.stripslashes($data['content']).'</span></tr>';
				}
			}
			else
			{
				$table .=  '<tr><td style="width:20%">'.$name[$key].'</td><td style="width:30%;overflow:hidden;"><span class="editable">'.showitem($module,$data[$value],$value,10).'</span></td></tr>';
			}
		}
	}
	if($havecontent)
	{
		if($module=="news")
		{
			eval ("\$previewnews .= \"" . $tpl->get("previewnews",$template). "\";");
			$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;"><span class="editable">'.$previewnews.'</span></tr>';
		}
		else if($module=="report")
		{
			$report_data = $data;
			//内容
			if(empty($data['content']))
			{
				$titles = json_decode($report_data['titles'],true);
				$contents = json_decode($report_data['contents'],true);
				$photoArray = json_decode($report_data['photos'],true);
				$videoArray = json_decode($report_data['videos'],true);
				$datacontent_temp = '';
				foreach($titles as $k=>$v)
				{
					$title1 = stripslashes($v);
//					$content1 = $contents[$k];
					$content1 = nl2br(str_replace(' ', '&nbsp;', $contents[$k]));
					$photo1 = explode(',',$photoArray[$k]);
					$imgStr = '';
					foreach($photo1 as $vv)
					{
						if(!empty($vv))
						{
							$imgStr .=  "<div class='imgouter'><img src='{$uploaddir}$vv'/></div>";
						}
					}
					$video1 = $videoArray[$k];
					if($video1)
					{
						$video_filePath = $site_engine_root.$uploaddir.$video1;
						$img=explode('.',$video1)[0].'.jpg';
						$videoStr .=  "<div class='videoouter'><video src='{$uploaddir}$video1' poster='$weixin_qrcode' controls=\"controls\"/></div>";
					}
					eval ("\$datacontent_temp = \"" . $tpl->get('report_view_item', 'mobile') . "\";");
					$report_content .= $datacontent_temp;
				}
			}
			eval ("\$previewreport .= \"" . $tpl->get("previewreport",$template). "\";");
			$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;"><span class="editable">'.$previewreport.'</span></tr>';
		}
		else
		{
			$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;"><span class="editable">'.stripslashes($data['content']).'</span></tr>';
		}
	}

	$sql = "SELECT uid FROM {$tablepre}views WHERE module='$module' AND  moduleid='$id' ORDER BY id DESC LIMIT 500";
	$views = $db->fetchColBySql($sql);
	if(!empty($views))
	{
		$ids = '\''.implode('\',\'', $views).'\'';
		$sql = "SELECT uid,linkman FROM {$tablepre}members WHERE uid in (".$ids.") AND subscribe=1"; //
		$userinfo = $db->fetchAssocArrBySql($sql);
		if(!empty($userinfo))
		{
			foreach($userinfo as $key=> $value)
			{
				$viewmembers .= '<a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\')"><img src="'.$uploaddir.'/'.avatar($value['uid']).'" class="img-circle" style="width:30px;">'.$value['linkman'].'</a>';
			}
		}
	}
	$chart_html=chart_module_html('count',$data['title'],'1','sum','c_d',$datestyle,$datenum,$module,$id);

}
else if($module =='members')
{
	if(empty($uid) && !empty($id))
	{
		$uid = $id;
	}
	$data = '';
	$sql = "SELECT * FROM {$tablepre}$module WHERE uid='$uid'";
	$data = $db->fetchSingleAssocBySql($sql);

	$ownid = $data['ownid'];
	if($data['ownid'])
	{
		$sql = "SELECT linkman FROM {$tablepre}members WHERE uid='$data[ownid]'";
		$data['ownid'] = $db->fetchOneBySql($sql);
	}
	if($data['uploader'])
	{
		$sql = "SELECT linkman FROM {$tablepre}members WHERE uid='$data[uploader]'";
		$uploader = $data['uploader'];
		$uploadname = $db->fetchOneBySql($sql);
	}
	else
	{
		$uploadname = '';
	}
	$data['uploader'] = '<a href="index.php?action=uploader&module=members&uid='.$data['uploader'].'">'.$uploadname.'</a>';
	$data['avatar'] = avatar($data['uid']);

	// if($data['subscribe']==1)
	// {
	// 	$data['myappid'] = $weixin_name;
	// 	$data['myappid'] .= '&nbsp;'.$account;
	// }

	$sql = "select title from {$tablepre}mpgroups where groupid='$data[wxgroup]'";
	$wxgroup = $db->fetchOneBySql($sql);
	if($wxgroup)
	{
		$data['wxgroup'] = $wxgroup;
	}
	$sql = "select * from {$tablepre}membersinfo where uid='$uid'";
	$extendinfo = $db->fetchSingleAssocBySql($sql);
	foreach($extendinfo as $key=>$value)
	{
		$data[$key] = $value;
	}
	// 从地址库中拉取默认的地址信息
	$sql = "SELECT * FROM {$tablepre}address WHERE uid='$uid' AND isdefault=1";
	$addressinfo = $db->fetchSingleAssocBySql($sql);
	if(!empty($addressinfo))
	{
		$data['position'] = $addressinfo['linkman'];
		$data['address'] = $addressinfo['address'];
		$data['telephone'] = $addressinfo['telephone'];
	}
	$lang = '';
	$header = '<tr style="white-space:nowrap"><td colspan=4 style="text-align:center;">'.$data['linkman'].'</tr>';
	$table = '';
	$i = 0;
	$chars = array_unique($chars);
	foreach($chars as $key=> $value)
	{
		if($view[$key]!=1)
		{
			continue;
		}

		if($value=='linkman')
		{
			$_info = '<input type="hidden" id="'.$value.'" value="'.$data[$value].'" />';
		}
		else
		{
			$_info = '';
		}
		if($name[$key])
		{
			$l[$value] = $name[$key];
		}
		if($agenttype=='site')
		{
			if($value=='content')
			{
				$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;">'.$data['content'].'</tr>';
			}
			else if($i==0)
			{
				$table .=  '<tr><td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;" '.$value.'>'.$_info.showitem($module,$data[$value],$value,$type[$key]).'</td>';
				$i =1;
			}
			else if($i==1)
			{
				$table .=  '<td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;">'.$_info.showitem($module,$data[$value],$value,$type[$key]).'</td></tr>';
				$i=0;
			}
		}
		else
		{
			if($value=='content')
			{
				$table .=  '<tr><td colspan=2 style="text-align:left;white-space:normal;"><span class="editable">'.$data['content'].'</span></tr>';
			}
			else
			{
				$table .=  '<tr><td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;"><span class="editable">'.$_info.showitem($module,$data[$value],$value,$type[$key]).'</span></td></tr>';
			}
		}
	}
	$sql = "SELECT * FROM {$tablepre}record WHERE uid='$uid'";
	$info = $db->fetchAssocArrBySql($sql);
	$subscribe = $data['subscribe'];
	$idsui=1;
	$sql = "select max(shares) as sharesum,max(visitor)as visitorsum,max(subscribes) as subscribesum from {$tablepre}members";
	$sumresult = $db->fetchSingleAssocBySql($sql);
	$sql = "select sum(special) as ordersum from {$tablepre}orders where flag>1 and backflag=0 and ordertype=1";
	$sumorders = $db->fetchOneBySql($sql);
	$sql = "select sum(special) as ordersum from {$tablepre}orders where flag>1 and backflag=0 and ordertype=1 and uid='$data[uid]'";
	$usersumorders = $db->fetchOneBySql($sql);

	$ret = array();
	$ret[0] = $sumresult['sharesum']>0 ? round($data['shares']*100/$sumresult['sharesum']) : 100;
	$ret[1] = $sumresult['visitorsum']>0 ? round($data['visitor']*100/$sumresult['visitorsum']) : 100;
	$ret[2] = $sumresult['subscribesum']>0 ? round($data['subscribes']*100/$sumresult['subscribesum']) : 100;
	$ret[3] = $sumorders>0 ? round($usersumorders*100/$sumorders) : 100;
	$chartdata = jsondata($ret);

	eval ("\$chart_html= \"" . $tpl->get('radar',$template). "\";");
}
else if ($module == 'orders')
{
	$data = '';
	$sql = "SELECT * FROM {$tablepre}$module WHERE 1 {$attasql} AND id='$id'";
	$data = $db->fetchSingleAssocBySql($sql);
	// $data = parsemodule($data);
	$lang = '';
	//$string = 'content,classid,views,dateline,origin,vicetitle,photo,shares,visitor,subscribes';
	$header = '<tr style="white-space:nowrap"><td colspan=4 style="text-align:center;"><span class="editable">'.$data['title'].'</span></tr>';
	$table = '';
	$array = explode(',',$string);
	$i = 0;
	$variable = $module.'sys';
	$variable = $$variable;
	$chararray = array_unique(explode(',',$variable['char_setting']));
	$name = explode(',',$variable['name_setting']);
	$havecontent = 0;

	foreach($chararray as $key=> $value)
	{
		if($view[$key]==0)
		{
			continue;
		}
		if(in_array('classid',$chararray))
		{
			if($value == 'classid' && $module!='channel')
			{
				$sql = "SELECT title FROM {$tablepre}{$module}class WHERE id='$data[classid]'";
				$data[$value] = $db->fetchOneBySql($sql);
			}
		}
		if($type[$key]==10)
		{
			$havecontent = 1;
			continue;
		}
		if($agenttype=='site')
		{
			if($i==0)
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
				if($module=="news")
				{
					eval ("\$previewnews .= \"" . $tpl->get("previewnews",$template). "\";");
					$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;"><span class="editable">'.$previewnews.'</span></tr>';
				}
				else
				{
					$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;"><span class="editable">'.stripslashes($data['content']).'</span></tr>';
				}
			}
			else
			{
				$table .=  '<tr><td style="width:20%">'.$name[$key].'</td><td style="width:30%;overflow:hidden;"><span class="editable">'.showitem($module,$data[$value],$value,10).'</span></td></tr>';
			}
		}
	}
	if($havecontent)
	{
		$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;"><span class="editable">'.stripslashes($data['content']).'</span></tr>';
	}

	$sql = "SELECT uid FROM {$tablepre}views WHERE module='$module' AND  moduleid='$id' ORDER BY id DESC LIMIT 500";
	$views = $db->fetchColBySql($sql);
	if(!empty($views))
	{
		$ids = '\''.implode('\',\'', $views).'\'';
		$sql = "SELECT uid,linkman FROM {$tablepre}members WHERE uid in (".$ids.") AND subscribe=1"; //
		$userinfo = $db->fetchAssocArrBySql($sql);
		if(!empty($userinfo))
		{
			foreach($userinfo as $key=> $value)
			{
				$viewmembers .= '<a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\')"><img src="'.$uploaddir.'/'.avatar($value['uid']).'" class="img-circle" style="width:30px;">'.$value['linkman'].'</a>';
			}
		}
	}
	$chart_html=chart_module_html('count',$data['title'],'1','sum','c_d',$datestyle,$datenum,$module,$id);

	$orderlist = '';
	$sql = "select * from {$tablepre}orderlist where orderid='$id'";
	$res = $db->fetchAssocArrBySql($sql);
	foreach($res as $key => $value)
	{
		$orderlist .= '<tr>';
		$orderlist .= '<td>'.$value['pid'].'</td>';
		$orderlist .= '<td style="text-align:left;white-space:normal;">'.$value['name'].'</td>';
		$poption = getOptionById($value['options']);

		$orderlist .= '<td>'.$poption.'</td>';
		$orderlist .= '<td>'.$value['quantity'].'</td>';
		$orderlist .= '<td>'.$value['special'].'</td>';
		$orderlist .= '<td>'.number_format($value['special']*$value['quantity'],2).'</td>';
		$agencyuser = $db->fetchOneBySql("select title from {$tablepre}agency where uid='".$value['agencyuid']."' and module='agency'");
		$orderlist .= '<td>'.$agencyuser.'</td>';
		$orderlist .= '<td>'.$value['commission'].'</td>';
		$shareuser = '';
		if($value['shareuid'])
		{
			$shareuser = $db->fetchOneBySql("select linkman from {$tablepre}members where uid='".$value['shareuid']."'");
		}
		$orderlist .= '<td>'.$shareuser.'</td>';
		$orderlist .= '<td>'.$value['sharecommission'].'</td>';
		$shopstoreuser = $db->fetchOneBySql("select title from {$tablepre}agency where uid='".$value['shopstoreuid']."' and module='shopstore'");
		$orderlist .= '<td>'.$shopstoreuser.'</td>';
		$orderlist .= '<td>'.$value['takemoney'].'</td>';
		$shopincome = $value['special']*$value['quantity']-$value['commission']-$value['sharecommission']-$value['takemoney'];
		$orderlist .= '<td>'.number_format($shopincome,2).'</td>';
		$orderlist .= '</tr>';
	}
	$orderrecord = '<tr><td align="left">下单时间:'.date('Y-m-d H:i',$data['dateline']).'</td></tr>';
	if($data['paytime'])
	{
		$orderrecord .= '<tr><td align="left">支付时间:'.date('Y-m-d H:i',$data['paytime']).'</td></tr>';
	}
	if($data['delivertime'])
	{
		$orderrecord .= '<tr><td align="left">发货时间:'.date('Y-m-d H:i',$data['delivertime']).'</td></tr>';
	}
	if($data['receivetime'])
	{
		$orderrecord .= '<tr><td align="left">收货时间:'.date('Y-m-d H:i',$data['receivetime']).'</td></tr>';
	}
	if($data['applyreturntime'])
	{
		$orderrecord .= '<tr><td align="left">申请退货时间:'.date('Y-m-d H:i',$data['applyreturntime']).'</td></tr>';
	}
	if($data['censorreturntime'])
	{
		$orderrecord .= '<tr><td align="left">退货审核时间:'.date('Y-m-d H:i',$data['censorreturntime']).'</td></tr>';
	}
	$deliverinfo = '';
	$sql = "select deliverno from {$tablepre}deliver where orderid='$id' order by dateline desc limit 1";
	$deliverno = $db->fetchOneBySql($sql);

	if ($deliverno && $interfaceurl['deliver'])
	{
		$url = $interfaceurl['deliver'].'?courierno='.$deliverno;
		//echo $url;
		$message = @file_get_contents($url);
		//echo $message;
		if($message)
		{
			$dinfo = json_decode($message,true);
			$ret = $dinfo['lastResult']['data'];
			foreach($ret as $key=>$value)
			{
				$deliverinfo .= '<tr><td align="left">'.$value['time'].'</td><td align="left">'.$value['context'].'</td><td>'.$value['status'].'</td></tr>';
			}
		}
	}
}
else if($module=='meeting')
{
	$data = '';
	$sql = "SELECT * FROM {$tablepre}$module WHERE 1 {$attasql} AND id='$id'";
	$data = $db->fetchSingleAssocBySql($sql);
	//$string = $meeting['view_char'];
	$header = '<tr style="white-space:nowrap"><td colspan=4 style="text-align:center;">'.$data['title'].'</tr>';
	$table = '';
	$array = explode(',',$string);
	$i = 0;
	foreach($chars as $key=> $value)
	{
		if($view[$key]!=1)
		{
			continue;
		}
		if($agenttype=='site')
		{
			if($value=='content')
			{
				$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;">'.$data['content'].'</tr>';
			}
			else if($i==0)
			{
				$table .=  '<tr><td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;" '.$value.'>'.$_info.showitem($module,$data[$value],$value,$type[$key]).'</td>';
				$i =1;
			}
			else if($i==1)
			{
				$table .=  '<td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;">'.showitem($module,$data[$value],$value,$type[$key]).'</td></tr>';
				$i=0;
			}
		}
		else
		{
			if($value=='content')
			{
				$table .=  '<tr><td colspan=2 style="text-align:left;white-space:normal;"><span class="editable">'.$data['content'].'</span></tr>';
			}
			else
			{
				$table .=  '<tr><td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;"><span class="editable">'.$_info.showitem($module,$data[$value],$value,$type[$key]).'</span></td></tr>';
			}
		}
	}
}
else if($module == 'website')
{
	if(!empty($_POST))
	{
		$sql = "UPDATE {$tablepre}websiteinfo SET value=0 WHERE siteid='$id'";
		$db->query($sql);
		foreach($_POST as $key=>$value)
		{
			if(preg_match('/_/',$key))
			{
				$sql = "UPDATE {$tablepre}websiteinfo SET value=1,uploader='$user[uid]' WHERE siteid='$id' AND module='$key'";
				$db->query($sql);
				if(!$db->affectedRows())
				{
					$sql = "INSERT INTO {$tablepre}websiteinfo(siteid,module,value,dateline,uploader,moderate) VALUES('$id','$key','1','$time','$user[uid]',1)";
					$db->query($sql);
				}
			}
		}
		$sql = "select sitedomain from {$tablepre}website where id='$id'";
		$sitedomain = $db->fetchOneBySql($sql);
		$rdb->redis->del($main_dbname.$space.'sitepermission_'.$sitedomain);
		exitandreturn(1,$module,$action);
	}
	else
	{
		$data = '';
		$sql = "SELECT * FROM {$tablepre}$module WHERE 1 {$attasql} AND id='$id'";
		$data = $db->fetchSingleAssocBySql($sql);
		$header = '<tr style="white-space:nowrap"><td colspan=4 style="text-align:center;">'.$data['title'].'</tr>';
		$table = '';
		//$string = $$variable['char_setting'];
		//$array = explode(',',$string);
		$i = 0;
		foreach($chars as $key=> $value)
		{
			if($view[$key]!=1)
			{
				continue;
			}
			if($agenttype=='site')
			{
				if($value=='content')
				{
					$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;">'.$data['content'].'</tr>';
				}
				else if($i==0)
				{
					$table .=  '<tr><td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;" '.$value.'>'.$_info.showitem($module,$data[$value],$value,$type[$key]).'</td>';
					$i =1;
				}
				else if($i==1)
				{
					$table .=  '<td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;">'.showitem($module,$data[$value],$value,$type[$key]).'</td></tr>';
					$i=0;
				}
			}
			else
			{
				if($value=='content')
				{
					$table .=  '<tr><td colspan=2 style="text-align:left;white-space:normal;"><span class="editable">'.$data['content'].'</span></tr>';
				}
				else
				{
					$table .=  '<tr><td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;"><span class="editable">'.$_info.showitem($module,$data[$value],$value,$type[$key]).'</span></td></tr>';
				}
			}
		}
		$sql = "SELECT * FROM {$tablepre}websiteinfo WHERE siteid='$id'";
		$info = $db->fetchAssocArrBySql($sql);
		if(!empty($info))
		{
			foreach($info as $key=> $value)
			{
				if($value['value']==1)
				{
					$array[$value['module']] = 1;
				}
			}
		}
		$permission = '';
		$db_name = $siteinfo['sitedomain'];
		/*$systemarray = array();
		foreach($system_preview as $key=>$value)
		{
			$value = explode(',',$value);
			$variable = $key.'sys';
			$variable = $$variable;
			if($variable['show']!='1')
			{
				continue;
			}
			$newarray = array();
			if(!empty($value))
			{
				foreach($value as $k=> $v)
				{
					$newarray[$v] = $l[$v];
				}
			}
			$systemarray[$key] = $newarray;
		}
		printarray($systemarray);exit;
		$adminarray = array_merge($adminarray,$systemarray);
		*/
		require_once 'include/language/menu.php';

		if(!empty($adminarray))
		{
			foreach($adminarray as $key=> $value)
			{
				$permission .= $l[$key].'<br>';
				foreach($value as $k=> $v)
				{
					if($array[$key.'_'.$k]==1)
					{
						$permission .= '<input name="'.$key.'_'.$k.'" type="checkbox" value="1" checked>&nbsp;'.$l[$k].'&nbsp;';
					}
					else
					{
						$permission .='<input name="'.$key.'_'.$k.'" type="checkbox" value="0">&nbsp;'.$l[$k].'&nbsp;';
					}
				}
				$permission .='<br><br>';
			}
		}
	}
}
else if($module=='rank')
{
	$sql = "SELECT uid,linkman,avatar,rank,credits FROM {$tablepre}members where rank='$id' order by credits desc limit 100";
	$info = $db->fetchAssocArrBySql($sql);
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$num_i=ceil($key/20);
			if($num_i==0)
			{
				$num_i=1;
			}
			$str='user_list'.$num_i;
			$$str .= '<div class="input-group" uid="'.$value['uid'].'"><a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\');"><label class="form-control"><img src="'.$uploaddir.avatar($value['uid']).'" style="width:30px;">'.$value['linkman'].'<span style="color:red;float: right;">'.$value['credits'].'</span></label></a></div>';
		}
	}
}
else if($module == 'push')
{
	$sql = "SELECT * FROM {$tablepre}push where id='$id'";
	$data = $db->fetchSingleAssocBySql($sql);
	if($data['type'] ==1 || $data['type']==0 )
	{
		$prevtwodays = $time-2*86400;
		$sql = "SELECT count(uid) FROM {$tablepre}members WHERE lastmessage>'$prevtwodays' AND subscribe=1";
		$count = $db->fetchOneBySql($sql);
		$sql = "SELECT uid,linkman FROM {$tablepre}members WHERE lastmessage>'$prevtwodays' AND subscribe=1 ORDER BY uid DESC LIMIT 100";
		$userlist = $db->fetchAssocArrBySql($sql);
		$userinfo = '';
		if(!empty($userlist))
		{
			$i = 0;
			foreach($userlist as $key=> $value)
			{
				$userinfo .='<label style="font-weight:normal"><input type="checkbox" name="uid[]" value="'.$value['uid'].'">'.$value['linkman'].'</label>&nbsp;';// <a href="javascript:;" onclick=\'menuclick("admin.php?action=view&module=members&uid='.$value['uid'].'")\'>
				$i++;
			}
		}
	}
	else if($data['type']==2)
	{
		$sendtype = $data['grouptype'];
		if($sendtype==1) //按微信分组发送
		{
			$groupinfo = $weObj->getGroup();
			if(!empty($groupinfo['groups']))
			{
				$ret['name'] = 'wxgroupid';
				foreach($groupinfo['groups'] as $key=> $value)
				{
					$ret['data'][$key]['id'] = $value['id'];
					$ret['data'][$key]['name'] = $value['name'];
					$ret['data'][$key]['count'] = $value['count'];
				}
			}
		}
		else if($sendtype==2)//按系统分组发送
		{
			$sql = "select id,title from {$tablepre}groups where type=1 order by id asc";
			$result = $db->fetchAssocArrBySql($sql);
			$ret['name']='groupid';
			foreach($result as $key=>$value)
			{
				$ret['data'][$key]['id'] = $value['id'];
				$ret['data'][$key]['name'] = $value['title'];
				$sql = "select count(uid) from {$tablepre}usergroup where id='$value[id]'";
				$lcount = $db->fetchOneBySql($sql);
				$ret['data'][$key]['count'] = $lcount;
			}
			$ret['data'][$key+1]['id'] = 0;
			$ret['data'][$key+1]['name'] = '未分组';
			$sql = "select count(uid) from {$tablepre}members where uid not in (select uid from {$tablepre}usergroup where id>999)";
			$notcount = $db->fetchOneBySql($sql);
			$ret['data'][$key+1]['count'] = $notcount;
		}
		else //群发
		{

		}
		if($ret)
		{
			$pushgroups = explode(",",$data['groups']);
			$text = '';
			$prefix = '';
			foreach($ret['data'] as $key=>$value)
			{
				if(!in_array($value['id'],$pushgroups))
				{
					continue;
				}
				$text .= $prefix.$value['name'].'('.$value['count'].'人)';
				$prefix = ',';
			}
			$data['groups'] = $text;

		}
		else
		{
			$data['groups'] = '全部';
		}

	}
	$header = '<tr style="white-space:nowrap"><td colspan=4 style="text-align:center;">'.$data['title'].'</tr>';
	$table = '';
	$i = 0;
	foreach($chars as $key=> $value)
	{
		if($view[$key]!=1)
		{
			continue;
		}

		if($agenttype=='site')
		{
			if($value=='content')
			{
				$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;">'.$data['content'].'</tr>';
			}
			else if($i==0)
			{
				$table .=  '<tr><td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;" '.$value.'>'.$_info.showitem($module,$data[$value],$value,$type[$key]).'</td>';
				$i =1;
			}
			else if($i==1)
			{
				$table .=  '<td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;">'.showitem($module,$data[$value],$value,$type[$key]).'</td></tr>';
				$i=0;
			}
		}
		else
		{
			if($value=='content')
			{
				$table .=  '<tr><td colspan=2 style="text-align:left;white-space:normal;"><span class="editable">'.$data['content'].'</span></tr>';
			}
			else
			{
				$table .=  '<tr><td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;"><span class="editable">'.$_info.showitem($module,$data[$value],$value,$type[$key]).'</span></td></tr>';
			}
		}
	}


	$sql = "SELECT * FROM {$tablepre}pushit WHERE pushid='$id' ORDER BY orderid DESC";
	$result = $db->fetchAssocArrBySql($sql);
	$pushdata = '';
	if(!empty($result))
	{
		$pushdata .= '<tr><td colspan="5" style="width;100%;">';
		$pushdata .= '<div class="push_list"><ul>';
		$i = 0;
		foreach($result as $key=> $value)
		{
			$photo = $uploaddir.$value['photo'];
			$i++;
			eval ("\$pushdata .= \"" . $tpl->get("pushlist",$template). "\";");
		}
		$pushdata .= '</ul>';
		$j = 0;
		foreach($result as $key=> $value)
		{
			$photo = $uploaddir.$value['photo'];
			if($value['module']=='news')
			{
				$sqln = "select content from {$tablepre}newscontent where id='$value[moduleid]'";
				$value['content'] = $db->fetchOneBySql($sqln);
			}
			else
			{
				if($value['moduleid'])
				{
					$sqln = "select * from {$tablepre}{$module} where id='$value[moduleid]'";
					$ss = $db->fetchSingleAssocBySql($sqln);
					$value['content'] = $ss['content'];
				}
			}
			$value['content'] = stripslashes($value['content']);
			$j++;
			eval ("\$pushdata .= \"" . $tpl->get("pushlistright",$template). "\";");
		}
		$pushdata .= '</div></td></tr>';
	}
	else
	{
		$pushdata .= '<tr><td colspan="5"><span style=""><a href="javascript:;" onclick="menuclick(\'admin.php?module=news&action=list&pushid='.$id.'\')">请从新闻模块中的列表中点击推送链接选择推送文章</a></span></td></tr>';
	}
}
else
{
	$data = '';
	if($module=='shopstore')
	{
		$sql = "SELECT * FROM {$tablepre}agency WHERE 1 {$attasql} AND id='$id'";
	}
	else
	{
		if($module == 'operator')
		{
			$sql = "SELECT templatetype FROM {$tablepre}templates WHERE operatorid='$id'";
			$templatetype = $db->fetchOneBySql($sql);
		}
		$sql = "SELECT * FROM {$tablepre}$module WHERE 1 {$attasql} AND id='$id'";
	}
	$data = $db->fetchSingleAssocBySql($sql);
	if($module == 'agency' || $module=='shopstore')
	{
		if($data['areacode'])
		{
			$data['address'] = codetoarea($data['areacode']).$data['address'];
		}
		$data['uid'] = $rdb->userInfo($data['uid'])['linkman'];
	}
	if($data['modules'] && $data['moduleid'])
	{
		$sql = "SELECT title FROM {$tablepre}{$data['modules']} WHERE id='$data[moduleid]'";
		$data['moduleid'] = $db->fetchOneBySql($sql);
		$data['moduletype']  = $l[$data['modules']];
	}
	$header = '<tr style="white-space:nowrap"><td colspan=4 style="text-align:center;">'.$data['title'].'</tr>';
	$table = '';
	$string = $variable['view_setting'];
	$array = explode(',',$string);

	$i = 0;
	foreach($chars as $key=> $value)
	{
		if($view[$key]!=1)
		{
			continue;
		}
		if($agenttype=='site')
		{
			if($value=='content')
			{
				$table .=  '<tr><td colspan=4 style="text-align:left;white-space:normal;">'.$data['content'].'</tr>';
			}
			else if($i==0)
			{
				$table .=  '<tr><td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;" '.$value.'>'.$_info.showitem($module,$data[$value],$value,$type[$key]).'</td>';
				$i =1;
			}
			else if($i==1)
			{
				$table .=  '<td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;">'.showitem($module,$data[$value],$value,$type[$key]).'</td></tr>';
				$i=0;
			}
		}
		else
		{
			if($value=='content')
			{
				$table .=  '<tr><td colspan=2 style="text-align:left;white-space:normal;"><span class="editable">'.$data['content'].'</span></tr>';
			}
			else
			{
				$table .=  '<tr><td style="width:20%">'.$l[$value].'</td><td style="width:30%;overflow:hidden;"><span class="editable">'.$_info.showitem($module,$data[$value],$value,$type[$key]).'</span></td></tr>';
			}
		}
	}

}