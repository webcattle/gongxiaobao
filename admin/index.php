<?php
/******************************************************************************************************
**  企业Plus 7.0 - 企业Plus社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业Plus授权协议,查看或使用企业Plus的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: index file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
require_once '../mobile/ajax/header.php';
require_once 'include/settings.php';
require_once 'include/function.php';
require_once 'include/global.php';
require_once $site_engine_root."mobile/ajax/validate.php";
require_once $site_engine_root."mobile/lib/admin.php";
require_once $site_engine_root."mobile/lib/wechat.php";
require_once $site_engine_root."mobile/lib/error.php";
require_once $site_engine_root."mobile/lib/function.php";
require_once $site_engine_root."mobile/lib/shopfunctions.php";
$options = options(0);
$weObj = new Wechat($options);
if(empty($siteinfo) && $domaintype =='subdomain' && !$directdomain)
{
	exit('站点尚未开通');
}
else
{
    if($siteinfo['moderate']==0 && $domaintype =='subdomain' && !$directdomain && $domain!=$maindomain)
    {
	    exit('站点尚未开通,请等待审批');
    }
	if(file_exists('../install.php') && $domain!=$maindomain && !$accesstoken)
	{
		exit('为了您的系统安全,请删除根目录下的install.php 文件');
	}
}

$template = str_replace("/","",dirname($_SERVER['SCRIPT_NAME']));
if(!$user['uid'])
{
	@header("Location:login.php");exit;
}
else if(min($usergroup)>=1000)
{
	ssetcookie('auth','', time());
	@header("Location:/mobile/#user.php");exit;
}
else
{
	if($ifredis==1)
	{
		$rdb->sync();
	}
	if(!empty($siteinfo))
	{
		require_once './include/siteinfo.php';
	}
	if(!$module&&!$action)
	{
		$newmembers = $newweixin = $dau = $todaypv = $todayshare = $todayshareview = $peruser =0;
		$today =  mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		if(empty($_COOKIE['totalmembers']))
		{
			$info = $weObj->getUserList();
			$totalmembers = $info['total'];
			if($totalmembers>0)
			{
				ssetcookie('totalmembers',$totalmembers,time()+300);
			}
		}
		else
		{
			$totalmembers = $_COOKIE['totalmembers'];
		}
		$totalmembers = intval($totalmembers)? $totalmembers:0;
		$sql = "SELECT count(uid) FROM {$tablepre}members WHERE dateline>'$today' AND subscribe=1";
		$newweixin = $db->fetchOneBySql($sql);
		$sql = "SELECT count(*) FROM {$tablepre}members WHERE lastmessage>'$today' AND subscribe='-1'";
		$quguanweixin = $db->fetchOneBySql($sql);


		$sql = "SELECT count(uid) FROM {$tablepre}membersinfo WHERE lastvisit>'$today'";
		$dau = $db->fetchOneBySql($sql);
                $day = date('Ymd');
		$sql = "SELECT sum(c_d) FROM {$tablepre}count WHERE day='$day'";
		$todaypv = $db->fetchOneBySql($sql);
		if(!isset($todaypv))
		{
			$todaypv = 0;
		}
		$sql = "SELECT count(id) FROM {$tablepre}mpmsg WHERE dateline>'$today'";
		$todayweixin = $db->fetchOneBySql($sql);
		$sql = "SELECT count(id) FROM {$tablepre}share WHERE dateline>'$today'";
		$todayshare = $db->fetchOneBySql($sql);
		$sql = "SELECT count(id) FROM {$tablepre}shareview WHERE dateline>'$today'";
		$todayshareview = $db->fetchOneBySql($sql);
		$sql = "SELECT count(uid) FROM {$tablepre}members";
		$total = $db->fetchOneBySql($sql);
		$peruser = @ceil($todaypv/$dau);
		eval ("\$home = \"" . $tpl->get("home",$template). "\";");
		//网站 访问统计
		$echart=chart_module_html('count','访问量',1,'sum','c_d',$datestyle,$datenum);
		if($agenttype=='site'&&!empty($adminarray['weixin']))
		{
			//微信分享月统计
			$echart2=chart_module_html('share','微信分享月统计','3','count','id',$datestyle,$datenum);
			//微信分享点击月统计
			$echart3=chart_module_html('shareview','微信分享点击月统计','4','count','id',$datestyle,$datenum);
		}
		//统计四开始
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
			//$today =  strtotime($datenum.' month')-$cha;
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
		}
		else if($datestyle=='d')
		{
			$date_y_show='H:i';
			$today =  strtotime($datenum.' day');
			$daynum=24;
		}
		else if($datestyle=='y')
		{
			$date_y_show='Y-m';
			$cha =  time()-mktime(0, 0, 0, date("m"), date("d"), date("Y"));
			$today =  strtotime($datenum.' Year')-$cha;
			$daynum=12;

		}
		$today_cz=$today;
		if($domain != $maindomain)
		{
			$sql = "SELECT count(uid) FROM {$tablepre}members WHERE subscribe=1";
			$total = $db->fetchOneBySql($sql);
			$array =$ret= array();
			$name='微信每日关注数';
			for($i=0;$i < $daynum;$i++)
			{
				if($datestyle=='m')
				{
					$today += 86400;
					$nextday = $today+86400;
				}
				else if($datestyle=='d')
				{
					$today+=3600;
					$nextday = $today+3600;
				}
				else
				{
					$today += (86400*30);
					$nextday = $today+(86400*30);
				}
				$sql = "SELECT count(uid) FROM {$tablepre}members WHERE type=1 AND dateline>='$today' AND dateline<'$nextday'";
				$info = $db->fetchOneBySql($sql);
				if($info)
				{
					$ret['series'][$name][] = $info;
				}
				else
				{
					$ret['series'][$name][] = 0;
				}

				$ret['xAxis'][] = date($date_y_show,$today);
			}
			$ret['title']=$name."(".$total.")";
			$ret['legend']=array($name);
			$echart4=chart_data($ret,'line','5');
			$echart5=chart_data($ret,'word','6');
		}
		//统计四结束
		$ret=array();
		$ret['title']='关键词';
		$sql="SELECT keyword , count(keyword) as num FROM {$tablepre}userdict GROUP BY keyword ORDER BY count(keyword) DESC limit 30";
		$sqlarr=$db->fetchAssocArrBySql($sql);
		foreach($sqlarr as $value)
		{
			$ret['series'][$value['keyword']]=$value['num'];
		}
		if(!empty($ret['series']))
		{
			$echart5=chart_data($ret,'wordCloud','6');
		}
		$sql = "SELECT dateline FROM {$tablepre}logs WHERE username='$user[username]' AND act='login'";
		$prevdateline = $db->fetchOneBySql($sql);
		// 关注地址

	}
	/*if(empty($followurl))
	{
		require_once $site_engine_root."mobile/lib/wechat.php";
		require_once $site_engine_root."mobile/lib/error.php";
		require_once $site_engine_root."mobile/ajax/settings.php";
		require_once $site_engine_root."mobile/lib/function.php";
		$options = options(0);
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
		$array = array("title"=> '欢迎关注'.$weixin_name,
		"thumb_media_id"=> $media_id['media_id'],
		"author"=> '',
		"digest"=>'感谢关注'.$weixin_name,
		"show_cover_pic"=>1,
		"content"=> $content,
		"content_source_url"=>'http://'.$_SERVER['HTTP_HOST'].'/mobile/');
		$followurldata[0] = $array;
		$followurldata = array("articles"=>$followurldata);
		$info = $weObj->uploadForeverArticles($followurldata);
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
	}*/

}
$httpurl = 'http://'.$_SERVER['HTTP_HOST'];
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $weixin_name?>--企业Plus社会化营销平台</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <link rel="Shortcut Icon" href="/mobile/data/images/favicon.ico" type="image/x-icon">
	<link href="css/animate.css" rel="stylesheet" type="text/css" />
	<link href="css/admin.css" rel="stylesheet" type="text/css" />
        <link href="css/font.css" rel="stylesheet" type="text/css" />
        <link href="css/icons.css" rel="stylesheet" type="text/css" />
        <link href="css/boka.css" rel="stylesheet" type="text/css" />
        <link href="css/accordion.css" rel="stylesheet" type="text/css" />
        <link href="css/mobiledate.css" rel="stylesheet" type="text/css" />
	<link href="css/editor/spectrum.css" rel="stylesheet" type="text/css" />
        <link href="css/colorpicker.css" rel="stylesheet" type="text/css" />
	<link href="css/block.css" rel="stylesheet" type="text/css" />
<?php
	if (($multidomain && $domain!=$maindomain)||$directdomain==1)
	{
		$plugin_admin_cssdir = $userdir.'plugins/admin/css';
		echo '<script>var pluginadmindir = "'.$userdir.'plugins/admin/";</script>';
		if(is_dir($site_engine_root.$plugin_admin_cssdir))
		{
			$path_pattern = $site_engine_root.$plugin_admin_cssdir.'/*.css';
			foreach(glob($path_pattern) as $file)
			{
				$cssname = $plugin_admin_cssdir.'/'.end(explode("/",$file));
				echo '<link href="'.$cssname.'" rel="stylesheet" type="text/css" />';
			}
		}
	}
?>
	<script type="text/javascript">
		var weixin_name = "<?php echo $weixin_name ?>";
		var weixin_appid = "<?php echo $weixin_appid ?>";
		var followurl = "<?php echo $followurl ?>";
		var englishname = "<?php echo $englishname ?>";
		var uploaddir = "<?php echo $uploaddir ?>";
		var httpurl = "<?php echo $httpurl ?>";
		var ticket = '<?php echo $ticket;?>';
	</script>
        <script src="js/jquery.min.js" type="text/javascript"></script>
        <script src="js/jquery.form.js" type="text/javascript"></script>
        <script src="js/h5media.js" type="text/javascript"></script>
        <script src="js/base64.min.js" type="text/javascript"></script>
    	<script src="js/echarts/echarts.min.js" type="text/javascript"></script>
        <script src="js/boka.js" type="text/javascript"></script>
        <script src="js/mobiledate.js" type="text/javascript"></script>
        <script src="js/neweditor/spectrum.js" type="text/javascript"></script>
        <script src="js/colorpicker-mini.js" type="text/javascript"></script>
        <script src="js/iscroll.js" type="text/javascript"></script>
	<script src="js/selectTree.js" type="text/javascript"></script>
	<script src="/mobile/data/js/jweixin.js" type="text/javascript"></script>
	<script type="text/javascript">
		document.write('<script src="js/app.js?_dc='+new Date().getTime()+'"><\/script>');
		document.write('<script src="js/index.js?_dc='+new Date().getTime()+'"><\/script>');
		document.write('<script src="js/weixin.js?_dc='+new Date().getTime()+'"><\/script>');
	</script>
</head>
<body class="skin-blue">
	<header class="header">
		<?php
		if($siteinfo['verify_type_info']==-1)
		{
			echo '<div style="text-align:center;background-color: #f39c12 !important;color:#fff;">未认证公众帐号，<a href="http://mp.weixin.qq.com" target="_blank"><span  style="text-align:center;background-color: #f39c12 !important;color:#fff;">点击去认证</span></a></div>';
		}
		?>
		<a href="javascript:;" class="logo">企业<span style="font-size:26px">+</span></a>
	    	<nav class="navbar navbar-static-top" role="navigation">
	        	<a href="javascript:;" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
	            		<span class="sr-only"></span>
	            		<span class="icon-bar"></span>
	            		<span class="icon-bar"></span>
	            		<span class="icon-bar"></span>
	            		<div style="width: 50px;height: 50px;line-height: 50px;position: absolute;left: 37px;top: 0;color: #fff;">菜单</div>
	        	</a>
	        	<div class="navbar-right">
				<ul class="nav navbar-nav">
		        		<li class="dropdown notifications-menu" style="display:none;">
		                    		<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
		                        		<i class="fa fa-warning"></i>
		                        		<span class="label label-warning">0</span>
		                    		</a>
		        		</li>
		        		<li class="dropdown tasks-menu">
		            			<a href="javascript:;" onclick="menuclick('admin.php?module=mpmsg&action=list');" class="dropdown-toggle" data-toggle="dropdown">
		                			<i class="fa fa-message"></i>&nbsp;消息
		                			<span class="label label-danger" style="display:none;"></span>
		            			</a>
		        		</li>
		        		<li class="tasks-help">
		            			<a href="javascript:;"><i class="fa fa-female"></i>&nbsp;帮助</a>
		        		</li>
		        		<?php if($agenttype!='weixin'){?>
		        		<li class="dropdown user user-menu">
		        			<!--
		            			<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
		            			-->
		            			<a href="javascript:;">
		                			<i class="glyphicon glyphicon-user"></i>
		                			<span><?php echo $user['linkman'];?><i class="caret"></i></span>
		            			</a>
						<ul class="dropdown-menu">
		                			<li class="user-header bg-light-blue">
		                    				<img src="<?=$uploaddir?><?=avatar($user['uid'])?>" class="img-circle" alt="User Image" />
		                    				<p>
		                       					<?php echo $user['linkman'];?>
		                        				<small></small>
		                    				</p>
		                			</li>
		               	 			<li class="user-footer">
		                    				<div class="pull-left">
		                        				<a href="javascript:;"  onclick="menuclick('admin.php?module=members&action=password')" class="btn btn-default btn-flat">修改密码</a>
		                    				</div>
		                    				<div class="pull-right">
		                        				<a href="javascript:;" id="logout" class="btn btn-default btn-flat">退出</a>
		                    				</div>
		                			</li>
		            			</ul>
		        		</li>
					<?php }?>
		        		<li class="dropdown fast-search" style="positin:relative;">
		            			<a id="searchInfo" href="javascript:;"  class="dropdown-toggle" data-toggle="dropdown">
		                			<i class="fa fa-search"></i>&nbsp;搜索
		            			</a>
		            			<div id="searcharea" style="position:absolute;right:0;top:42px;display:none;">
					            	<form method="POST" id="search" action="admin.php?module=site&amp;action=search" class="sidebar-form">
								<div class="input-group" style="width:300px;">
									<input type="text" id="q" name="q" class="form-control" placeholder="搜索...">
									<span class="input-group-btn">
										<button type="submit" name="seach" id="search-btn" class="btn btn-flat">
											<i class="fa fa-search"></i>
										</button>
									</span>
								</div>
							</form>
		            			</div>
		        		</li>
		   	 	</ul>
			</div>
		</nav>
	</header>
	<div class="wrapper wrapper-outer row-offcanvas row-offcanvas-left">
		<aside class="left-side sidebar-offcanvas">
			<section class="sidebar">
				<div class="user-panel">
					<div class="pull-left image">
							<img src="<?=$uploaddir?><?=avatar($user['uid'])?>" class="img-circle" alt="User Image" />
					</div>
					<div class="pull-left info">
						<p><?=$user['linkman']?></p>
					</div>
				</div>
				<!--
				<form method="POST" id="search" action="admin.php?module=site&action=search" class="sidebar-form">
					<div class="input-group">
						<input type="text" id="q" name="q" class="form-control" placeholder="搜索..."/>
						<span class="input-group-btn">
						<button type='submit' name='seach' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
						</span>
					</div>
				</form>
				-->
				<ul class="menu-tabs">
					<!--<li class="active" role="default"><div><span>运营菜单</span></div></li>
					<li role="function"><div><span>功能菜单</span></div></li>
					-->
				</ul>
				<ul class="sidebar-menu" role="default" id="adminmenu"></ul>
				<ul class="sidebar-menu" role="function" id="adminmenu1" style="display:none;"></ul>
				<div class="site-panel">
					<div class="pull-left image">
						<img src="/data/images/boka.png" style="float:left;" />
						<?php if($accesstoken && $siteinfo['qrcode_url'])
						{
							echo '<img style="float:left;" src="'.$uploaddir.'/qrcode.jpg" />';
						}
						?>
					</div>
				</div>
			 </section>
		</aside>
		<aside class="right-side cloud">
			<section class="content-header">
				<h1>&nbsp;一云在手 多平无忧
					<small></small>
				</h1>
	                    	<ol class="breadcrumb">
	                        	<li><a class="home" href="index.php"><i class="fa fa-dashboard"></i> 企业Plus</a></li>
	                        	<li class="active">首页</li>
	                    	</ol>
                	</section>
			<section class="content">
				<?=$home?>
				<div class="row">
					<div class="tab-title" style="margin-left:15px;margin-right:15px;margin-bottom:5px;">
						<span class="widget-icon"><i class="fa fa-bar-chart-o"></i></span>
						<h2>统计图</h2>
						<ul class="nav-tabs">
							<li role="y"><a><span>年统计<span></a></li>
							<li class="active" role="m"><a><span>月统计<span></a></li>
							<li role="d"><a><span>日统计<span></a></li>
						</ul>
					</div>
					<?php if(!empty($echart4)){?>
	                        	<section class="col-lg-6 connectedSortable">
	                        		<?php echo $echart4;?>
	                        	</section>
	                          	<?php }?>
					<section class="col-lg-6 connectedSortable">
						<?php echo $echart;?>
	                     		</section>
	                        	<?php if(!empty($echart2)){?>
	                       		<section class="col-lg-6 connectedSortable">
						<?php echo $echart2;?>
	                        	</section>
	                         	<?php }?>
	                         	<?php if(!empty($echart3)){?>
	                        	<section class="col-lg-6 connectedSortable">
	                        		<?php echo $echart3;?>
	                        	</section>
	                          	<?php }?>
	                          	<!--
	                          	<?php if(!empty($echart5)){?>
	                        	<section class="col-lg-6 connectedSortable">
	                        		<?php echo $echart5;?>
	                        	</section>
	                          	<?php }?>
	                          	-->
	                        	<script type="text/javascript">
					var setTabActive = function(){
						var herf_url=window.location.href;
						var index = herf_url.indexOf('?');
						var newUrl = herf_url.substr(0,index+1);
						var params = herf_url.substr(index+1).split("&");
						for(var i=0;i<params.length;i++){
							var p=params[i].split("=");
							if(p[0]=="datestyle"){
								$(".nav-tabs li").removeClass("active");
								$(".nav-tabs li[role="+p[1]+"]").addClass("active");
								break;
							}
						}
					};
					setTabActive();
			   		$(".nav-tabs li").click(function(event){
						event.preventDefault();
						var datestyle = $(this).attr('role');
						var herf_url=window.location.href;
						var index = herf_url.indexOf('?');
						var backurl;
						if(index>0)
						{
							var newUrl = herf_url.substr(0,index+1);
							var params = herf_url.substr(index+1).split("&");
							var pstr = [];
							for(var i=0;i<params.length;i++){
								var p=params[i].split("=");
								if(p[0]!="datestyle"){
									pstr.push(params[i]);
								}
							}
							backurl=newUrl+pstr.join("&")+"&datestyle="+datestyle;
						}
						else
						{
							backurl=window.location.href+"?datestyle="+datestyle;
						}
						location.href= backurl;
					});
			   		</script>
				</div>
			</section>
		</aside>
		<div class="helptemplate" style="display:none;">
			<div class="form-group">
				<div class="input-group" style="width:100%;">
					<!--
					<table style="width:100%;height:100%;" border="0">
						<tr>
							<td>
					-->
								<div class="tab-title ts">
									<ul class="nav-tabs">
										<li class="active" role="tab1">
											<a>
												<i class="fa fa-help" style="color:green;margin-right:3px;"></i>
												<span>常见问题</span>
											</a>
										</li>
										<li role="tab2">
											<a>
												<i class="fa fa-video" style="color:green;margin-right:3px;"></i>
												<span>视频教程</span>
											</a>
										</li>
										<li role="tab3">
											<a>
												<i class="fa fa-feedback" style="color:green;margin-right:3px;"></i>
												<span>意见建议</span>
											</a>
										</li>
										<li role="tab4">
											<a>
												<i class="fa fa-comments" style="color:green;margin-right:3px;"></i>
												<span>对话开发者</span>
											</a>
										</li>
									</ul>
								</div>
								<div class="tab-content">
									<div class="tab-item tab-tab1" style="display:block;opacity:1;">
									</div>
									<div class="tab-item tab-tab2" style="display:none;opacity:0;">
									</div>
									<div class="tab-item tab-tab3" style="display:none;opacity:0;">
										<input type="hidden" name="modules" />
										<input type="hidden" name="actions" />
										<input type="hidden" name="domain" value="<?=$domain ?>" />
											<!--
										<div class="input-group" style="width:100%;">
											<div>
												<span class="input-group-addon">建议</span>
												<div class="form-control">
													<textarea name="content" style="width:100%;height:120px;"></textarea>
												</div>
											</div>
											<div style="text-align:center;">
												<button class="btn btn-warning subadvice">提交</button>
											</div>
										</div>
											-->

									</div>
									<div class="tab-item tab-tab4" style="display:none;opacity:0;">
									</div>
								</div>
								<!--
							</td>
						</tr>
					</table>
					-->
				</div>
			</div>
		</div>
		<div class="helptemplate1" style="display:none;">
			<div class="input-group" style="width:100%;">
				<div class="form-control">
					<i class="fa fa-question" style="color:green;margin-right:3px;"></i>
					<a class="discon distitle"></a>
					<div class="hrefcon" style="display:none;">
						答:<span class="discontent" style="margin-left:3px;"></span>
					</div>
				</div>
			</div>
		</div>
		<div class="helptemplate2" style="display:none;">
			<video class="myvideo" src="http://qiyeplus.qiyeplus.com" width="320" height="240" controls="" autobuffer=""></video>
		</div>
		<div class="helptemplate3" style="display:none;">
			<div class="chatmessages chatlist"></div>
		</div>
	</div>
	<?php
	if($newui=='1')
	{
		eval ("\$footer = \"" . $tpl->get("footer",$template). "\";");
		echo $footer;
	}
	exitandreturn('','index','index');
	?>
</body>
</html>