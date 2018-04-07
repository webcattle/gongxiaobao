<?php
/******************************************************************************************************
**  企业+ 7.0 - 企业+社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业+授权协议,查看或使用企业+的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: setting file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
if($module=='shopsetting')
{
	$module = 'shop';
}
$module_system = $module;
$data = array();
if(!empty($_POST))
{
	$content = '';
	require $site_engine_root.'/mobile/lib/upload.php';
	$upload = upload();
	if (is_array($upload))
	{
		foreach ($upload as $key=> $value)
		{
			if (!empty($value)&&is_array($value))
			{
				$key_m=$value['module'];
				$_POST[$key_m]=$value['attachment'];
			}
		}
	}
	else
	{
		if(!empty($upload_array))
		{
			foreach($upload_array as $key=> $value)
			{
				unset($_POST[$value]);
			}
		}
	}
	foreach($_POST as $key=> $value)
	{
		if($value && !is_array($value))
		{
			$value = addslashes(trim($value));
		}

		if(in_array($module,array_keys($system_preview)) && $module!='agency')
		{
			$variable = $module.'sys[\''.$key.'\']';
			$sql = "UPDATE {$tablepre}settings set value='$value' where variable=\"".$variable."\" and module='$module'";
			$db->query($sql);
		}
		else
		{
			if($key=='files'||$key=='appid')
			{
				continue;
			}
			$sql = "select id from {$tablepre}settings where variable='$key' and module='$module'";
			$haveid = $db->fetchOneBySql($sql);
			if(!$haveid)
			{
				$sql = "INSERT INTO {$tablepre}settings(variable,value,module) values('$key','$value','$module')";
			}
			else
			{
				$sql = "UPDATE {$tablepre}settings SET value='$value' WHERE variable='$key'";
			}

			if($key!='weixin_appsecret' && $key!='weixin_token' && $key!='ticket' && $key!='qyweixintoken' && $key!='qyencodingaeskey' && $key!='qyappid' && $key!='qyappsecret' && $key!='partnerid' && $key!='partnerkey' && $key!='paysignkey' && $key!='weixin_encodingaeskey' && $key!='')
			{
				$content .= 'var '.$key.'=\''.$value.'\';'."\n";
			}
			$db->query($sql);
		}
	}
	$ret = array();

	if($module == 'weixin') {
		if($_POST['industry'])
		{
			/*if(file_exists($site_engine_root.'data/sql/'.$_POST['industry'].".sql"))
			{
				$sql="SELECT VERSION() as mysql_version";
				$result = $db->fetchOneBySql($sql);
				$version = explode("-",$result);
				$mysqlversion = $version[0];

				require_once $site_engine_root.'mobile/lib/query.php';
				$query = file_get_contents($site_engine_root.'data/sql/'.$_POST['industry'].'.sql');
				if($mysqlversion<'5.5')
				{
					$query=str_replace('utf8mb4','utf8',$query);
				}

				if($tablepre!=$softwareprefix) $query=str_replace($softwareprefix,$tablepre,$query);
				$sql_query = new query($query);
				$sql_query->doquery();
				unset($query);
			}*/

			foreach($industry_module as $key=>$value)
			{
				if($key==$_POST['industry'])
				{
					continue;
				}
				foreach($value as $k=>$v)
				{
					$var = $v."sys['show']";
					$sql = "update {$tablepre}settings set value=0 where variable='".addslashes($var)."'";
					$db->query($sql);
				}
			}
		}
		if($_POST['mp_verify_file'] && !file_exists($site_engine_root.$_POST['mp_verify_file']))
		{
			$filename = str_replace(".txt","",$_POST['mp_verify_file']);

			if (strpos($filename, "MP_verify_") === false)
			{
				echo  '文件名格式错误';
				exit;
			}
			$temp = explode("MP_verify_",$filename);
			$filecontent = $temp[1];
			if(strlen($temp[1])!=16)
			{
				echo  '文件名格式错误';
				exit;
			}
			$fp = fopen($site_engine_root.$filename.'.txt','w');
			if(!$fp)
			{
				echo  '没有写权限，请联系系统管理员';
				exit;
			}
			fwrite($fp,$temp[1]);
			fclose($fp);
		}
		if($_POST['weixin_logo'])//更改logo同时更新关注二维码
		{
			require_once $site_engine_root.'mobile/lib/imagefunctions.php';
			$logo_path = getImgUrl($_POST['weixin_logo'],1);
			$qrcode_path = getImgUrl($weixin_qrcode,1);
			@chmod($site_engine_root.$weixin_qrcode,0666);
//			@chmod($site_engine_root.$uploaddir.'/qrcode',0777);
			$p2 = image_copy_image($qrcode_path,$logo_path,$add_x=506,$add_y=506,$add_w=270,$add_h=270,$site_engine_root.$weixin_qrcode);
			imagejpeg($p2,$site_engine_root.'/data/upload/qrcode_11.png');
			unset($p2);
		}
	}
	else if($module=='shop')
	{
		if($_POST['supplier_mode']==1 && $_POST['supplier_defaultadmin'])
		{
			$title = $_POST['supplier_defaultname']?$_POST['supplier_defaultname']:'默认供应商';
			$sql = "select id,uid,levels from {$tablepre}agency where module='shopstore' and levels='99'";
			$result = $db->fetchSingleAssocBySql($sql);
			if($result)
			{
				//已设置，不用修改
				if ($result['uid'] == $_POST['supplier_defaultadmin'])
				{
					$sql = "update {$tablepre}agency set title=? where id=?";
					$db->query($sql,[$title,$result['id']]);
				}
				else
				{
					$sql = "update {$tablepre}agency set title=?,uid=? where id=?";
					$db->query($sql,[$title,$_POST['supplier_defaultadmin'],$result['id']]);
					changeSupplierUser($result['uid'],$_POST['supplier_defaultadmin'],0);
				}
			}
			else
			{
				$sql = "select id from {$tablepre}agency where module='shopstore' and uid='$_POST[supplier_defaultadmin]'";
				$haveid = $db->fetchOneBySql($sql);
				if(!$haveid)
				{
					$sql = "insert into {$tablepre}agency(uid,title,levels,moderate,dateline,module,express,expresstype,expresspara) values(?,?,?,?,?,?,?,?,?)";
					$db->query($sql,[$_POST['supplier_defaultadmin'],$title,'99','1',time(),'shopstore',$_POST['shop_express'],$_POST['shop_expresstype'],$_POST['shop_expresspara']]);
				}
				else
				{
					$ret['flag'] = 0;
					$ret['error'] = '该供应商已存在，请换个用户';
					echo jsondata($ret);
					exit;
				}
			}
		}
	}
	require_once $site_engine_root.'mobile/lib/cache.php';
	$cache = new cache;
	$cache->clear();
	$cache->writetocache('system');
	$ret['flag'] = 1;
	echo jsondata($ret);
	exit;
}
if ($module == 'channel')
{
	if(!empty($industryarray))
	{
		foreach($industryarray as $key=> $value)
		{
			$industry.= '<option value="'.$value['id'].'">'.$value['title'].'</option>';
		}
	}
	$industry .= '</select>';
	$chararray  = array('config_website','config_email','config_company','config_telephone','config_fax','config_address','config_postcode','config_icp','censorlevel'); // ,'adminlogin'
	if($version >=2)
	{
		$chararray = array_merge($chararray,array('industry'));
	}
	$sql = "SELECT * FROM {$tablepre}settings WHERE module='site' ORDER BY id ASC";
	$info = $db->fetchAssocArrBySql($sql);
	if(!empty($info))
	{
	 	foreach($info as $key => $value)
	 	{
	 		if(in_array($value['variable'],$chararray))
	 		{
	 			$data[$value['variable']] = $value['value'];
	 		}

	 	}
	}
}
else if(in_array($module,array_keys($system_preview)))
{
	$array  = array('name','char_setting','type_setting','list_setting','add_setting','required_setting','view_setting','name_setting','search_setting');
	$sql = "SELECT * FROM {$tablepre}settings WHERE module='$module'";
	$info = $db->fetchAssocArrBySql($sql);
	if(!empty($info))
	{
	 	foreach($info as $key => $value)
	 	{
	 		if(strpos($value['variable'],'system_preview')!==false)
			{
				continue;
			}
	 		$havefind = 0;
	 		foreach($array as $k=>$v)
			{
				$newname = $module."sys['".$v."']";
				if($value['variable']==$newname)
				{
					$havefind=1;
					break;
				}
			}

	 		if(!$havefind)
	 		{
				if(preg_match('/'.$module.'sys\[\'(.*?)\'\]/i',$value['variable'],$matches))
				{
					$data[$matches[1]] = $value['value'];
				}
	 		}

	 	}
	}
}
