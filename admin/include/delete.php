<?php
/******************************************************************************************************
**  企业+ 5.0 - 企业+社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业+授权协议,查看或使用企业+的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: delete file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
$alertinfo = $alert_info[$module][$action];
$variable = $module.'sys';
$variable = $$variable;
$chars = explode(',',$variable['char_setting']);
if((in_array($module,$system_key) && $module!='members') || $module=='channel')  //不等于 members的时候才会进入if  所以以下对于members的判断都是多余  已经注释  liuqing  17-2-23
{
	if(in_array('moderate',$chars) && $module!='supplierproduct' && $module!='kfaccount')
	{
		$sql = "SELECT moderate FROM {$tablepre}$module WHERE id='$id'";
		$moderate = $db->fetchOneBySql($sql);
		if($moderate!=-1 && $variable['isshow']==1)
		{
			// liuqing 17-2-23
//			if($module == 'members')
//			{
//				$sql = "UPDATE {$tablepre}$module SET moderate=-1 WHERE uid='$id'";
//			}
//			else
//			{
				$sql = "UPDATE {$tablepre}$module SET moderate=-1 WHERE id='$id'";
//			}
			$db->query($sql);

		}
		else
		{
			if($module=='mpmenu')
			{
				$sql = "select parentid from {$tablepre}mpmenu where id='$id'";
				$parentid = $db->fetchOneBySql($sql);
				if($parentid>0)
				{
					$sql = "update {$tablepre}mpmenu set type='click' where id='$parentid'";
					$db->query($sql);
				}
				$sql = "delete from {$tablepre}mpmenu where id='$id'";

			}
			else if($module=='pages')
			{
				$sql = "select * from {$tablepre}pages where id='$id'";
				$pageinfo = $db->fetchSingleAssocBySql($sql);
				if($pageinfo['page_id'])
				{
					require_once $site_engine_root."mobile/lib/wechat.php";
					require_once $site_engine_root."mobile/lib/error.php";
					require_once $site_engine_root."mobile/ajax/settings.php";
					require_once $site_engine_root."mobile/lib/function.php";
					$options = options(0);
					$weObj = new Wechat($options);
					$weObj->deleteShakeAroundPage(intval($pageinfo['page_id']));

				}
				$sql = "DELETE FROM {$tablepre}$module WHERE id=$id";
				$db->query($sql);
			}
			else if($module=='card')
			{
				$sql = "select card_id from {$tablepre}card where id='$id'";
				$card_id = $db->fetchOneBySql($sql);
				if($card_id==""){
					echo "删除不成功";exit;
				}
				require_once $site_engine_root."mobile/lib/wechat.php";
				$options = options(0);
				$weObj = new Wechat($options);
				$weObj->delCard($card_id);
				$sql = "UPDATE {$tablepre}card set moderate = '-1' WHERE id=$id";
				$db->query($sql);

			}
			else
			{
				$sql = "DELETE FROM {$tablepre}$module WHERE id='$id'";
			}
			$db->query($sql);
			if($module=='news')
			{
				$sql = "delete from {$tablepre}newscontent where id='$id'";
				$db->query($sql);
			}
		}
	}
	else if($module=='creditsettings')
	{
		$sql = "DELETE FROM {$tablepre}$module WHERE id='$id'";
		$db->query($sql);
		if($rdb->onoff)
		{
			$creditsettings = $rdb->redis->keys($englishname.':creditsettings*');
			foreach($creditsettings as $v)
			{
				$rdb->redis->del($v);
			}
		}
		$rdb->del($act.$space.$_POST['type']);
	}
	else if($module=='usergroup')
	{
		$sql = "DELETE FROM {$tablepre}usergroup WHERE id='$groupid' AND uid=?";
		$db->query($sql,[$uid]);
		$rdb->del('usergroups_'.$uid);
	}
	else if($module=='supplierproduct')
	{
		$sql = "select productid,supplierid from {$tablepre}supplierproduct where id='$id' and moderate=2";
		$authpinfo = $db->fetchSingleAssocBySql($sql);

		$sql = "select id from {$tablepre}supplierproduct where productid='$authpinfo[productid]' and supplierid='$authpinfo[supplierid]' and moderate!=2";
		$spids = $db->fetchColBySql($sql);

		if($spids)
		{
			$sql = "DELETE from {$tablepre}agencyproduct WHERE productid in(".implode(",",$spids).")";
			$db->query($sql);
			$sql = "update {$tablepre}supplierproduct set moderate=-1 WHERE id in(".implode(",",$spids).")";
			$db->query($sql);
			$sql = "DELETE from {$tablepre}discount WHERE productid in (".implode(",",$spids).")";
			$db->query($sql);
			$sql = "DELETE from {$tablepre}recommend WHERE productid in (".implode(",",$spids).")";
			$db->query($sql);
			$sql = "DELETE from {$tablepre}favorites WHERE type='product' AND moduleid in(".implode(",",$spids).")";
			$db->query($sql);
			$sql = "DELETE from {$tablepre}shops WHERE flag='-1' AND pid in(".implode(",",$spids).")";
			$db->query($sql);
			$sql = "DELETE FROM {$tablepre}pushit WHERE module='product' AND moduleid in(".implode(",",$spids).")";
			$db->query($sql);
			$sql = "DELETE FROM {$tablepre}sorts WHERE module='$module' AND moduleid in(".implode(",",$spids).")";
			$db->query($sql);
		}
		foreach($spids as $k=>$v)
		{
			$rdb->delRow("supplierproduct",$v);
		}

		$sql = "delete from {$tablepre}supplierproduct where id='$id' and moderate=2";
		$db->query($sql);
		exitandreturn(1,$module,$action);
	}
	else if($module == 'kfaccount')
	{
		$sql = "select uid from {$tablepre}kfaccount where id =?";
		$uid = $db->fetchOneBySql($sql,[$id]);
		$sql = "DELETE FROM {$tablepre}kfaccount where uid=?";
		$db->query($sql,[$uid]);
		if($uid)
		{
			$sql = "SELECT uid from {$tablepre}members where ownid=?";
			$uids = $db->fetchColBySql($sql,[$uid]);
//			p($uids);
			foreach($uids as  $_uid)
			{
				$rdb->update('members',$_uid,['ownid'=>0]);
			}
			$sql = "UPDATE {$tablepre}members set ownid=0 where ownid=?";
			$db->query($sql,[$uid]);
		}
	}
	else
	{
		$sql = "DELETE FROM {$tablepre}$module WHERE id='$id'";
		$db->query($sql);
	}

	if($module=='channel' || preg_match('/class/',$module))
	{
		$sql = "SELECT parentid FROM {$tablepre}$module WHERE id='$id'";
		$parentid = $db->fetchOneBySql($sql);
		if($parentid)
		{
			$sql = "select count(id) from {$tablepre}$module where parentid='$parentid' and moderate!=-1";
			$branch = $db->fetchOneBySql($sql);
			$sql = "UPDATE {$tablepre}$module SET branch='$branch' WHERE id='$parentid'";
			@$db->query($sql);
		}
		$sql = "UPDATE {$tablepre}$module SET moderate=-1 WHERE parentid='$id'";
		$db->query($sql);
	}
	else if($module=='product')
	{
		$sql = "select id from {$tablepre}supplierproduct where productid='$id' and moderate!=2 ";
		$spids = $db->fetchColBySql($sql);

		if($spids)
		{
			$sql = "delete from {$tablepre}agencyproduct where productid in(".implode(",",$spids).")";
			$db->query($sql);
			$sql = "update {$tablepre}supplierproduct set moderate=-1 where id in(".implode(",",$spids).")";
			$db->query($sql);
			$sql = "delete from {$tablepre}discount where productid in (".implode(",",$spids).")";
			$db->query($sql);
			$sql = "delete from {$tablepre}recommend where productid in (".implode(",",$spids).")";
			$db->query($sql);
			$sql = "delete from {$tablepre}favorites where type='product' and moduleid in(".implode(",",$spids).")";
			$db->query($sql);
			$sql = "delete from {$tablepre}shops where flag='-1' and pid in(".implode(",",$spids).")";
			$db->query($sql);
			$sql = "DELETE FROM {$tablepre}pushit WHERE module='product' AND moduleid in(".implode(",",$spids).")";
			$db->query($sql);
			$sql = "DELETE FROM {$tablepre}sorts WHERE module='$module' AND moduleid in(".implode(",",$spids).")";
			$db->query($sql);
		}
		$sql = "delete from {$tablepre}supplierproduct where productid='$id' and moderate=2";
		$db->query($sql);
		foreach($spids as $k=>$v)
		{
			$rdb->delRow("supplierproduct",$v);
		}
	}
	else if($module=='productoptions')
	{
		$sql = "select id,options from {$tablepre}supplierproduct where options='$id'";
		$spinfo = $db->fetchSingleAssocBySql($sql);
		$spid = $spinfo['id'];
		if($spid)
		{
			$sql = "update {$tablepre}supplierproduct set moderate=-1 where options='$spinfo[options]'";
			$db->query($sql);
			$sql = "delete from {$tablepre}agencyproduct where productid='$spid'";
			$db->query($sql);
			$sql = "delete from {$tablepre}discount where productid='$spid'";
			$db->query($sql);
			$sql = "delete from {$tablepre}recommend where productid='$spid'";
			$db->query($sql);
			$sql = "delete from {$tablepre}favorites where type='product' and moduleid='$spid'";
			$db->query($sql);
			$sql = "delete from {$tablepre}shops where flag='-1' and pid='$spid'";
			$db->query($sql);
			$sql = "DELETE FROM {$tablepre}pushit WHERE module='product' AND moduleid='$spid'";
			$db->query($sql);
			$sql = "DELETE FROM {$tablepre}sorts WHERE module='$module' AND moduleid='$spid'";
			$db->query($sql);
		}
		$rdb->delRow("supplierproduct",$spid);
	}
	else if($module=='meeting')
	{
		$sql = "delete from {$tablepre}meetingmembers where meetingid=?";
		$db->query($sql,[$id]);
		$sql = "select id from {$tablepre}operator where meetingid=?";
		$operator_ids = $db->fetchColBySql($sql,[$id]);
		$sql = "update {$tablepre}operator set meetingid=0 where meetingid=?";
		$db->query($sql,[$id]);
		foreach($operator_ids as $v)
		{
			$rdb->update('operator',$v,['meetingid'=>0]);
		}

	}
	else if($module=='operator')
	{
		$sql = "delete from {$tablepre}templates where operatorid=?";
		$db->query($sql,[$id]);
		$rdb->delRow($module,$id);
		$sql = "delete from {$tablepre}record where module=? and moduleid=?";
		$db->query($sql,[$module,$id]);
		$sql = "delete from {$tablepre}operatorlottery where operatorid=?";
		$db->query($sql,[$id]);
		$sql = "delete from {$tablepre}operatormenu where operatorid=?";
		$db->query($sql,[$id]);
		$sql = "delete from {$tablepre}lotteryresult where drawid=?";
		$db->query($sql,[$id]);
		$sql = "delete from {$tablepre}operatorseats where module=? and moduleid=?";
		$db->query($sql,[$module,$id]);
	}
	else if($module == 'helpbuy')//助力活动
	{
		$rdb->delRow('helpbuy',$id);
		$sql = "select id from {$tablepre}crowduser where module='helpbuy' and moduleid=?";
		$crowduser_ids = $db->fetchColBySql($sql,[$id]);
		$sql = "delete from {$tablepre}crowduser where module='helpbuy' and moduleid=?";
		$db->query($sql,[$id]);
		foreach ($crowduser_ids as $v)
		{
			$rdb->delRow('crowduser',$v);
		}
	}
	else if($module == 'groups')     // add   liuqing  17-2-23
	{
		$sql = "DELETE FROM {$tablepre}usergroup where id='$id'";//删部门在上面已经做了现在删人员
		$db->query($sql);
		$sql = "DELETE FROM {$tablepre}permission where groupid='$id'";
		$db->query($sql);
	}
	$sql = "DELETE FROM {$tablepre}pushit WHERE module='$module' AND moduleid='$id'";
	$db->query($sql);
	$sql = "DELETE FROM {$tablepre}infodict WHERE module='$module' AND moduleid='$id'";
	$db->query($sql);
	$sql = "DELETE FROM {$tablepre}sorts WHERE module='$module' AND moduleid='$id'";
	$db->query($sql);
	if($ifredis==1)
	{
		$rdb->flushall();
	}
//	$sql="DELETE FROM {$tablepre}timeline WHERE module='$module' and moduleid=$id ";
//	$db->query($sql);
	logs($module,$id,$action);
}
else if($module=='blacklist')
{
	$sql = "delete from {$tablepre}blacklist where uid='$id'";
	$db->query($sql);
	$rdb->del('blacklist_hongbao');
	$rdb->del('blacklist_credit');
}
else if($module=='weixin')
{
	//printarray($_GET);EXIT;
	$sql = "DELETE FROM {$tablepre}mpmsg WHERE id='$id'";
	$db->query($sql);
}
else if($module =='meeting')
{
	$sql = "DELETE FROM {$tablepre}$module WHERE id='$id'";
	$db->query($sql);
	$rdb->delRow('meeting'.$id);
	if($pwd = $rdb->getRow('meeting',$id,'password'))
	{
		$rdb->del('meeting'.$space.'pwd'.$space.$pwd);
	}
}
//groups的删除没走这里   liuqing  17-2-23
//else if($module=='groups')
//{
//	if($id)
//	{
//		$sql = "DELETE FROM {$tablepre}usergroup where id='$id'";
//		$db->query($sql);
//
//		$sql = "DELETE FROM {$tablepre}permission where groupid='$id'";
//		$db->query($sql);
//
//		$sql = "DELETE FROM {$tablepre}groups WHERE id='$id' ";
//		$db->query($sql);
//		exitandreturn(1,$module,$action);
//	}
//}
else if(preg_match('/class/',$module))
{
	$sql = "SELECT moderate FROM {$tablepre}$module WHERE id='$id'";
	$moderate = $db->fetchOneBySql($sql);
	if($moderate!=-1)
	{
		$sql = "UPDATE {$tablepre}$module SET moderate=-1 WHERE id='$id'";
	}
	else
	{
		$sql = "DELETE FROM {$tablepre}$module WHERE id='$id'";
	}
	$db->query($sql);
	$sql = "SELECT parentid FROM {$tablepre}$module WHERE id='$id'";
	$parentid = $db->fetchOneBySql($sql);
	if($parentid)
	{
		$sql = "UPDATE {$tablepre}$module SET branch=branch-1 WHERE id='$parentid'";
		$db->query($sql);
	}
	$sql = "UPDATE {$tablepre}$module SET moderate=-1 WHERE parentid='$id'";
	$db->query($sql);
}
else if($module=='members')
{
	$uid = $uid ? $uid :$id;
	$openid = $rdb->userInfo($uid)['openid'];
	$sql = "SELECT uid FROM {$tablepre}members WHERE openid='$openid'";
	$this_uids = $db->fetchColBySql($sql);
	$sql = "DELETE FROM {$tablepre}members WHERE openid='$openid'";
	$db->query($sql);
	foreach($this_uids as $uid)
	{
		$sql = "DELETE FROM {$tablepre}membersinfo WHERE uid='$uid'";
		$db->query($sql);
		$sql = "DELETE FROM {$tablepre}kfaccount WHERE uid='$uid'";
		$db->query($sql);
		$sql = "DELETE FROM {$tablepre}views WHERE uid='$uid'";
		$db->query($sql);
		$sql = "DELETE FROM {$tablepre}kviews WHERE uid='$uid'";
		$db->query($sql);
		$sql = "DELETE FROM {$tablepre}message WHERE uid='$uid'";
		$db->query($sql);
		$sql = "DELETE FROM {$tablepre}record WHERE uid='$uid'";
		$db->query($sql);
		$sql = "DELETE FROM {$tablepre}share WHERE uid='$uid'";
		$db->query($sql);
		$sql = "DELETE FROM {$tablepre}shareview WHERE uid='$uid'";
		$db->query($sql);
		$sql = "DELETE FROM {$tablepre}usergroup WHERE uid='$uid'";
		$db->query($sql);
		$sql = "select uid,touid from {$tablepre}friends where uid='$uid' or touid='$uid'";
		$fdata = $db->fetchAssocArrBySql($sql);
		foreach($fdata as $v)
		{
			if($v['uid'] !=$uid)
			{
				$rdb->del("friendof".$v['uid']);
			}
			if($v['touid'] !=$uid)
			{
				$rdb->del("friendof".$v['touid']);
			}
		}
		$sql = "DELETE FROM {$tablepre}friends WHERE uid='$uid' or touid='$uid'";
		$db->query($sql);
		$rdb->del("friendof".$uid);
		$sql = "update {$tablepre}members set ownid=0 WHERE ownid='$uid'";
		$db->query($sql);
		$rdb->delUser($uid);
	}
}
else
{
	if($module=='shopstore')
	{
		$module='agency';
	}
	if($module=='agency')
	{
		$useruid = $db->fetchOneBySql("select uid from {$tablepre}agency where id='$id'");
		$rdb->del('shopidentity_'.$useruid);
	}
	if($module=='website')
	{
		$sql = "select * from {$tablepre}website where id=$id";
		$_siteinfo = $db->fetchSingleAssocBySql($sql);
		
		$_sitedomain = $_siteinfo['sitedomain'];
		$rdb->redis->del($main_dbname.$space.'website_'.$_sitedomain);
		$rdb->redis->del($main_dbname.$space.'website_'.$_siteinfo['authorizer_appid']);
		$rdb->redis->del($main_dbname.$space.'sitepermission_'.$_sitedomain);
	}
	$sql = "DELETE FROM {$tablepre}$module WHERE id=$id";
	$db->query($sql);
	$sql = "DELETE FROM {$tablepre}pushit WHERE module='$module' AND moduleid='$id'";
	$db->query($sql);
	$sql = "DELETE FROM {$tablepre}sorts WHERE module='$module' AND moduleid='$id'";
	$db->query($sql);

}
$sql = "DELETE FROM {$tablepre}qrcode WHERE module='$module' AND moduleid='$id'";
$db->query($sql);
logs($module,$id,$action);
// 清除缓存
$delarray = array('members','news','navi');
if(in_array($module,$delarray))
{
	if($module == 'members')
	{
		$rdb->delUser($id);
	}
	else if($module =='news')
	{
		$rdb->delRow('news',$id);
		$rdb->delRow('newscontent',$id);
	}
	else if($module == 'navi')
	{
		$rdb->del('webnavi');
	}

}
if($module == 'news')
{
	$sql = "DELETE FROM {$tablepre}views WHERE module='$module' AND moduleid='$id'";
	$db->query($sql);
	$sql = "DELETE FROM {$tablepre}shareview WHERE module='$module' AND moduleid='$id'";
	$db->query($sql);
}
exitandreturn(1,$module,$action);
?>
