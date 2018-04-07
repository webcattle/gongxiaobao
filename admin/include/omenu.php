<?php
/******************************************************************************************************
**  企业Plus 7.0 - 企业Plus社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2015. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业Plus授权协议,查看或使用企业Plus的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: menu file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
if($module == 'admincp')
{
	$array = array();
	$data = $data1 = $data2 = '';
	if($action == 'omenu')
	{
		if(!empty($adminarray))
		{
			foreach($adminarray as $key => $value)
			{
				if($key=='operations')
				{
					break;
				}
				$permission = checkadmin($key,$value);
				if(!empty($permission))
				{
					if(!empty($value))
					{
		  				$data .='<li class="treeview"><a href="#"><i class="fa fa-'.$key.'"></i>'.$l[$key].'<i class="fa fa-angle-left pull-right"></i></a>'."\n";
		                        	$data .='<ul class="treeview-menu">'."\n";
						foreach($value as $k=> $v)
						{
							if(in_array($k,$permission) || in_array(1,$usergroup))
							{
								if($key=='dingyue')
								{
									$sql = "SELECT id FROM {$tablepre}account WHERE appid='$dingyue_appid'";
									$appid = $db->fetchOneBySql($sql);
		                                			$data .= "\t".'<li><a href="admin.php?module='.$key.'&action='.$k.'&appid='.$appid.'"><i class="fa fa-'.$k.'"></i> '.$v.'</a></li>'."\n";
		                                		}
		                                		else
		                                		{
		                                			$data .= "\t".'<li><a href="admin.php?module='.$key.'&action='.$k.'"><i class="fa fa-'.$k.'"></i> '.$v.'</a></li>'."\n";
		                                		}
		                                	}
		                                }
		                                $appid =0;
		                                $data .="</ul>\n</li>"."\n\n";
		                        }
		                }
			}
		}
//		foreach($system_preview as $key => $value)
//		{
//			if ($key == 'website' || $key=='template' || $key=='category'|| $key=='product' || $key == 'channel' ||$key == 'knowledge'||$key=='recommend'||$key=='shopstore')
//			{
//				continue;
//			}
//			$permission = checkadmin($key,$v);
//			if(!empty($permission))
//			{
//				if($key=='product')
//				{
//					$systempreview[] = 'orders';
//				}
//				$systempreview = explode(',',$value);
//				$systempreview[] = 'setting';
//				$systempreview[] = 'stat';
//				$systempreview = array_delete_value($systempreview,'delete');
//				$systempreview = array_delete_value($systempreview,'view');
//				if(!empty($systempreview))
//				{
//					$data .='<li class="treeview"><a href="#"><i class="fa fa-'.$key.'"></i>'.$l[$key].'<i class="fa fa-angle-left pull-right"></i></a>'."\n";
//		                	$data .='<ul class="treeview-menu">'."\n";
//					foreach($systempreview as $k=> $v)
//					{
//						if($v=='stat' || in_array($v,$permission))
//						{
//	                        			$data .= "\t".'<li><a href="admin.php?module='.$key.'&action='.$v.'"><i class="fa fa-'.$v.'"></i> '.$l[$v].'</a></li>'."\n";
//	                        		}
//	                        		else if(($v=='list' || $v=='add') && in_array('edit',$permission))
//	                        		{
//	                        			$data .= "\t".'<li><a href="admin.php?module='.$key.'&action='.$v.'"><i class="fa fa-'.$v.'"></i> '.$l[$v].'</a></li>'."\n";
//	                        		}
//		                        }
//		                        $data .="</ul>\n</li>"."\n\n";
//		                }
//		        }
//		}
//		if($data)
//		{
//			$data = '<li class="p_menu" style="text-align:left;" style="bgcolor:#52B72A;"><a href="javascript:;"><i class="fa fa-th-large"></i>运营管理&nbsp;<i class="fa fa-th-large"></i><i class="fa fa-angle-left pull-right"></i></a></li>'.$data;
//		}
		if(!empty($adminarray))
		{
			foreach($adminarray as $key => $value)
			{
				if($key!='operations' && $key!='meeting')
				{
					continue;
				}
				$permission = checkadmin($key,$value);
				if(!empty($permission))
				{
					if(!empty($value))
					{
		  				$data1 .='<li class="treeview" style="display:none"><a href="#"><i class="fa fa-'.$key.'"></i>'.$l[$key].'<i class="fa fa-angle-left pull-right"></i></a>'."\n";
		                        	$data1 .='<ul class="treeview-menu">'."\n";
						foreach($value as $k=> $v)
						{
							if(in_array($k,$permission) || in_array(1,$usergroup))
							{
		                                		$data1 .= "\t".'<li><a href="admin.php?module='.$key.'&action='.$k.'"><i class="fa fa-'.$k.'"></i> '.$v.'</a></li>'."\n";
		                                	}
		                                }
		                                $data1 .="</ul>\n</li>"."\n\n";
		                        }
		                }
			}
		}
		if($data1)
		{
			$data = $data.'<li class="p_menu hide"  style="text-align:left;"><a href="javascript:;"><i class="fa fa-th-large"></i>营销管理&nbsp;<i class="fa fa-th-large"></i><i class="fa fa-angle-left pull-right"></i></a></li>'.$data1;
		}
		if(!empty($adminarray))
		{
			foreach($adminarray as $key => $value)
			{
				if($key!='marketing' && $key!='kfaccount' && $key!='knowledge')
				{
					continue;
				}
				$permission = checkadmin($key,$value);
				if(!empty($permission))
				{
					if(!empty($value))
					{
		  				$data2 .='<li class="treeview" style="display:none"><a href="#"><i class="fa fa-'.$key.'"></i>'.$l[$key].'<i class="fa fa-angle-left pull-right"></i></a>'."\n";
		                        	$data2 .='<ul class="treeview-menu">'."\n";
						foreach($value as $k=> $v)
						{
							if(in_array($k,$permission) || in_array(1,$usergroup))
							{
		                                		$data2 .= "\t".'<li><a href="admin.php?module='.$key.'&action='.$k.'"><i class="fa fa-'.$k.'"></i> '.$v.'</a></li>'."\n";
		                                	}
		                                }
		                                $data2 .="</ul>\n</li>"."\n\n";
		                        }
		                }
			}
		}
		if($data2)
		{
			$data = $data.'<li class="p_menu hide"  style="text-align:left;"><a href="javascript:;"><i class="fa fa-th-large"></i>服务管理&nbsp;<i class="fa fa-th-large"></i><i class="fa fa-angle-left pull-right"></i></a></li>'.$data2;
		}
		$data3 = '';
		if(!empty($adminarray))
		{
			foreach($adminarray as $key => $value)
			{
				if($key!='tools')
				{
					continue;
				}
				// $permission = checkadmin($key,$value);
				// if(!empty($permission))
				// {
					if(!empty($value))
					{
		  				$data3 .='<li class="treeview" style="display:none"><a href="#"><i class="fa fa-'.$key.'"></i>'.$l[$key].'<i class="fa fa-angle-left pull-right"></i></a>'."\n";
		                        	$data3 .='<ul class="treeview-menu">'."\n";
						foreach($value as $k=> $v)
						{
							if(in_array($k,$permission) || in_array(1,$usergroup))
							{
		                                		$data3 .= "\t".'<li><a href="admin.php?module='.$key.'&action='.$k.'"><i class="fa fa-'.$k.'"></i> '.$v.'</a></li>'."\n";
		                                	}
		                                }
		                                $data3.="</ul>\n</li>"."\n\n";
		                        }
		                // }
			}
		}
		$data = $data.$data3;
		$data4 = '';
		if(!empty($adminarray))
		{
			foreach($adminarray as $key => $value)
			{
				if($key!='product' && $key!='distribution' && $key!='recommend' && $key!='shopsetting')
				{
					continue;
				}
				if(!empty($value))
				{
					if(is_array($value))
                			{
	  					$data4 .='<li class="treeview" style="display:none"><a href="#"><i class="fa fa-'.$key.'"></i>'.$l[$key].'<i class="fa fa-angle-left pull-right"></i></a>'."\n";
	                			$data4 .='<ul class="treeview-menu">'."\n";

						foreach($value as $k => $v)
						{
							if(in_array($k,$permission) || in_array(1,$usergroup))
							{
                        					$data4 .= "\t".'<li><a href="admin.php?module='.$key.'&action='.$k.'"><i class="fa fa-'.$k.'"></i> '.$v.'</a></li>'."\n";
                        				}
		                 		}
		            		}
		            		else
			            	{
			            		$data4 .= "\t".'<li class="treeview"><a href="admin.php?module=shop&action='.$key.'"><i class="fa fa-'.$key.'"></i> '.$value.'</a></li>'."\n";
			            	}
	                		$data4 .="</ul>\n</li>"."\n\n";
	            		}
			}
		}
		if($data4)
		{
			$data = $data.'<li class="p_menu hide"  style="text-align:left;"><a href="javascript:;"><i class="fa fa-th-large"></i>微商城&nbsp;<i class="fa fa-th-large"></i><i class="fa fa-angle-left pull-right"></i></a></li>'.$data4;
		}
		if($domain == $maindomain && in_array(1,$usergroup))
		{
			$key = 'website';
			$systempreview = array_delete_value($systempreview,'add');
			if(!empty($systempreview))
			{
				$data5 .='<li class="treeview" style="display:none"><a href="#"><i class="fa fa-'.$key.'"></i>平台管理<i class="fa fa-angle-left pull-right"></i></a>'."\n";
	                	$data5 .='<ul class="treeview-menu">'."\n";
				foreach($systempreview as $k=> $v)
				{
					if($v=='stat' || in_array($v,$permission))
					{
                        			$data5 .= "\t".'<li><a href="admin.php?module='.$key.'&action='.$v.'"><i class="fa fa-'.$v.'"></i> '.$l[$v].'</a></li>'."\n";
                        		}
                        		else if(($v=='list' || $v=='add') && in_array('edit',$permission))
                        		{
                        			$data5 .= "\t".'<li><a href="admin.php?module='.$key.'&action='.$v.'"><i class="fa fa-'.$v.'"></i> '.$l[$v].'</a></li>'."\n";
                        		}
	                        }
	                        $data5 .="</ul>\n</li>"."\n\n";
	                }
		}
		if($data5)
		{
			$data = $data.'<li class="p_menu hide"  style="text-align:left;"><a href="javascript:;"><i class="fa fa-th-large"></i>平台管理&nbsp;<i class="fa fa-th-large"></i><i class="fa fa-angle-left pull-right"></i></a></li>'.$data5;
		}
		exitandreturn($data,$module,$action);
	}
}
else
{
	$sql="SELECT * from {$tablepre}mpmenu where module='menu' ORDER BY $orderby $ascdesc LIMIT $pagestart,$limit ";
	$data = $db->fetchAssocArrBySql($sql);
}
