<?php
require_once $site_engine_root.'mobile/lib/admin.php';
require_once $site_engine_root.'mobile/lib/function.php';
function rankfield($module,$field)
{
	global $db,$tablepre;
	$sql = "select $field,count(id) c from {$tablepre}$module group by $field order by c desc limit 10";
	$info = $db->fetchAssocArrBySql($sql);
	$typearr = array('friend'=>'分享给朋友','timeline'=>'分享到朋友圈','qq'=>'分享到QQ');
	$data = '';
	if(!empty($info))
	{
		if($module == 'share')
		{
			foreach($info as $key => $value)
			{
				$data .= '<div class="form-group" uid="'.$value['uid'].'">
					<label class="input-group">
						<div class="form-control"><div class="name">'.($key+1).'. '.$typearr[$value[$field]].": $value[c]".'</div></div>
					</label></div>';
			}
		}
		else
		{
			foreach($info as $key => $value)
			{
				$data .= '<div class="form-group" uid="'.$value['uid'].'">
				<label class="input-group">
					<div class="form-control"><div class="name">'.($key+1).'. '.$value[$field].": $value[c]".'</div></div>
				</label></div>';
			}
		}
		
	}
	return $data;
}
function rankusercount($module,$fun='count',$field='id',$where='')//某表uid
{
	global $db,$tablepre,$uploaddir,$stattime;
	$sql = "select uid,$fun($field) c from {$tablepre}$module where dateline>$stattime $where group by uid order by c desc limit 10";
	$dictinfo = $db->fetchAssocArrBySql($sql);
	foreach($dictinfo as $v)
	{
		$uids[] = $v['uid'];
	}
	if(!empty($uids))
	{
		$uids = implode(',',$uids);
		$sql = "SELECT uid,linkman,avatar FROM {$tablepre}members where subscribe=1 and uid in ($uids)";
		$info = $db->fetchAssocArrBySql($sql);
	}
	$data = '';
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$data .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$module.'&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.avatar($value['uid']).'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['linkman'].'</div></div>
				<div class="input-group-addon">
					<span class="badge">'.$dictinfo[$key]['c'].'</span>
				</div>
			</label></a>';
		}
	}
	return $data;
}
function rankitemcount($module,$groupby='uid')//某表某字段
{
	global $db,$tablepre,$uploaddir;
	$sql = "select $groupby,count(*) c from {$tablepre}$module group by $groupby order by c desc limit 10";
	$dictinfo = $db->fetchAssocArrBySql($sql);
	foreach($dictinfo as $v)
	{
		$info[] = $v[$groupby];
	}
	$data = '';
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$data .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\');">
			<label class="input-group">
				<div class="form-control"><div class="name">'.($key+1).'. '.$value.'</div></div>
			</label></a>';
		}
	}
	return $data;
}
function rankitem($module,$field,$where='moderate',$nophoto=0)
{
	global $db,$tablepre,$uploaddir;
	$data = '';
	if($nophoto==0)//有图
	{
		$sql = "select id,title,photo,$field from {$tablepre}$module where $where=1 order by $field desc limit 10";
		$info = $db->fetchAssocArrBySql($sql);
		if(!empty($info))
		{
		
			foreach($info as $key => $value)
			{
				$photoarray= explode(',',$value['photo']);
				$data .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$module.'&id='.$value['id'].'\');">
				<label class="input-group">
					<div class="input-group-addon">
						<img src="'.$uploaddir.$photoarray[0].'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
					</div>
					<div class="form-control"><div class="name">'.$value['title'].'</div></div>
				</label></a>';
			}
		}
	}
	else//无图
	{
		$sql = "select id,title,$field from {$tablepre}$module where $where=1 order by $field desc limit 10";
		$info = $db->fetchAssocArrBySql($sql);
		if(!empty($info))
		{
			foreach($info as $key => $value)
			{
				$data .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$module.'&id='.$value['id'].'\');">
				<label class="input-group">
					<div class="form-control"><div class="name">'.($key+1).'. '.$value[$field].' '.$value['title'].'</div></div>
				</label></a>';
			}
		}
	}
	return $data;
}
function rankuser($module,$field)
{
	global $db,$tablepre,$uploaddir;
	$sql = "SELECT uid,linkman,avatar,$field FROM {$tablepre}$module where subscribe=1 ORDER BY $field DESC LIMIT 10";
	$info = $db->fetchAssocArrBySql($sql);
	$data = '';
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$data .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=members&uid='.$value['uid'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.avatar($value['uid']).'" onerror="javascript:this.src=\'.$uploaddir.avatar/user.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['linkman'].'</div></div>
			</label></a>';
		}
	}
	return $data;
}
function rankitem2($module,$module2)
{
	global $tablepre,$db,$stattime;
	global $uploaddir;
	
	$field = 'moduleid';
	if($module=='meeting')
	{
		$field = 'meetingid';
	}
	$sql = "SELECT $field,count(*) c FROM {$tablepre}$module2 where $field!=0 and dateline>$stattime group by $field ORDER BY c DESC LIMIT 10";
	$msginfo = $db->fetchAssocArrBySql($sql);
	foreach($msginfo as $v)
	{
		$mid[] = $v[$field];
	}
	if(!empty($mid))
	{
		$mid = implode(',',$mid);
		$sql = "select id,title,photo from {$tablepre}$module where moderate=1 and id in($mid)";
		$info = $db->fetchAssocArrBySql($sql);
	}
	$data = '';
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$data .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module=operator&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.$value['photo'].'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['title'].'</div></div>
			</label></a>';
		}
	}
	return $data;
}
function sharestatonly($module,$field)
{
	global $db,$tablepre,$stattime,$uploaddir;
	$sql = "SELECT url,$field as c FROM {$tablepre}$module where dateline>$stattime group by url ORDER BY c DESC LIMIT 10";
	$shareinfo = $db->fetchAssocArrBySql($sql);
	foreach($shareinfo as $v)
	{
		$shareurl[] = $v['url'];
	}
	foreach($shareurl as $v)
	{
		$url = explode('_',$v);
		$param1 = $url[0];
		$param2 = $url[1];
		$param3 = $url[2];
		if($param1=='module' || $param1=='service'|| empty($v))
		{
			continue;
		}
		$sql = "select id,title,photo from {$tablepre}$param1 where $param2=$param3";
		$result = $db->fetchSingleAssocBySql($sql);
		if($result)
		{
			$info[] = array_merge($result,array('module'=>$param1));
		}
	}
	$data = '';
	if(!empty($info))
	{
		foreach($info as $key => $value)
		{
			$data .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$module.'&id='.$value['id'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.$value['photo'].'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
					<span>'.$value[$field].'</span>
				</div>
				<div class="form-control"><div class="name">'.$value['title'].'</div></div>
				<div class="input-group-addon">
					<span class="badge">'.$value[$field].'</span>
				</div>
			</label></a>';
		}
	}
	return $data;
}

function getmemberstr($subscribe)
{
	global $tablepre,$stattime,$db;
	$sql = "SELECT uid from {$tablepre}members WHERE dateline>$stattime AND subscribe = $subscribe";
	$memberdata = $db->fetchAssocArrBySql($sql);
	$link = '';
	$memberstr = '';
	foreach ($memberdata as $key=>$value)
	{
		$memberstr.=$link.$value['uid'];
		$link = ',';
	}
	return $memberstr;
}

function newmemberviews($memberstr)
{
	$dataview = '';
	if(empty($memberstr))
	{
		return $dataview;exit;
	}
	global $tablepre,$db,$uploaddir,$field;
	$sql = "SELECT count(id) c,module,moduleid from {$tablepre}views where uid in($memberstr)  group by module,moduleid order by c desc limit 10";
	$viewsdata = $db->fetchAssocArrBySql($sql);
	foreach ($viewsdata as $key=>$value)
	{
		if($value[module]!='testing'&&$value[module]!='module')
		{
			$sql = "SELECT title,photo from {$tablepre}$value[module] WHERE id = $value[moduleid]";
			$newsdate= $db->fetchAssocArrBySql($sql);
			$data[$key]['title'] = $newsdate[0]['title'];
			$photo = explode(',',$newsdate[0]['photo']);
			$data[$key]['photo'] = $photo[0];
			$data[$key]['count'] = $value['c'];
			$data[$key]['module'] = $value['module'];
			$data[$key]['moduleid'] = $value['moduleid'];
		}
	}
	if(!empty($data))
	{
		foreach($data as $key => $value)
		{
			$dataview .='<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$value['module'].'&id='.$value['moduleid'].'\');">
			<label class="input-group">
				<div class="input-group-addon">
					<img src="'.$uploaddir.$value['photo'].'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
				</div>
				<div class="form-control"><div class="name">'.$value['title'].'</div></div>
				<div class="input-group-addon">
					<span class="badge">'.$value['count'].'</span>
				</div>
			</label></a>';
		}
	}
	return $dataview;
}

function newmembershare($memberstr)
{
	$datashare = '';
	if(empty($memberstr))
	{
		return $datashare;exit;
	}
	global $tablepre,$db,$uploaddir;
	$sql = "SELECT count(id) c,module,moduleid,title from {$tablepre}share where uid in($memberstr)  group by module,moduleid order by c desc limit 10";
	$shareinfodata = $db->fetchAssocArrBySql($sql);
	if(!empty($shareinfodata))
	{
		foreach($shareinfodata as $key => $value)
		{
			if($value[module]!='testing'&&$value[module]!='module'){
				if($value['moduleid'])
				{
					$sql = "SELECT photo from {$tablepre}$value[module] WHERE id = ".$value['moduleid']." ";
					$photo = $db->fetchColBySql($sql);
				}
				$photo = explode(',',$photo[0]);
				if(!$value['moduleid'])
				{
					$datashare .= '<label class="input-group"><div class="form-control"><div class="name">该条已被删除</div></div></label></a>';
				}
				else
				{
					$datashare .= '<a class="form-group" uid="'.$value['uid'].'" href="javascript:;" onclick="menuclick(\'admin.php?action=view&module='.$value['module'].'&id='.$value['moduleid'].'\');">
					<label class="input-group">
						<div class="input-group-addon">
							<img src="'.$uploaddir.$photo[0].'" onerror="javascript:this.src=\'/mobile/data/images/nopic.jpg\';">
						</div>
						<div class="form-control"><div class="name">'.$value['title'].'</div></div>
						<div class="input-group-addon">
							<span class="badge">'.$value['c'].'</span>
						</div>
					</label></a>';
				}
			}
		}
	}
	return $datashare;
}

function memberevent($memberstr)
{
	global $tablepre,$db,$uploaddir;
	$sql = "SELECT count(id) c,content from {$tablepre}mpmsg where uid in($memberstr) AND content!='' AND MsgType = 'event' group by content order by c desc limit 10";
	$data = $db->fetchAssocArrBySql($sql);
	$eventdata = '';
	foreach ($data as $key => $value)
	{
		$eventdata .= '<div class="form-group" uid="'.$value['uid'].'">
		<label class="input-group">
			<div class="form-control"><div class="name">'.$value['content'].'</div></div>
			<div class="input-group-addon">
				<span class="badge">'.$value['c'].'</span>
			</div>
		</label></div>';
	}
	return $eventdata;
}
?>