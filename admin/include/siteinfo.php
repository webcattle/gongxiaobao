<?php
/******************************************************************************************************
**  企业Plus 7.0 - 企业Plus社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业Plus授权协议,查看或使用企业Plus的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: siteinfo file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 6/30/2015 12:37:44 AM
***************************************/
/******************************************************************************************************
**  文件目录类
*******************************************************************************************************/
if(!defined('IN_SITEENGINE')) exit('Access Denied');
require_once $site_engine_root.'mobile/lib/file.php';
// 创建目录
if (!is_dir($site_engine_root.$userdir))
{
	@mkdir($site_engine_root.$userdir);
}
// 拷贝文件
if(!file_exists($site_engine_root.$uploaddir.'avatar/user.jpg'))
{
	@copy($site_engine_root.'data/upload/boka.jpg',$site_engine_root.$uploaddir.'boka.jpg');
	@copy($site_engine_root.'data/upload/avatar/user.jpg',$site_engine_root.$uploaddir.'avatar/user.jpg');
	//@folderCopy($site_engine_root.'data/upload/menu',$site_engine_root.$uploaddir.'menu/');
	//@folderCopy($site_engine_root.'data/upload/default',$site_engine_root.$uploaddir.'default/');
	//@folderCopy($site_engine_root.'data/upload/meeting/default',$site_engine_root.$uploaddir.'meeting/default/');
	require_once SITEENGINEROOT.'mobile/lib/cache.php';
	$cache = new cache;
	$cache->writetocache('system');
}
if(!file_exists($site_engine_root.$uploaddir.'nopic.jpg'))
{
	@copy($site_engine_root.'data/upload/nopic.jpg',$site_engine_root.$uploaddir.'nopic.jpg');
}
if($siteinfo['service_type_info']==2)
{
	$siteinfo['service_type_info'] = 'service';
}
else
{
	$siteinfo['service_type_info'] = 'dingyue';
}
if(!file_exists($site_engine_root .$uploaddir.'/qrcode.jpg'))
{
	$info = http_get($siteinfo['qrcode_url']);
	file_put_contents($site_engine_root .$uploaddir.'/qrcode.jpg', $info);
}
/******************************************************************************************************
**  数据类
*******************************************************************************************************/
// 关注地址
if(empty($followurl))
{
	require_once $site_engine_root."mobile/lib/wechat.php";
	require_once $site_engine_root."mobile/lib/error.php";
	require_once $site_engine_root."mobile/ajax/settings.php";
	require_once $site_engine_root."mobile/lib/function.php";
	$options = options($appid);
	$weObj = new Wechat($options);

	$photosrc = $site_engine_root.'data/images/0.gif';
	if (class_exists('\CURLFile'))
	{
		$_data = array("media"=>new \CURLFile($photosrc));
	}
	else
	{
		$_data = array("media"=>'@'.$photosrc);
	}

	$media_id = $weObj->uploadForeverMedia($_data,'image');

	$content = '点击蓝字关注';
	$content = changeimagepath(stripslashes($content));
	$array = array("title"=> '欢迎关注'.$siteinfo['title'],
	"thumb_media_id"=> $media_id['media_id'],
	"author"=> '',
	"digest"=>'感谢关注'.$siteinfo['title'],
	"show_cover_pic"=>1,
	"content"=> $content,
	"content_source_url"=>'http://'.$siteinfo['sitedomain'].'.boka.cn/mobile/');
	$data[0] = $array;
	$data = array("articles"=>$data);
	$info = $weObj->uploadForeverArticles($data);
	$info = $weObj->getForeverMedia($info['media_id']);
	if($info['news_item'][0]['url'])
	{
		$url = $info['news_item'][0]['url'];
		$sql = "UPDATE {$tablepre}settings SET value='$url' WHERE variable='followurl'";
		$db->query($sql);
		require_once $site_engine_root.'mobile/lib/cache.php';
		$cache = new cache;
		$cache->writetocache('system');

	}
}
?>