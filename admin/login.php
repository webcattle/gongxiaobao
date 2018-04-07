<?php
/******************************************************************************************************
**  企业+ 3.0 - 企业+社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2015. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业+' 微信号
**  请详细阅读企业+授权协议,查看或使用企业+的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: login file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
$admincp = 1;
require_once '../mobile/ajax/header.php';
require_once 'include/settings.php';
require_once 'include/function.php';
require_once '../mobile/lib/admin.php';

if($_COOKIE['mystate'])
{
	$mystate = $_COOKIE['mystate'];
}
else
{
	$mystate = md5(random(8));
	ssetcookie('mystate',$mystate,0);
}

if($_GET['loginstate'] && $_GET['loginstate']==$mystate)
{
	$openid = $rdb->get("scanlogin".$mystate);
	if($openid)
	{
		$user = $rdb->userInfo($openid);
		$_groupkey = 'usergroups_'.$user['uid'];
		if($rdb->onoff)
		{
			if(!$rdb->exists($_groupkey))
			{
				$sql = "SELECT id FROM {$tablepre}usergroup WHERE uid='$user[uid]' ORDER BY dateline ASC";
				$usergroup = $db->fetchColBySql($sql);
				$rdb->set($_groupkey,implode(",",$usergroup));
			}
			else
			{
				$usergroup = explode(",",$rdb->get($_groupkey));
			}
		}
		else
		{
			$sql = "SELECT id FROM {$tablepre}usergroup WHERE uid='$user[uid]' ORDER BY dateline ASC";
			$usergroup = $db->fetchColBySql($sql);
		}
		$usergroup = deletenullvalue($usergroup);
		if($usergroup && min($usergroup)<1000)
		{
			User::UserLogin('','');
            logs('login','','微信扫描登录');
            header('Location:index.php');exit;
			//header("Location:user.php");
		}
		else
		{
			echo '您没有管理权限';
			exit;
		}
	}
}
if($action=='qrconnect')
{
	if($_COOKIE['state'] == $_GET['state'])
	{
		$access_token = http_get('https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$adminloginappid.'&secret='.$adminloginappsecret.'&code='.$_GET['code'].'&grant_type=authorization_code');
		$info = json_decode($access_token,true);
		//debugtofile($info,'dangwd.txt');
		$userinfo = http_get("https://api.weixin.qq.com/sns/userinfo?access_token=".$info['access_token']."&openid=".$info['openid'].'&lang=zh_CN');
		$userinfo = json_decode($userinfo,true);
        if($userinfo['unionid'])
        {
            $sql = "SELECT openid FROM {$tablepre}members WHERE unionid=:id";
            $openid = $db->fetchOneBySql($sql,array(":id"=>$userinfo['unionid']));
            User::userLogin('','');
            logs('login','','微信扫描登录');
            header('Location:index.php');exit;
        }
        else
        {
			$openid = $userinfo['openid'];
			User::userLogin('','');
			logs('login','','微信扫描登录');
			header('Location:index.php');exit;
		}
	}
}

$username = addslashes($_COOKIE['myusername']);
if($user['uid'])
{
	@header("Location:index.php");exit;
}
if(strlen($username)>64)
{
	exit('ni');
}
$sql = "SELECT * FROM {$tablepre}members WHERE username=:username";
$uid = $db->fetchOneBySql($sql,array(":username"=>$username));
if($uid)
{
	$avatar = $uploaddir.avatar($uid);
}
else
{
	$avatar = $uploaddir.'avatar/user.jpg';
}

$sql = "delete from {$tablepre}qrcode where expire<? and module='weixinlogin'";
$db->query($sql,[time()]);
$sql = "select id from {$tablepre}qrcode where module='weixinlogin' and sourceurl=?";
$haveid = $db->fetchOneBySql($sql,['weixinlogin'.$mystate]);
$expire = 1800;
if(!$haveid)
{
	$title = '微信用户登录';
	$sql = "insert into {$tablepre}qrcode(title,module,moduleid,dateline,sourceurl,expire) values(?,'weixinlogin',0,?,?,?)";
	$db->query($sql,[$title,time(),'weixinlogin'.$mystate,time()+$expire]);
	$haveid = $db->insertId();
}
require_once $site_engine_root."mobile/lib/wechat.php";
require_once $site_engine_root."mobile/lib/error.php";
require_once $site_engine_root.'mobile/lib/function.php';

$options = options(0);
$weObj = new Wechat($options);
$ret = array();
$ticket = $weObj->getQRCode($haveid,0,$expire);
if($ticket)
{
	$qrcodefile = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket['ticket']);
	$sql = "update {$tablepre}qrcode set ticket=? where id=?";
	$db->query($sql,[$ticket['ticket'],$haveid]);
	if(!$englishname)
	{
		$englishname = $db_name;
	}
	$rdb->set("scanlogin".$mystate,0);
}
?>
<!DOCTYPE html>
<html class="">
    <head>
        <meta charset="UTF-8">
        <title>登录</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
	<link href="css/admin.css" rel="stylesheet" type="text/css" />
        <link href="css/boka.css" rel="stylesheet" type="text/css" />
        <link href="css/font.css" rel="stylesheet" type="text/css" />
        <link href="/mobile/data/css/fonts.css" rel="stylesheet" type="text/css" />
        <script src="js/jquery.min.js"></script>
        <script src="js/jquery.form.js" type="text/javascript"></script>
        <script src="js/base64.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		var ticket = '<?php echo $ticket;?>';
		var cur_domain = document.domain;
		var firstIndex = cur_domain.indexOf(".");
		var lastIndex = cur_domain.lastIndexOf(".");
		var uploaddir = "<?=$uploaddir?>";

		var preStr="";
		if(firstIndex==lastIndex){
			preStr = "www";
		}else{
			preStr = cur_domain.substr(0,firstIndex);
		}
		//document.write('<script src="/data/config/'+preStr+'_config.js?_dc='+new Date().getTime()+'"><\/script>')
	</script>
        <script src="js/app.js" type="text/javascript"></script>
        <script src="js/index.js" type="text/javascript"></script>
        <script>
        function toQzoneLogin()
		{
			childWindow = window.open("/mobile/ajax/user.php?action=qqlogin","TencentLogin","width=450,height=320,menubar=0,scrollbars=1, resizable=1,status=1,titlebar=0,toolbar=0,location=1");
		}

		function closeChildWindow()
		{
			childWindow.close();
		}
        </script>
    </head>
    <body class="loginarea">
                        <style>
                                .align_center{text-align:center;}
                                .loginarea{background-color:#2D2D2D;position:relative;}
                                .loginouter{position:absolute;left:0;top:0;right:0;bottom:0;}
                                .loginarea .inner{height:100%;}
                                .loginarea .inner-cell {
                                    width: 100%;
                                    height: 100%;
                                    vertical-align: middle;
                                    text-align: center;
                                    display: table-cell;
                                }
                                .loginarea .txtborder {
                                    width: 40%;
                                    margin: 0 auto;
                                    border-radius: 30px;
                                    background-color: #202020;
                                    padding: 10px;
                                    color: #fff;
                                }
                                .loginarea .pic .qrcode{max-width:100%;}
                                .sign-in *{margin:0 auto;}
                                .sign-in{position:relative;width:420px;height:400px;background-color:#fff;border-radius:5px;margin:0 auto;padding:50px 0 25px 0;}
                                .sign-in .login_title{width:100%;height: 18px;line-height: 18px; text-align:left;font-size: 16px;color: #3c3c3c;padding-top: 9px;padding-bottom: 8px;margin-bottom:20px;font-weight: 700;box-sizing: initial;}
                                .sign-in .code-in,.sign-in .password-in{width:70%;margin:0 auto;}
                                .sign-in .password-in span{display: block;width:100%;margin:0 auto 25px auto;}
                                .sign-in .password-in input{width:100%;font-size: 14px;line-height: 18px;padding: 11px 8px 11px 8px;border:1px solid #ddd;display: block;}
                                .sign-in .password-in .submit{background-color:#f40;font-size:16px;width:100%;height:42px;border:none;border-radius:3px;color:#fff;font-weight:bold;cursor: pointer;}
                                .sign-in .password-in .submit:hover{background-color:#f52b00;}
                                .sign-in i {color:#f40;font-size:52px;cursor: pointer;height:52px;line-height:52px;position: absolute;top:5px;right:5px;}
                                .sign-in .tab_password{position:absolute;top:0px;right:0px;height:52px;}
                                .sign-in .login-tip{position: absolute;top: 12px;right: 64px;width: 150px;}
                                .sign-in .poptip{border: 1px solid #f3d995;height: 16px;line-height: 16px;padding: 5px 20px 5px 15px;background: #fefcee;position: relative;box-sizing: initial;}
                                .sign-in .poptip-arrow{top: 8px;right: 0;position: absolute;z-index: 10;}
                                .sign-in .poptip-arrow em, .poptip-arrow span{position: absolute;width: 0;height: 0;border-color: rgba(255,255,255,0);border-color: transparent \0;_border-color: tomato;_filter: chroma(color=tomato);border-style: solid;overflow: hidden;top: 0;left: 0;}
                                .sign-in .poptip-arrow span {border-left-color: #fefcee;border-width: 6px 0 6px 6px;}
                                .sign-in .poptip-arrow em{top: 0;left: 1px;border-left-color: #f3d995;border-width: 6px 0 6px 6px;}
                                .sign-in .poptip-content{color:#ff9974;}
                                .sign-in i:hover{color:#f52b00}
                        </style>
                        <div class="loginouter">
                                <div class="t-table inner">
                                        <div class="t-cell inner-cell">
                                                <div class="sign-in">
                                                        <div class="hd">
                                                                <div class="login-switch">
                                                                        <div class="tab_code"><i class="al al-dengludiannao toqrcode"></i></div>
                                                                        <div class="tab_password" style="display:none;">
                                                                                <i class="al al-saomadenglu01 topwd"></i>
                                                                                <div class="login-tip topwd">
                                                                                        <div class="poptip">
                                                                                                <div class="poptip-arrow">
                                                                                                        <em></em>
                                                                                                        <span></span>
                                                                                                </div>
                                                                                                <div class="poptip-content">
                                                                                                       扫码登录更安全
                                                                                                </div>
                                                                                        </div>
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                        </div>
                                                        <div class="tab_content">
                                                                <div class="qrcodeloginarea">
                                                                        <div class="code-in">
                                                                                <div style="font-size:20px;">微信扫码登录</div>
                                                                                <div class="pic" style="margin-top:12px;">
                                                                                        <img class="qrcode" src="<? echo $qrcodefile ?>" />
                                                                                </div>
                                                                                <!--
                                                                                <div class="txtborder" style="margin-top:12px;">
                                                                                        <div>请使用微信扫描二维码登录</div>
                                                                                        <div class="mt5">“<? echo $weixin_name ?>”</div>
                                                                                </div>
                                                                                -->
                                                                        </div>
                                                                </div>
                                                                <div class="pwdloginarea" style="display: none;">
                                                                        <div class="password-in">
                                                                                <form id="login_form">
                                                                                        <p class="login_title">密码登录</p>
                                                                                        <span><input id="u_username" placeholder="用户名" value="<?=$_COOKIE['myusername']?>"></span>
                                                                                        <span><input type="password" id="u_password" placeholder="密码"></span>
                                                                                        <button id="login" class="submit" type="submit">登录</button>
                                                                                </form>
                                                                        </div>
                                                                        <div class="font14 color-000 align_center" style="margin-top:30px;">请用<span style="color:#f40">Chrome</span>或者<span style="color:#f40">Firefox</span>浏览器登录管理控制台</div>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
        <script type="text/javascript">
                var qrcodearea = $(".qrcodeloginarea"),
                        pwdarea = $(".pwdloginarea"),
                        loginInterval,
                        mystate = '<? echo $mystate?>',
                        qrcodeFun = function(){
                                $.ajax({
                                        url:"/mobile/ajax/misc.php?action=getloginscanstate&admincp=1&mystate="+mystate,
                                        dataType:"json",
                                        success:function(data){
                                                if(data.flag && data.status){
                                                        clearInterval(loginInterval);
                                                        location.href = data.url;
                                                }
                                        }
                                });
                        };
                loginInterval = setInterval(function(){
                        qrcodeFun();
                },1000);
		$(".topwd").click(function(){
		        $(".tab_code").show();
		        $(".tab_password").hide();
		        qrcodearea.show();
		        pwdarea.hide();
                        loginInterval = setInterval(function(){
                                qrcodeFun();
                        },1000);
		});
		$(".toqrcode").click(function(){
		        $(".tab_code").hide();
		        $(".tab_password").show();
		        qrcodearea.hide();
		        pwdarea.show();
		        clearInterval(loginInterval);
		});

            $(function() {
                startTime();
                $(".center").center();
                $(window).resize(function() {
                    $(".center").center();
                });
            });

            function startTime()
            {
                var today = new Date();
                var h = today.getHours();
                var m = today.getMinutes();
                var s = today.getSeconds();

                // add a zero in front of numbers<10
                m = checkTime(m);
                s = checkTime(s);

                var day_or_night = (h > 11) ? "PM" : "AM";

                if (h > 12)
                    h -= 12;

                $('#time').html(h + ":" + m + ":" + s + " " + day_or_night);
                setTimeout(function() {
                    startTime()
                }, 500);
            }

            function checkTime(i)
            {
                if (i < 10)
                {
                    i = "0" + i;
                }
                return i;
            }

            jQuery.fn.center = function() {
                this.css("position", "absolute");
                this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) +
                        $(window).scrollTop()) - 30 + "px");
                this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) +
                        $(window).scrollLeft()) + "px");
                return this;
            }
        </script>
    </body>
</html>