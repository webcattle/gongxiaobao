<?php
if(empty($_POST))
{
	eval ("\$content = \"" . $tpl->get($module.'_wizard','admin'). "\";");
	exitandreturn($content,$module,$action);
}
else
{
	// if($module == 'settings')
	{
		if($step==1 || empty($step))
		{
			$weixin_appsecret = trim($_POST['weixin_appsecret']);
			if($weixin_appsecret)
			{
				$sql = "UPDATE {$tablepre}settings SET value='$weixin_appsecret' WHERE variable='weixin_appsecret'";
				$db->query($sql);
				if(!$subdomain)
				{
					$file= $site_engine_root.'data/config/config.js';
				}
				else
				{
					$file= $site_engine_root.'data/config/'.$subdomain.'_config.js';
				}
				if(file_exists($file))
				{
					$data = file_get_contents('../data/config/config.js');
					preg_match_all("/var .*?='.*?'/",$data,$matches);
					foreach($matches[0] as $k=>$v)
					{
						if(strpos($v,'appsecret')>0)
						{
							$search = explode("'",$v)[1];
							$old_content = file_get_contents($file);
							$content = str_replace($search,$weixin_appsecret,$old_content);
							break;
						}
					}
				}
				else
				{
					$content = '';
					$content .= "var weixin_name ='$weixin_name'<br>";
					$content .= "var weixin_appid ='$weixin_appid '<br>";
					$content .= "var followurl='$followurl'<br>";
					$content .= "var englishname='$englishname'<br>";
					$content .= "var wakeupdays='$wakeupdays'<br>";
					$content .= "var weixin_type='$weixin_type'<br>";
					$content .= "var encodingaeskey='$encodingaeskey'<br>";
					$content .= "var adminloginappid='$adminloginappid'<br>";
					$content .= "var adminloginappsecret='$weixin_appsecret'<br>";
					$content .= "var uploaddir='$uploaddir'<br>";
					$content .= "var httpurl='$httpurl'<br>";
				}
				if($content)
				{
					file_put_contents($file,$content);
				}
				require_once $site_engine_root.'mobile/lib/cache.php';
				$cache = new cache;
				$cache->clear();
				$cache->writetocache('system');
				$cache->clear();
				$cache->writetocache('system');
			}
		}
		else if($step == 4)
		{
			$partnerid = trim($_POST['partnerid']);
			if($partnerid)
			{
				$sql = "UPDATE {$tablepre}settings SET value='$partnerid' WHERE variable='partnerid'";
				$db->query($sql);
			}
			$paysignkey = trim($_POST['paysignkey']);
			if($paysignkey)
			{
				$sql = "UPDATE {$tablepre}settings SET value='$paysignkey' WHERE variable='paysignkey'";
				$db->query($sql);
			}
			// 导入模版消息
			$ret = importtpl();
			require_once $site_engine_root.'mobile/lib/cache.php';
			$cache = new cache;
			$cache->clear();
			$cache->writetocache('system');
		}
		// else if($step==5)
		// {
		// 	$upload = upload();
		// 	if (is_array($upload))
		// 	{
		// 		foreach ($upload as $key=> $value)
		// 		{
		// 			if (!empty($value)&&is_array($value))
		// 			{
		// 				$key_m=$value['module'];
		// 				$_POST[$key_m]=$value['attachment'];
		// 			}
		// 		}
		// 	}
		// 	else
		// 	{
		// 		if(!empty($upload_array))
		// 		{
		// 			foreach($upload_array as $key=> $value)
		// 			{
		// 				unset($_POST[$value]);
		// 			}
		// 		}
		// 	}
		// 	debugtofile($_POST);
		// }
	}
}
echo 1;exit;
?>