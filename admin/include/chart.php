<?php
/******************************************************************************************************
**  企业+ 5.0 - 企业+社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业+授权协议,查看或使用企业+的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: vieworders file
** Author.......: Winston Dang
** Version......: 5.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
function  chart_data($data,$type,$divid)
{
	global  $tpl,$template;
	$ret=array();
	$colorarr=array("#82c2f1","#d7cdeb","#61d3d4","#fbbba4","#fb7eda","#ffa180");
	foreach($data as $k => $v)
	{
		if($k=='legend')
		{
			if($type=='pie')
			{
				$ret[$k]['x']='left';
				$ret[$k]['orient']='vertical';
			}
			else
			{
				$ret[$k]['y']='bottom';
			}
			$ret[$k]['data']=$v;

		}
		else if($k=='xAxis')
		{
			$ret[$k]['type']='category';
			$ret[$k]['data']=$v;
		}
		else if($k=='series')
		{
			if($type=='pie')
			{
				$arr=array();
				$arr['radius']='60%';
				$arr['type']=$type;
				foreach($v as $ks => $vs)
				{
					$arr['data'][]=array('value'=> $vs[0],'name'=> $ks);
				}
				$ret[$k][]=$arr;
			}
			else{
				$i=0;
				foreach($v as $ks => $vs)
				{
					$colori=$divid+$i;
					$i++;
					if($i>=count($colorarr))
					{
						$i=0;
					}
					$arr=array();
					$arr['name']=$ks;
					$arr['type']=$type;
					if($type=='line')
					{
						$arr['smooth']=true;
						$arr['itemStyle']=array("normal"=>array("areaStyle"=>array("type"=>"default","color"=>$colorarr[$colori]),"color"=>$colorarr[$colori]));
					}
					$arr['data']=$vs;
					$ret[$k][]=$arr;
				}
			}
		}
		else if($k=='title')
		{
			$ret[$k]['text']=$v;
			$ret[$k]['y']='top';
			$ret[$k]['x']='center';
			$ret[$k]['color']='red';
			$ret[$k]['textStyle']['fontSize']=20;
		}
	}
	$ret= substr(json_encode($ret),1,-1);
	eval ("\$ret= \"" . $tpl->get($type,$template). "\";");
	return $ret;
}
function chart_module_html($module,$name,$divid,$sumcount,$sumcountvalue='id')
{
	global $db,$db,$template,$tablepre;
	$today =  strtotime('-1 month')-$cha;
	$sql = "SELECT $sumcount($sumcountvalue) FROM {$tablepre}$module WHERE 1";
	$total = $db->fetchOneBySql($sql);
	$array =$ret= array();
	for($i=0;$i<31;$i++)
	{
		$today += 86400;

		if($module=='count')
		{
			$day = date('Ymd',$today);
			$sql = "SELECT $sumcount($sumcountvalue) FROM {$tablepre}$module WHERE day='$day' ";
		}
		else
		{
			$nextday = $today+86400;
			$sql = "SELECT $sumcount($sumcountvalue) FROM {$tablepre}$module WHERE dateline>='$today' AND dateline<'$nextday' ";
		}
		$info = $db->fetchOneBySql($sql);
		if($info)
		{
			$ret['series'][$name][] = $info;
		}
		else
		{
			$ret['series'][$name][] = 0;
		}
		$ret['xAxis'][] = date("md",$today);
	}
	$ret['title']=$name."(".$total.")";
	$ret['legend']=array($name);
	$info=chart_data($ret,'line',$divid);
	return $info;
}

$newmembers = $newweixin = $dau = $todaypv = $todayshare = $todayshareview = $peruser =0;
$today =  mktime(0, 0, 0, date("m"), date("d"), date("Y"));
$sql = "SELECT count(uid) FROM {$tablepre}members WHERE dateline>'$today'";
$newmembers = $db->fetchOneBySql($sql);
$sql = "SELECT count(uid) FROM {$tablepre}members WHERE dateline>'$today' AND type=1";
$newweixin = $db->fetchOneBySql($sql);
$sql = "SELECT count(uid) FROM {$tablepre}membersinfo WHERE lastvisit>'$today'";
$dau = $db->fetchOneBySql($sql);
$day = date('Ymd');
$sql = "SELECT sum(c_d) FROM {$tablepre}count WHERE day='$day'";
$todaypv = $db->fetchOneBySql($sql);
$sql = "SELECT count(id) FROM {$tablepre}mpmsg WHERE dateline>'$today'";
$todayweixin = $db->fetchOneBySql($sql);
$sql = "SELECT count(id) FROM {$tablepre}share WHERE dateline>'$today'";
$todayshare = $db->fetchOneBySql($sql);
$sql = "SELECT count(id) FROM {$tablepre}shareview WHERE dateline>'$today'";
$todayshareview = $db->fetchOneBySql($sql);
$sql = "SELECT count(uid) FROM {$tablepre}members";
$total = $db->fetchOneBySql($sql);
$peruser = ceil($todaypv/$dau);

$namearr =array('weixin'=>'΢х','app' =>'¿ͻ§¶ɧ,'site'=>'θ');
foreach($namearr as $k => $v)
{
	$sql = "SELECT sum(c_d) FROM {$tablepre}count WHERE 1 and module='$k' ";
	$info = $db->fetchOneBySql($sql);
	if($info)
	{
		$ret1['series'][$v][] = $info;
	}
	else
	{
		$ret1['series'][$v][] = 0;
	}
}
$ret1['title']=$name1;
$ret1['legend']=array_values($namearr);
$echart1=chart_data($ret1,'pie','1');
//΢х·׏ͳ¼Ċ$echart2=chart_module_html('share','΢х·׏ͳ¼ħ,'3','count');
$shareviewnum=$db->fetchOneBySql( "SELECT count(id) FROM {$tablepre}shareview WHERE 1");
$s_snum=sprintf('%.2f',$shareviewnum/$sharenum);
eval ("\$echart4= \"" . $tpl->get('../gauge',$template). "\";");
