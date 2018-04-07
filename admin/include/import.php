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
set_time_limit(0);
require_once $site_engine_root."mobile/lib/wechat.php";
require_once $site_engine_root."mobile/lib/error.php";
//require_once $site_engine_root."mobile/ajax/settings.php";
require_once $site_engine_root."mobile/lib/function.php";
$options = options(0);
$weObj = new Wechat($options);

$sql = "select count(uid) from {$tablepre}members where openid!='' and length(openid)=28";
$havecount = $db->fetchOneBySql($sql);
//$openidarray = array();
$rediskey = $englishname.$space.'haveopenids';
$rdb->redis->del($rediskey);
$i=1;
for($k=0;$k <= $havecount/10000;$k++)
{
	$pagestart = $k*10000;

	$sql = "SELECT distinct openid FROM {$tablepre}members WHERE openid!='' and length(openid)=28 order by uid asc limit $pagestart,10000";
	$temp = $db->fetchColBySql($sql);
	foreach($temp as $_openid)
	{
		$rdb->redis->zAdd($rediskey,$i,$_openid);
		$i++;
	}
}

$info = $weObj->getUserList('');
$result = array();
if(intval($weObj->errCode))
{
	$ret=ErrCode::getErrText($weObj->errCode);

	$result['flag'] = 0;
	$result['error'] = $ret;
	return $result;
}
$total = $info['total'];
$count = $info['count'];
$next_openid = $info['next_openid'];
$rediskey2 = $englishname.$space."needimportopenids";
$rdb->redis->del($rediskey2);
$i = 1;
while(!empty($info['data']['openid']))
{
	foreach($info['data']['openid'] as $key=>$value)
	{
		$ishave = $rdb->redis->zScore($rediskey,$value);
		if(!$ishave)
		{
			$rdb->redis->zAdd($rediskey2,$i,$value);
			$i++;
		}
	}
	$info = $weObj->getUserList($info['next_openid']);

}
$needcount = $rdb->redis->zSize($rediskey2);
require_once $site_engine_root.'mobile/lib/admin.php';

$ret = array();

//每次导入50条数据
for($i=0;$i<=$needcount/50;$i++)
{
	$start = $i*50;
	$end = $start+49;
	$openids = $rdb->redis->zRange($rediskey2,$start,$end);
	$result = importuser($weObj,$openids);
	if($result!='success')
	{
		$ret['flag'] = 0;
		$ret['error'] = $result;
		echo jsondata($ret);
		exit;
	}
}
$ret['flag'] = 1;
$ret['data'] = $needcount;
echo jsondata($ret);
exit;
?>