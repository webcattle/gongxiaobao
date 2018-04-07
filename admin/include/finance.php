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
$data = array();
$startdate = $_GET['startdate'] ? $_GET['startdate']:date('Y-m',time()).'-01';
$enddate = $_GET['enddate'] ? $_GET['enddate']:date('Y-m-d',time());
$datestart = strtotime("$startdate".' 00:00:00');
$dateend = strtotime("$enddate".' 23:59:59');

//供应商选择
$shopstoreids = $_GET['shopstoreids'] ? explode(",",$_GET['shopstoreids']) : array();
$sql = "select id,uid,title,costtype from {$tablepre}agency where module='shopstore' and moderate=1";
$shopstores = $db->fetchAssocArrBySql($sql);
$shopstorelist = '';
foreach($shopstores as $key=>$value)
{
	$checked = '';
	if(in_array($value['id'],$shopstoreids))
	{
		$checked = ' checked';
	}
	$shopstorelist .= '<label class="checkbox"><input type="checkbox" name="shopstoreid" value="'.$value['id'].'"'.$checked.'><i></i>'.$value['title'].'</label>';
}

$sql = "select distinct orderid from {$tablepre}revenue where dateline>='$datestart' and dateline<='$dateend'";
if($orderno)
{
	$sql .= " and orderno like '%".$orderno."%' ";
}
if($action=='shopstore' || $action=='finance')
{
	if($shopstoreids)
	{
		$uids = array();
		foreach($shopstores as $key=>$value)
		{
			if(in_array($value['id'],$shopstoreids))
			{
				$uids[]=$value['uid'];
			}
		}
		if($uids)
		{
			$uidstr = implode(",",$uids);
			$sql .= " and identity='shopstore' and uid in($uidstr)";
		}

	}
	if($action=='shopstore')
	{
		$sql .= " and identity='shopstore' and cashed=0 and cashintime>0 and cashintime<'".time()."'";
	}
	else
	{
		if(isset($status))
		{
			if(intval($status)==0 || intval($status)==1)
			{
				$sql .= " and identity='shopstore' and cashed='$status' and cashintime>0 and cashintime<'".time()."'";
			}
			else if(intval($status)==200)
			{
				$sql .=  " and identity='shopstore' and cashed='0' and (cashintime>'".time()."' or cashintime=0)";
			}
		}
		else
		{
			$sql .= " and identity='shopstore'";
		}
	}
}
else if($action=='agency')
{
	if($status!='')
	{
		if(intval($status)==0)
		{
			$sql .= " and (identity='agency' or identity='share') and cashed='0' and cashintime<'".time()."' and cashintime>0";
		}
		else if(intval($status)==1)
		{
			$sql .= " and (identity='agency' or identity='share') and cashed='1'";
		}
		else if(intval($status)==200)
		{
			$sql .=  " and (identity='agency' or identity='share') and cashed='0' and (cashintime>'".time()."' or cashintime=0)";
		}
	}
	if($agencyname)
	{
		$sql .= " and title like '%".$agencyname."%' ";
	}
	else if($shareuser)
	{
		$sql .= " and title like '%".$shareuser."%' ";
	}
}
else if($action == 'moneyflow')
{
	$sql = "select * from {$tablepre}moneyflow where  dateline>='$datestart' and dateline<='$dateend'";
	if($orderno)
	{
		$sql .= " and orderno like '%".$orderno."%'";
	}
	if($payrefund!='')
	{
		$sql .= " and type='$payrefund'";
	}
	$sql .= " order by dateline desc,orderno desc ";
	$result = $db->fetchAssocArrBySql($sql);

	$uids = array();
	$shopnames = array();
	if($shopstoreids)
	{
		foreach($shopstores as $key=>$value)
		{
			if(in_array($value['id'],$shopstoreids))
			{
				$uids[]=$value['uid'];
				$shopnames[$value['uid']]= $value['title'];
			}
		}
	}
	else
	{
		foreach($shopstores as $key=>$value)
		{
			$uids[]=$value['uid'];
			$shopnames[$value['uid']]= $value['title'];
		}
	}
	$ret=array();
	$i = 0;
	foreach($result as $key => $value)
	{
		$sql = "select * from {$tablepre}orders where id='".$value['orderid']."'";
		$orderinfo = $db->fetchSingleAssocBySql($sql);
		if(in_array($orderinfo['shopstoreuid'],$uids) || !$shopstoreids)
		{
			$ret[$i]['orderno'] = $value['orderno'];
			$ret[$i]['payorder'] = $value['weixinno'];
			$ret[$i]['dateline'] = date('Y-m-d H:i',$value['dateline']);
			$ret[$i]['user'] = $db->fetchOneBySql("select linkman from {$tablepre}members where uid='".$value['uid']."'");
			$ret[$i]['money'] = $value['money']/100;
			$ret[$i]['shopstore'] = $shopnames[$orderinfo['shopstoreuid']];
			$ret[$i]['type'] = $moneyflowtype[$value['type']];
			$i++;
		}

	}
}
if($action=='shopstore')
{
	$ret = array();
	$orderids = $db->fetchColBySql($sql);
	$time = time();
	if($orderids)
	{
		$sql = "select * from {$tablepre}revenue where orderid in (".implode(",",$orderids).") order by orderid desc";
		$result = $db->fetchAssocArrBySql($sql);

		$i = 0;
		$curorderid = '';
		foreach($result as $key => $value)
		{
			if($key>0 && $curorderid!=$value['orderid'])
			{
				$i ++;
			}
			$ret[$i]['orderno'] = $value['orderno'];
			if($value['identity']=='system')
			{
				$ret[$i]['takemoney'] = $value['money'];
				$ret[$i]['cost'] = $value['cost'];
			}
			else if($value['identity']=='shopstore')
			{
				foreach($shopstores as $k=>$v)
				{
					if($value['uid']==$v['uid'])
					{
						$ret[$i]['shopstoreuid'] = $v['uid'];
						$ret[$i]['shopstore'] = $v['title'];
						break;
					}
				}
				$ret[$i]['shopstore_money'] = $value['money'];
				$ret[$i]['cashintime'] = $value['cashintime'];
				if($action=='finance')
				{
					$ret[$i]['cashed'] = $value['cashed'];
					$ret[$i]['payouttime'] = $value['payouttime'];
					$cashed = intval($value['cashed']);
					$cashintime = intval($value['cashintime']);
					if($cashed==0)
					{
						if($cashintime < $time && $cashintime>0)
						{
							$modstr = '待分账';
							$ret[$i]['shopcashintime'] = $cashintime;
						}
						else
						{
							$modstr = "预分账";
						}
					}
					else if($cashed == 1)
					{
						$modstr = "已分账";
						$ret[$i]['shopcashintime'] = $cashintime;
					}
					else if($cashed == 2)
					{
						$modstr = "用户退款";
					}
					else if($cashed==100)
					{
						$modstr = "暂停分账";
					}
					$ret[$i]['shopmodstr'] = $modstr;
				}
			}
			else if($value['identity']=='agency')
			{
				$ret[$i]['agency_money'] += $value['money'];
			}
			else if($value['identity']=='share')
			{
				$ret[$i]['share'] = $value['title'];
				$ret[$i]['share_money'] += $value['money'];
			}
			$ret[$i]['dateline'] = date('Y-m-d H:i',$value['dateline']);
			$ret[$i]['id'] = $value['orderid'];
			$curorderid = $value['orderid'];
		}
	}
}
else if($action == 'agency')
{
	$ret = array();
	$orderids = $db->fetchColBySql($sql);
	$time = time();
	if($orderids)
	{
		$sql = "select * from {$tablepre}revenue where orderid in (".implode(",",$orderids).") and identity ='agency' order by orderid desc";
		$result = $db->fetchAssocArrBySql($sql);

		$i = 0;
		$curorderid = '';
		foreach($result as $key => $value)
		{
			$ret[$i]['orderno'] = $value['orderno'];
			$sql = "select linkman from {$tablepre}members where uid='$value[uid]'";
			$agencyuser = $db->fetchOneBySql($sql);

			$ret[$i]['agency'] = $value['title'];
			$ret[$i]['agency_user'] = $agencyuser;
			$ret[$i]['agency_money'] = $value['money'];
			$cashed = intval($value['cashed']);
			$cashintime = intval($value['cashintime']);
			$ret[$i]['canselect'] = 0;

			if($cashed==0)
			{
				if($cashintime < $time && $cashintime>0)
				{
					$modstr = '待分账';
					$ret[$i]['canselect'] = 1;
					$ret[$i]['agencycashintime'] = $value['cashintime'];
				}
				else
				{
					$modstr = "预分账";
				}
			}
			else if($cashed == 1)
			{
				$modstr = "已分账";
				$ret[$i]['agencycashintime'] = $value['cashintime'];
			}
			else if($cashed == 2)
			{
				$modstr = "用户退款";
			}
			else if($cashed==100)
			{
				$modstr = "暂停分账";
			}
			$ret[$i]['agencymodstr'] = $modstr;
			$ret[$i]['dateline'] = date('Y-m-d H:i',$value['dateline']);
			$ret[$i]['id'] = $value['orderid'];
			$sql = "select * from {$tablepre}revenue where identity='share' and productid='".$value['productid']."' and orderid='".$value['orderid']."'";
			$shareinfo = $db->fetchSingleAssocBySql($sql);
			if($shareinfo)
			{
				$ret[$i]['share'] = $shareinfo['title'];
				$ret[$i]['share_money'] = $shareinfo['money'];
			}
			$i++;
		}
	}
}
else if($action=='finance')
{
	$ret = array();
	$orderids = $db->fetchColBySql($sql);
	$time = time();
	if($orderids)
	{
		$sql = "select * from {$tablepre}revenue where orderid in (".implode(",",$orderids).") order by orderid desc";
		$result = $db->fetchAssocArrBySql($sql);

		$i = 0;
		$curorderid = '';
		foreach($result as $key => $value)
		{
			if($key>0 && $curorderid!=$value['orderid'])
			{
				$i ++;
			}
			$ret[$i]['orderno'] = $value['orderno'];
			if($value['identity']=='system')
			{
				$ret[$i]['takemoney'] = $value['money'];
				$ret[$i]['cost'] = $value['cost'];
			}
			else if($value['identity']=='shopstore')
			{
				foreach($shopstores as $k=>$v)
				{
					if($value['uid']==$v['uid'])
					{
						$ret[$i]['shopstoreuid'] = $v['uid'];
						$ret[$i]['shopstore'] = $v['title'];
						break;
					}
				}
				$ret[$i]['shopstore_money'] = $value['money'];
				$ret[$i]['cashintime'] = $value['cashintime'];
				if($action=='finance')
				{
					$ret[$i]['cashed'] = $value['cashed'];
					$ret[$i]['payouttime'] = $value['payouttime'];
					$cashed = intval($value['cashed']);
					$cashintime = intval($value['cashintime']);
					if($cashed==0)
					{
						if($cashintime < $time && $cashintime>0)
						{
							$modstr = '待分账';
							$ret[$i]['shopcashintime'] = $cashintime;
						}
						else
						{
							$modstr = "预分账";
						}
					}
					else if($cashed == 1)
					{
						$modstr = "已分账";
						$ret[$i]['shopcashintime'] = $cashintime;
					}
					else if($cashed == 2)
					{
						$modstr = "用户退款";
					}
					else if($cashed==100)
					{
						$modstr = "暂停分账";
					}
					$ret[$i]['shopmodstr'] = $modstr;
				}
			}
			else if($value['identity']=='agency')
			{
				$sql = "select linkman from {$tablepre}members where uid='$value[uid]'";
				$agencyuser = $db->fetchOneBySql($sql);

				$ret[$i]['agency'] = $value['title'];
				$ret[$i]['agency_money'] += $value['money'];
				$ret[$i]['agency_user'] =$agencyuser;
				if($action=='agency')
				{
					$ret[$i]['cashed'] = $value['cashed'];
					$ret[$i]['payouttime'] = $value['payouttime'];
				}
				$cashed = intval($value['cashed']);
				$cashintime = intval($value['cashintime']);
				$ret[$i]['canselect'] = 0;

				if($cashed==0)
				{
					if($cashintime < $time && $cashintime>0)
					{
						$modstr = '待分账';
						$ret[$i]['canselect'] = 1;
						$ret[$i]['agencycashintime'] = $value['cashintime'];
					}
					else
					{
						$modstr = "预分账";
					}
				}
				else if($cashed == 1)
				{
					$modstr = "已分账";
					$ret[$i]['agencycashintime'] = $value['cashintime'];
				}
				else if($cashed == 2)
				{
					$modstr = "用户退款";
				}
				else if($cashed==100)
				{
					$modstr = "暂停分账";
				}
				if($ret[$i]['agencymodstr'])
				{
					$ret[$i]['agencymodstr'] .= ",";
				}
				$ret[$i]['agencymodstr'] .= $modstr;

			}
			else if($value['identity']=='share')
			{
				$ret[$i]['share'] = $value['title'];
				$ret[$i]['share_money'] += $value['money'];
				$sql = "select bankuser,idcardno from {$tablepre}membersinfo where uid='$value[uid]'";
				$uinfo = $db->fetchSingleAssocBySql($sql);

				$ret[$i]['share_bankuser'] =$uinfo['bankuser'];
				$ret[$i]['share_idcard'] = $uinfo['idcardno'];
			}
			$ret[$i]['dateline'] = date('Y-m-d H:i',$value['dateline']);
			$ret[$i]['id'] = $value['orderid'];
			$curorderid = $value['orderid'];
		}
	}
}

$totalinfo = '';
$all = array();
if($action=='shopstore')
{
	if($do=='exportexcel')
	{
		ob_start();
		ob_end_flush();
		header ( "Content-type:application/vnd.ms-excel" );
		header ( "Content-Disposition:attachment;filename=finance_shop.xls" );
		echo '<table><tr>';
		echo '<td align="center">凭证类别</td>
			<td align="center">订单号</td>
			<td align="center">订单金额</td>
			<td align="center">关联凭证号</td>
			<td align="center">供应商</td>
			<td align="center">运费</td>
			<td align="center">金币抵扣</td>
			<td align="center">支付总额</td>
			<td align="center">分销佣金</td>
			<td align="center">平台提留</td>
			<td align="center">成本</td>
			<td align="center">供应商分账</td>
			<td align="center">分账生效时间</td>
			</tr>';
	}
	foreach($ret as $key => $value)
	{
		$sql = "select * from {$tablepre}orders where id='".$value['id']."'";
		$orderinfo = $db->fetchSingleAssocBySql($sql);
		if($orderinfo['ordertype']==20)
		{
			$ordertype = '退款';
			$payorder = $orderinfo['refundno'];
		}
		else
		{
			$ordertype = '订单';
			$payorder = $orderinfo['payorder'];
		}
		foreach($shopstores as $k=>$v)
		{
			if($value['shopstoreuid']==$v['uid'])
			{
				$costtype = $v['costtype'];
				break;
			}
		}
		if($costtype==1)//成本核算
		{
			$allcost = $value['cost'];
		}
		else
		{
			$allcost = '-';
		}
		$totalinfo .= '<tr>';
		$totalinfo .= '<td>'.$ordertype.'</td>';
		$totalinfo .= '<td><label class="checkbox">';
		$totalinfo .= '<input type="checkbox" name="selectorder" id="selectorder_'.$value['id'].'" value="'.$value['id'].'" checked><i></i>'.$value['orderno'].'</label></td>';
		$totalinfo .= '<td>'.number_format($orderinfo['special'],2).'</td>';
		$totalinfo .= '<td>a'.$payorder.'</td>';
		$totalinfo .= '<td>'.$value['shopstore'].'</td>';
		$totalinfo .= '<td>'.$orderinfo['postage'].'</td>';
		$totalinfo .= '<td>'.number_format($orderinfo['credit']/$moneycredit,2).'</td>';
		$paymoney = number_format($orderinfo['special']+$orderinfo['postage']-$orderinfo['credit']/$moneycredit,2);

		$totalinfo .= '<td>'.$paymoney.'</td>';
		$totalinfo .= '<td>'.number_format($value['agency_money']+$value['share_money'],2).'</td>';
		$totalinfo .= '<td>'.$value['takemoney'].'</td>';
		$totalinfo .= '<td>'.$allcost.'</td>';
		$totalinfo .= '<td>'.$value['shopstore_money'].'</td>';
		$totalinfo .= '<td>'.date('Y-m-d H:i',$value['cashintime']).'</td>';
		$totalinfo .= '</tr>';
		$all['paymoney'] += $orderinfo['special']+$orderinfo['postage']-$orderinfo['credit']/$moneycredit;
		$all['agencymoney'] += $value['agency_money']+$value['share_money'];
		$all['takemoney'] += $value['takemoney'];
		$all['shopstoremoney'] += $value['shopstore_money'];
		$all['cost'] += floatval($allcost);
		$all['special'] += $orderinfo['special'];
		$all['postage'] +=$orderinfo['postage'];
		$all['credit'] +=$orderinfo['credit']/$moneycredit;
	}
	$footer = '<td colspan=2>合计</td>';
	$footer .= '<td>'.number_format($all['special'],2).'</td>';
	$footer .= '<td colspan=2>&nbsp;</td>';
	$footer .= '<td>'.number_format($all['postage'],2).'</td>';
	$footer .= '<td>'.number_format($all['credit'],2).'</td>';
	$footer .= '<td>'.number_format($all['paymoney'],2).'</td>';
	$footer .= '<td>'.number_format($all['agencymoney'],2).'</td>';
	$footer .= '<td>'.number_format($all['takemoney'],2).'</td>';
	$footer .= '<td>'.number_format($all['cost'],2).'</td>';
	$footer .= '<td>'.number_format($all['shopstoremoney'],2).'</td>';
	$footer .= '<td>-</td>';

}
else if($action == 'agency')
{
	if($do=='exportexcel')
	{
		ob_start();
		ob_end_flush();
		header ( "Content-type:application/vnd.ms-excel" );
		header ( "Content-Disposition:attachment;filename=finance_agency.xls" );
		echo '<table><tr>';
		echo '<td align="center">订单号</td>
			<td align="center">关联凭证号</td>
			<td align="center">支付时间</td>
			<td align="center">支付金额</td>
			<td align="center">微店</td>
			<td align="center">微店主</td>
			<td align="center">微店分账额</td>
			<td align="center">推广用户</td>
			<td align="center">推广佣金</td>
			<td align="center">分销佣金</td>
			<td align="center">财务状态</td>
			<td align="center">分账时间</td></tr>';
	}
	foreach($ret as $key => $value)
	{
		$sql = "select * from {$tablepre}orders where id='".$value['id']."'";
		$orderinfo = $db->fetchSingleAssocBySql($sql);
		if($orderinfo['ordertype']==20)
		{
			$ordertype = '退款';
			$payorder = $orderinfo['refundno'];
			if($orderinfo['refundtime'])
			{
				$paytime = date('Y-m-d H:i',$orderinfo['refundtime']);
			}
			else
			{
				$paytime = '-';
			}
		}
		else
		{
			$ordertype = '订单';
			$payorder = $orderinfo['payorder'];
			$paytime = date('Y-m-d H:i',$orderinfo['paytime']);
		}
		$totalinfo .= '<tr>';
		$totalinfo .= '<td>';
		if($value['canselect'])
		{
			$totalinfo .= '<label class="checkbox"><input type="checkbox" name="selectorder" id="selectorder_'.$value['id'].'" value="'.$value['id'].'" checked><i></i>';
			$totalinfo .=$value['orderno'].'</label></td>';
		}
		else
		{
			$totalinfo .= $value['orderno'].'</td>';
		}

		$totalinfo .= '<td>a'.$payorder.'</td>';
		$totalinfo .= '<td>'.$paytime.'</td>';

		$paymoney = $orderinfo['special']+$orderinfo['postage']-$orderinfo['credit']/$moneycredit;

		$totalinfo .= '<td>'.number_format($paymoney,2).'</td>';
		$totalinfo .= '<td>'.$value['agency'].'</td>';
		$totalinfo .= '<td>'.$value['agency_user'].'</td>';
		$totalinfo .= '<td>'.number_format($value['agency_money'],2).'</td>';
		$totalinfo .= '<td>'.$value['share'].'</td>';
		$totalinfo .= '<td>'.number_format($value['share_money'],2).'</td>';

		$totalinfo .= '<td>'.number_format($value['agency_money']+$value['share_money'],2).'</td>';

		$totalinfo .= '<td>'.$value['agencymodstr'].'</td>';
		if($value['payouttime'])
		{
			$totalinfo .= '<td>'.date('Y-m-d H:i',$value['payouttime']).'</td>';
		}
		else
		{
			$totalinfo .= '<td>-</td>';
		}
		$totalinfo .= '</tr>';
		$all['paymoney'] += $paymoney;
		$all['agencymoney'] += $value['agency_money'];
		$all['sharemoney'] += $value['share_money'];
		$all['commission'] += $value['agency_money']+$value['share_money'];
	}
	$footer = '<td colspan=3>合计</td>';
	$footer .= '<td>'.number_format($all['paymoney'],2).'</td>';
	$footer .= '<td colspan=2>-</td>';
	$footer .= '<td>'.number_format($all['agencymoney'],2).'</td>';
	$footer .= '<td>-</td>';
	$footer .= '<td>'.number_format($all['sharemoney'],2).'</td>';
	$footer .= '<td>'.number_format($all['commission'],2).'</td>';
	$footer .= '<td>-</td>';
	$footer .= '<td>-</td>';
}
else if($action=='moneyflow')
{
	if($do=='exportexcel')
	{
		ob_start();
		ob_end_flush();
		header ( "Content-type:application/vnd.ms-excel" );
		header ( "Content-Disposition:attachment;filename=finance_money.xls" );
		echo '<table><tr>';
		echo '<td align="center">订单号</td>
			<td align="center">关联凭证号</td>
			<td align="center">交易时间</td>
			<td align="center">用户</td>
			<td align="center">交易金额</td>
			<td align="center">供应商</td>
			<td align="center">交易类型</td></tr>';
	}
	foreach($ret as $key=>$value)
	{
		$totalinfo .= '<tr>';
		$totalinfo .= '<td>'.$value['orderno'].'</label></td>';
		$totalinfo .= '<td>a'.$value['payorder'].'</td>';
		$totalinfo .= '<td>'.$value['dateline'].'</td>';
		$totalinfo .= '<td>'.$value['user'].'</td>';
		$totalinfo .= '<td>'.number_format($value['money'],2).'</td>';
		$totalinfo .= '<td>'.$value['shopstore'].'</td>';
		$totalinfo .= '<td>'.$value['type'].'</td>';
		$totalinfo .= '</tr>';
		$all['money'] += $value['money'];
	}
	$footer = '<td colspan=4>合计</td>';
	$footer .= '<td>'.number_format($all['money'],2).'</td>';
	$footer .= '<td>-</td>';
	$footer .= '<td>-</td>';
}
else
{
	if($do=='exportexcel')
	{
		ob_start();
		ob_end_flush();
		header ( "Content-type:application/vnd.ms-excel" );
		header ( "Content-Disposition:attachment;filename=finance.xls" );
		echo '<table><tr>';
		echo '<td align="center">凭证类型</td>
			<td align="center">订单号</td>
			<td align="center">订单商品</td>
			<td align="center">支付/退款时间</td>
			<td align="center">订单状态</td>
			<td align="center">订单金额</td>
			<td align="center">关联凭证号</td>
			<td align="center">供应商</td>
			<td align="center">买家</td>
			<td align="center">运费</td>
			<td align="center">金币抵扣</td>
			<td align="center">支付总额</td>
			<td align="center">微店</td>
			<td align="center">微店佣金</td>
			<td align="center">推客</td>
			<td align="center">推客佣金</td>
			<td align="center">佣金分账状态</td>
			<td align="center">佣金分账时间</td>
			<td align="center">平台提留</td>
			<td align="center">成本</td>
			<td align="center">供应商分账</td>
			<td align="center">供应商分账状态</td>
			<td align="center">分账时间</td></tr>';
	}
	foreach($ret as $key => $value)
	{
		$sql = "select * from {$tablepre}orders where id='".$value['id']."'";
		$orderinfo = $db->fetchSingleAssocBySql($sql);
		//$payorder = substr($orderinfo['payorder'],0,14)."<br/>".substr($orderinfo['payorder'],14);
		if($orderinfo['ordertype']==20)
		{
			$ordertype = '退款';
			$payorder = $orderinfo['refundno'];
			if($orderinfo['refundtime'])
			{
				$paytime = date('Y-m-d H:i',$orderinfo['refundtime']);
			}
			else
			{
				$paytime = '-';
			}
		}
		else
		{
			$ordertype = '订单';
			$payorder = $orderinfo['payorder'];
			$paytime = date('Y-m-d H:i',$orderinfo['paytime']);
		}
		$payorder = 'a'.$payorder;

		$totalinfo .= '<tr>';
		$totalinfo .= '<td>'.$ordertype.'</td>';
		$totalinfo .= '<td>'.$value['orderno'].'</td>';
		$sql = "select * from {$tablepre}orderlist where orderid='$value[id]'";
		$plist = $db->fetchAssocArrBySql($sql);
		$products = '';
		foreach($plist as $k => $v)
		{
			$poption = getOptionById($v['options']);
			$sql = "select itemno from {$tablepre}supplierproduct where id='$v[pid]'";
			$itemno = $db->fetchOneBySql($sql);
			$products .=$prefix.csubstr($v['name'],0,20).' '.$poption.'(数量：'.$v['quantity'].')(单价：'.$v['special'].')';
			if($itemno)
			{
				$products .= '(货号：'.$itemno.')';
			}
			$prefix = '<br/>';
		}
		$totalinfo .= '<td>'.$products.'</td>';
		$totalinfo .= '<td>'.$paytime.'</td>';
		$totalinfo .= '<td>'.$orderflagarray[$orderinfo['flag']].'</td>';
		$totalinfo .= '<td>'.number_format($orderinfo['special'],2).'</td>';
		$totalinfo .= '<td>'.$payorder.'</td>';
		$totalinfo .= '<td>'.$value['shopstore'].'</td>';
		$totalinfo .= '<td>'.$orderinfo['username'].'</td>';
		$totalinfo .= '<td>'.number_format($orderinfo['postage'],2).'</td>';
		$totalinfo .= '<td>'.number_format($orderinfo['credit']/$moneycredit,2).'</td>';
		$paymoney = $orderinfo['special']+$orderinfo['postage']-$orderinfo['credit']/$moneycredit;
		$totalinfo .= '<td>'.number_format($paymoney,2).'</td>';
		$totalinfo .= '<td>'.$value['agency'].'</td>';
		$totalinfo .= '<td>'.number_format($value['agency_money'],2).'</td>';
		$totalinfo .= '<td>'.$value['share'].'</td>';
		$totalinfo .= '<td>'.number_format($value['share_money'],2).'</td>';
		$totalinfo .= '<td>'.$value['agencymodstr'].'</td>';
		$totalinfo .= '<td>'.($value['agencycashintime']?date('Y-m-d H:i',$value['agencycashintime']):'-').'</td>';
		$totalinfo .= '<td>'.number_format($value['takemoney'],2).'</td>';
		$totalinfo .= '<td>'.number_format($value['cost'],2).'</td>';
		$totalinfo .= '<td>'.number_format($value['shopstore_money'],2).'</td>';
		$totalinfo .= '<td>'.$value['shopmodstr'].'</td>';
		$totalinfo .= '<td>'.($value['shopcashintime']?date('Y-m-d H:i',$value['shopcashintime']):'-').'</td>';

		$totalinfo .= '</tr>';
		$all['special'] += $orderinfo['special'];
		$all['postage'] += $orderinfo['postage'];
		$all['credit'] += $orderinfo['credit']/$moneycredit;
		$all['paymoney'] += $paymoney;
		$all['agencymoney'] += $value['agency_money'];
		$all['sharemoney'] += $value['share_money'];
		$all['commission'] += $value['agency_money']+$value['share_money'];
		$all['shopstoremoney'] += $value['shopstore_money'];
		$all['takemoney'] += $value['takemoney'];
		$all['cost'] += $value['cost'];
	}
	$footer = '<td colspan=4>合计</td>';
	$footer .= '<td>'.number_format($all['special'],2).'</td>';
	$footer .= '<td colspan=3>-</td>';
	$footer .= '<td>'.number_format($all['postage'],2).'</td>';
	$footer .= '<td>'.number_format($all['credit'],2).'</td>';
	$footer .= '<td>'.number_format($all['paymoney'],2).'</td>';
	$footer .= '<td>-</td>';
	$footer .= '<td>'.number_format($all['commission'],2).'</td>';
	$footer .= '<td>-</td>';
	$footer .= '<td>'.number_format($all['sharemoney'],2).'</td>';
	$footer .= '<td colspan=2>-</td>';
	$footer .= '<td>'.number_format($all['takemoney'],2).'</td>';
	$footer .= '<td>'.number_format($all['cost'],2).'</td>';
	$footer .= '<td>'.number_format($all['shopstoremoney'],2).'</td>';
	$footer .= '<td>-</td>';
	$footer .= '<td>-</td>';
}
if($do == 'exportexcel')
{
	echo $totalinfo.'<tr>'.$footer.'</tr></table>';
	exit;
}
?>