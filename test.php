<?php

$redis = new \Redis();
$redis->connect('bylonaspetredis01.redis.cache.windows.net', 6379);
$redis->set('foo', 'bar');
echo $redis->get('foo');
