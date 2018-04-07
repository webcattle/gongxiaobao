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
$display = 'none';
if($action=='productstat')
{
	$firstrole = 'orders';
	$ana_array = array('成交订单数'=>'orders','分类销量统计'=>'productclass','商品销量统计'=>'product');
	$chart_type = array('orders'=>'line','productclass'=>'line','product'=>'line');
	$init_div = 1;
	$limit = 10;
	$tab_html = '';
	$lastyearday = strtotime('-1 year',time());
	$starttime = mktime(0,0,0,date('n',$lastyearday),1,date('Y',$lastyearday));
	foreach($ana_array as $key=>$value)
	{
		$alldata = array();
		$alldata['title'] = $key;
		$data = array();
		
		if($value=='orders')
		{
			$sql = "select from_unixtime(dateline,'%Y-%m') as item,sum(special) as salesmoney,count(id) as salescount from {$tablepre}orders where flag>1 and ordertype=1 and backflag=0 and dateline>'$starttime' and (module is null or module='' or module='product') group by item order by item asc";
                        $result = $db->fetchAssocArrBySql($sql);
			$th1 = '时间';
		}
		else if($value=='productclass')
		{
			$sql = "select sp.classid as item,sum(l.special*l.quantity) as salesmoney,sum(l.quantity) as salescount from ";
			$sql .= "{$tablepre}orderlist l left join {$tablepre}orders o on l.orderid=o.id left join {$tablepre}supplierproduct sp on l.pid=sp.id ";
			$sql .= " where l.type='product' and o.flag>1 and o.ordertype=1 and o.backflag=0 and sp.moderate!=-1 and o.dateline>'$starttime'";
			$sql .= " group by item order by item asc";
			$result = $db->fetchAssocArrBySql($sql);
			$th1 = '商品分类';
		}
		else if($value=='product')
		{
			$sql = "select sp.productid as item,sum(l.special*l.quantity) as salesmoney,sum(l.quantity) as salescount from ";
			$sql .= "{$tablepre}orderlist l left join {$tablepre}orders o on l.orderid=o.id left join {$tablepre}supplierproduct sp on l.pid=sp.id ";
			$sql .= " where l.type='product' and o.flag>1 and o.ordertype=1 and o.backflag=0 and sp.moderate!=-1 and o.dateline>'$starttime'";
			$sql .= " group by item order by item asc";
			$result = $db->fetchAssocArrBySql($sql);
			$th1 = '商品';
		}
		$th2 = '销售额';
		$th3 = '销售量';
		
		foreach($result as $k=>$v)
		{
			$data[$k] = $v;
			if($value=='productclass')
			{
				$_classid = $v['item'];
				$sql = "select title from {$tablepre}productclass where id='$_classid'";
				$data[$k]['item'] = $db->fetchOneBySql($sql);
			}
			else if($value=='product')
			{
				$_id = $v['item'];
				$sql = "select title from {$tablepre}product where id='$_id'";
				$data[$k]['item'] = $db->fetchOneBySql($sql);
			}
		}
		if($value=='orders')
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
			$alldata['xAxis'][] = $v['item'];
			$alldata['series']['销售额'][] = round($v['salesmoney'],2);
			$alldata['series']['销售量'][] = $v['salescount'];
			$alldata['legend'][] = $v['item'];
			$listhtml .= sprintf($html_item,$v['item'],round($v['salesmoney'],2),$v['salescount']);
		}
		
		$data_html .= sprintf($datahtml,$value,$datastyle,$th1,$th2,$th3,$listhtml);
		$alldata['name'] = $key;
		$varname = $value.'div';
		if($alldata['series'])
		{
			for($i=0;$i<2;$i++)
			{
				$_tempdata = $alldata;
				if($i==0)
				{
					unset($_tempdata['series']['销售量']);
					$_tempdata['title'] .='(按销售额统计)';
				}
				else
				{
					unset($_tempdata['series']['销售额']);
					$_tempdata['title'] .='(按销售量统计)';
				}
				$varname .=$i;
				echo $$varname = chart_data($_tempdata,$chart_type[$value],$init_div);
				++$init_div;
			}
			
		}
	}
}
else if($action=='agencystat')
{
	$firstrole = 'orders';
	$ana_array = array('销售量统计'=>'salescount','销售额统计'=>'salesmoney');
	$chart_type = array('salescount'=>'bar','salesmoney'=>'bar');
	$init_div = 1;
	$limit = 10;
	$tab_html = '';
	$lastyearday = strtotime('-1 year',time());
	$starttime = mktime(0,0,0,date('n',$lastyear),1,date('Y',$lastyear));
	$sql = "select l.agencyuid as item,sum(l.special*l.quantity) as salesmoney,sum(l.quantity) as salescount from ";
	$sql .= "{$tablepre}orderlist l left join {$tablepre}orders o on l.orderid=o.id left join {$tablepre}supplierproduct sp on l.pid=sp.id ";
	$sql .= " where l.type='product' and o.flag>1 and o.ordertype=1 and o.backflag=0 and sp.moderate!=-1 and o.dateline>'$starttime' and l.agencyuid>0";
	$sql .= " group by item order by salescount desc";
	$result = $db->fetchAssocArrBySql($sql);
	
	$th1 = '微店';
	$th2 = '销售额';
	$th3 = '销售量';
	foreach($ana_array as $key=>$value)
	{
		$alldata = array();
		$alldata['title'] = $key;
		$alldata['name'] = $key;
		foreach($result as $k=>$v)
		{
			$sql = "select title from {$tablepre}agency where uid='$v[item]' and module='agency'";
			$title = $db->fetchOneBySql($sql);
			
			$alldata['xAxis'][] = $title;
			if($value=='salescount')
			{
				$alldata['series']['销售量'][] =$v['salescount'];
			}
			else
			{
				$alldata['series']['销售额'][] = round($v['salesmoney'],2);
			}
			$alldata['legend'][] = $title;
			if($value=='salescount')
			{
				$listhtml .= sprintf($html_item,$title,round($v['salesmoney'],2),$v['salescount']);
			}
			
		}
		if($value=='salescount')
		{
			$tab_html.='<a class="item first current" href="javascript:;" role="'.$value.'">'.$key.'</a>';
			$datastyle = '';
			$data_html .= sprintf($datahtml,$value,$datastyle,$th1,$th2,$th3,$listhtml);
			
		}
		$varname = $value.'div';
		echo $$varname = chart_data($alldata,$chart_type[$value],$init_div);
		++$init_div;
	}
}
eval ("\$content .= \"" . $tpl->get("stat_useracts",$template). "\";");
echo $content;
exit;