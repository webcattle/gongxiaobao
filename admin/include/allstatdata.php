<?php
function cmdmyuser($a,$b)
{
	if ($a['partcounts'] == $b['partcounts']) {
		return 0;
	}
	return ($a['partcounts'] < $b['partcounts']) ? 1 : -1;
}
if($module=='useracts')
{
	if($role=='userdict')
	{
		$sql = "select keyword as item,count(id) as c from {$tablepre}userdict where keyword!='' group by keyword order by c desc limit $pagestart,$limit";
		$sqlcount = "select count(distinct keyword) as mycount from {$tablepre}userdict where keyword!=''";
	}
	else if($role=='modules')
	{
		$sql = "select module as item,sum(number) as c from {$tablepre}views where module!='' group by module order by c desc limit $pagestart,$limit";
		$sqlcount = "select count(distinct module) as mycount from {$tablepre}views where module!=''";
	}
	else if($role=='shares')
	{
		$sql = "select module as item,count(id) as c from {$tablepre}share where module!='' group by module order by c desc limit $pagestart,$limit";
		$sqlcount = "select count(distinct module) as mycount from {$tablepre}share where module!=''";
	}
	else if($role=='mpmenu')
	{
		$sql = "select mpmenuid as item,count(id) as c from {$tablepre}mpmsg where mpmenuid>0 group by mpmenuid order by c desc limit $pagestart,$limit";
		$sqlcount = "select count(distinct mpmenuid) as mycount from {$tablepre}mpmsg where mpmenuid>0";
	}
	$data = $db->fetchAssocArrBySql($sql);
	foreach($data as $k=>$v)
	{
		if($role=='modules' || $role=='shares')
		{
			$_modulesys = $v['item'].'sys';
			
			$_modulesys = $$_modulesys;
			if($_modulesys)
			{
				$data[$k]['item'] = $_modulesys['name'];
			}
		}
		else if($role=='mpmenu')
		{
			$sql = "select title from {$tablepre}mpmenu where id='$v[item]'";
			$data[$k]['item'] = $db->fetchOneBySql($sql);
		}
	}
}
else if($module=='uservalues')
{
	if($role=='orders')
	{
		$sql = "select username as item,sum(special) as c from {$tablepre}orders where flag>1 and backflag=0 and ordertype=1 group by username order by c desc limit $pagestart,$limit";
		$sqlcount = "select count(distinct username) as mycount from {$tablepre}orders where flag>1 and backflag=0 and ordertype=1";
	}
	else
	{
		$sql = "select username as item,$role as c from {$tablepre}members where subscribe=1 order by c desc limit $pagestart,$limit ";
		$sqlcount = "select count(uid) as mycount from {$tablepre}members where subscribe=1";
		
	}
	$data = $db->fetchAssocArrBySql($sql);
}
else if($module=='effect')
{
	$operator_module = array('operator','games','polls','exam','helpbuy');
	if($role=='myusers')
	{
		foreach ($operator_module as $k => $v)
		{
			if ($v == 'operator')
			{
				$sql = "select moduleid,count(id) as partcounts from {$tablepre}views where module='$v' and moduleid>0 group by moduleid order by partcounts desc";
				$ret = $db->fetchAssocArrBySql($sql);
			}
			elseif($v=='helpbuy')
			{
				$sql = "select moduleid,count(id) as partcounts from {$tablepre}crowduser where module='$v' and moduleid>0 group by moduleid order by partcounts desc";
				$ret = $db->fetchAssocArrBySql($sql);
			}
			else
			{
				$sql = "select moduleid,count(distinct uid) as partcounts from {$tablepre}record where module='$v' and moduleid>0 group by moduleid order by partcounts desc ";
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
		$count = count($tempdata);
		$tempdata = array_splice($tempdata,$pagestart,$limit);
		$ii = 0;
		foreach($tempdata as $k=>$v)
		{
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
		foreach($operator_module as $k=>$v)
		{
			$countsql = "select sum($value) as totalcount from {$tablepre}{$v} where moderate=1";
			$count += $db->fetchOneBySql($countsql);
			
			$sql = "select id,$value as partcounts from {$tablepre}{$v} where moderate=1 having partcounts>0 order by partcounts desc";
			$ret = $db->fetchAssocArrBySql($sql);
			foreach ($ret as $kk => $vv)
			{
				$tempdata[$i] = $vv;
				$tempdata[$i]['module'] = $v;
				$i++;
			}
		}
		usort($tempdata,'cmdmyuser');
		$count = count($tempdata);
		$tempdata = array_splice($tempdata,$pagestart,$limit);
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
}
else if($module=='creditsstat')
{
	$count = 0;
	if ($role == 'membercredits')
	{
		$sql = "select username as item,sum(credit) as c from {$tablepre}credits where credit>0 group by username order by c desc limit $pagestart,$limit";
		$data = $db->fetchAssocArrBySql($sql);
		$sqlcount = "select count(distinct username) from {$tablepre}credits where credit>0";
	}
	else if($role=='getcredits')
	{
		$sql = "select act as item,sum(credit) as c from {$tablepre}credits where credit>0 and act!='' group by act order by c desc";
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
		$count=count($data);
	}
	else
	{
		$sql= "select module as item,sum(credit) as c from {$tablepre}credits where credit>0 and module!='' and module!='register' and module!='0' group by module order by c desc limit $pagestart,$limit";
		$data = $db->fetchAssocArrBySql($sql);
		$sqlcount = "select count(distinct module) from {$tablepre}credits where credit>0 and module!='' and module!='register' and module!='0' ";
	}
	
}
else if($module=='hongbaostat')
{
	if ($role == 'memberhongbao')
	{
		$sql = "select username as item,sum(money)/100 as c from {$tablepre}hongbao where money>0 group by username order by c desc limit $pagestart,$limit";
		$data = $db->fetchAssocArrBySql($sql);
		$sqlcount ="select count(distinct username) from {$tablepre}hongbao where money>0";
	}
	else
	{
		$sql= "select module as item,sum(money)/100 as c from {$tablepre}hongbao where money>0 and module!='' and module!='0' group by module order by c desc limit $pagestart,$limit";
		$data = $db->fetchAssocArrBySql($sql);
		$sqlcount ="select count(distinct module) from {$tablepre}hongbao where money>0";
	}
	foreach($data as $key=>$value)
	{
		$data[$key]['c'] = number_format($value[c],2);
	}
}
if($data)
{
	$l['c'] = '数量';
	$char = array('item','c');
	if(!$count)
	{
		$count = $db->fetchOneBySql($sqlcount);
	}
}
?>