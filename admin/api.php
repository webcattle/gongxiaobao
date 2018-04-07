<?php
/******************************************************************************************************
**  企业+ 3.0 - 企业+社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2015. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业+' 微信号
**  请详细阅读企业+授权协议,查看或使用企业+的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: api file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
header('Content-Type: application/json');
require_once '../mobile/ajax/header.php';
require_once './include/settings.php';
require_once 'include/language.php';
require_once 'include/function.php';
require_once 'include/global.php';
require_once '../mobile/lib/admin.php';
require_once '../mobile/lib/function.php';
require_once '../mobile/lib/service.php';
require_once '../mobile/lib/shopfunctions.php';

if(file_exists($site_engine_root.$userdir.'plugins/admin/include/settings.php'))
{
	require_once $site_engine_root.$userdir.'plugins/admin/include/settings.php';
}
if(file_exists($site_engine_root.$userdir.'plugins/admin/include/language.php'))
{
	require_once $site_engine_root.$userdir.'plugins/admin/include/language.php';
}
// 安全保护
if(strlen($type)>16 || strlen($action)>16 || strlen($module)>16|| strlen($moduleid)>16 || strlen($parentid)>16 ||  strlen($pagestart)>16 || strlen($limit)>16 || strlen($ascdesc)>16)
{
	exit('ni');
}
if(min($usergroup)>1000)
{
	exit('ni');
}
$limit	 = intval($_GET['limit']) ? intval($_GET['limit']):'30';
$pagestart= intval($_GET['pagestart']) ? intval($_GET['pagestart']):'0';
$today=strtotime(date('Y-m-d',time()));
$yesterday=$today-24*60;
$system_key = array_keys($system_preview);
if(preg_match('/class/',$module))
{
	$module = str_replace('class','',$module);
	$flag = 1;
}
$variable = $module.'sys';
$variable = $$variable;
$chars = explode(',',$variable['char_setting']);
// 页面
if($module=='pages')
{
	$sql = "SELECT * FROM {$tablepre}devices WHERE id='$id'";
	$devicesinfo = $db->fetchSingleAssocBySql($sql);
	require_once $site_engine_root."mobile/lib/wechat.php";
	require_once $site_engine_root."mobile/lib/error.php";
	require_once $site_engine_root."mobile/lib/function.php";
	$options = options();
	$weObj = new Wechat($options);
	$info = $weObj->searchShakeAroundPage($page_ids=array(),$begin=0,$count=10);
	$info = $info['data']['pages'];
	// printarray($info);
	// exit;
	if(!empty($info))
	{
		foreach($info as $key=> $value)
		{
			$sql = "SELECT id FROM {$tablepre}pages WHERE page_id='$value[page_id]'";
			$id = $db->fetchOneBySql($sql);
			if(empty($id))
			{
				$sql = "INSERT INTO {$tablepre}pages(title,comment,description,icon_url,page_id,page_url) VALUES('$value[title]','$value[comment]','$value[description]','$value[icon_url]','$value[page_id]','$value[page_url]')";
				$db->query($sql);
			}
			else
			{
				$sql = "UPDATE {$tablepre}pages SET title='$value[title]',comment='$value[comment]',description='$value[description]',icon_url='$value[icon_url]',page_id='$value[page_id]',page_url='$value[page_url]' WHERE id='$id'";
				$db->query($sql);
			}
		}
	}
	$sql = "SELECT * FROM {$tablepre}pages WHERE 1 AND page_id > 0 ORDER BY id DESC";
	$data['pages'] = $db->fetchAssocArrBySql($sql);
	$data['page_ids'] = explode(",",$devicesinfo['page_ids']);
	echo jsondata($data);exit;
}
else if($module == 'agency')
{
	if ($action == 'getxiaji')
	{
		$sql = "select a.id,a.title from {$tablepre}{$module} a left join {$tablepre}members b on a.uid=b.uid where b.uploader='$uid' and b.uploader!=b.uid and a.moderate=1 and a.module='$module'";
		$xiaji = $db->fetchAssocArrBySql($sql);
		echo json_encode($xiaji);
		exit;
	}
	else if($action == 'modifylevels')
	{
		$sql = "update {$tablepre}agency set levels='$levels' where id='$id'";
		$db->query($sql);
		echo '1';
		exit;
	}
	else if($action == 'getsupplier')
	{
		$sql = "select supplierid from {$tablepre}supplierproduct where productid='$productid' and moderate=2";
		$haveid = $db->fetchOneBySql($sql);
		$ret =array();
		if ($haveid)
		{
			$ret['flag']=0;
			$ret['error'] = '已经有授权的供应商了，请删除后再添加别的供应商';
			echo jsondata($ret);exit;

		}
		$sql = "select id,title from {$tablepre}{$module} where module='shopstore' and id not in (select supplierid from {$tablepre}supplierproduct where productid='$productid' and moderate=2) order by id desc";

		$result = $db->fetchAssocArrBySql($sql);
		$ret['flag']=1;
		$ret['data'] = $result;

		echo json_encode($ret);
		exit;
	}
}
else if((in_array($module,$system_key) && $module!='message' && in_array('classid',$chars)) && $module!='supplierproduct'||$module=='channel')
{
	$top = array('0'=>array('id'=>0,'title'=>'顶级','parentid'=>'0'));
	if($module=='channel')
	{
		$sql = "SELECT id,title,parentid,branch FROM {$tablepre}$module WHERE moderate=1";
	}
	elseif($module=='replytemplate')
	{
		$sql = "SELECT id,title,parentid,branch FROM {$tablepre}{$module}class WHERE moderate=1 and uid=1";
	}
	else
	{
		if(in_array('classid',$chars))
		{
			$sql = "SELECT id,title,parentid FROM {$tablepre}{$module}class	WHERE moderate=1";
		}
	}

	$data = $db->fetchAssocArrBySql($sql);
	if($module=='channel' || $flag==1)
	{
		$data = array_merge($top,$data);
	}
	if($moduleid)
	{
		$sql = "select id,classid from {$tablepre}{$module} where (id='$moduleid' or targetid='$moduleid') AND moderate=1";
		$result = $db->fetchAssocArrBySql($sql);
		$mainclassid = 0;
		$linkclassid = array();
		foreach($result as $key => $value)
		{
			if ($value['id']==$moduleid)
			{
				$mainclassid = $value['classid'];
			}
			else
			{
				$linkclassid[]=$value['classid'];
			}
		}
		foreach($data as $key => $value)
		{
			if ($value['id']==$mainclassid)
			{
				$data[$key]['classflag']=100;
			}
			else if(in_array($value['id'],$linkclassid))
			{
				$data[$key]['classflag']=1;
			}
			else
			{
				$data[$key]['classflag']=0;
			}
		}
	}
	echo jsondata($data);exit;
}
else if($module == 'mpmenu')
{
	$top = array('0'=>array('id'=>0,'title'=>'顶级','parentid'=>'0'));
	$sql = "SELECT id,title,parentid FROM {$tablepre}{$module} WHERE module='menu' AND parentid=0 AND appid='$appid'";
	$data = $db->fetchAssocArrBySql($sql);
	$data = array_merge($top,$data);
	echo jsondata($data);exit;
}
else if($module == 'members')
{
	$sql = "SELECT id,title FROM {$tablepre}groups";
	$info = $db->fetchAssocArrBySql($sql);
	$data['groups'] = $info;
	$sql = "SELECT id,uid,username FROM {$tablepre}usergroup";
	$info = $db->fetchAssocArrBySql($sql);
	$data['members'] = $info;
	echo jsondata($data);exit;
}
else if($module == 'groups')
{
	$sql = "SELECT id,title FROM {$tablepre}groups";
	$info = $db->fetchAssocArrBySql($sql);
	echo jsondata($info);exit;
}
else if($module == 'message')
{
	// $today =  mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	$sql = "SELECT value FROM {$tablepre}settings WHERE variable='lastcheckmessage'";
	$today = $db->fetchOneBySql($sql);
	$sql = "SELECT count(*) FROM {$tablepre}mpmsg WHERE dateline>'$today' AND meetingid=0";
	$count = $db->fetchOneBySql($sql);
	$data['count'] = $count;
	echo jsondata($data);exit;
}
else if($module=='search')
{
	$data = array();
	if($keyword)
	{
		if($type=='members')
		{
			$sql = "SELECT uid, linkman as username,avatar FROM {$tablepre}members WHERE username LIKE '%".$keyword."%' OR linkman LIKE '%".$keyword."%'";
			$data = $db->fetchAssocArrBySql($sql);
		}
		else if($type=='supplierproduct')
		{
			$sql = "SELECT id, productname as title,options FROM {$tablepre}{$type} WHERE productname LIKE '%".$keyword."%' and moderate=1";
			$data = $db->fetchAssocArrBySql($sql);
			foreach($data as $key => $value)
			{
				$_title = $value['title'];
				if($value['options'])
				{
					$_title .= '('.getOptionById($value['options']).')';
				}
				$data[$key]['title'] = $_title;
			}
		}
		else
		{
			$sql = "SELECT id, title FROM {$tablepre}{$type} WHERE title LIKE '%".$keyword."%'";
			$data = $db->fetchAssocArrBySql($sql);
		}
	}
	echo jsondata($data);exit;
}
else if($module=='searchproduct')
{
	$data = array();
	if($keyword)
	{
		if($from=='product')
		{
			$sql = "SELECT id,title as productname,photo from {$tablepre}product where moderate=1 and title like '%".$keyword."%'";
		}
		else
		{
			$sql = "SELECT a.id,a.supplierid,b.photo,a.productname,a.options,c.title FROM {$tablepre}supplierproduct a left join {$tablepre}product b on a.productid=b.id left join {$tablepre}agency c on a.supplierid=c.id WHERE a.moderate=1 and b.moderate=1 and a.productname LIKE '%".$keyword."%'";
		}
		$data = $db->fetchAssocArrBySql($sql);
		if($from != 'product')
		{
			foreach($data as $key=>$value)
			{
				$data[$key]['options'] = getOptionStr($value['options']);
			}
		}
	}
	echo jsondata($data);exit;
}
else if($module=='getmodules')
{
	printarray($system_key);
}
else if($module=='getmodulesclassid')
{
	printarray($system_key);
}
else if($module=='cascade')
{
	if($action)
	{
		$variable = $action.'sys';
		$variable = $$variable;

		$chars = explode(",",$variable['char_setting']);
	}
	if(!$action)
	{
		$data = array();
		$i = 0;
		if($type!=1)
		{
			$data[$i]['id'] = 'channel';
			$data[$i]['name'] = $l['channel'];
			$data[$i]['haveclass'] = 1;
			$data[$i]['haveitems'] = 0;
			$i++;
		}
		// printarray($adminarray);
		if(!empty($adminarray['operator']) )
		{
			if($type==1)
			{
				unset($adminarray['operator']['operator']);
			}
			// printarray($adminarray);
			$oper_notarray = array('getcard','share','qrcode','qrpay','devices','credits','sharetask','hongbao','qyhongbao','eventhongbao','sharehongbao','credittohongbao','creditsettings','gratuity');
			foreach($adminarray['operator'] as $key=> $value)
			{
				if(in_array($key,$oper_notarray))
				{
					continue;
				}
				else
				{
					$data[$i]['id'] = $key;
					$data[$i]['name'] = $l[$key];
					$data[$i]['haveclass'] = 0;
					$data[$i]['haveitems'] = 1;
					$i++;
				}
			}
		}

		if($type!=1)
		{
			if(!empty($system_preview))
			{
				foreach($system_preview as $key => $value)
				{
					$variable = $key.'sys';
					$variable = $$variable;
					if($variable['show']==1 && $key!='report')
					{

						$data[$i]['id'] = $key;
						$data[$i]['name'] = $variable['name'];
						$chars = explode(",",$variable['char_setting']);
						if(in_array('classid',$chars))
						{
							$data[$i]['haveclass']=1;
						}
						else
						{
							$data[$i]['haveclass'] = 0;
						}
						$data[$i]['haveitems'] = 1;
						if($key=='baoming')
						{
							$data[$i]['url'] = '/mobile/service.php?module=baoming&action=add';
							$data[$i]['haveitems'] = 0;
						}

						$i++;
					}
				}
			}
			$data[$i]['id'] = 'navi';
			$data[$i]['name'] = '其他';
			$data[$i]['haveclass'] = 0;
			$data[$i]['haveitems'] = 1;
			$i++;
		}

		if(sitepermission('shop'))
		{
			$data[$i]['id'] = 'shop';
			$data[$i]['name'] = '商城';
			$data[$i]['haveclass'] = 0;
			$data[$i]['haveitems'] = 0;
			$i++;
		}

		$data[$i]['id'] = 'creditshop';
		$data[$i]['name'] = '金币商城';
		$data[$i]['haveclass'] = 0;
		$data[$i]['haveitems'] = 0;
		$i++;

		$data[$i]['id'] = 'wifi';
		$data[$i]['name'] = '微信连wifi';
		$data[$i]['haveclass'] = 0;
		$data[$i]['haveitems'] = 0;
		$i++;

		$data[$i]['id'] = 'keywords';
		$data[$i]['name'] = '自动回复';
		$data[$i]['haveclass'] = 0;
		$data[$i]['haveitems'] = 1;
		$data[$i]['url'] = 'keywords_{id}';
	}
	else if($action=='operatormenu')
	{
		$data = array();
		$i = 0;
		if($type!=1)
		{
			$data[$i]['id'] = 'channel';
			$data[$i]['name'] = $l['channel'];
			$data[$i]['haveclass'] = 1;
			$data[$i]['haveitems'] = 0;
			$i++;
		}
		$data[$i]['id'] = 'parent';
		$data[$i]['name'] = '父级菜单';
		$data[$i]['haveclass'] = 0;
		$data[$i]['haveitems'] = 0;
		$i++;

		$data[$i]['id'] = 'function';
		$data[$i]['name'] = '活动功能';
		$data[$i]['haveclass'] = 0;
		$data[$i]['haveitems'] = 0;
		$i++;
		// printarray($adminarray);
		if(!empty($adminarray['operator']))
		{
			if($type==1)
			{
				unset($adminarray['operator']['operator']);
			}
			// printarray($adminarray);
			$oper_notarray = array('getcard','share','qrcode','qrpay','devices','credits','sharetask','hongbao','qyhongbao','eventhongbao','sharehongbao','credittohongbao','creditsettings','gratuity');
			foreach($adminarray['operator'] as $key=> $value)
			{
				if(in_array($key,$oper_notarray))
				{
					continue;
				}
				else
				{
					$data[$i]['id'] = $key;
					$data[$i]['name'] = $l[$key];
					$data[$i]['haveclass'] = 0;
					$data[$i]['haveitems'] = 1;
					$i++;
				}
			}
		}

		if($type!=1)
		{
			if(!empty($system_preview))
			{
				foreach($system_preview as $key => $value)
				{
					$variable = $key.'sys';
					$variable = $$variable;
					if($variable['show']==1)
					{
						$data[$i]['id'] = $key;
						$data[$i]['name'] = $variable['name'];
						$chars = explode(",",$variable['char_setting']);
						if(in_array('classid',$chars))
						{
							$data[$i]['haveclass']=1;
						}
						else
						{
							$data[$i]['haveclass'] = 0;
						}

						$data[$i]['haveitems'] = 1;
						$i++;
					}
				}
			}
			$data[$i]['id'] = 'navi';
			$data[$i]['name'] = '其他';
			$data[$i]['haveclass'] = 0;
			$data[$i]['haveitems'] = 1;
			$i++;
		}

		if($isshopping)
		{
			$data[$i]['id'] = 'shop';
			$data[$i]['name'] = '商城';
			$data[$i]['haveclass'] = 0;
			$data[$i]['haveitems'] = 0;
			$i++;
		}

		$data[$i]['id'] = 'wifi';
		$data[$i]['name'] = '微信连wifi';
		$data[$i]['haveclass'] = 0;
		$data[$i]['haveitems'] = 0;


	}
	else if($action=='function')
	{
		foreach($select['operator']['functions']  as $key=>$value)
		{
			$data[$key] =$value;
		}
	}
	else if($action == 'channel')
	{
		$sql = "SELECT id,title,parentid,branch FROM {$tablepre}$action WHERE moderate=1 ORDER BY id ASC";
		$data = $db->fetchAssocArrBySql($sql);
	}
	else if($action == 'navi' || in_array($action,array_keys($naviarray)))
	{
		$data = $naviarray;
	}
	else if(preg_match("/class/",$action))
	{
		$sql = "SELECT id,title,parentid,branch FROM {$tablepre}{$action}  WHERE moderate=1 ORDER BY id ASC";
		$data = $db->fetchAssocArrBySql($sql);
	}
	else if(in_array($action,array_keys($system_preview)) && in_array('classid',$chars))
	{
		$sql = "SELECT id,title,parentid,branch FROM {$tablepre}{$action}class  WHERE moderate=1 ORDER BY id ASC";
		$data = $db->fetchAssocArrBySql($sql);
	}
	else if(in_array($action,array_keys($adminarray)))
	{
		$sql = "SELECT id,parentid FROM {$tablepre}{$action} WHERE moderate=1 ORDER BY id ASC";
		$data = $db->fetchAssocArrBySql($sql);
	}
	else if($action=='plugin'|| $action=='wifi')
	{
		$data = array();
	}
	else if($action=='areacode')
	{
		$sql = "select code as id,parentcode as parentid,title from {$tablepre}areacode order by parentid asc,id asc";
		$data = $db->fetchAssocArrBySql($sql);
	}
	else if($action=='system')
	{
		$data = array();
	}
	else
	{
		$sql = "SELECT id,title,dateline FROM {$tablepre}$action ORDER BY id DESC";
		$data = $db->fetchAssocArrBySql($sql);
	}
	echo jsondata($data);exit;
}
else if($module=='push')
{
	$sql = "SELECT id,title,photo FROM {$tablepre}pushit WHERE flag=0 ORDER BY orderid DESC LIMIT 10";
	$data = $db->fetchAssocArrBySql($sql);
	echo jsondata($data);exit;
}
else if($module == 'getkeyword')
{
	$keys = splitwords($title);
	if(is_array($keys))
	{
		foreach($keys as $key=> $value)
		{
			$newkeys[] = $value;
		}
	}
	echo jsondata($newkeys);exit;
}
else if($module=='update')
{
	if(intval($uid))
	{
		$sql = "SELECT * FROM {$tablepre}members WHERE uid='$uid'";
		$data = $db->fetchSingleAssocBySql($sql);
		$data['avatar'] = avatar($data['uid']);
		// 更新用户资料
		if($data['subscribe']==1)
		{
			require_once $site_engine_root."mobile/lib/wechat.php";
			require_once $site_engine_root."mobile/lib/error.php";
			require_once $site_engine_root."mobile/lib/function.php";
			$options = options($data['appid']);
			$weObj = new Wechat($options);
			$userinfo = $weObj->getUserInfo($data['openid']);
			$userinfo['nickname'] = addslashes($userinfo['nickname']);
			if($userinfo['nickname'])
			{
				$userinfo['nickname'] = addslashes($userinfo['nickname']);
				$avatar = avatar($uid);
				$sql = "UPDATE {$tablepre}members SET
				sex='$userinfo[sex]',
				unionid='$userinfo[unionid]',
				linkman='$userinfo[nickname]',
				nickname='$userinfo[nickname]',
				subscribe='$userinfo[subscribe]',
				type=1,
				avatar='$avatar'
				WHERE uid='$uid'";
				$db->query($sql);

				$sql = "UPDATE {$tablepre}membersinfo SET
				language='$userinfo[language]',
				subscribe_time='$userinfo[subscribe_time]',
				headimgurl='$userinfo[headimgurl]',
				country='$userinfo[country]'
				WHERE uid='$uid'";
				$db->query($sql);
			}
			if($userinfo['headimgurl']!=$data['headimgurl'] && !empty($userinfo['headimgurl']))
			{
				$sql = "UPDATE {$tablepre}membersinfo SET headimgurl='$userinfo[headimgurl]' WHERE uid='$uid'";
				$db->query($sql);
			}
			$userinfo['headimgurl']= empty($userinfo['headimgurl']) ? $data['headimgurl']:$userinfo['headimgurl'];
			if($data['appid'] && $multiaccount==1)
			{
				$sql = "SELECT title,ctype FROM {$tablepre}account WHERE id='$data[appid]'";
				$info = $db->fetchSingleAssocBySql($sql);
				$data['myappid'] = $info['title'].'('.$select['account']['ctype'][$info['ctype']].')';
			}
			else
			{
				$data['myappid'] = $weixin_name;
				$data['myappid'] .= '&nbsp;'.$account;
			}
			$avatar = avatar($uid,'get');
			// if((!file_exists($site_engine_root.$uploaddir.$avatar) || filesize($site_engine_root.$uploaddir.$avatar)<1000))
			// {
			 	$avatar = saveavatar($uid,$userinfo['headimgurl']);
			 	$data['avatar'] = $avatar;
			// }
			// else
			// {
				if(empty($data['avatar']))
				{
			 		$data['avatar'] = 'avatar/user.jpg';
			 	}
			// }
		}
	}
	echo 1;exit;
}
else if($module == 'knowledge_msg')
{
	if(!empty($keyword))
	{
		$data = array();
		$ids = explode(",",$keyword);
		if(!empty($ids))
		{
			foreach ($ids as $key => $value)
			{
				$sql="SELECT title,content FROM ".$tablepre."knowledge  WHERE (title like '%".$value."%' OR keyword like '%".$value."%') AND moderate=1 LIMIT 10";
				$sqlarr=$db->fetchAssocArrBySql($sql);
					foreach($sqlarr as $k => $v)
					{
						if(!empty($v))
						{
							$v['content']=strip_tags($v['content']);
							$data['knowledge'][] = $v;
						}
					}
				$sql="SELECT n.title,n.id FROM ".$tablepre."news n left join ".$tablepre."newscontent c on n.id=c.id WHERE (n.title like '%".$value."%' OR n.keyword like '%".$value."%') AND n.moderate=1 LIMIT 10";
				$sqlarr=$db->fetchAssocArrBySql($sql);
					foreach($sqlarr as $k => $v)
					{
						if(!empty($v))
						{
							$data['news'][] = $v;
						}
					}
				$sql="SELECT title ,id , photo FROM ".$tablepre."product  WHERE (title like '%".$value."%' OR keyword like '%".$value."%') AND moderate=1 LIMIT 10";
				$sqlarr=$db->fetchAssocArrBySql($sql);
					foreach($sqlarr as $k => $v)
					{
						if(!empty($v))
						{
							$data['product'][] = $v;
						}
					}
			}
		}
		$data = array_unique($data);
		echo jsondata($data);exit;
	}
}
else if($module=='ticket')
{
	// 获得ticket
	$ticketime = time()-7200;
	$sql = "SELECT value FROM {$tablepre}settings WHERE variable='ticket' AND dateline>$ticketime";
	$ticket = $db->fetchOneBySql($sql);
	if(!$ticket)
	{
		require_once $site_engine_root."mobile/lib/wechat.php";
		require_once $site_engine_root."mobile/lib/error.php";
		require_once $site_engine_root."mobile/ajax/settings.php";
		require_once $site_engine_root."mobile/lib/function.php";
		$options = options($_GET['appid']);
		$weObj = new Wechat($options);
		$token = $weObj->checkAuth();
		$ticket = http_get("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$token."&type=jsapi");
		$ticket = json_decode($ticket,TRUE);
		$ticket = $ticket['ticket'];
		$sql = "UPDATE {$tablepre}settings SET value='$ticket',dateline='$time' WHERE variable='ticket'";
		$db->query($sql);
	}
	echo $ticket;exit;
}
else if($module == 'industry')
{
	$data = '';
	$industry = file($site_engine_root.'data/dict/industry.txt');
	$i =1;
	if(!empty($industry))
	{
		foreach($industry as $key=> $value)
		{
			$perindustry = explode(":",$value);
			$data[] = array('id' => $i,'title'=> $perindustry[0],'parentid'=>0);
			$industryitem = explode(",",$perindustry[1]);
			$j= $i;
			if(!empty($industryitem))
			{
				foreach($industryitem as $k=> $v)
				{
					$data[] = array('id' => $i+1,'title'=> $v,'parentid'=>$j);
					$i++;
				}
			}
			$i++;
		}
	}
	echo jsondata($data);exit;
}
else if($module == 'orders')
{
	if($action == 'deliverinfo')
	{
		$sql = "select flag from {$tablepre}orders where id='$id'";
		$orderflag = $db->fetchOneBySql($sql);
		$deliverinfo = array();
		if ($orderflag>1)
		{
			$sql = "select delivertype,deliverno,content from {$tablepre}deliver where orderid='$id'";
			$deliverinfo = $db->fetchSingleAssocBySql($sql);
			$deliverinfo['flag'] = $orderflag;
		}
		else
		{
			$deliverinfo = array('flag'=>$orderflag);
		}
		$deliverinfo['companys'] = $express_company;
		echo json_encode($deliverinfo);
		exit;
	}
	else if ($action == 'deliver')
	{
		$sql = "select id from {$tablepre}deliver where orderid='$id'";
		$haveid = $db->fetchOneBySql($sql);
		if ($haveid)
		{
			$sql = "update {$tablepre}deliver set delivertype='0',deliverno='$deliverno',content='$delivercontent' where id='$haveid'";
		}
		else
		{
			$sql = "insert into {$tablepre}deliver(orderid,delivertype,deliverno,content,dateline,uploader,companycode) values('$id','0','$deliverno','$delivercontent','".time()."','".$user['uid']."','$companycode')";
		}
		$db->query($sql);
		if($haveid)
		{
			$deliverid = $haveid;
		}
		else
		{
			$deliverid = $db->insertId();
		}
		$sql = "update {$tablepre}orders set flag=3,delivertime='".time()."' where id='$id'";
		$db->query($sql);
		$sql = "select * from {$tablepre}orders where id='$id'";
		$orders = $db->fetchSingleAssocBySql($sql);
		senddelivermessage($id);
		addorderrecord($id,'deliver','发货');
		exit;
	}
	else if ($action == 'changestate')
	{
		if($orderflag == 2)
		{
			$addsql = ",paytime='".time()."' ";
		}
		else if($orderflag ==3)
		{
			$addsql = ",delivertime='".time()."' ";
		}
		else if($orderflag==4)
		{
			$addsql = ",receivetime='".time()."' ";
		}
		else
		{
			$addsql = '';
		}
		$sql = "update {$tablepre}orders set flag = '$orderflag'".$addsql." where id='$id'";
		$db->query($sql);
		if($orderflag == 2)
		{
			sendpaymessage($id);
		}
		else if($orderflag == 3)
		{
			senddelivermessage($id);
		}
		else if($orderflag == 4)
		{
			sendreceivemessage($id);
		}
		addorderrecord($id,'order','修改订单状态',$orderflagarray[$orderflag].':'.$changecontent);
		exit;
	}
}
else if($module=='supplierproduct')
{
	if($action == 'authorize')
	{
		$takemoney = floatval($_POST['takemoney']);
		$productid = intval($productid);
		$sql = "select classid,title from {$tablepre}product where id='$productid'";
		$ret = $db->fetchSingleAssocBySql($sql);

		$productname = addslashes($ret['title']);
		$classid = $ret['classid'];
		$supplierid = intval($_POST['suppliers']);

		$sql = "insert into {$tablepre}supplierproduct(supplierid,productid,productname,classid,takemoney,moderate) values('$supplierid','$productid','$productname','$classid','$takemoney','2')";
		$db->query($sql);
		echo 1;
		exit;
	}
	else if($action == 'editauth')
	{
		$takemoney=floatval($_POST['takemoney']);
		$sql = "select productid,supplierid from {$tablepre}supplierproduct where id='$id'";
		$pinfo = $db->fetchSingleAssocBySql($sql);
		if ($pinfo)
		{
			$sql = "update {$tablepre}supplierproduct set takemoney='$takemoney' where supplierid='$pinfo[supplierid]' and productid='$pinfo[productid]'";
			$db->query($sql);
		}
		//$sql = "update {$tablepre}supplierproduct set takemoney='$takemoney' where id='$id'";
		//$db->query($sql);
		echo 1;
		exit;
	}
}
// else if($module == 'msg_current')//客服当前
// {
// 	$sql="SELECT * FROM {$tablepre}mpmsg WHERE replydateline=0 AND kefuid = '$user[uid]' order by id desc LIMIT $pagestart,$limit";
// 	$data=$db->fetchAssocArrBySql($sql);
// 	foreach($data as $k => $v)
// 	{
// 		$data[$k]['content']=emojishow($v['content']);
// 	}
// 	echo jsondata($data);exit;
// }
// else if($module == 'msg_history')//客服历史
// {
// 	$sql = "SELECT a.uid,a.linkman,a.nickname,a.username,b.lastmessage as dateline FROM {$tablepre}members a left join {$tablepre}membersinfo b on a.uid=b.uid WHERE a.ownid='$user[uid]' ORDER BY b.lastmessage DESC LIMIT $pagestart,$limit";
// 	$data=$db->fetchAssocArrBySql($sql);
// 	echo jsondata($data);exit;
// }
// else if($module == 'msg_user')//客服客户
// {
// 	$sql = "SELECT a.uid,a.linkman,a.nickname,a.username,b.lastmessage FROM {$tablepre}members a left join {$tablepre}membersinfo b on a.uid=b.uid WHERE a.ownid='$user[uid]' AND a.subscribe=1 ORDER BY b.lastmessage ASC ";
// 	$data=$db->fetchAssocArrBySql($sql);
// 	echo jsondata($data);exit;
// }
// else if($module == 'msg_new')//客服新消息
// {
// 	$sql="SELECT * FROM {$tablepre}mpmsg WHERE kefuid = '$user[uid]' AND replydateline='0' AND id>'$startid' order by id desc";
// 	$data=$db->fetchAssocArrBySql($sql);
// 	foreach($data as $k => $v)
// 	{
// 		$data[$k]['content']=emojishow($v['content']);
// 	}
// 	echo jsondata($data);exit;
// }
else if($module == 'marketing')
{
	$orderby = $_GET['orderby'] ? $_GET['orderby']:'uid';
	$ascdesc = $_GET['ascdesc'] ? $_GET['ascdesc']:'DESC';
	$sql = "SELECT a.uid,a.linkman as username,b.lastmessage FROM {$tablepre}members a left join {$tablepre}membersinfo b on a.uid=b.uid  WHERE 1 AND a.subscribe=1  AND a.openid!='' AND a.ownid='0' $attasql ORDER BY $orderby $ascdesc LIMIT $pagestart,$limit";
	$data = $db->fetchAssocArrBySql($sql);
	echo jsondata($data);exit;
}
else if($module == 'reply')
{
	$messageid = intval($_GET['messageid']);
	if($messageid)
	{
		$sql = "SELECT * FROM {$tablepre}mpmsg WHERE id='$messageid'";
		$data = $db->fetchSingleAssocBySql($sql);
		$keyword = splitwords($data['content']);
		$keyword = implode(',',$keyword);
		$keyword= str_replace(array("\r\n", "\r", "\n"), "", $keyword);
		$username = $data['username'];
		$uid = $data['uid'];
	}
	else
	{
		$uid = intval($_GET['uid']);
	}
	$sql = "SELECT * FROM {$tablepre}mpmsg WHERE uid='$uid' AND MsgType!='event' AND meetingid=0 ORDER BY id DESC  LIMIT $pagestart,$limit";
	$data = $db->fetchAssocArrBySql($sql);
	echo jsondata($data);exit;
}
else if($module == 'customerlevel')
{
	$data = $select['members']['customerlevel'];
	echo jsondata($data);exit;
}
else if($module == 'friends')
{
	$sql = "SELECT id,uid,touid,username,tousername,dateline FROM {$tablepre}friends WHERE uid='$uid' OR touid='$uid' ORDER BY id ASC";
	$info = $db->fetchAssocArrBySql($sql);
	$friendarray = array();
	$count=0;
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			if($value['uid']==$user['uid'])
			{
				$friendarray[$key] = array('id'=>$value['id'],'uid'=> $value['touid'],'linkman' => $value['tousername'],'dateline'=>$value['dateline']);
			}
			else
			{
				$friendarray[$key] = array('id'=>$value['id'],'uid'=> $value['uid'],'linkman' => $value['username'],'dateline'=>$value['dateline']);
			}
		}
	}
	$data = $friendarray;
	echo jsondata($data);exit;
}
else if($module=='newsshow')//新闻详情
{
	$sql="SELECT n.* ,c.content,summary FROM {$tablepre}news n LEFT JOIN {$tablepre}newscontent c ON n.id=c.id WHERE n.id=$id";
	$data=$db->fetchSingleAssocBySql($sql);
	$data['content']=stripslashes($data['content']);
	$data['content']=preg_replace("/style=\"[^\"]*?\"/i","",$data['content']);
	$data['content'] = preg_replace('/<img.*src="(.*)"\s*.*>/iU',"<img src='\\1 ' style='width:100%;'>", $data['content']);
	$data['content']=str_replace('//data/','/data/',$data['content']);

	$data['content'] = str_replace('\\&quot;',"",$data['content']);
	$data['content'] = str_replace("\\\\","",$data['content']);
	$data['content'] = str_replace("&quot;",'"',$data['content']);
	$data['content'] = str_replace("\\\"",'',$data['content']);

	$data['classname']=$db->fetchOneBySql("SELECT title FROM {$tablepre}newsclass WHERE id='$data[classid]'");
	echo jsondata($data);exit;
}
else if($module == 'mpmenudata')
{
	$sql = "SELECT * FROM {$tablepre}mpmenu WHERE module='menu'  AND appid='$appid' and moderate=1 ORDER BY parentid ASC , orderid ASC ";
	$data = $db->fetchAssocArrBySql($sql);
	echo jsondata($data);exit;
}
else if($module=='navidata')
{
	$sql = "SELECT * FROM {$tablepre}navi WHERE 1  AND appid='$appid' ORDER BY parentid ASC , orderid ASC ";
	$data = $db->fetchAssocArrBySql($sql);
	echo jsondata($data);exit;
}
else if($module=='opermenudata')
{
	$sql = "SELECT * FROM {$tablepre}operatormenu WHERE 1  AND appid='$appid' and operatorid='$id' and module='$linkmodule' ORDER BY parentid ASC , orderid ASC ";
	$data = $db->fetchAssocArrBySql($sql);
	echo jsondata($data);exit;
}
else if($module == 'pushit')
{
	$sql = "SELECT * FROM {$tablepre}push WHERE 1 ORDER BY id DESC";
	$data = $db->fetchAssocArrBySql($sql);
	echo jsondata($data);exit;
}
// 取得推送的内容
else if($module == 'getcontent')
{
	$sql = "SELECT * FROM {$tablepre}pushit WHERE id='$id'";
	$data = $db->fetchSingleAssocBySql($sql);
	if($data['module']=='news')
	{
		$sql= "SELECT content FROM {$tablepre}newscontent WHERE id='$data[moduleid]'";
		$data['content'] = $db->fetchOneBySql($sql);
	}
	else
	{
		$sql= "SELECT content FROM {$tablepre}{$data['module']} WHERE id='$data[moduleid]'";
		$data['content'] = $db->fetchSingleAssocBySql($sql);
	}
	$data['content'] = stripslashes($data['content']);
	echo jsondata($data);exit;
}
// 取得企业号部门
else if($module == 'department')
{
	require_once $site_engine_root."mobile/lib/qiye.php";
	require_once $site_engine_root."mobile/ajax/settings.php";
	require_once $site_engine_root."mobile/lib/function.php";
	$options = options('qiye');
	$weObj = new Qiye($options);

	$info = $weObj->getDepartment();
	$data = array();
	if(!empty($info['department']))
	{
		foreach($info['department'] as $key=>$value)
		{
			$data[$value['id']] = $value['name'];
		}
	}
	echo jsondata($data);exit;
}
else if($module =='qiyemembers')
{
	require_once $site_engine_root."mobile/lib/qiye.php";
	require_once $site_engine_root."mobile/ajax/settings.php";
	require_once $site_engine_root."mobile/lib/function.php";
	$options = options('qiye');
	$weObj = new Qiye($options);
	$info = $weObj->getUserListInfo($id,0,0);
	// printarray($info);
	if(!empty($info['userlist']))
	{
		foreach($info['userlist'] as $key=>$value)
		{
			$data[] = array($value['userid'],$value['name'],$value['avatar']);
		}
	}
	echo jsondata($data);exit;
}
//  网点
else if($module=='poi')
{
	require_once $site_engine_root."mobile/lib/wechat.php";
	require_once $site_engine_root."mobile/lib/error.php";
	require_once $site_engine_root."mobile/ajax/settings.php";
	require_once $site_engine_root."mobile/lib/function.php";
	$options = options($_GET['appid']);
	$weObj = new Wechat($options);
	$token = $weObj->checkAuth();
	$data = $weObj->getCardLocations();
	echo jsondata($data);exit;
}

//shakearound list
else if($module=='shakelottery')
{
	$action=empty($_GET['action']) ? 'list' : $_GET['action'];
	if($action=='list')
	{
		$sql="SELECT * FROM {$tablepre}shakelottery WHERE id>0 ORDER BY id DESC";
		$data=$db->fetchAssocArrBySql($sql);
		echo jsondata($data);exit;
	}
	else if($action=='info')
	{
		$sql="SELECT * FROM {$tablepre}shakelottery WHERE id=0";
		$data=$db->fetchAssocArrBySql($sql);
		echo jsondata($data);exit;
	}
	else if($action=='addlotteryid')
	{
		require_once $site_engine_root."mobile/lib/function.php";
		printarray($_POST);
	}
}
elseif($module=='editor')
{
	if($action=='get')
	{
		$editor_color=file_get_contents($site_engine_root.'data/config/editor_color.js');exit;
	}
	elseif($action=='add')
	{
		$color=$_POST['color']? $_POST['color']:$_GET['color'];
		if(empty($color))
		{
			$ret['flag']=0;
			$ret['error']='缺少颜色参数';
			echo jsondata($ret);exit;
		}
		if(!preg_match('/#/',$color))
		{
			$color=RGBToHex($color);
		}
		$tpl='<li class="myLi" color="###" onmousedown="changeColor(\'###\')">
				<div class="bg" style="background-color:###;"></div>
				<span class="delColor">×</span>
			</li>';
		$config=file_get_contents($site_engine_root.'data/config/editor_color_config.js');
		if(empty($config))
		{
			$config=$color;
			$html=str_replace('(###)',"'".$color."'",$tpl);
			$html=str_replace('###',$color,$html);
			file_put_contents($site_engine_root.'data/config/editor_color.js',$html);
		}
		else
		{
			$config=explode(';',$config);
			$config[]=$color;
			$html='';
			foreach($config as $k=>$v)
			{
				$temp=str_replace('(###)',"('".$v."')",$tpl);
				$temp=str_replace('###',$v,$temp);
				$html.=$temp;
			}

			file_put_contents($site_engine_root.'data/config/editor_color.js',$html);
			$config=implode(';',$config);
		}

		file_put_contents($site_engine_root.'data/config/editor_color_config.js',$config);
		$ret['flag']=1;
		$ret['error']='';
		echo jsondata($ret);exit;
	}
	elseif($action=='del')
	{
		$color=$_POST['color']? $_POST['color']:$_GET['color'];
		if(empty($color))
		{
			$ret['flag']=0;
			$ret['error']='缺少颜色参数';
			echo jsondata($ret);exit;
		}
		$tpl='<li class="myLi" color="###" onmousedown="changeColor(\'###\')">
				<div class="bg" style="background-color:###;"></div>
				<span class="delColor">×</span>
			</li>';
		$config=file_get_contents($site_engine_root.'data/config/editor_color_config.js');
		if(empty($config))
		{
			$ret['flag']=1;
			$ret['error']='';
			echo jsondata($ret);exit;
		}
		else
		{
			$config=explode(';',$config);
			$html='';
			foreach($config as $k=>$v)
			{
				if($v==$color)
				{
					unset($config[$k]);continue;
				}
				$temp=str_replace('(###)',"('".$v."')",$tpl);
				$temp=str_replace('###',$v,$temp);
				$html.=$temp;
			}
			if(!empty($config))
			{
				file_put_contents($site_engine_root.'data/config/editor_color.js',$html);
				$config=implode(';',$config);
			}

		}

		file_put_contents($site_engine_root.'data/config/editor_color_config.js',$config);
		$ret['flag']=1;
		$ret['error']='';
		echo jsondata($ret);exit;
	}
}
else if($module=='getkeywords')
{
	require_once $site_engine_root.'mobile/lib/bosonnlp.php';
	$bnlp = new bosonnlp();
	$keywords = $bnlp->getkeywords($_REQUEST['content']);
	echo jsondata($keywords);exit;
}
// 扩展
// plugins('api');