<?php
/******************************************************************************************************
**  企业Plus 7.0 - 企业Plus社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业Plus授权协议,查看或使用企业Plus的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: security file
** Author.......: Paul
** Version......: 4.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
$submit=$_GET['submit'] ? $_GET['submit']:'';
function each_dir($dir) //获取所有目录
{
	global $dirarr;
	$file_dir_open = opendir($dir);
	if (!$file_dir_open) return false;
	while ($valuearr = readdir($file_dir_open))
	{
		if ($valuearr == '.' || $valuearr == '..')
		 {
		 	continue;
		 }
		else
		{
			$file_dir = $dir . '/'.$valuearr;
			if (is_dir($file_dir))
			{
				$dirarr[]=$file_dir;
				each_dir($file_dir );
			}
		}
	}
	closedir($file_dir_open);
	return $dirarr;
}
function each_dir_file($dir,$style)//获取当前目录文件
{
	global $dirarr;
	$file_dir_open = opendir($dir);
	if (!$file_dir_open) return false;
	while ($valuearr = readdir($file_dir_open))
	{
		if ($valuearr == '.' || $valuearr == '..')
		 {
		 	continue;
		 }
		else
		{
			$file_dir = $dir . '/'.$valuearr;
			if (is_dir($file_dir))
			{
				continue;
			}
			else
			{
				if($style=='bug')
				{
					 check_file_bug($file_dir);
				}
				else if($style=='content')
				{
					check_file_lasttime($file_dir);
				}
			}

		}
	}
	closedir($file_dir_open);
	return true;
}
function check_file_bug($file_dir) //获取文件内容中是否有危险
{
	global $db,$tablepre;

	$dir_arr=explode("/", __FILE__);
	$dirs='';
	for($i=0;$i< count($dir_arr)-3;$i++)
	{
		$dirs.=$dir_arr[$i].'/';
	}
	//不需检查
	$anquan_arr=array(
	'/admin/js/editor/third-party/video-js/video.dev.js',
	'/admin/js/editor/third-party/webuploader/webuploader.custom.js',
	'/admin/js/editor/third-party/webuploader/webuploader.flashonly.js',
	'/admin/js/editor/third-party/webuploader/webuploader.html5only.js',
	'/admin/js/editor/third-party/webuploader/webuploader.js',
	'/admin/js/editor/third-party/webuploader/webuploader.withoutimage.js',
	'/admin/include/security.php',
	'/mobile/data/requirejs/jquery.js',
	'/mobile/data/js/jquery.js'
	);
	$file_dir_li=str_replace($dirs, '',$file_dir);

	if(in_array($file_dir_li,$anquan_arr))
	{
		return false;
	}
	if ($file_dir == str_replace('\\', '/', __FILE__))
	{
		return false;
	}
	$filesize_f = ffile_size($file_dir);
	if (!$filesize_f) {
	return false;
	}

	$file_get_return = ffile_get_content($file_dir);
	$searcharr = array(
	'assert(' => '高',
	'assert (' => '高',
	'new COM' => '高',
	'eval($_' => '高',
	'eval ($_' => '高',
	'eval(gzinflate' => '高',
	'eval(base64_decode' => '高',
	'shell_exec(' => '高',
	'pcntl_exec(' => '高',
	'file_put_contents($_' => '高',
	'system(' => '高',
	'popen(' => '高',
	'copy($_' => '中',
	'fopen($_' => '中',
	'Create Function' => '中',
	'into dumpfile' => '中',
	'into outfile' => '中',
	'udp://' => '低',
	'vidun.com' => '低',
	'symlink(' => '低'
	);
	foreach ($searcharr as $ffile_size => $valuearr)
	{
		if (stristr($file_get_return, $ffile_size))
		{
			//$sql="insert into  {$tablepre}security (style,file_dir,last_time,file_size,code,dateline) values('content','$file_dir','".ffile_last_time($file_dir)."','$filesize_f','".str_replace('$', '_', str_replace('(', '_', $ffile_size)).",".$valuearr."','".time()."')";
			//return $db->query($sql);
		}
	}
	return false;
}
function check_file_lasttime($file_dir) //检查文件最后修改事件
{
	global $db,$tablepre;
	$filestat=stat($file_dir);
	if($filestat['mtime'] >= strtotime('-1 day'))
	{
		$filesize_f=ffile_size($file_dir);
		//$sql="insert into  {$tablepre}security (style,file_dir,last_time,file_size,code,dateline) values('filetime','$file_dir','".date('Y-m-d h:i',$filestat['mtime'])."','$filesize_f','edit_last_time,最近修改','".time()."')";
		//$db->query($sql);
		//文件最近一周修改过的文件;
		clearstatcache();//清除缓存并再次检查文件大小
	}
	//$file_content =ffile_get_content($file_dir);
	return true;
}
function  check_file_dirstyle($dir)//检查文件类型
{
	global $db,$tablepre;
	$dir_arr=explode("/", __FILE__);
	$dirs='';
	for($i=0;$i< count($dir_arr)-3;$i++)
	{
		$dirs.=$dir_arr[$i].'/';
	}
	//文件类型规则
	$sharcharr=array(
		$dirs.'data/upload'=>'jpg,png,gif,html,doc,docx,xls,xlsx,docx,doc,amr,htm,mp3',
		$dirs.'data/images'=>'jpg,png,gif,html,htm',
		$dirs.'data/css'=>'css,html,htm',
		$dirs.'data/dict'=>'txt,html,htm',
		$dirs.'data/sql'=>'sql,html,htm',
		$dirs.'data/js'=>'js,html',
		$dirs.'data/template'=>'xlsx,pptx,docx,xls,ppt,doc,html,htm',
		$dirs.'mobile/ajax'=>'php,html',
		$dirs.'mobile/data/images'=>'jpg,png,gif,html,ico',
		$dirs.'mobile/data/js'=>'js,html',
		$dirs.'data/logs'=>'txt,html',
		$dirs.'mobile/data/music'=>'css,png,gif,jpg,js,html,htm,mp3',
		$dirs.'mobile/jplayer'=>'swf,html,js,css,jpg,png,gif',
		$dirs.'mobile/lib'=>'php,html,htm',
	);
	foreach($sharcharr as $k => $v)
	{
		if(stristr($dir,$k))//函数查找字符串在另一个字符串中第一次出现的位
		{
			$file_dir_open = opendir($dir);
			if (!$file_dir_open) return false;
			while ($valuearr = readdir($file_dir_open))
			{
				if ($valuearr == '.' || $valuearr == '..')
				{
				 	continue;
				}
				else
				{
					$file_dir = $dir . '/'.$valuearr;
					if (is_dir($file_dir))
					{
						continue;
					}
					else
					{
						$style=strtolower(substr($file_dir,strrpos($file_dir,'.')+1));
						$stylearr=explode(',',$v);
						$filesize_f=ffile_size($file_dir);
						if(!in_array($style,$stylearr))
						{
							//$sql="insert into  {$tablepre}security (style,file_dir,last_time,file_size,code,dateline) values('filestyle','$file_dir','".date('Y-m-d h:i',$filestat['mtime'])."','$filesize_f','filestyle,文件类型不符合','".time()."')";
							//$db->query($sql);
						}
					}
				}
			}
			closedir($file_dir_open);
		}
	}
	return true;

}
function ffile_size($file_dir)  //文件大小
{
	$filesize = filesize($file_dir);
	if ($filesize > 500000) return false;
	elseif ($filesize > 1024) $filesize = round($filesize / 1024 * 100) / 100 . ' K';
	else $filesize = $filesize . ' B';
	return $filesize;
}
function ffile_get_content($file_dir)//获取文件内容
{
	$file_dir_open = fopen($file_dir, 'r');
	$filesize=filesize($file_dir);
	if($filesize > 0)
	{
		$file_get_return = fread($file_dir_open,$filesize);
	}
	fclose($file_dir_open);
	return $file_get_return;
}
function ffile_last_time($file_dir)
{
    	return date("Y-m-d H:i", fileatime($file_dir));
}
//if(!empty($submit))
//{
//	$dir_arr=explode("/", __FILE__);
//	$dir='';
//	for($i=0;$i< count($dir_arr)-3;$i++)
//	{
//		$dir.=$dir_arr[$i].'/';
//	}
//	require_once $dir.'/mobile/ajax/header.php';
//}
if($submit=='ok')//开始扫描文件
{
	if($db->query("TRUNCATE {$tablepre}security"))
	{
		$catalogarr=each_dir($site_engine_root); //获取所有文件夹中的目录
		$catalogarr[]=$dir;
		if(is_array($catalogarr) && count($catalogarr)> 0)
		{
			$sql="insert into  {$tablepre}security (file_dir,last_time,file_size,code,dateline) values('doing_step','1','','','".time()."')";
			$db->query($sql);
			//便利数组成功；
			foreach($catalogarr as  $dirv)
			{
				each_dir_file($dirv,'bug');
			}
			//$sql="UPDATE {$tablepre}security SET last_time =2  WHERE file_dir = 'doing_step'";
			$sql="UPDATE {$tablepre}security SET last_time =2  WHERE id=1 ";
			$db->query($sql);
			//文件漏洞处理；

			foreach($catalogarr as  $dirv)
			{
				if(!stristr($dirv,$dir."/data/upload"))//最近修改事件去掉upload目录
				{
					each_dir_file($dirv,'content');
				}
			}
			$sql="UPDATE {$tablepre}security SET last_time =3   WHERE id=1 ";
			$db->query($sql);
			//文件内容处理；

			foreach($catalogarr as  $dirv)
			{
				check_file_dirstyle($dirv);
			}
			$sql="UPDATE {$tablepre}security SET last_time = 4  WHERE id=1 ";
			$db->query($sql);
			//文件类型处理；

		}
		exit;
	}
}
else if($submit=='get')//扫描文件的分类
{
	$pagestart=intval($_GET[pagestart])?intval($_GET[pagestart]):0;
	if($_GET['style'])
	{
		$stylesql= " and style='".$_GET['style']."'";
	}
	$sql="SELECT * from {$tablepre}security where id > $pagestart and file_dir !='doing_step' $stylesql";
	$ret=$db->fetchAssocArrBySql($sql);
	if(count($ret) > 0)
	{
		foreach($ret as $k => $v)
		{
			foreach($v as $k1 => $v1)
			{
				if($k1=='file_dir')
				{
					$ret[$k][$k1]=str_replace($dir,'',$v1);
				}
			}
		}
		echo json_encode($ret);exit;
	}
	else
	{

		$ret=array(array('file_dir'=>'end_dir'));
		echo json_encode($ret);exit;
	}


}
else if($submit=='doing')//获取当前扫描文件的动态
{
	$sql="SELECT last_time from {$tablepre}security   WHERE file_dir = 'doing_step'";
	$ret=$db->fetchOneBySql($sql);
	echo jsondata($ret);
	exit;
}
else if($submit=='edit_file')// 网站漏洞修复
{
	//1.文件类型不对 删除
	$sql="SELECT id,file_dir from {$tablepre}security where style ='filestyle' and moderate!=1 ";
	$sqlarr=$db->fetchAssocArrBySql($sql);
	foreach($sqlarr as $k => $v)
	{

		if(file_exists($dir.$v['file_dir']))
		{
			unlink($dir.$v['file_dir']);
		}
		$db->query("delete from {$tablepre}security where id=".$v['id']);
	}
	$ret=1;
	echo jsondata($ret);exit;
}

$sql="SELECT dateline from {$tablepre}security   WHERE file_dir = 'doing_step'";
$lastestscan = $db->fetchOneBySql($sql);
$lastestscan = date('Y-m-d H:i',$lastestscan);
$wxwjs=$db->fetchOneBySql("SELECT count(id) from {$tablepre}security  where style='content'");
$zjxgs=$db->fetchOneBySql("SELECT count(id) from {$tablepre}security  where style='filetime'");
$wjlx=$db->fetchOneBySql("SELECT count(id) from {$tablepre}security  where style='filestyle'");
