<?php
$info = $rdb->redis->info();
eval ("\$showcontent = \"" . $tpl->get('redisinfo','admin'). "\";");
echo $showcontent;exit;