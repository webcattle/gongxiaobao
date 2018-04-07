<?php
/******************************************************************************************************
**  企业Plus 7.0 - 企业Plus社交网络营销中心管理系统
**  Copyright(C) Beijing BokaVan Software Development CO.,LTD , 2002-2016. All rights reserved
**  北京博卡先锋软件开发有限公司 www.qiyeplus.com    *技术支持 请关注'企业Plus' 微信号
**  请详细阅读企业Plus授权协议,查看或使用企业Plus的任何部分意味着完全同意
**  协议中的全部条款,请支持国内软件事业,严禁一切违反协议的侵权行为.
*******************************************************************************************************/
/***************************************
** Title........: search file
** Author.......: Paul Qiu
** Version......: 4.0.0
** Last changed.: 4/20/2015 1:21:55 AM
***************************************/
@header("Content-Type: text/html; charset=UTF-8");
if(!defined('IN_SITEENGINE')) exit('Access Denied');
$info = '';
$keyword = trim($keyword);

if($keyword)
{
	if(!@in_array('snatch',$cando) && !in_array(1,$usergroup))
	{
		exit('no permission');
	}
	if($version=='meeting')
	{
		if($search =='wx' || $search=='wb' || $search=='bd')
		{
			$search = 'www';
		}
	}
	$data=array();//初始化
	if($search=='wx')
	{
		$data=array();
		require_once $site_engine_root.'/mobile/lib/phpQuery.php';
		$url='http://weixin.sogou.com/weixin?type=2&query='.urlencode($keyword);
		$html = phpQuery::newDocumentFile($url.'&page='.$page);
		$page_num=pq("#scd_num")->text();
		$count=str_replace(',','',$page_num);
		//$page_num_f=ceil($page_num/10);
		//wx-rb
		$html_content=pq(".wx-rb");
		$data=array();
		$k=-1;
		foreach($html_content as $v_html)
		{
			$k++;
			$data[$k]['title']=pq($v_html)->find(".txt-box")->find("h4")->text();

			$img_html=pq($v_html)->find(".img_box2")->find("a")->html();
			$img_arr=explode('"',$img_html);
			$data[$k]['photo']='<img src="'.$img_arr[7].'" class=wx_img>';
			$ad_str=pq($v_html)->find(".txt-box")->find(".s-p")->text();
			$ad_array=explode("'",$ad_str);
			$account =  str_replace('document.write(cutLength("','',$ad_array[0]);
			$account =  str_replace('", 16))vrTimeHandle552write(','',$account);
			$data[$k]['account']=strip_tags($account);
			$data[$k]['dateline']=$ad_array[1];
			$a_html=pq($v_html)->find(".txt-box")->find("h4")->html();
			$a_array=explode('"',$a_html);
			$data[$k]['url']='http://weixin.sogou.com/'.$a_array[3];
			//echo $a_array[3];
		}
		if(empty($count))
		{
			$count=$k+1;
		}
	}
	else if($search=='bd')
	{
		$data=array();
		require_once $site_engine_root.'/mobile/lib/phpQuery.php';
		$url='http://www.baidu.com/s?wd='.urlencode($keyword);
		if(empty($page)||$page<=1)
		{
			$pn=0;
		}
		else
		{
			$pn=($page-1)*10;
		}
		$html = phpQuery::newDocumentFile($url.'&pn='.$pn);
		$page_num=pq(".nums")->text();
		$page_num=str_replace('百度为您找到相关结果约','',$page_num);
		$page_num=str_replace('个','',$page_num);
		$count=str_replace(',','',$page_num);

		$html_content=pq("#content_left")->find(".c-container");
		$k=-1;
		foreach($html_content as $v_html)
		{
			$k++;
			$data[$k]['title']=pq($v_html)->find("h3")->text();
			$a_html=pq($v_html)->find("h3")->html();
			$a_array=explode('"',$a_html);

			$data[$k]['account']=pq($v_html)->find(".f13")->find(".g")->text();
			//$data[$k]['dataline']=date('Y-m-d H:i:s',$ad_array[3]);
			if($a_array[3]=='_blank')
			{
				$data[$k]['module']='百度百科';
				$data[$k]['url']=$a_array[1];
			}
			elseif(stripos($a_array[3],'}'))
			{
				$data[$k]['module']='百度文科';
				$data[$k]['url']=$a_array[5];
			}
			else
			{
				$data[$k]['module']='百度搜索';

				$data[$k]['url']=$a_array[3];
			}

		}
		//$limit=$k;
		if(empty($count))
		{
			$count=$k+1;
		}
	}
	else if($search=='wb')
	{
		$data=array();
		$url='http://search.sina.com.cn/?c=news&from=index&q='.urlencode(iconv('UTF-8', 'gb2312', $keyword)).'&page='.$page;
		$html = file_get_contents($url);
		$html= iconv("GBK","utf-8",$html)."<br>";
		preg_match_all('/\<h2\>(.*?)\<\/h2\>/',$html,$html_h2arr);
		foreach($html_h2arr[0] as $k => $v)
		{
			$v_html_arr=explode('"',$v);
			$data[$k]['title']=str_replace('>','',strip_tags($v_html_arr[4]));
			$data[$k]['account']=str_replace('>','',strip_tags($v_html_arr[6]));
			$data[$k]['module']='新浪新闻';
			$data[$k]['url']=strip_tags($v_html_arr[1]);

		}
	}
	else
	{

		//用户搜索
		$sql="SELECT uid as id,username,linkman as title,dateline,shares,visitor,subscribes FROM ".$tablepre."members  WHERE linkman like '%".$keyword."%' OR username like '%".$keyword."%' LIMIT $pagestart,$limit";
		$info['members'] = $db->fetchAssocArrBySql($sql);
		//模块搜索
		$count = $db->fetchOneBySql(countsql($sql));
		$system_key = array_merge($system_key,array('channel'));  // 加入微站搜索
		$system_key =array_delete_value($system_key,'members');
		foreach($system_key as $key => $value)
		{
			$variable = $value.'sys';
			$variable = $$variable;
			if(!$variable['show'] && $value!='channel')
			{
				continue;
			}
			if ($value == 'channel')
			{
				$sql="SELECT id,title,dateline,views,shares,visitor,subscribes FROM ".$tablepre."channel  WHERE (content like '%".$keyword."%' or title like '%".$keyword."%' ) AND moderate=1 LIMIT $pagestart,$limit";
			}
			else
			{
				if ($value=='news')
				{
					$sql="SELECT a.id,title,dateline,views,shares,visitor,subscribes FROM ".$tablepre.$value." a LEFT JOIN {$tablepre}{$value}content b ON a.id=b.id WHERE 1 $searchsql_n AND (title like '%".$keyword."%' OR content like '%".$keyword."%') and moderate=1 ORDER BY a.id DESC  limit $pagestart,$limit";
				}
				else
				{
					
					$chars = explode(',',$variable['char_setting']);
					if(in_array('title',$chars) && in_array('dateline',$chars) && in_array('views',$chars) && in_array('shares',$chars)&& in_array('visitor',$chars)&& in_array('subscribes',$chars) )
					{
						if(in_array('content',$chars))
						{
							$sql="SELECT  id,title,dateline,views,shares,visitor,subscribes from ".$tablepre.$value." WHERE 1 $searchsql AND (title like '%".$keyword."%' OR content like '%".$keyword."%')  ORDER BY id DESC  limit $pagestart,$limit";
						}
						else
						{
							$sql="SELECT  id,title,dateline,views,shares,visitor,subscribes from ".$tablepre.$value." WHERE 1 $searchsql AND (title like '%".$keyword."%')  ORDER BY id DESC  limit $pagestart,$limit";
						}
					}
					else
					{
						continue;
					}
				}
			}
			$info[$value]=$db->fetchAssocArrBySql($sql);
			$count += $db->fetchOneBySql(countsql($sql));
		}


		//消息搜索
		$sql="SELECT  id,content as title,dateline FROM ".$tablepre."mpmsg  WHERE content like '%".$keyword."%' limit $pagestart,$limit";
		$info['mpmsg'] = $db->fetchAssocArrBySql($sql);
		$count += $db->fetchOneBySql(countsql($sql));
		if(!empty($info))
		{
			foreach($info as $key=> $value)
			{
				if(!empty($value)  && is_array($value))
				{
					foreach($value as $k=> $v)
					{
						$data[] = array('id'=>$v['id'],'title'=>strip_tags($v['title']),'dateline'=>$v['dateline'],'shares'=>$v['shares'],'visitor'=>$v['visitor'],'module'=> $key,'views'=>$v['views'],'subscribes'=>$v['subscribes']);
					}
				}
			}
		}

	}

}
else
{
	$data = array();
}
$char = array('title','module','dateline','shares','visitor','views');