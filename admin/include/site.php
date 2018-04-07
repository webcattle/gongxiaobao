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
$variable = $module.'sys';
$variable = $$variable;
if($variable['show']==1 && !$a[$module][$action])
{
	$a[$module][$action] = array('edit','delete');
}
foreach($data as $key=> $value)
{
	//$trmodule = '';
	/*****************************************************
	** 赋值给每行 方便js 处理
	*****************************************************/
	$adminstring = '';
	if (!$value['module'])
	{
		$value['module']=$module;
	}
	if($module == 'customer' || $action=='members' || $action=='pool')
	{
		$trmodule = 'members';
		$value['id'] = $value['uid'];
	}
	if($module=='site' && $action=='search')
	{
		$trmodule = $value['module'];
	}
	if($value['uid']!==false)
	{
		$truid = $value['uid'];
	}
	else
	{
		$truid = $value['id'];
	}
	$trclassid = $value['classid'];
	$trusername = $value['linkman']? $value['linkman']:$value['username'];

	/*****************************************************
	** 动作处理
	*****************************************************/
	if(!empty($a[$module][$action]))
	{
		if( $action == 'search' && ($search=='bd' || $search=='wb'|| $search=='wx'))
		{
			$adminaction = array();
		}
		else
		{
			// 如果是没有审批过的 直接不显示置顶 推送 和 展示功能

			if($value['moderate']!=1)
			{
				$b[$module][$action] = array_delete_value($a[$module][$action],'pushit');
				$b[$module][$action] = array_delete_value($b[$module][$action],'sorts');
				$b[$module][$action] = array_delete_value($b[$module][$action],'priority');
				$b[$module][$action] = array_delete_value($b[$module][$action],'cancel');
				$b[$module][$action] = array_delete_value($b[$module][$action],'copytoblog');
				$a[$module][$action] = array_delete_value($a[$module][$action],'desend');
				$b[$module][$action] = array_delete_value($b[$module][$action],'multiclass');
			}
			else
			{
				if($value['desend']==1 || ($multidomain==1 && $_SERVER['HTTP_HOST']!=$maindomain))
				{
					$a[$module][$action] = array_delete_value($a[$module][$action],'desend');
				}
				$b[$module][$action] = $a[$module][$action];
				$b[$module][$action] = array_delete_value($b[$module][$action],'censor');
			}
			if ($value['targetid']>0)
			{
				$b[$module][$action] = array_delete_value($b[$module][$action],'multiclass');
			}
			if($module=='groups' && $value['id']<1000)
			{
				if($value['id']==1)
				{
					$b[$module][$action] = array_delete_value($b[$module][$action],'delete');
				}
				$b[$module][$action] = array_delete_value($b[$module][$action],'grouprules');
			}
			if($module=='usergroup' && $id >= 1000)
			{
				$b[$module][$action] = array_delete_value($b[$module][$action],'setpassword');
				$b[$module][$action] = array_delete_value($b[$module][$action],'setusername');
			}
			if($module=='product' )
			{
				if($supplier_mode==1)
				{
					$b[$module][$action] = array_delete_value($b[$module][$action],'authorize');
				}
				else
				{
					$b[$module][$action] = array_delete_value($b[$module][$action],'setprice');
				}
			}
			if($module=='activity')
			{
				if(!in_array($value['type'],$lotterytypes))
				{
					$b[$module][$action] = array_delete_value($b[$module][$action],'setlottery');
				}
			}
			$adminaction = $b[$module][$action];
		}
		foreach($adminaction as $ak=> $av)
		{
			if($av=='up' || $av=='down')
			{
				$adminstring .= '<i class="fa fa-'.$av.'"></i><a href="javascript:;">&nbsp;'.trim($l[$av]).'</a>&nbsp;';
			}
			else if($av == 'setprice')//设置商品规格价格
			{
				$adminstring .= '<i class="fa fa-'.$av.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?module=product&action=setprice&id='.$value['id'].'\');" class="'.$av.'" >&nbsp;'.trim($l[$av]).'</a>&nbsp;';
			}
			else
			{
				$adminstring .= '<i class="fa fa-'.$av.'"></i><a href="javascript:;" class="'.$av.'" >&nbsp;'.trim($l[$av]).'</a>&nbsp;';
			}
		}
	}

	// 动作结束
	/*****************************************************
	** 数据处理
	*****************************************************/
	if(!$trmodule)
	{
		if($value['module'])
		{
			if($module=='site'&&$action=='search')
			{
				$trmodule=$value['module'];
			}
			else if($module=='recyclebin' || $module=='share')
			{
				$trmodule = $value['module'];
			}
			else
			{
				$trmodule = $module;
			}
		}
		else
		{
			$trmodule=$module;
		}
	}

	$trid = ($module == 'members')? $value['uid'] : $value['id'];
	$info .= '<tr trid="'.$trid.'" trmodule="'.$trmodule.'"  truid="'.$truid.'" trclassid="'.$trclassid.'" trusername="'.$trusername.'">';
        foreach($char as $k=> $v)
	{
		if($v=='id')
		{
			continue;
		}
		//codemaker
		$fields_type=0;
		foreach($chararray as $_k => $_v)
		{
			if($_v==$v)
			{
				$fields_type = $typearray[$_k];
				break;
			}
		}

		if($fields_type==4)
		{
			if(!empty($fieldsarray[$v][$value[$v]]))
			{
				$listvalue=$fieldsarray[$v][$value[$v]];
			}
			else
			{
				$listvalue = '';
			}
			$info .= '<td style="white-space:nowrap;text-align:center;">&nbsp;'.$listvalue.'</td>';
		}
		else
		{
			if($v== 'id') // 编号
			{
				$info .= '<td style="white-space:nowrap;text-align:center;">&nbsp;'.$value[$v].'</td>';
			}
			else if($v == 'uid' || $fields_type==13)
			{
				if($value[$v])
				{
					$userinfo = $rdb->userInfo($value[$v]);
					$value['avatar'] = avatar($value[$v]);
					$info .= '<td style="white-space:nowrap;min-width:100px;overflow:hidden;text-align:left;"><img src="'.getImgUrl($value['avatar']).'" style="width:30px;height:30px;">&nbsp;<a href="javascript:;"   title="'.strip_tags($value[$v]).'" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value[$v].'\')">'.$userinfo['linkman'].'</a></td>';
				}
				else
				{
					$info .= '<td style="white-space:nowrap;min-width:100px;overflow:hidden;text-align:center;">未分配</td>';
				}
			}
			else if($v == 'uploader')
			{
				if($value[$v])
				{
					$sql = "SELECT * FROM {$tablepre}members WHERE uid='$value[$v]'";
					$userinfo = $db->fetchSingleAssocBySql($sql);
					$value['avatar'] = avatar($value['uid']);
					$info .= '<td style="white-space:nowrap;min-width:100px;overflow:hidden;text-align:left;">&nbsp;<a href="javascript:;"   title="'.strip_tags($value[$v]).'" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\')">'.$userinfo['linkman'].'</a></td>';
				}
				else
				{
					$info .= '<td style="white-space:nowrap;min-width:100px;overflow:hidden;text-align:center;">未分配</td>';
				}
			}
			else if($v== 'username' || $v=='linkman' || $v=='nickname' ) // 用户名
			{
				if($sourcemodule == 'members')
				{
					$value[$v] = threedays($value['dateline'],$value[$v],$value['appid'],1);
				}
				else if($sourcemodule =='unsubscribe')
				{
					$value[$v] = threedays($value['lastmessage'],$value[$v],$value['appid'],1);
				}
				$value['avatar'] = getImgUrl(avatar($value['uid']));
				if(empty($value[$v]) && $value['uid'])
				{
					$sql = "SELECT $v FROM {$tablepre}members WHERE uid='$value[uid]'";
					$value[$v] = $db->fetchOneBySql($sql);
				}
				$value[$v] = !empty($value[$v]) ? $value[$v]:$l['deleteduser'];
				$info .= '<td style="white-space:nowrap;min-width:100px;overflow:hidden;text-align:left;"><img src="'.$value['avatar'].'" style="width:30px;height:30px;">&nbsp;<a href="javascript:;"   title="'.strip_tags($value[$v]).'" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\')">'.$value[$v].'</a></td>';
			}
			else if($v=='content') // 内容
			{
				if($module == 'mpmsg' || $module == 'message')
				{
					if($value['MsgType']=='shortvideo')
					{
						$value['content']= '<br><video class="myvideo" src="'.getImgUrl($value['localfile']).'"  controls="" autobuffer="" style="width:200px;">';
					}
					else if($value['msgtype']=='video')
					{
						$value['content']= '<br><video class="myvideo" src="'.getImgUrl($value['picurl']).'"  controls="" autobuffer="" style="width:200px;">';
					}
					else if($value['MsgType']=='voice'||$value['msgtype']=='voice')
					{
						if($module=='mpmsg')
						{
							$value['content']= $value['Recognition'].'<button class="icon-audio playerBtn" style="margin:0 5px;cursor:pointer;" playerurl="'.$uploaddir.$value['localfile'].'"><i class="fa fa-music"></i></button><div style="width:100px;height:10px;" id="playerQT"></div>';
						}
						else
						{
							$value['content']= '<button class="icon-audio playerBtn" style="margin:0 5px;cursor:pointer;" playerurl="'.getImgUrl($value['picurl']).'"><i class="fa fa-music"></i></button><div style="width:100px;height:10px;" id="playerQT"></div>';
						}
					}
					else if($value['MsgType']=='image'||$value['msgtype']=='image' )
					{
						if(file_exists($site_engine_root.$uploaddir.$value['localfile'])||file_exists($site_engine_root.$uploaddir.$value['picurl']))
						{
							if($module=='mpmsg')
							{
								$photoinfo = @getimagesize($site_engine_root.$uploaddir.$value['localfile']);
								if($photoinfo[0]>$photoinfo[1])
								{
									if($photoinfo[0]>100)
									{
										$style = 'width:80px;';
									}
								}
								else
								{
									if($photoinfo[1]>100)
									{
										$style = 'height:80px;';
									}
								}
								$value['content']= '<img src="'.$uploaddir.$value['localfile'].'" style="'.$style.'">';
							}
							else
							{
								$photoinfo = @getimagesize($site_engine_root.$uploaddir.$value['picurl']);
								if($photoinfo[0]>$photoinfo[1])
								{
									if($photoinfo[0]>100)
									{
										$style = 'width:80px;';
									}
								}
								else
								{
									if($photoinfo[1]>100)
									{
										$style = 'height:80px;';
									}
								}
								$value['content']= '<img src="'.$uploaddir.$value['picurl'].'" style="'.$style.'">';
							}
						}
					}
					$value['avatar'] = avatar($value['uid']);
					// 如果没有用户的id但是有openid
					if($value['uid']==0 || empty($value['username']) && $module == 'weixin')
					{
						if($value['openid'])
						{
							$userinfo = $weObj->getUserInfo($value['openid']);
							if($userinfo['subscribe']==0)
							{

							}
							else
							{
								$uid = adduser($userinfo);
							}
							$sql = "UPDATE {$tablepre}mpmsg SET username ='$userinfo[nickname]',uid='$uid' WHERE id='$value[id]'";
							$db->query($sql);
							$value['linkman']= $userinfo['nickname'];
							$value['uid']= $uid;
						}
					}
					if(empty($value['linkman']) && $value['uid'])
					{
						$sql = "SELECT linkman FROM {$tablepre}members WHERE uid='$value[uid]'";
						$value['linkman'] = $db->fetchOneBySql($sql);
					}
					$value['content']=emojishow($value['content']);
					if(strlen(strip_tags($value[$v])) > 200 )
					{
						$value['content'] = '<img src="'.getImgUrl($value['avatar']).'" style="width:30px;height:30px;">&nbsp;<a href="javascript:;"  onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\')">'.$value['linkman'].'</a>:<span class="expand_con" style="display:block;width:100%;height:20px;line-height:20px;overflow:hidden;white-space:normal;max-width:100%;">'.nl2br($value['content']).'</span><span class="expand down" style="color: #3c8dbc;margin-top:3px;">展开</span>';
					}
					else
					{
						$value['content'] = '<img src="'.getImgUrl($value['avatar']).'" style="width:30px;height:30px;">&nbsp;<a href="javascript:;"  onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\')">'.$value['linkman'].'</a>:'.nl2br($value['content']);
					}

				}
				$value[$v] = threedays($value['dateline'],$value[$v],$value['appid'],1);
				$info .= '<td style="width:20%;WORD-WRAP:break-word;vertical-align:middle;white-space:normal;max-width:200px;" class="message">'.$value[$v].'</td>';
			}
			else if($v=='moderate')
			{
				$sql = "select value from {$tablepre}fieldslist where module='$trmodule' and fields='moderate' and skey='$value[$v]'";
				$listv = $db->fetchOneBySql($sql);
				if(!$listv)
				{

					$vvv = $trmodule.'sys';
					$vvv = $$vvv;
					if($vvv['censorlevel'])
					{
						if($value[$v] == '1')
						{
							$listv = '已发布';
						}
						else if($value[$v] == '2')
						{
							$listv = '终审中';
						}
						else if($vvv['censorlevel']==1 && $value[$v]==0)
						{
							$listv = '审批中';
						}
						else
						{
							$listv = ($vvv['censorlevel']+2-$value[$v]).'审中';
						}
						$listv = '<a href="javascript:;"  onclick="menuclick(\'admin.php?action=list&module=censorlogs&linkmodule='.$module.'&linkmoduleid='.$value['id'].'\')">'.$listv.'</a>';
					}
					else
					{
						if($value[$v] == '1')
						{
							$listv = '已发布';
						}
						else
						{
							$listv = '审批中';
						}
					}
				}
				$info .= '<td style="white-space:nowrap;text-align:center;">'.$listv.'</td>';
			}
			else if($v == 'flag' && $module=='orders')
			{
				$info .= '<td style="white-space:nowrap;text-align:center;">'.$orderflagarray[$value[$v]].'</td>';
			}
			else if($v == 'classid')
			{
				if($module!='channel' && $module!='operations')
				{
					$sql = "SELECT title FROM {$tablepre}{$module}class WHERE id='$value[$v]'";
					$_name = $db->fetchOneBySql($sql);
				}
				if(!$classid)
				{
					$info .= '<td style="white-space:nowrap;text-align:center;"><a href="javascript:;" onclick="menuclick(\''.$_SERVER['REQUEST_URI'].'&classid='.$value[$v].'\')">'.$_name.'</a></td>';
				}
				else
				{
					$info .= '<td style="white-space:nowrap;text-align:center;">'.$_name.'</td>';
				}
			}
			else if($v== 'parentid')
			{
				if($module == 'mpmenu')
				{
					if($value[$v]==0)
					{
						$value[$v]='顶级';
					}
					else
					{
						$sql = "SELECT title FROM {$tablepre}mpmenu WHERE id='$value[$v]'";
						$value[$v] = $db->fetchOneBySql($sql);
					}
					$info .= '<td style="white-space:nowrap;text-align:center;">'.$value[$v].'</td>';
				}
				else
				{
					if($value[$v]==0)
					{
						$value[$v]='顶级';
					}
					else
					{
						$sql = "SELECT title FROM {$tablepre}{$trmodule} WHERE id='$value[$v]'";
						$value[$v] = $db->fetchOneBySql($sql);
					}
					$info .= '<td style="white-space:nowrap;text-align:center;">'.$value[$v].'</td>';
				}
			}
			else if($v=='zhuchi' || $v=='jiabin')
			{
				$info .= '<td style="text-align:center;white-space:nowrap;">'.$value[$v].'</td>';
			}
			else if($v=='seconds')
			{
				$value[$v] = ($value[$v]==0)? '离开':$value[$v];
				$info .= '<td style="text-align:center;white-space:nowrap;">'.$value[$v].'</td>';
			}
			else if($v=='options')
			{
				$value[$v] = getOptionById($value[$v]);
				$info .= '<td style="text-align:center;white-space:nowrap;">'.$value[$v].'</td>';
			}
			else if($v=='fromid')
			{
				if($value[$v] == '0')
				{
					$value[$v] = '系统';
				}
				else
				{
					$sqlagency = "select title from {$tablepre}agency where id='$value[$v]'";
					$value[$v]=$db->fetchOneBySql($sqlagency);
				}
				$info .= '<td style="text-align:center;white-space:nowrap;">'.$value[$v].'</td>';
			}
			else if($v=='agencyid')
			{
				if($value[$v] == '0')
				{
					$value[$v] = '未使用';
				}
				else
				{
					$sqlagency = "select title from {$tablepre}agency where id='$value[$v]'";
					$value[$v]=$db->fetchOneBySql($sqlagency);
				}
				$info .= '<td style="text-align:center;white-space:nowrap;">'.$value[$v].'</td>';
			}
			else if($v== 'photo')
			{
				// 不是系统默认的图像处理
				if($action =='search' && ($search=='wx'||$search=='bd'||$search=='wb'))
				{
					$info .= '<td style="white-space:nowrap;text-align:center;">"'.$value[$v].'"</td>';
				}
				else if($module=='operator')
				{
					$info .= '<td style="white-space:nowrap;text-align:center;">';
					$photoarr=explode(',',$value[$v]);
					if(count($photoarr)>=1 && !empty($value[$v]))
					{
						foreach($photoarr as $p_v)
						{
							$info .= '<img src="'.getImgUrl($p_v).'" style="width:30px;height:30px;">';
						}
					}
					else
					{
						$photoarr[0] = 'nopic.jpg';
						$info .= '<img src="'.$uploaddir.$photoarr[0].'" style="width:30px;height:30px;">';
					}
					$info .= '</td>';
				}
				else if($module == 'qrcode')
				{
					if($module=='supplierproduct')
					{
						$value['module'] = 'qrpay';
						$value['moduleid'] = '11'.$value['id'];
					}
					else if($module == 'qrpay')
					{
						$value['module']='qrpay';
						$value['moduleid'] = '10'.$value['id'];
					}
					else
					{
						$value['module']='qrcode';
						$value['moduleid'] = $value['id'];
					}
					$qrcodefile = 'qrcode/'.$value['module'].'_'.$value['moduleid'].'.png';
					if(file_exists($site_engine_root.$uploaddir.$qrcodefile))
					{
						$qrcodefile = $uploaddir.$qrcodefile;
					}
					else
					{
						$qrcodefile = '';
					}

					if($qrcodefile)
					{
						$qrcode = '<div><img  id="'.$value['module'].'" src="'.$qrcodefile.'" style="width:100px;"></div>';
						$qrcode.= '<a href="javascript:;" onclick="buildqrcode(this,\''.$value['module'].'\',\''.$value['moduleid'].'\',\''.$value['id'].'\')">重新生成</a>';
					}
					else
					{
						$qrcode = '<a href="javascript:;" onclick="buildqrcode(this,\''.$value['module'].'\',\''.$value['moduleid'].'\',\''.$value['id'].'\')">生成</a>';
					}
					$info .= '<td style="white-space:nowrap;text-align:center;">'.$qrcode.'</td>';
				}
				else
				{
					$photoarr=explode(',',$value[$v]);
					if($photoarr[0]=='')
					{
						$photoarr[0] = 'nopic.jpg';
					}
					$info .= '<td style="white-space:nowrap;text-align:center;"><img src="'.getImgUrl($photoarr[0]).'" style="width:30px;height:30px;"></td>';
				}
			}
			else if($v=='replyer') // 需要取得用户数据
			{
				if($value[$v])
				{
					$sql = "SELECT uid,linkman FROM {$tablepre}members WHERE uid='$value[$v]'";
					$_user = $db->fetchSingleAssocBySql($sql);
					$info .= '<td style="text-align:center;white-space:nowrap;">'.$_user['linkman'].'</td>';
				}
				else
				{
					$info .=  '<td style="text-align:center;white-space:nowrap;"></td>';
				}
			}
			else if($v=='reply')
			{
				$value[$v]=emojishow($value[$v]);
				if(strlen(strip_tags($value[$v])) > 200 )
				{
					$info .= '<td style="width:20%;WORD-WRAP:break-word;vertical-align:middle;min-height:40px;" class="message" ><span class="expand_con" style="display:block;width:100%;height:20px;line-height:20px;overflow:hidden;white-space:normal;max-width:100%;">'.nl2br($value[$v]).'</span><span class="expand down" style="color: #3c8dbc;margin-top:3px;">展开</span></td>';
				}
				else
				{
					$info .= '<td style="width:20%;WORD-WRAP:break-word;vertical-align:middle;" class="message" >'.nl2br($value[$v]).'</td>';
				}
			}
			else if($v=='action')
			{
				if($action =='search' && ($search=='wx'))
				{
					$info .= '<td style="white-space:nowrap;text-align:center;"><input type="checkbox" name="cjid" class=cjclass value="'.$value['id'].'">&nbsp;'.$value[$v].'<input type="hidden" class="form-control c_input" value="'.base64_encode($value['url']).'"></td>';
				}
				else if( $action == 'search' && ($search=='bd'||$search=='wb'))
				{
					// $info .= '<td style="text-align:center;">'.$adminstring.'</td>';
				}
				else
				{
					$info .= '<td style="text-align:center;white-space:nowrap;">'.$adminstring.'</td>';
				}
			}
			else if($v=='supplierid')
			{
				$sql = "SELECT title FROM {$tablepre}agency WHERE id='$value[$v]'";
				$supplier = $db->fetchOneBySql($sql);
				$info .= '<td style="text-align:center;white-space:nowrap;">'.$supplier.'</td>';
			}
			else if(in_array($v,$timearray)) // 时间序列
			{
				if($value[$v]==0)
				{
					$value[$v]='无';
				}
				else
				{
					$value[$v] = date("y-m-d H:i",$value[$v]);
				}
				$info .= '<td style="text-align:center;white-space:nowrap;">'.$value[$v].'</td>';
			}
			else if($v=='title') // 标题
			{
				$value[$v] = csubstr($value[$v],0,40);
				$value[$v] = threedays($value['dateline'],$value[$v],$value['appid'],1);
				if($module=='share' || ($action=='tracks' && (in_array($value['module'],$system_key) || $module=='channel') && $value['moduleid']))
				{
					$info .= '<td style="width:40%;WORD-WRAP:break-word;"><a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$value['module'].'&id='.$value['moduleid'].'\')">'.$value['id'].'.&nbsp;&nbsp;'.$value[$v].'</a></td>';
				}
				else if($module=='qrcode')
				{
					if(empty($value[$v]))
					{
						if($value['module'] == 'weixin'||$value['module'] == 'site')
						{
							$value[$v]=$title = $weixin_name;
						}
						else if($value['module']=='agencyproduct')
						{
							$value[$v] = $title = '微店商品';
						}
						else
						{
							if(in_array('title',$chars))
							{
								$sql = "SELECT title FROM {$tablepre}$value[module] WHERE id='$value[moduleid]'";
								$value[$v] = $title = $db->fetchOneBySql($sql);
							}
						}
						$sql = "UPDATE {$tablepre}qrcode SET title='$title' WHERE id='$value[id]'";
						$db->query($sql);
					}
					$info .= '<td style="white-space:nowrap;text-align:left;">'.$value['id'].'.&nbsp;&nbsp;'.showitemadmin($module,$value[$v],$v).'</td>';
				}
				else if($module=='operations')
				{
					if($action=='tracks')
					{
						$info .= '<td>'.$value['id'].'.&nbsp;&nbsp;'.$value[$v].'</td>';
					}
					else
					{
						if($value['devices'])
						{
							$sql = "SELECT linkman FROM {$tablepre}members WHERE uid='$value[devices]'";
							$kflinkman = $db->fetchOneBySql($sql);
						}
						if($kflinkman)
						{
							$info .= '<td>'.$value['id'].'.&nbsp;&nbsp;<a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$action.'&id='.$value['id'].'\')">'.$value[$v].'('.$kflinkman.')</a></td>';
						}
						else
						{
							$info .= '<td>'.$value['id'].'.&nbsp;&nbsp;<a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$action.'&id='.$value['id'].'\')">'.$value[$v].'</a></td>';
						}
					}
				}
				else if($module == 'codemaker')
				{
					$info .= '<td>'.$value[$v].'</td>';
				}
				else if($module=='groups')
				{
					if($value['id']<1000)
					{
						$info .= '<td>'.$value['id'].'.&nbsp;&nbsp;<a href="javascript:;" onclick="menuclick(\'admin.php?action=permission&module='.$module.'&id='.$value['id'].'\')">'.$value[$v].'</a></td>';
					}
					else
					{
						$info .= '<td>'.$value['id'].'.&nbsp;&nbsp;'.$value[$v].'</td>';
					}
				}
				else
				{
					if($action=='category')
					{
						$info .= '<td>'.$value['id'].'.&nbsp;&nbsp;<a href="javascript:;" onclick="menuclick(\'admin.php?action=category&module='.$module.'&parentid='.$value['id'].'\')">'.$value[$v].'</a></td>';
					}
					else if($module=='weixin')
					{
						if($action == 'pushit'  || $action =='sorts')
						{
							$info .= '<td style="width:40%;WORD-WRAP:break-word;">'.$value['id'].'.&nbsp;&nbsp;<a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$value['module'].'&id='.$value['moduleid'].'\')">'.$value[$v].'</a></td>';

						}

						else
						{
							$info .= '<td>'.$value['id'].'.&nbsp;&nbsp;<a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$action.'&id='.$value['id'].'\')">'.$value[$v].'</a></td>';
						}
					}
					else if($module == 'pages')
					{
						$info .= '<td>'.$value['id'].'.&nbsp;&nbsp;<a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$module.'&id='.$value['id'].'\')">'.$value[$v].'('.$value['description'].')</a></td>';
					}
					else if($action=='search' || $module =='recyclebin')
					{
						 if($action=='search' && ($search == 'wx' || $search=='wb' || $search =='bd'))
						{
							$info .= '<td><a href="'.$value['url'].'" target="_blank">'.$value['title'].'</a></td>';
						}
						else
						{
							if($value['module']=='members')
							{
								$info .= '<td style="width:40%;WORD-WRAP:break-word;">'.$value['id'].'.&nbsp;&nbsp;<a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$value['module'].'&uid='.$value['id'].'\')"><img src="'.getImgUrl(avatar($value['id'])).'" style="width:25px;height:25px;">&nbsp;'.$value[$v].'</a></td>';
							}
							else
							{
								$info .= '<td style="width:40%;WORD-WRAP:break-word;">'.$value['id'].'.&nbsp;&nbsp;<a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$value['module'].'&id='.$value['id'].'\')">'.$value[$v].'</a></td>';
							}
						}
					}

					else if($module == 'department')
					{
						$info .= '<td>'.$value['id'].'.&nbsp;&nbsp;<a href="javascript:;" onclick="menuclick(\'admin.php?action=members&module=department&id='.$value['id'].'\')">'.$value[$v].'</a></td>';
					}
					else if($action == 'meetinginfo')
					{
						$info .= '<td >'.$value['id'].'.&nbsp;&nbsp;'.$value[$v].'</td>';
					}
					else
					{
					/*	if($value['module'] && $value['moduleid'])
						{
							$sql = " SHOW TABLES LIKE '{$tablepre}{$value['module']}'";
							$tableexists = $db->fetchOneBySql($sql);
							if($tableexists)
							{
								if($value['module']=='members')
								{
									$sql = "SELECT linkman as title FROM {$tablepre}$value[module] WHERE uid='$value[moduleid]'";
									$value[$v] = $title = $db->fetchOneBySql($sql);
								}
								else
								{
									$vv = $value['module'].'sys';
									$vv = $$vv;
									$_chars = explode(",",$vv['char_setting']);
									if(in_array('title',$_chars))
									{
										$sql = "SELECT title FROM {$tablepre}$value[module] WHERE id='$value[moduleid]'";
										$value[$v] = $title = $db->fetchOneBySql($sql);
									}
								}
							}
						}
						*/
						$info .= '<td >'.$value['id'].'.&nbsp;&nbsp;<a href="javascript:;"  onclick="menuclick(\'admin.php?action=view&module='.$trmodule.'&id='.$value['id'].'\')">'.$value[$v].'</a></td>';
					}
				}
			}
			else if($v=='shares' || $v=='visitor' || $v =='subscribes') // 分享
			{
				if($isclass ==1)
				{
					if($module=='channel')
					{
						$sql = "SELECT sum(".$v.") FROM {$tablepre}$module WHERE id='$value[id]'";
					}
					else
					{
						$sql = "SELECT sum(".$v.") FROM {$tablepre}$_module WHERE classid='$value[id]'";
					}
					$number = $db->fetchOneBySql($sql);
					$info .= '<td style="white-space:nowrap;text-align:center;">'.$number.'</td>';
				}
				else
				{
					if($value['uid'])
					{
						if($v == 'shares')
						{
							$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=share&module=operations&uid='.$value['uid'].'\')">&nbsp;'.$value[$v].'</a></td>';
						}
						else if($v== 'visitor')
						{
							$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=shareview&module=shareview&uid='.$value['uid'].'\')">&nbsp;'.$value[$v].'</a></td>';
						}
						else if($v == 'subscribes')
						{
							$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action='.$v.'&module=members&uid='.$value['uid'].'\')">&nbsp;'.$value[$v].'</a></td>';
						}
					}
					else if($value['moduleid'])
					{
						if($v == 'shares')
						{
							$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=share&module='.$value['module'].'&id='.$value['moduleid'].'\')">&nbsp;'.$value[$v].'</a></td>';
						}
						else if($v== 'visitor')
						{
							$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=shareview&module='.$value['module'].'&id='.$value['moduleid'].'&uid='.$value['uid'].'\')">&nbsp;'.$value[$v].'</a></td>';
						}
						else if($v == 'subscribes')
						{
							$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action='.$v.'&module='.$value['module'].'&id='.$value['moduleid'].'\')">&nbsp;'.$value[$v].'</a></td>';

						}
					}
					else
					{
						if($v == 'shares')
						{
							$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=share&module='.$module.'&id='.$value['id'].'\')">&nbsp;'.$value[$v].'</a></td>';
						}
						else if($v== 'visitor')
						{
							$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=shareview&module='.$module.'&id='.$value['id'].'\')">&nbsp;'.$value[$v].'</a></td>';
						}
						else if($v == 'subscribes')
						{
							$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action='.$v.'&module='.$module.'&id='.$value['id'].'\')">&nbsp;'.$value[$v].'</a></td>';
						}
					}
				}
			}
			else if($v == 'comments')
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><a href="javascript:;" onclick="menuclick(\'admin.php?action=comments&module=tools&type='.$module.'&id='.$value['id'].'\')">&nbsp;'.$value[$v].'</a></td>';
			}
			else if($v == 'orderno')
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=orders&id='.$value['id'].'\')">&nbsp;'.$value[$v].'</a></td></td>';
			}
			else if($v == 'shareuid')
			{
				$sql = "SELECT linkman FROM {$tablepre}members WHERE uid='$value[shareuid]'";
				$linkman = $db->fetchOneBySql($sql);
				$info .= '<td style="white-space:nowrap;text-align:center;">'.$linkman.'</td>';
			}
			else if($v == 'qrcode') //网站、微信平台二维码
			{
				if($module=='supplierproduct')
				{
					$value['module'] = 'qrpay';
					$value['moduleid'] = '11'.$value['id'];
				}
				else
				{
					$value['module']='qrcode';
					$value['moduleid'] = $value['id'];
				}
				if(file_exists($site_engine_root.$uploaddir.$qrcodefile))
				{
					$qrcodefile = $uploaddir.$qrcodefile;
				}
				else
				{
					$qrcodefile = '';
				}
				if($qrcodefile && file_exists($qrcodefile) && file_get_contents($qrcodefile))
				{
					$qrcode = '<div><img  id="'.$value['module'].'" src="'.$qrcodefile.'" style="width:100px;"></div>';
					$qrcode.= '<a href="javascript:;" onclick="buildqrcode(this,\''.$value['module'].'\',\''.$value['moduleid'].'\',\''.$value['id'].'\')">重新生成</a>';
				}
				else
				{
					$qrcode = '<a href="javascript:;" onclick="buildqrcode(this,\''.$value['module'].'\',\''.$value['moduleid'].'\',\''.$value['id'].'\')">生成</a>';
				}
				$info .= '<td style="white-space:nowrap;text-align:center;">'.$qrcode.'</td>';
			}
			else if($v=='module' && $action=='qrcode') // 公众群等二维码
			{
				if($value['module']=='meeting')
				{
					$sql = "SELECT title FROM {$tablepre}meeting WHERE id='$value[moduleid]'";
					$name = $db->fetchOneBySql($sql);
				}
				else
				{
					$name = $value[$v];
				}
				$info .= '<td style="white-space:nowrap;text-align:center;">'.$name.'</td>';
			}
			else if($v=='usergroup' || $v == 'user') // 组和用户
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=list&module=usergroup&groupid='.$value['id'].'\')"   >'.$l['usergroup'].'</a></td>';
			}
			else if($v=='moduleid')
			{
				$moduleid = $value['moduleid'];
				if($moduleid)
				{
					if($value['module']=='signin' || $value['module']=='lottery' || $value['module']=='shake')
					{
						$sql = "SELECT title FROM {$tablepre}operator WHERE id='".$value[$v]."'"; //@@
					}
					else if($value['module']=='order')
					{
						if($value[$v])
						{
							$sql = "select orderno from {$tablepre}orders where id='".$value[$v]."'";
							$value[$v] = $db->fetchOneBySql($sql);
						}
					}
					else if($module!='logs' && $module!='tracks')
					{
						if($value['module']=='members')
						{
							$sql = "SELECT linkman as title FROM {$tablepre}{$value['module']} WHERE uid='$value[$v]'";
							$value[$v]= $db->fetchOneBySql($sql);
						}
						else if(!empty($value['module']) && $module!='hongbao')
						{
							$sql = " SHOW TABLES LIKE '{$tablepre}{$value['module']}'";
							$tableexists = $db->fetchOneBySql($sql);
							if($tableexists)
							{
								if($value['module']=='members')
								{
									$sql = "SELECT linkman as title FROM {$tablepre}$value[module] WHERE uid='$value[moduleid]'";
									$value[$v] = $title = $db->fetchOneBySql($sql);
								}
								else if($value['module']=='supplierproduct')
								{
									$sql = "select productname as title from {$tablepre}supplierproduct where id='$value[moduleid]'";
									$value[$v] = $title = $db->fetchOneBySql($sql);
								}
								else
								{
									$sql = "SELECT title FROM {$tablepre}$value[module] WHERE id='$value[moduleid]'";
									$value[$v] = $title = $db->fetchOneBySql($sql);
								}
							}
						}
					}
					$linkmodule = $value['module']=='order'? 'orders':$value['module'];
					$info .= '<td style="text-align:center;"><a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$linkmodule.'&id='.$moduleid.'\')">'.$value[$v].'</td>';
				}
				else
				{
					$info .= '<td style="text-align:center;">&nbsp;</td>';
				}
			}
			else if($v == 'number')
			{
				$info .= '<td style="text-align:center;white-space:nowrap;">'.$value[$v].'</td>';
			}
			else if($v=='stat') // 统计链接
			{
				if($action == 'push')
				{
					$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$action.'&id='.$value['id'].'\')">&nbsp;'.$l[$v].'</a></td>';
				}
				else
				{
					$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$module.'&id='.$value['id'].'\')">&nbsp;'.$l[$v].'</a></td>';
				}
			}
			else if($v=='upgrade') // 晋级条件
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=upgrade&module='.$module.'&groupid='.$value['id'].'\')">&nbsp;'.$l[$v].'</a></td>';
			}
			else if($v=='import') // 导入链接
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclicks="menuclick(\'admin.php?action=import&module='.$module.'&id='.$value['id'].'\' )" class="import">&nbsp;'.$l[$v].'</a></td>';
			}
			else if($v=='menus') // 多帐号菜单
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=mpmenu&module='.$module.'&appid='.$value['id'].'\')">&nbsp;'.$l[$v].'</a></td>';
			}
			else if($v=='followmenu') // 多帐号关注菜单
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=follow&module='.$module.'&appid='.$value['id'].'\')">&nbsp;'.$l[$v].'</a></td>';
			}
			else if($v=='pushmessage') // 多帐号关注菜单
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=push&module=weixin&appid='.$value['id'].'\')">&nbsp;'.$l[$v].'</a></td>';
			}
			else if($v=='meetinginfo') // 多帐号关注菜单
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=meetinginfo&module='.$module.'&appid='.$value['id'].'\')">&nbsp;'.$l[$v].'</a></td>';
			}
			else if($v=='listing') // 统计链接
			{
				$sql = "SELECT count(id) FROM {$tablepre}{$_module} WHERE classid='$value[id]' AND moderate=1";
				$countnumber = $db->fetchOneBySql($sql);
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=list&module='.$_module.'&classid='.$value['id'].'\')">&nbsp;共'.$countnumber.'条</a></td>';
			}
			else if($v == 'messages')
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=message&module=weixin&uid='.$value['uid'].'\')">&nbsp;'.$value[$v].'</a></td>';
			}
			else if($v=='myusers') //参与用户
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=myusers&module='.$module.'&id='.$value['id'].'\')">&nbsp;'.$l['myusers'].'</a></td>';
			}
			else if($v=='infos') //相关信息
			{
//				if($domain=='qiyeplus.qiyeplus.com')
//				{
//					$after = " <a href='../mobile/ajax/opentbs.php?module=operator&id=".$value['id']."'>".$l['generatereport']."</a></td>";
//				}
//				else
//				{
					$after = '</td>';
				//}
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?module=infos&action=list&id='.$value['id'].'\')">&nbsp;'.$l['infos'].'</a>'.$after;
			}
			else if($v=='generatereport') //生成报告
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="../mobile/ajax/opentbs.php?module=operator&id='.$value['id'].'">&nbsp;生成报告</a></td>';
			}
			else if($v=='coupon') //相关信息
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?module=couponcode&action=list&id='.$value['id'].'\')">&nbsp;兑换码</a></td>';
			}
                        else if($v=='couponrecord') //领取详情
                        {
                                $info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?module=couponrecord&action=list&couponid='.$value['id'].'\')">&nbsp;卡券领取详情</a></td>';
                        }
			else if($v=='child') //下级关键词
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action='.$action.'&module='.$module.'&parentid='.$value['id'].'\')">&nbsp;下级关键词</a></td>';
			}
			else if($v=='switch') //切换
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=switch&module=service&uid='.$value['uid'].'\')">&nbsp;切换</a></td>';
			}
			else if($v == 'record') //  群消息
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=list&module=message&meetingid='.$value['id'].'\')">&nbsp;'.$l['record'].'</a></td>';
			}
			else if($v=='servicenumber') // 服务次数
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=list&module=message&uid='.$value['uid'].'\')">'.$value[$v].'</a></td>';
			}
			else if($v == 'share')
			{
				if($value[$v]>0)
				{
					$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" onclick="menuclick(\'admin.php?action=shareview&module=news&shareid='.$value[$v].'\')">分享</a></td>';
				}
				else
				{
					$info .= '<td>&nbsp;</td>';
				}
			}
			else if($v == 'censor_push')
			{
				if($value['pushflag']==0 || $value['pushflag']==1 )
				{
					$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-'.$v.'"></i><a href="javascript:;" class="pushmessage">&nbsp;推送</a></td>';
				}
				else
				{
					$info .= '<td style="white-space:nowrap;text-align:center;">已推送</td>';
				}
			}
			else if($v == 'status')
			{
				if($module == 'weixin' && ($action == 'hongbao' || $action == 'qyhongbao'))
				{
					if($value[$v]==0)
					{
						$info .= '<td style="text-align:center;white-space:nowrap;"><a href="javascript:;" class="sendhongbao">未发送</a></td>';
					}
					else
					{
						$info .= '<td style="text-align:center;white-space:nowrap;">已发送</td>';
					}
				}
				else
				{
					// $module = $action;
					$info .= '<td style="text-align:center;white-space:nowrap;">'.showitemadmin($action,$value[$v],$v).'</td>';
				}
			}
			else if($v=='productid')
			{
				$sql = "select productname from {$tablepre}supplierproduct where id='".$value[$v]."'";
				$info .= '<td style="text-align:left;">'.$db->fetchOneBySql($sql).'</td>';
			}
			else if($multiselect[$action][$v])
			{
				$listarray = explode(",",$value[$v]);
				$liststrarr = array();
				foreach($listarray as $vv)
				{
					$liststrarr[] = $multiselect[$action][$v][$vv];
				}
				$info .= '<td style="text-align:center;">'.implode(",",$liststrarr).'</td>';
			}
			else if(in_array($v,$numberarray)) // 数组序列
			{
				$info .= '<td style="text-align:center;">'.$value[$v].'</td>';
			}
			else if($v == 'ctype')
			{
				$info .= '<td style="text-align:center;">'.$select[$action][$v][$value[$v]].'</td>';
			}
			else if($select[$module][$v])
			{
				$info .= '<td style="text-align:center;">'.$select[$module][$v][$value[$v]].'</td>';
			}
			else if($v == 'nid' && $module=='comments')
			{
				if($value['type'] && $value[$v])
				{
					$sql = "show tables like '".$tablepre.$value['type']."'";
					$ret = $db->fetchAssocArrBySql($sql);
					if($ret && $value['type']!='comments') {
						if ($value['type'] != 'members') {
							$sql = "select title from {$tablepre}" . $value['type'] . " where id='$value[$v]'";
							$title = $db->fetchOneBySql($sql);
						} else {
							$sql = "select linkman from {$tablepre}" . $value['type'] . " where uid='$value[$v]'";
							$title = $db->fetchOneBySql($sql);
						}
						if ($title) {
							$title = '<a href="javascript:;" onclick="menuclick(\'admin.php?module=' . $value['type'] . '&action=view&id=' . $value['nid'] . '\')">' . $title . '</a>';
							$info .= '<td style="text-align:left;">' . $value[$v] . '.' . $title . '</td>';
						} else
						{
							$info .= '<td style="text-align:left;">&nbsp;无标题</td>';
						}
					}
					else
					{
						$info .= '<td style="text-align:left;">&nbsp;无关联内容</td>';
					}
				}
				else
				{
					$info .= '<td style="text-align:left;">&nbsp;无关联内容</td>';
				}
			}
			else if($v=='module') // 默认的语言包中的文字
			{
				if($value[$v]=='order')
				{
					$lname = '订单';
				}
				else if($value[$v]=='register')
				{
					$lname = '关注';
				}
				else
				{
					$var = $value[$v].'sys';
					$var = $$var;
					$lname = $var['name'];
				}
				if(!$lname && $value[$v])
				{
					$lname = $l[$value[$v]];
				}
				$info .= '<td style="white-space:nowrap;text-align:center;" >'.$lname.'</td>';
			}
			else if($l[$value[$v]] && $module!='codemaker') // 默认的语言包中的文字
			{
			 	$info .= '<td style="white-space:nowrap;text-align:left;" >'.$l[$value[$v]].'</td>';
			}
			else if(strpos($v,"xiaji_level_")!==false)
			{
				$info .= '<td style="white-space:nowrap;text-align:center;"><i class="fa fa-xiaji"></i><a href="javascript:;" onclick="showxiaji('.$value['uid'].')">&nbsp;'.$value[$v].'</a></td>';
			}
			else // 其他情况
			{
				$info .= '<td style="text-align:left;">'.showitemadmin($module,$value[$v],$v,1).'</td>';//white-space:nowrap;{messages 要换行去掉了}
			}
		}
	}
	$info .= '</tr>';
	$adminstring = '';
	$canmessage = 0;

}