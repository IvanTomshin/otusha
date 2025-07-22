<?php

$_title = "Test LUA";

require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/head.php";

$auth_user_id = (int)rand(1, 1000);
$count_key = 1000;
$count_data = 1000;

// $redis = new Redis();
// $redis->connect($GLOBALS['redis_conn'], 6379);

$sql = "select ROW_NUMBER() OVER (ORDER BY P.id desc) AS row_number, P.user_id, P.dt, P.id, P.msg from public.posts P order by P.id desc limit $count_data";
$_cache_Key = $auth_user_id . ":12345";
$result = pg_query($GLOBALS['db_postgresql_conn_w'], $sql);


echo "<p>Key: " . $count_key." | Data: ".$count_data."</p>";

$_posts_data = array();
if ($result) {
    while ($_post_data = pg_fetch_array($result, null, PGSQL_ASSOC))
        $_posts_data[] = $_post_data;
}

for ($i = 0; $i < $count_key; $i++) {
    $redis->set($_cache_Key, json_encode($_posts_data));
    $redis->expire($_cache_Key, 600);
}


$start = microtime(true);
while ($keys = $GLOBALS['redis']->scan($iterator, $auth_user_id . ":*"))
    if ($keys !== false)
        $GLOBALS['redis']->del($keys);

$duration = (microtime(true) - $start) * 1000;
printf("%-30s: %.4f ms<br>", "PHP", $duration);

for ($i = 0; $i < $count_key; $i++) {
    $redis->set($_cache_Key, json_encode($_posts_data));
    $redis->expire($_cache_Key, 600);
}


$start = microtime(true);
$redis->rawCommand('FCALL', 'delete_user_keys', 0, $auth_user_id . ':');
$duration = (microtime(true) - $start) * 1000;
printf("%-30s: %.4f ms<br>", "LUA", $duration);

?>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/common/footer.php"; ?>
