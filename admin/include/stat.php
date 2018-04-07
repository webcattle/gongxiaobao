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
require_once 'include/statfunction.php';

$variable = $module.'sys';
$modulesystem = $$variable;
$datestyle=$_GET['datestyle'];
$datenum=$_GET['datenum'];
$data = array();
if(empty($datestyle))
{
	$datestyle='m';
}
if(empty($datenum))
{
	$datenum=-1;
}
if($datestyle=='m')
{
	$date_y_show='m-d';
	$cha =  time()-mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	if(in_array(intval(date('m',$today)),array(1,3,5,7,8,10,12)))
	{
		$daynum=31;
	}
	else if(intval(date('m',$today))==2)
	{
		if(intval(date('m',$today))%4==0)
		{
			$daynum=29;
		}
		else
		{
			$daynum=28;
		}

	}
	else
	{
		$daynum=30;
	}
	$today =  strtotime('-'.$daynum.' day')-$cha;
	$stattime = time()-86400*30;
}
else if($datestyle=='d')
{
	$date_y_show='H:i';
	$today =  strtotime($datenum.' day');
	$daynum=24;
	$stattime = time()-86400;
}
else if($datestyle=='y')
{
	$date_y_show='Y-m';
	$cha =  time()-mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	$today =  strtotime($datenum.' Year')-$cha;
	$daynum=12;
	$stattime = time()-86400*365;
}
if($action == 'userincrease')
{
	echo 'user';
	exit;
}
else if($action == 'useranalysis')
{
	echo 1;
	exit;
}
$today_cz=$today;
if($module == 'stat')
{
	$content = '';
	if(file_exists('./language/menu.php'))
	{
		require_once './language/menu.php';
	}
	$admin_block = array('content','operator','service');
	
	foreach($adminarray as $k=> $v)
	{
		if(!in_array($k,$admin_block))
		{
			continue;
		}
		else
		{
			$content .= '<br>'.$l[$k].'<br>';
			foreach($adminarray[$k] as $key=> $value)
			{
				if(preg_match('/class/',$key) || preg_match('/setting/',$key) || $key=='weixin')
				{
					continue;
				}
				$value = 'stat';
				eval ("\$content .= \"" . $tpl->get("operator_item",$template). "\";");
			}
		}
	}
	eval ("\$content = \"" . $tpl->get('stat_stat','admin'). "\";");
	exitandreturn($content,$module,$action);
}
else if(in_array($module,array_keys($system_preview)) || $module =='channel')
{
	//模块的增加量
	if($module=='channel')
	{
		$sql = "SELECT sum(c_d) FROM {$tablepre}count WHERE 1";
	}
	else
	{
		if($module=='members')
		{
			$sql = "SELECT count(uid) FROM {$tablepre}$module";
		}
		else
		{
			$sql = "SELECT count(id) FROM {$tablepre}$module";
		}
	}
	$total = $db->fetchOneBySql($sql);
	$array = array();
	//除频道以外的模块 访问统计
	$ret=array();
	$today=$today_cz;
	if($module=='channel')
	{
		$ret=array();
		$name='频道统计';
		$sql="select title,views,id from {$tablepre}channel WHERE moderate =1";
		$sqlarr=$db->fetchAssocArrBySql($sql);
		foreach($sqlarr as $k => $v)
		{
			$ret['series'][$name][]=$v['views'];
			$ret['xAxis'][]=$v['title'];
		}
		$ret['title']=$name;
		$ret['legend']=array($name);
		$template = 'admin';
		$contentinfo.=chart_data($ret,'bar','3');
	}
	else
	{
		$contentinfo.=chart_module_html('count',$l[$module]."访问统计",'3','sum','c_d',$datestyle,$datenum,$module);

	}
}
if($module=='members')//用户
{
	$valuesdata = rankuser($module,'subscribes');// 最具价值
	$subscribesdata = rankuser($module,'visitor');// 最具影响力
	$messagesdata = rankuser($module,'messages');// 交互次数最多
	$creditsdata = rankuser($module,'credits');// 金币最多
}
else if($module=='memberacts')//用户行为
{	//最新关注用户浏览状况
	$memberstr = getmemberstr(1);
	$dataview = newmemberviews($memberstr);
	//最新关注用户分享状况
	$datashare = newmembershare($memberstr);
	//新增未关注用户浏览状况
	$nomemberstr = getmemberstr(0);
	$nodataview = newmemberviews($nomemberstr);
	//新增未关注用户分享状况
	$nodatashare = newmembershare($nomemberstr);

	$eventcontent = memberevent($memberstr);

}
else if($module=='news' || $module=='operator' || $module =='knowledge' || $module=='product'||$module=='studyresources')//新闻
{
	$visitorsdata = rankitem($module,'visitor');// 访问最多
	if($module!='knowledge')
	{
		$digsdata = rankitem($module,'dig');// 点赞最多
	}
	$subscribesdata = rankitem($module,'subscribes');// 带来关注最多
	$commentsdata = rankitem($module,'comments');// 评论最多
	$sharesdata = rankitem($module,'shares');// 分享最多
}
else if($module=='meeting')//公众群
{
	//群活跃排行
	$sql = "SELECT meetingid,count(*) c FROM {$tablepre}mpmsg where meetingid!=0 and dateline>$stattime group by meetingid ORDER BY c DESC LIMIT 10";
	$msginfo = $db->fetchAssocArrBySql($sql);
	foreach($msginfo as $v)
	{
		$mid[] = $v['meetingid'];
	}
	if(!empty($mid))
	{
		$mid = implode(',',$mid);
		$sql = "select id,title,photo from {$tablepre}meeting where moderate=1 and id in($mid)";
		$info = $db->fetchAssocArrBySql($sql);
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$meetingdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=meeting&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.$value['photo'].'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['title'].'</div></div>
			</label></a>';
			$data .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=operator&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.$value['photo'].'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['title'].'</div></div>
			</label></a>';
		}
	}
	//最活跃用户
	$sql = "SELECT uid,count(*) c FROM {$tablepre}mpmsg where meetingid!=0 and dateline>$stattime group by uid ORDER BY c DESC LIMIT 10";
	$msginfo = $db->fetchAssocArrBySql($sql);
	foreach($msginfo as $v)
	{
		$muid[] = $v['uid'];
	}
	if(!empty($muid))
	{
		$muid = implode(',',$muid);
		$sql = "SELECT uid,linkman,avatar FROM {$tablepre}members where subscribe=1 and uid in ($muid) ORDER BY visitor DESC LIMIT 10";
		$info = $db->fetchAssocArrBySql($sql);
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$musersdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.avatar($value['uid']).'" onerror="javascript:this.src=\'.$uploaddir.avatar/user.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['linkman'].'</div></div>
			</label></a>';
		}
	}
	//群成员排行
	$sql = "SELECT meetingid,count(*) c FROM {$tablepre}membersinfo where meetingid!=0 group by meetingid ORDER BY c DESC LIMIT 10";
	$msginfo = $db->fetchAssocArrBySql($sql);
	foreach($msginfo as $v)
	{
		$mu[] = $v['meetingid'];
	}
	if(!empty($mu))
	{
		$mu = implode(',',$mu);
		$sql = "select id,title,photo from {$tablepre}meeting where moderate=1 and id in($mu)";
		$info = $db->fetchAssocArrBySql($sql);
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$mucdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$module.'&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.$value['photo'].'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['title'].'</div></div>
				<div class="input-group-addon">
					<span class="badge">'.$msginfo[$key]['c'].'</span>
				</div>
			</label></a>';
		}
	}
}
else if($module=='tasks')//任务
{
	//参与最多
	$sql = "SELECT moduleid,count(id) c FROM {$tablepre}task where module='task' group by moduleid ORDER BY c DESC LIMIT 10";
	$taskinfo = $db->fetchAssocArrBySql($sql);
	foreach ($taskinfo as $k=>$v)
	{
		$moduleids[] = $v['moduleid'];
	}
	if(!empty($moduleids))
	{
		$moduleids = implode(',',$moduleids);
		$sql = "select title from {$tablepre}$module where id in ($moduleids)";
		$info = $db->fetchAssocArrBySql($sql);
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$involvesdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=operator&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="form-control"><div class="name">'.($key+1).'. '.$value['title'].'</div></div>
			</label></a>';
		}
	}
	//完成最多
	$sql = "SELECT moduleid,count(id) c FROM {$tablepre}task where module='task' and moderate=1 group by moduleid ORDER BY c DESC LIMIT 10";
	$taskinfo = $db->fetchAssocArrBySql($sql);
	foreach ($taskinfo as $k=>$v)
	{
		$moduleids[] = $v['moduleid'];
	}
	if(!empty($moduleids))
	{
		$moduleids = implode(',',$moduleids);
		$sql = "select title from {$tablepre}$module where id in ($moduleids)";
		$info = $db->fetchAssocArrBySql($sql);
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$finishsdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=operator&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="form-control"><div class="name">'.($key+1).'. '.$value['title'].'</div></div>
			</label></a>';
		}
	}
}
else if($module=='card')//卡券
{
	//使用最多的卡券
	//活跃用户
}
else if($module=='games')//游戏
{
	$involvesdata = rankitem($module,'involved');//参与人数
	$sharesdata = rankitem($module,'shares');//分享最多
	$subscribesdata = rankitem($module,'subscribes');//分享带来关注
	$commentsdata = rankitem($module,'comments');//评论
	$visitorsdata = rankitem($module,'visitor');//分享带来访问
	$digsdata = rankitem($module,'dig');//点赞
}
else if($module=='anwser')//答题
{
	//答的最多
	//分享最多
	//活跃用户
}
else if($module=='crowdfund')//众筹
{
}
else if($module=='share')//分享
{
	//分享排行
	$sharenumrank = sharestatonly($module,'count(id)');
	//分享最多用户
	$shareuserrank = rankusercount($module,'count','id');
	//带来关注
	$sql = "SELECT url,subscribes c FROM {$tablepre}$module where dateline>$stattime group by url ORDER BY c DESC LIMIT 10";
	$shareinfo = $db->fetchAssocArrBySql($sql);
	unset($shareurl);
	foreach($shareinfo as $v)
	{
		$shareurl[] = $v['url'];
	}
	$info = array();
	foreach($shareurl as $v)
	{
		$url = explode('_',$v);
		$param1 = $url[0];
		$param2 = $url[1];
		$param3 = $url[2];
		if($param1=='module' || $param1=='service' || empty($v))
		{
			continue;
		}
		$sql = "select id,title,photo from {$tablepre}$param1 where $param2=$param3";
		$info[] = array_merge($db->fetchSingleAssocBySql($sql),array('module'=>$param1));
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$sharesubscriberank .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$value['module'].'&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.$value['photo'].'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['title'].'</div></div>
			</label></a>';
		}
	}
	//$sharesubscriberank = sharestatonly($module,'subscribes');
	//传播深度
	$sql = "SELECT url,max(level) c FROM {$tablepre}$module where dateline>$stattime group by url ORDER BY c DESC LIMIT 10";
	$shareinfo = $db->fetchAssocArrBySql($sql);
	unset($shareurl);
	foreach($shareinfo as $v)
	{
		$shareurl[] = $v['url'];
	}
	$info = array();
	foreach($shareurl as $v)
	{
		$url = explode('_',$v);
		$param1 = $url[0];
		$param2 = $url[1];
		$param3 = $url[2];
		if($param1=='module' || $param1=='service' || empty($v))
		{
			continue;
		}
		$sql = "show tables like '{$tablepre}$param1'";
		$table_exist = $db->fetchOneBySql($sql);
		if(!$table_exist)
		{
			continue;
		}
		$sql = "select id,title,photo from {$tablepre}$param1 where $param2=$param3";
		$info[] = array_merge($db->fetchSingleAssocBySql($sql),array('module'=>$param1));
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$sharelevelrank .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$value['module'].'&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.$value['photo'].'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['title'].'</div></div>
			</label></a>';
		}
	}

//	$sharelevelrank = sharestatonly($module,'max(level)');
	//分享类型
	$sql = "SELECT type,count(*) c FROM {$tablepre}$module where dateline>$stattime group by type ORDER BY c DESC LIMIT 10";
	$shareinfo = $db->fetchAssocArrBySql($sql);
	$typearr = array('friend'=>'分享给朋友','timeline'=>'分享到朋友圈','qq'=>'分享到QQ');
	if(!empty($shareinfo))
	{
		foreach($shareinfo as $key => $value)
		{
			$sharetyperank .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=operator&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="form-control"><div class="name">'.($key+1).'. '.$typearr[$value['type']].'</div></div>
			</label></a>';
		}
	}
	$sharetyperank = rankfield($module,'type');
}
else if($module=='qrcode')//二维码
{
	//用户扫描次数
	$sql = "SELECT id,title,module,moduleid,number FROM {$tablepre}$module group by module,moduleid ORDER BY number DESC LIMIT 10";
	$qrcodeinfo = $db->fetchAssocArrBySql($sql);
	foreach ($qrcodeinfo as $k=>$v)
	{
		if(empty($v['module'])  || empty($v['moduleid']) || $v['module']=='shareview' || $v['module']=='navi' || $v['module']=='recommend')
		{
			$info[] = array('title'=>$v['title'],
				'photo'=>'../qrcode/qrcode_'.$v['id'].'.png',
				'module'=>$v['module'],
				'href'=>''
				);
		}
		else
		{
			$sql = "select title,photo from {$tablepre}$v[module] where id=$v[moduleid]";
			$moduledata = $db->fetchSingleAssocBySql($sql);
			if(!empty($moduledata))
			{
				$info[] = array_merge($moduledata,array('module'=>$v['module'],'href'=>'onclick="menuclick(\'admin.php?action=view&module='.$v['module'].'&id='.$v['id'].'\');"'));
			}
		}
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$scannumdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" $value[href] >
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.$value['photo'].'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['title'].'</div></div>
				<div class="input-group-addon">
					<span class="badge">'.$qrcodeinfo[$key]['number'].'</span>
				</div>
			</label></a>';
		}
	}
	//带来关注
	$sql = "SELECT id,title,subscribes FROM {$tablepre}$module ORDER BY subscribes DESC LIMIT 10";
	$info = $db->fetchAssocArrBySql($sql);
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$subscribesdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" $value[href] >
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.'../qrcode/qrcode_'.$value['id'].'.png" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['title'].'</div></div>
				<div class="input-group-addon">
					<span class="badge">'.$value['subscribes'].'</span>
				</div>
			</label></a>';
		}
	}
}
else if($module=='qrpay')//扫码支付 todo
{
	//支付人数
	$sql = "SELECT moduleid,count(id) c FROM {$tablepre}orders where flag=2 and trade_type='NATIVE' group by moduleid ORDER BY c DESC LIMIT 10";
	$qrpayinfo = $db->fetchAssocArrBySql($sql);
	foreach ($qrpayinfo as $k=>$v)
	{
		$moduleids[] = $v['moduleid'];
	}

	if(!empty($moduleids))
	{
		$moduleids = implode(',',$moduleids);
		$sql = "SELECT id,title FROM {$tablepre}$module where moderate=1 and id in($moduleids) LIMIT 10";
		$info = $db->fetchAssocArrBySql($sql);
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$qrpaynum .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=qrpay&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="form-control"><div class="name">'.($key+1).'. '.$value['title'].'</div></div>
			</label></a>';
		}
	}
	//支付总额
	$sql = "SELECT moduleid,count(special) c FROM {$tablepre}orders where flag=2 and trade_type='NATIVE' group by moduleid ORDER BY c DESC LIMIT 10";
	$qrpayinfo = $db->fetchAssocArrBySql($sql);
	$moduleids = array();
	foreach ($qrpayinfo as $k=>$v)
	{
		$moduleids[] = $v['moduleid'];
	}

	if(!empty($moduleids))
	{
		$moduleids = implode(',',$moduleids);
		$sql = "SELECT id,title FROM {$tablepre}$module where moderate=1 and id in($moduleids) LIMIT 10";
		$info = $db->fetchAssocArrBySql($sql);
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$qrpayamount .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=qrpay&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="form-control"><div class="name">'.($key+1).'. '.$value['title'].'</div></div>
			</label></a>';
		}
	}
	//$qrpayamount = rankitemcount('orders');
}
else if($module=='devices')//IBeacon
{
	$last_active_time = rankitem($module,'last_active_time','status',1);//最后交互时间
}
else if($module=='credits')//累计金币
{
	$usersdata = rankusercount($module,'sum','credit');//用户金币排行
	$getbymoduledata = rankfield($module,'module');//获得金币途径
}
else if($module=='sharetask')//分享任务
{
	//参与用户
	$sql = "SELECT uid,count(*) c FROM {$tablepre}record where dateline>$stattime group by uid ORDER BY c DESC LIMIT 10";
	$shareinfo = $db->fetchAssocArrBySql($sql);
	foreach($shareinfo as $k=>$v)
	{
		$suid[] = $v['uid'];
	}
	if(!empty($suid))
	{
		$suid = implode(',',$suid);
		$sql = "SELECT uid,linkman,avatar,subscribes FROM {$tablepre}members where subscribe=1 and uid in($suid) LIMIT 10";
		$info = $db->fetchAssocArrBySql($sql);
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$usersdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.avatar($value['uid']).'" onerror="javascript:this.src=\'.$uploaddir.avatar/user.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['linkman'].'</div></div>
			</label></a>';
		}
	}
	//参与人数
	$sql = "SELECT moduleid,count(*) c FROM {$tablepre}record where module='$module' and dateline>$stattime group by moduleid ORDER BY c DESC LIMIT 10";
	$shareinfo = $db->fetchAssocArrBySql($sql);
	foreach($shareinfo as $k=>$v)
	{
		$smoduleid[] = $v['moduleid'];
	}
	if(!empty($smoduleid))
	{
		$smoduleid = implode(',',$smoduleid);
		$sql = "SELECT id,title FROM {$tablepre}$module where moderate=1 and id in($smoduleid) LIMIT 10";
		$info = $db->fetchAssocArrBySql($sql);
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$tasksdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=sharetask&module=sharetask&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="form-control"><div class="name">'.($key+1).'. '.$value['title'].'</div></div>
			</label></a>';
		}
	}
}
else if($module=='hongbao' || $module=='qyhongbao')//微信红包/企业付款
{
	//发放模块
	$modulesdata = rankfield($module,'module');
	//总额榜
	$tuhaomoneysdata = rankusercount($module,'sum','money');
	//次数榜
	$tuhaonumsdata = rankusercount($module,'count','id');
	//具体发放
	$sql = "SELECT module,moduleid,count(id) c FROM {$tablepre}$module where dateline>$stattime group by module,moduleid ORDER BY c DESC LIMIT 10";
	$hongbaoinfo = $db->fetchAssocArrBySql($sql);
	$info = array();
	foreach($hongbaoinfo as $v)
	{
		$muid = implode(',',$muid);
		if($v['module']=='企业Plus' ||empty($v['id']))
		{
			$info[] = array('title'=>$v['module']);
		}
		else
		{
			$sql = "SELECT title,photo FROM {$tablepre}$v[module] where id=$v[id] LIMIT 10";
			$info[] = $db->fetchAssocArrBySql($sql);
		}
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$detailsdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.$value['photo'].'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['title'].'</div></div>
			</label></a>';
		}
	}
}
else if($module=='eventhongbao')//签到红包
{
//	//总额榜
//	$tuhaomoneysdata = rankusercount($module,'sum','money');
//	//次数榜
//	$tuhaonumsdata = rankusercount($module,'count','id');
	//总额榜
	$sql = "SELECT uid,sum(money) c FROM {$tablepre}hongbao where dateline>$stattime and module='eventhongbao' group by uid ORDER BY c DESC LIMIT 10";
	$hongbaoinfo = $db->fetchAssocArrBySql($sql);
	foreach($hongbaoinfo as $v)
	{
		$muid[] = $v['uid'];
	}
	if(!empty($muid))
	{
		$muid = implode(',',$muid);
		$sql = "SELECT uid,linkman,avatar FROM {$tablepre}members where subscribe=1 and uid in ($muid) ORDER BY visitor DESC LIMIT 10";
		$info = $db->fetchAssocArrBySql($sql);
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$tuhaomoneysdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.avatar($value['uid']).'" onerror="javascript:this.src=\'.$uploaddir.avatar/user.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['linkman'].'</div></div>
			</label></a>';
		}
	}
	//次数榜
	$sql = "SELECT uid,count(id) c FROM {$tablepre}hongbao where dateline>$stattime and module='eventhongbao' group by uid ORDER BY c DESC LIMIT 10";
	$hongbaoinfo = $db->fetchAssocArrBySql($sql);
	$muid = array();
	foreach($hongbaoinfo as $v)
	{
		$muid[] = $v['uid'];
	}
	if(!empty($muid))
	{
		$muid = implode(',',$muid);
		$sql = "SELECT uid,linkman,avatar FROM {$tablepre}members where subscribe=1 and uid in ($muid) ORDER BY visitor DESC LIMIT 10";
		$info = $db->fetchAssocArrBySql($sql);
	}
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$tuhaonumsdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.avatar($value['uid']).'" onerror="javascript:this.src=\'.$uploaddir.avatar/user.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['linkman'].'</div></div>
			</label></a>';


		}
	}
}
else if($module=='sharehongbao')//分享红包
{
}
else if($module=='groups')//组管理
{
	//组人数
	$sql = "select id,title from {$tablepre}$module where moderate=1";
	$groupall = $db->fetchAssocArrBySql($sql);
	foreach($groupall as $k=>$v)
	{
		$newgroupall[$v['id']] = $v['title'];
	}
	foreach($newgroupall as $k=>$v)
	{
		$sql = "SELECT count(uid) c FROM {$tablepre}members where subscribe=1 and (groupid=$k or groupid like '$k,%' or groupid like '%,$k' or groupid like '%,$k,%')";
		$groupall[$k]['count'] = $count = $db->fetchOneBySql($sql);
		$temp[$k] = $count;
	}
	arsort($temp);
	$muid = array();
	$i=1;
	foreach($temp as $k=>$v)
	{
		$groupsdata .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\');">
		<label class="input-group">
			<div class="input-group-addon">
				<img src="'.$uploaddir.avatar($value['uid']).'" onerror="javascript:this.src=\'.$uploaddir.avatar/user.jpg\';">
			</div>
			<div class="form-control"><div class="name">'.$i++.'. '.$newgroupall[$k].': '.$v.'</div></div>
		</label></a>';

	}
}
else if($module=='userdict')//标签
{
	$keywordsdata = rankfield($module,'keyword');//标签
	$userdictsdata = rankusercount($module);//用户标签数
}
else if($module=='rank')//金币等级
{
	$rankuserdata = rankitemcount('members','rank');//等级用户
}
else if($module=='kfaccount')//客服
{
}
else if($module=='mpmsg')//消息
{
	$msguserdata = rankusercount($module,'',$groupby='uid');//交互最多
	$msgtypedata = rankitemcount($module,'MsgType');//消息类型
}
else if($module=='orders')//订单
{
	$modulesdata = rankitemcount($module,'module');//订单所属模块
	$orderusersdata = rankusercount($module);//订单所属用户
}
else if($module=='distribution')//渠道
{
}
else if($module=='invitecode')//邀请码
{
}
else if($module=='recommend')//促销
{
	$viewsdata = rankitem($module,'views');//浏览排行
	$sharesdata = rankitem($module,'shares');//分享排行
}
else if($module=='finance')//财务
{
}