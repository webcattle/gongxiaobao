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
require_once 'include/statfunction.php';
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
	$stattime = time()-86400*30;
}
else if($datestyle=='d')
{
	$date_y_show='H:i';
	$today =  strtotime($datenum.' day');
	$daynum=24;
	$stattime = time()-86400;
}
else if($datestyle=='y')
{
	$date_y_show='Y-m';
	$cha =  time()-mktime(0, 0, 0, date("m"), date("d"), date("Y"));
	$today =  strtotime($datenum.' Year')-$cha;
	$daynum=12;
	$stattime = time()-86400*365;
}

if($action == 'userincrease')
{
	$nowdate = time();
	$today = mktime(0,0,0,date("m",$nowdate),date("d",$nowdate),date("Y",$nowdate));
	
	$starttime = $today - 30*86400;
	$endtime = $today;
	if(isset($start) && isset($end))
	{
		$start = strtotime($start);
		$end = strtotime($end);
		if($start != $end && $start < $end)
		{
			$starttime = $start;
			$endtime = $end;
		}
	}
	$start = $starttime;
	$end = $endtime;
	$item = in_array($item,array('subscribe','unsubscribe','netsubscribe','totalsubscribe')) ? $item : 'subscribe';
	$items = array('subscribe'=>'新关注人数','unsubscribe'=>'取消关注人数','netsubscribe'=>'净增关注人数','totalsubscribe'=>'累积关注人数');
	//总关注
	if(empty($_COOKIE['totalmembers']))
	{
		require_once $site_engine_root."mobile/lib/wechat.php";
		require_once $site_engine_root."mobile/lib/error.php";
		require_once $site_engine_root."mobile/ajax/settings.php";
		require_once $site_engine_root."mobile/lib/function.php";
		$options = options(0);
		$weObj = new Wechat($options);
		$info = $weObj->getUserList();
		$totalsubscribe = $info['total'];
		if($totalsubscribe>0)
		{
			ssetcookie('totalmembers',$totalsubscribe,time()+300);
		}
	}
	else
	{
		$totalsubscribe = $_COOKIE['totalmembers'];
	}
	$subscribesql = "select count(uid) count from {$tablepre}members where subscribe=1 and dateline>:start and dateline<:end";
	$unsubscribesql = "SELECT count(*) FROM {$tablepre}members WHERE lastmessage>:start and lastmessage<:end AND subscribe='-1'";
	if(!$getajax)
	{
		$_time1 = $today -86400;
		$_time2 = $today;
		//昨日关注
		$yestodaysubscribe = $db->fetchOneBySql($subscribesql,array(":start"=>$_time1,":end"=>$_time2));
		//昨日取关
		$yestodayunsubscribe = $db->fetchOneBySql($unsubscribesql,array(":start"=>$_time1,":end"=>$_time2));
		//净关注
		$netsubscribe = $yestodaysubscribe - $yestodayunsubscribe;
	}
	
	
	$chart_cycle = $chart_cycle ? $chart_cycle : 'month';
	$chart_item = $chart_item ? $chart_item : 'subscribe';//subcribe,unsubcribe,netsubcribe,totalsubcribe
	
	$yestoday_date = date("Ymd",strtotime("-1 day"));
	$cycle_days = array('week'=>7,'month'=>30);
	if(0&&$rdb->exists($chart_item.'_month_'.$yestoday_date))
	{
		$subscribe_data = json_decode($rdb->get('subscribe_month_'.$yestoday_date),true);
		$unsubscribe_data = json_decode($rdb->get('unsubscribe_month_'.$yestoday_date),true);
		$netsubscribe_data = json_decode($rdb->get('netsubscribe_month_'.$yestoday_date),true);
	}
	else
	{
		$cycle = 'month';
		foreach(array('subscribe','unsubscribe') as $v)
		{
			$vstart = $start;
			while($vstart<$end)
			{
				$vend = $vstart + 86400;
				${$v.'_data'}[date("Y-m-d",$vstart)] = $db->fetchOneBySql(${$v.'sql'},array(":start"=>$vstart,":end"=>$vend));
				$vstart +=86400;
			}
		}
		$_totalsubscribe = $totalsubscribe;
		$_subscribe_data = array_reverse($subscribe_data);
		foreach($_subscribe_data as $k=>$v)
		{
			$_net = $netsubscribe_data[$k] = $v - $unsubscribe_data[$k];
			$totalsubscribe_data[$k] = $_totalsubscribe = $_totalsubscribe - $_net;
		}
		$redis_data = json_encode($netsubscribe_data,true);
		$rdb->set('netsubscribe_month_'.$yestoday_date,$redis_data,86400);
		
	}
	$data = array();
	$data['title'] = $title;
	$data['leged']  = array('语言');
	$i=0;
	$table_row = '<tr>
                <td class="table_cell">
                        :date
                </td>
                <td class="table_cell tr js_new_user">
                        :subscribe
                </td>
                <td class="table_cell tr js_cancel_user">
                        :unsubscribe
                </td>
                <td class="table_cell tr js_netgain_user">
                        :net
                </td>
                <td class="table_cell tr js_cumulate_user">
                        :total
                </td>
        </tr>';
	$page = array();
	$exportdata = array();
	
	foreach(${$chart_item.'_data'} as $k=>$v)
	{
		$data['xAxis'][] = $k;
		$data['series']['人数'][] = $v;
		
		if(!$getajax)
		{
			if($i<=29)
			{
				$html_temp = str_replace(':date',$k,$table_row);
				$html_temp = str_replace(':subscribe',$subscribe_data[$k],$html_temp);
				$html_temp = str_replace(':unsubscribe',$unsubscribe_data[$k],$html_temp);
				$html_temp = str_replace(':net',$netsubscribe_data[$k],$html_temp);
				$html_temp = str_replace(':total',$totalsubscribe_data[$k],$html_temp);
				$page[] = $html_temp;
				$exportdata[$i]['date'] = $k;
				$exportdata[$i]['subscribe'] =  $subscribe_data[$k];
				$exportdata[$i]['unsubscribe'] = $unsubscribe_data[$k];
				$exportdata[$i]['netsubscribe'] = $netsubscribe_data[$k];
				$exportdata[$i]['totalsubscribe'] = $totalsubscribe_data[$k];
				++$i;
				
				
			}
		}
	}
	$tempfile = $site_engine_root.$uploaddir.'tmp_'.$user['uid'].'.txt';
	if($exportdata)
	{
		$fp = fopen($tempfile,'w+');
		fputs($fp,'时间||新关注人数||取消关注人数||净增关注人数||累积关注人数'."\n");
		foreach($exportdata as $key=>$value)
		{
			$str = $prefix = '';
			foreach($value as $k=>$v)
			{
				$str .= $prefix.$v;
				$prefix = '||';
			}
			fputs($fp,$str."\n");
		}
		fclose($fp);
	}
	if($getajax)
	{
		echo json_encode(chart_data($data,'line',1,1));exit;
	}
	$chart_html = chart_data($data,'line',1);//$$varname =
	$page = array_reverse($page);
	foreach($page as $v)
	{
		$table_html .= $v;
	}
	
	eval ("\$content .= \"" . $tpl->get("stat_userincrease",$template). "\";");
	echo $content;
	exit;
}
else if($action == 'useranalysis')
{
	$ana_array = array('国家'=>'country','省份'=>'province','城市'=>'city','性别'=>'sex','语言'=>'language');
	$chart_type = array('country'=>'bar','province'=>'bar','city'=>'bar','sex'=>'pie','language'=>'bar',);
	$init_div = 1;
	$limit = 10;
	$html_item = ' <tr><td class="table_cell">%s </td>
			        <td class="table_cell tr">%s </td>
			        <td class="table_cell tr">%s </td></tr>';
	$sql = "select count(*) from {$tablepre}members";// where subscribe=1";
	$count = $db->fetchOneBySql($sql);
	foreach($ana_array as $title => $_title)
	{
		$table = $_title=='sex' ? 'members' : 'membersinfo';
		$limit = $_title=='province' ? 50 : $limit;
		$sql = "select $_title item,count(uid) c from {$tablepre}$table where  $_title!='' group by $_title order by c desc limit $limit";
		$$_title = $db->fetchAssocArrBySql($sql);
		$data = array();
		$data['title'] = $title;
		$data['leged']  = array('语言');
		foreach($$_title as $k=>$v)
		{
			if($_title == 'sex')
			{
				$v['item'] = $v['item']==1 ? '男' : '女';
			}
			else
			{
				if($v['item']!=0)
				{
					$v['item'] = '其他';
				}
			}
			if(in_array($chart_type[$_title],array('line','bar')))
			{
				$data['xAxis'][] = $v['item'];
				$data['series']['人数'][] = $v['c'];
			}
			else if($chart_type[$_title] == 'pie')
			{
				$data['series'][$v['item']] = array($v['c']);
			}
			
			
			${$_title}[$k]['percent'] = $percent = intval($v['c']*10000/$count)/100;
			${$_title.'_html'} .= sprintf($html_item,$v['item'],$v['c'],$percent.'%');
		}
		if($_title == 'sex')
		{
			$osexnum = $count - $sex[0]['c'] - $sex[1]['c'];
			$percent = 100 - $sex[0]['percent'] - $sex[1]['percent'];
			$sex_html .= sprintf($html_item,'未知',$osexnum,$percent.'%');
			$data['series']['未知'] = array($osexnum);
		}
		$data['name'] = $title;
		$varname = $_title.'div';
		echo $$varname = chart_data($data,$chart_type[$_title],$init_div);
		++$init_div;
	}
	//地图
	$title = '用户分布';
	foreach($province as $v)
	{
		$provincedata[] = array('name'=>$v['item'],'value'=>$v['c']);
	}
	$ret = json_encode($provincedata);
	eval ("\$chinausermap = \"" . $tpl->get("map",$template). "\";");
	
	eval ("\$content .= \"" . $tpl->get("stat_useranalysis",$template). "\";");
	echo $content;
	exit;
	
}
else if($action=='useracts')
{
	$ana_array = array('用户标签'=>'userdict','模块访问'=>'modules','模块分享'=>'shares','微信菜单访问'=>'mpmenu');
	$chart_type = array('userdict'=>'bar','modules'=>'bar','shares'=>'bar','mpmenu'=>'bar');
	$init_div = 1;
	$limit = 10;
	$firstrole = 'userdict';
	
	$html_item = ' <tr><td class="table_cell">%s </td>
			        <td class="table_cell tr">%s </td>
			        <td class="table_cell tr">%s </td>
			</tr>';
	
	$tab_html = '';
	$datahtml = '<div class="table_wrp" role="%s" style="%s">
                                <table class="table" cellspacing="0">
                                    <thead class="thead">
                                    <tr>
                                        <th class="table_cell tl">
						%s
                                        </th>
                                        <th class="table_cell rank_area tr rank">
						%s <span class="icon_rank"><i class="arrow arrow_up"></i><i class="arrow arrow_down"></i></span>
                                        </th>
                                        <th class="table_cell tr rank_area last_child no_extra">
						%s
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="tbody">%s</tbody>
                                </table>
                            </div>';
	
	
	foreach($ana_array as $key=>$value)
	{
		$data = array();
		$data['title'] = $key;
		if($value=='userdict')
		{
			$sqlcount = "select count(id) from {$tablepre}userdict where keyword!=''";
			$count = $db->fetchOneBySql($sqlcount);
			$th1 = '用户标签';
			$th2 = '命中次数';
			
			$sql = "select keyword as item,count(id) as c from {$tablepre}userdict where keyword!='' group by keyword order by c desc limit $limit";
		}
		else if($value=='modules')
		{
			$sqlcount = "select sum(number) from {$tablepre}views where module!=''";
			$count = $db->fetchOneBySql($sqlcount);
			$th1 = '模块';
			$th2 = '访问次数';
			$sql = "select module as item,sum(number) as c from {$tablepre}views where module!='' group by module order by c desc limit $limit";
		}
		else if($value=='shares')
		{
			$sqlcount = "select count(id) from {$tablepre}share where module!=''";
			$count = $db->fetchOneBySql($sqlcount);
			$th1 = '模块';
			$th2 = '分享次数';
			$sql = "select module as item,count(id) as c from {$tablepre}share where module!='' group by module order by c desc limit $limit";
		}
		else if($value=='mpmenu')
		{
			$sqlcount = "select count(id) from {$tablepre}mpmsg where mpmenuid>0";
			$count = $db->fetchOneBySql($sqlcount);
			$th1 = '菜单';
			$th2 = '访问次数';
			$sql = "select mpmenuid as item,count(id) as c from {$tablepre}mpmsg where mpmenuid>0 group by mpmenuid order by c desc limit $limit";
		}
		$th3 = '占比';
		$result = $db->fetchAssocArrBySql($sql);
		if($value=='userdict')
		{
			$tab_html.='<a class="item first current" href="javascript:;" role="'.$value.'">'.$key.'</a>';
			$datastyle = '';
		}
		else
		{
			$tab_html.='<a class="item" href="javascript:;" role="'.$value.'">'.$key.'</a>';
			$datastyle = 'display:none;';
		}
		$listhtml = '';
		foreach($result as $k=>$v)
		{
			if($value=='modules' || $value=='shares')
			{
				$_modulesys = $v['item'].'sys';
				
				$_modulesys = $$_modulesys;
				if($_modulesys)
				{
					$v['item'] = $_modulesys['name'];
				}
			}
			else if($value=='mpmenu')
			{
				$sql = "select title from {$tablepre}mpmenu where id='$v[item]'";
				$v['item'] = $db->fetchOneBySql($sql);
			}
			if(in_array($chart_type[$value],array('line','bar')))
			{
				$data['xAxis'][] = $v['item'];
				$data['series']['数量'][] = $v['c'];
				$data['legend'][] = $v['item'];
			}
			else if($chart_type[$_title] == 'pie')
			{
				$data['series'][$v['item']] = array($v['c']);
			}
			$percent = $count>0 ? intval($v['c']*10000/$count)/100 :0;
			$listhtml .= sprintf($html_item,$v['item'],$v['c'],$percent.'%');
		}
		
		$data_html .= sprintf($datahtml,$value,$datastyle,$th1,$th2,$th3,$listhtml);
		
		$data['name'] = $key;
		$varname = $value.'div';
		if($data['series'])
		{
			echo $$varname = chart_data($data,$chart_type[$value],$init_div);
			++$init_div;
		}
	}
	eval ("\$content .= \"" . $tpl->get("stat_useracts",$template). "\";");
	echo $content;
	exit;
}
else if($action=='uservalues')
{
	$ana_array = array('分享次数'=>'shares','分享带来访问'=>'visitor','分享带来关注'=>'subscribes','购买额'=>'orders');
	$chart_type = array('shares'=>'bar','visitor'=>'bar','subscribes'=>'bar','orders'=>'bar');
	$init_div = 1;
	$limit = 10;
	$firstrole = 'shares';
	
	$html_item = ' <tr><td class="table_cell">%s </td>
			        <td class="table_cell tr">%s </td>
			        <td class="table_cell tr">%s </td>
			</tr>';
	
	$tab_html = '';
	$datahtml = '<div class="table_wrp" role="%s" style="%s">
                                <table class="table" cellspacing="0">
                                    <thead class="thead">
                                    <tr>
                                        <th class="table_cell tl">
						%s
                                        </th>
                                        <th class="table_cell rank_area tr rank">
						%s <span class="icon_rank"><i class="arrow arrow_up"></i><i class="arrow arrow_down"></i></span>
                                        </th>
                                        <th class="table_cell tr rank_area last_child no_extra">
						%s
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody class="tbody">%s</tbody>
                                </table>
                            </div>';
	
	foreach($ana_array as $key=>$value)
	{
		$data = array();
		$data['title'] = $key;
		if($value=='orders')//todo
		{
			$sqlcount = "select sum(special) from {$tablepre}orders where flag>1 and backflag=0 and ordertype=1";
			$count = $db->fetchOneBySql($sqlcount);
			$th2 = '金额';
			$sql = "select username as item,sum(special) as c from {$tablepre}orders where flag>1 and backflag=0 and ordertype=1 group by username order by c desc limit $limit";
		}
		else
		{
			$th2 = '数量';
			$sqlcount = "select sum($value) from {$tablepre}members where subscribe=1";
			$count = $db->fetchOneBySql($sqlcount);
			$sql = "select username as item,$value as c from {$tablepre}members where subscribe=1 order by c desc limit $limit ";
		}
		
		$th1 = '昵称';
		$th3 = '占比';
		$result = $db->fetchAssocArrBySql($sql);
		
		if($value=='shares')
		{
			$tab_html.='<a class="item first current" href="javascript:;" role="'.$value.'">'.$key.'</a>';
			$datastyle = '';
		}
		else
		{
			$tab_html.='<a class="item" href="javascript:;" role="'.$value.'">'.$key.'</a>';
			$datastyle = 'display:none;';
		}
		$listhtml = '';
		foreach($result as $k=>$v)
		{
			if(in_array($chart_type[$value],array('line','bar')))
			{
				$data['xAxis'][] = $v['item'];
				$data['series']['数量'][] = $v['c'];
				$data['legend'][] = $v['item'];
			}
			else if($chart_type[$_title] == 'pie')
			{
				$data['series'][$v['item']] = array($v['c']);
			}
			${$value}[$k]['percent'] = $percent = intval($v['c']*10000/$count)/100;
			$listhtml .= sprintf($html_item,$v['item'],$v['c'],$percent.'%');
		}
		
		$data_html .= sprintf($datahtml,$value,$datastyle,$th1,$th2,$th3,$listhtml);
		
		$data['name'] = $key;
		$varname = $value.'div';
		if($data)
		{
			echo $$varname = chart_data($data,$chart_type[$value],$init_div);
			++$init_div;
		}
	}
	eval ("\$content .= \"" . $tpl->get("stat_useracts",$template). "\";");
	echo $content;
	exit;
}
?>