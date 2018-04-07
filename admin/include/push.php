<?php
/******************************************************************************************************
**  企业Plus 7.0 - 企业Plus社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业Plus授权协议,查看或使用企业Plus的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: push file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 6/14/2015 12:40:39 AM
***************************************/
set_time_limit(0);
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
if(empty($_POST))
{
	$orderby  = !empty($orderby) ? $orderby: ' id';
	$ascdesc  = !empty($ascdesc) ? $ascdesc: ' DESC';
	$headbuttons .= buildHeadButton('增加',0,'admin.php?action=add&module=push&appid='.$appid);
	$sql="SELECT * from {$tablepre}$action ORDER BY $orderby $ascdesc LIMIT $pagestart,$limit ";
	$data = $db->fetchAssocArrBySql($sql);
}
else
{
	require $site_engine_root.'/mobile/lib/upload.php';
	$success = $failed = 0;
	$prevtwodays = $time-2*86400;
	$user = array();
	if($checkall == 'all')
	{
		$sql = "SELECT openid FROM {$tablepre}members WHERE 1 AND subscribe=1 AND lastmessage>'$prevtwodays'";
		$user = $db->fetchColBySql($sql);
	}
	else
	{
		if(!empty($uid))
		{
			$ids = '\''.implode('\',\'', $_POST['uid']).'\'';
			$sql = "SELECT openid FROM {$tablepre}members WHERE 1 AND subscribe=1 AND uid IN (".$ids.")";
			$user = $db->fetchColBySql($sql);
		}
	}
	$count = count($user);
	$sql = "SELECT * FROM {$tablepre}push WHERE id='$id'";
	$pushinfo = $db->fetchSingleAssocBySql($sql);
	$data = array();
	$type = $pushinfo['type'];
	if($type==0 || $type == '1') // 48小时
	{
		$sendtype = 'news';
		if(!empty($_POST))
		{
			require_once $site_engine_root."mobile/lib/wechat.php";
			require_once $site_engine_root."mobile/lib/error.php";
			require_once $site_engine_root."mobile/lib/function.php";
			$options = options($appid);
			$weObj = new Wechat($options);

			$data = array();
			if(!empty($id))
			{
				$sql = "SELECT * FROM {$tablepre}pushit WHERE pushid='$id' ORDER BY orderid DESC LIMIT 10";
				$info = $db->fetchAssocArrBySql($sql);

				$data = array();
				if(!empty($info))
				{
					foreach($info as $k=> $v)
					{
						if(trim($v['link']))
						{
							if(preg_match('/\?/',$v['link']))
							{
								$url = $v['link'].'&appid='.$appid.'&pushid='.$id.'&sendtype=push';
							}
							else
							{
								$url = $v['link'].'?&appid='.$appid.'&pushid='.$id.'&sendtype=push';
							}
						}
						else
						{
							$url = 'http://'.$_SERVER['HTTP_HOST'].'/mobile/index/'.$v['module'].'.php?id='.$v['moduleid'].'&appid='.$appid.'&pushid='.$id.'&sendtype=push';
						}
						$photo = $v['photo'];
						$data[$k] = array("title" => $v['title'],"description" => $v['title'],"picurl"=>"http://".$_SERVER['HTTP_HOST'].$uploaddir.$photo,"url"=> $url);
					}
				}
				$articles = $data;
				if(!empty($user))
				{
					$i = 1;
					foreach($user as $key=> $value)
					{
						// if($i%100==0)
						// {
							// sleep(2);
						// }
						// debugtofile($value);
						$data = array("touser"=>"$value","msgtype"=>"news","news"=> array("articles" => $articles));
						$msg = $weObj->sendCustomMessage($data);
						if($msg['errcode']==0)
						{
								$success++;
						}
						else
						{
							 $failed++;
						}
						$i++;
					}
				}
			}

			if(intval($weObj->errCode))
			{
				$ret=ErrCode::getErrText($weObj->errCode);
				if(empty($ret))
				{
					$ret = 	$weObj->errMsg;
				}

			}
		}
	}
	else if($type=="2") // 群发
	{
		if(!empty($_POST))
		{
			if(!$pushinfo['media_id'])
			{
				exitandreturn('请先上传微信素材',$module,$action);
			}
			$sendtype = 'news';
			$wakeupstring = '';
			require_once $site_engine_root."mobile/lib/wechat.php";
			require_once $site_engine_root."mobile/lib/error.php";
			require_once $site_engine_root."mobile/lib/function.php";
			$options = options();
			$weObj = new Wechat($options);

			$data = array();
			$sql = "SELECT * FROM {$tablepre}pushit WHERE pushid='$id' ORDER BY orderid DESC LIMIT 10";
			$info = $db->fetchAssocArrBySql($sql);
			if(!empty($info))
			{
				foreach($info as $k=> $v)
				{
					if(trim($v['link']))
					{
						if(preg_match('/\?/',$v['link']))
						{
							$url = $v['link'].'&appid='.$appid.'&pushid='.$id.'&sendtype=push';
						}
						else
						{
							$url = $v['link'].'?&appid='.$appid.'&pushid='.$id.'&sendtype=push';
						}
					}
					else
					{
						$url = 'http://'.$_SERVER['HTTP_HOST'].'/mobile/index/'.$v['module'].'.php?id='.$v['moduleid'].'&appid='.$appid.'&pushid='.$id.'&sendtype=push';
					}
					$photo = $v['photo'];
					$description = $v['description'] ? $v['description']:$v['title'];
					$data[$k] = array("title" => $v['title'],"description" => $description,"picurl"=>"http://".$_SERVER['HTTP_HOST'].$uploaddir.$photo,"url"=> $url);
				}
			}
			$articles = $data;
			if($pushinfo['grouptype']==0 || $pushinfo['groups'] == '')
			{
				//全体群发
				$pushdata = array();
				$pushdata['filter']['is_to_all'] = true;
				$pushdata['msgtype']='mpnews';
				$pushdata['mpnews'] = array('media_id'=>$pushinfo['media_id']);
				$weObj->sendGroupMassMessage($pushdata);

			}
			else
			{
				$success = massPush($id,$articles);
			}

		}
		logs($module,$id,$action);

	}
	$sql = "UPDATE {$tablepre}push SET status=127,success=success+$success,failed=failed+$failed,total=total+$count WHERE id='$id'";
	$db->query($sql);
	exitandreturn(1,$module,$action);
}
?>