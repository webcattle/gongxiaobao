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
	if ($a['views'] == $b['views']) {
		return 0;
	}
	return ($a['views'] < $b['views']) ? 1 : -1;
}
$display = 'none';
if($action)
{
	$_module = str_replace('viewstat','',$action);
	$ana_array = array('总体统计'=>'allinfo','访问排行'=>'paihang');
	$chart_type = array('allinfo'=>'line','paihang'=>'bar');
	$lastyearday = strtotime('-1 year',time());
	$starttime = date("Y",$lastyearday).date('m',$lastyearday).'01';
	
	foreach($ana_array as $key=>$value)
	{
		$alldata = array();
		$alldata['title'] = $key;
		$alldata['name'] = $key;
		
		if($value=='allinfo')
		{
			$sql = "select concat(date_y,date_m) as item,sum(c_d) as views from {$tablepre}count where module='$_module' and day>'$starttime' group by item order by day asc ";
		}
		else
		{
			$sql = "select moduleid as item,sum(c_d) as views from {$tablepre}count where module='$_module' and day>'$starttime' group by item order by views desc limit $limit";
			
		}
		$result = $db->fetchAssocArrBySql($sql);
		
		if($value=='allinfo')
		{
			$tab_html.='<a class="item first current" href="javascript:;" role="'.$value.'">'.$key.'</a>';
			$datastyle = '';
			$th1 = '日期';
			$newdata = $result;
			usort($newdata,'cmdmyuser');
			
		}
		else
		{
			$tab_html.='<a class="item" href="javascript:;" role="'.$value.'">'.$key.'</a>';
			$datastyle = 'display:none;';
			$th1 = '标题';
		}
		$th2 = '访问量';
		$th3 = '排行';
		
		$listhtml = '';
		foreach($result as $k=>$v)
		{
			if($value=='paihang')
			{
				$sql = "select title from {$tablepre}{$_module} where id='$v[item]'";
				$title = $db->fetchOneBySql($sql);
			}
			else
			{
				$title = $v['item'];
			}
			$alldata['xAxis'][] = $title;
			$alldata['series']['访问量'][] = $v['views'];
			$alldata['legend'][] = $title;
			if($value=='allinfo')
			{
				foreach($newdata as $kk=>$vv)
				{
					if($vv['item']==$v['item'])
					{
						$paihang =$kk+1;
						break;
					}
				}
			}
			else
			{
				$paihang = $k+1;
			}
			$listhtml .= sprintf($html_item,$title,$v['views'],$paihang);
		}
		$data_html .= sprintf($datahtml,$value,$datastyle,$th1,$th2,$th3,$listhtml);
		$varname = $value.'div';
		echo $$varname = chart_data($alldata,$chart_type[$value],$init_div);
		++$init_div;
	}
}
eval ("\$content .= \"" . $tpl->get("stat_useracts",$template). "\";");
echo $content;
exit;