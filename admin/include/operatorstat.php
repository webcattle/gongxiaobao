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
require_once $site_engine_root.'mobile/lib/admin.php';

$html_item = ' <tr><td class="table_cell">%s </td>
			        <td class="table_cell tr">%s </td>
			        <td class="table_cell tr">%s </td>
			</tr>';

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
function cmdmyuser($a,$b)
{
	if ($a['partcounts'] == $b['partcounts']) {
		return 0;
	}
	return ($a['partcounts'] < $b['partcounts']) ? 1 : -1;
}
$display = '';
if($action=='effect')
{
	$firstrole = 'myusers';
	
	$ana_array = array('参与人数'=>'myusers','分享人数'=>'shares','分享点击'=>'visitor','分享关注'=>'subscribes');
	$chart_type = array('myusers'=>'bar','shares'=>'bar','visitor'=>'bar','subscribes'=>'bar');
	$init_div = 1;
	$limit = 10;
	$tab_html = '';
	$operator_module = array('operator','games','polls','exam','helpbuy');
	foreach($ana_array as $key=>$value)
	{
		$alldata = array();
		$alldata['title'] = $key;
		$data = array();
		if ($value == 'myusers')
		{
			$i = 0;
			$tempdata = array();
			$count = 0;
			foreach ($operator_module as $k => $v)
			{
				if ($v == 'operator')
				{
					$countsql = "select count(id) as totalcount from {$tablepre}views where module='$v' and moduleid>0";
					$count += $db->fetchOneBySql($countsql);
					
					$sql = "select moduleid,count(id) as partcounts from {$tablepre}views where module='$v' and moduleid>0 group by moduleid order by partcounts desc limit $limit";
					$ret = $db->fetchAssocArrBySql($sql);
				}
				elseif($v=='helpbuy')
				{
					$countsql = "select count(id) as totalcount from {$tablepre}crowduser where module='$v' and moduleid>0";
					$count += $db->fetchOneBySql($countsql);
					
					$sql = "select moduleid,count(id) as partcounts from {$tablepre}crowduser where module='$v' and moduleid>0 group by moduleid order by partcounts desc limit $limit";
					$ret = $db->fetchAssocArrBySql($sql);
				}
				else
				{
					$countsql = "select count(distinct uid) as totalcount from {$tablepre}record where module='$v' and moduleid>0";
					$count += $db->fetchOneBySql($countsql);
					
					$sql = "select moduleid,count(distinct uid) as partcounts from {$tablepre}record where module='$v' and moduleid>0 group by moduleid order by partcounts desc limit $limit";
					$ret = $db->fetchAssocArrBySql($sql);
				}
				
				foreach ($ret as $kk => $vv)
				{
					$tempdata[$i] = $vv;
					$tempdata[$i]['module'] = $v;
					$i++;
				}
			}
			usort($tempdata,'cmdmyuser');
			$tempdata = array_splice($tempdata,0,$limit*2);
			$ii = 0;
			foreach($tempdata as $k=>$v)
			{
				if($ii>=$limit)
				{
					break;
				}
				$sql = "select title from {$tablepre}{$v[module]} where id='$v[moduleid]'";
				$title = $db->fetchOneBySql($sql);
				if($title)
				{
					$data[$ii]['item'] ="(".$l[$v['module']].")".$title;
					$data[$ii]['c'] = $v['partcounts'];
					$ii++;
				}
			}
		}
		else
		{
			$i = 0;
			$tempdata = array();
			$count = 0;
			
			foreach($operator_module as $k=>$v)
			{
				$countsql = "select sum($value) as totalcount from {$tablepre}{$v} where moderate=1";
				$count += $db->fetchOneBySql($countsql);
				
				$sql = "select id,$value as partcounts from {$tablepre}{$v} where moderate=1 order by partcounts desc limit $limit";
				$ret = $db->fetchAssocArrBySql($sql);
				foreach ($ret as $kk => $vv)
				{
					$tempdata[$i] = $vv;
					$tempdata[$i]['module'] = $v;
					$i++;
				}
			}
			usort($tempdata,'cmdmyuser');
			$tempdata = array_splice($tempdata,0,$limit*2);
			$ii = 0;
			foreach($tempdata as $k=>$v)
			{
				if ($ii >= $limit) {
					break;
				}
				$sql = "select title from {$tablepre}{$v[module]} where id='$v[id]'";
				$title = $db->fetchOneBySql($sql);
				if ($title)
				{
					$data[$ii]['item'] = "(" . $l[$v['module']] . ")" . $title;
					$data[$ii]['c'] = $v['partcounts'];
					$ii++;
				}
			}
		}
		$th1 = '活动名称';
		$th2 = '人数';
		$th3 = '占比';
		if($value=='myusers')
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
		foreach($data as $k=>$v)
		{
			if(in_array($chart_type[$value],array('line','bar')))
			{
				$alldata['xAxis'][] = $v['item'];
				$alldata['series']['数量'][] = $v['c'];
				$alldata['legend'][] = $v['item'];
			}
			else if($chart_type[$_title] == 'pie')
			{
				$alldata['series'][$v['item']] = array($v['c']);
			}
			$percent = $count>0 ? intval($v['c']*10000/$count)/100 :0;
			$listhtml .= sprintf($html_item,$v['item'],$v['c'],$percent.'%');
		}
		
		$data_html .= sprintf($datahtml,$value,$datastyle,$th1,$th2,$th3,$listhtml);
		$alldata['name'] = $key;
		$varname = $value.'div';
//		p(function_exists('chart_data'));
		if($alldata['series'])
		{
			echo $$varname = chart_data($alldata,$chart_type[$value],$init_div);
			++$init_div;
		}
		
	}
}
else if($action=='creditsstat')
{
	$firstrole = 'membercredits';
	$ana_array = array('金币排行'=>'membercredits','获取渠道'=>'getcredits','获取模块'=>'getmodules');
	$chart_type = array('membercredits'=>'bar','getcredits'=>'bar','getmodules'=>'bar');
	$init_div = 1;
	$limit = 10;
	$tab_html = '';
	$countsql = "select sum(credit) as totalcount from {$tablepre}credits where credit>0";
	$count = $db->fetchOneBySql($countsql);
	foreach($ana_array as $key=>$value)
	{
		$alldata = array();
		$alldata['title'] = $key;
		$data = array();
		if ($value == 'membercredits')
		{
			$sql = "select username as item,sum(credit) as c from {$tablepre}credits where credit>0 group by username order by c desc limit $limit";
			$data = $db->fetchAssocArrBySql($sql);
			$th1 = '用户';
		}
		else if($value=='getcredits')
		{
			$sql = "select act as item,sum(credit) as c from {$tablepre}credits where credit>0 and act!='' group by act order by c desc limit $limit";
			$tempdata = $db->fetchAssocArrBySql($sql);
			$sql = "select sum(credit) from {$tablepre}credits where credit>0 and module='register'";
			$registers = $db->fetchOneBySql($sql);
			$i = 0;
			$haveadd=0;
			foreach($tempdata as $k=>$v)
			{
				if($v['c']>$registers)
				{
					$data[$i] = $v;
				}
				else
				{
					if(!$haveadd)
					{
						$data[$i]['item'] = 'register';
						$data[$i]['c'] = $registers;
						$haveadd = 1;
						$i++;
					}
					$data[$i] = $v;
				}
				$i++;
			}
			$th1 = '来源';
		}
		else
		{
			$sql= "select module as item,sum(credit) as c from {$tablepre}credits where credit>0 and module!='' and module!='register' and module!='0' group by module order by c desc limit $limit";
			$data = $db->fetchAssocArrBySql($sql);
			$th1 = '模块';
		}
		
		$th2 = '金币数';
		$th3 = '占比';
		if($value=='membercredits')
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
		foreach($data as $k=>$v)
		{
			if(in_array($chart_type[$value],array('line','bar')))
			{
				$alldata['xAxis'][] = $l[$v['item']]?$l[$v['item']]:$v['item'];
				$alldata['series']['数量'][] = $v['c'];
				$alldata['legend'][] = $l[$v['item']]?$l[$v['item']]:$v['item'];
			}
			else if($chart_type[$_title] == 'pie')
			{
				$alldata['series'][$v['item']] = array($v['c']);
			}
			$percent = $count>0 ? intval($v['c']*10000/$count)/100 :0;
			$listhtml .= sprintf($html_item,$l[$v['item']]?$l[$v['item']]:$v['item'],$v['c'],$percent.'%');
		}
		
		$data_html .= sprintf($datahtml,$value,$datastyle,$th1,$th2,$th3,$listhtml);
		$alldata['name'] = $key;
		$varname = $value.'div';
		if($alldata['series'])
		{
			echo $$varname = chart_data($alldata,$chart_type[$value],$init_div);
			++$init_div;
		}
	}
}
else if($action=='hongbaostat')
{
	$firstrole = 'memberhongbao';
	$ana_array = array('领取排行'=>'memberhongbao','获取模块'=>'getmodules');
	$chart_type = array('memberhongbao'=>'bar','getmodules'=>'bar');
	$init_div = 1;
	$limit = 10;
	$tab_html = '';
	$countsql = "select sum(money) as totalcount from {$tablepre}hongbao where money>0";
	$count = $db->fetchOneBySql($countsql);
	foreach($ana_array as $key=>$value)
	{
		$alldata = array();
		$alldata['title'] = $key;
		$data = array();
		if ($value == 'memberhongbao')
		{
			$sql = "select username as item,sum(money) as c from {$tablepre}hongbao where money>0 group by username order by c desc limit $limit";
			$data = $db->fetchAssocArrBySql($sql);
			$th1 = '用户';
		}
		else
		{
			$sql= "select module as item,sum(money) as c from {$tablepre}hongbao where money>0 and module!='' and module!='0' group by module order by c desc limit $limit";
			$data = $db->fetchAssocArrBySql($sql);
			$th1 = '模块';
		}
		
		$th2 = '红包金额';
		$th3 = '占比';
		if($value=='memberhongbao')
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
		foreach($data as $k=>$v)
		{
			if(in_array($chart_type[$value],array('line','bar')))
			{
				$alldata['xAxis'][] = $l[$v['item']]?$l[$v['item']]:$v['item'];
				$alldata['series']['金额'][] = $v['c']/100;
				$alldata['legend'][] = $l[$v['item']]?$l[$v['item']]:$v['item'];
			}
			else if($chart_type[$_title] == 'pie')
			{
				$alldata['series'][$v['item']] = array($v['c']);
			}
			$percent = $count>0 ? intval($v['c']*10000/$count)/100 :0;
			$listhtml .= sprintf($html_item,$l[$v['item']]?$l[$v['item']]:$v['item'],$v['c']/100,$percent.'%');
		}
		
		$data_html .= sprintf($datahtml,$value,$datastyle,$th1,$th2,$th3,$listhtml);
		$alldata['name'] = $key;
		$varname = $value.'div';
		if($alldata['series'])
		{
			echo $$varname = chart_data($alldata,$chart_type[$value],$init_div);
			++$init_div;
		}
	}
}
eval ("\$content .= \"" . $tpl->get("stat_useracts",$template). "\";");
echo $content;
exit;