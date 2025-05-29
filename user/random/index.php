<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_nocache.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_header_rest.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_core.php";

function view(&$param)
{
    $rnd_id = (int)rand(1,200000);
//    $sql = "select id, token from public.users where id in (select user_id from users_rnd where id = $rnd_id )";
    $sql = "select friend_id as id, '' as token from public.friends order by random() limit 1";
    $result = pg_query($GLOBALS['db_postgresql_conn_r2'], $sql);

    unset($param['data']);
    if ($result) {
        $row = pg_fetch_array($result);
        $res = array(
            "token" => $row['token'],
            "user_id" => $row['id']
        );
        $param["success"] = true;
        $param["data"] = $res;
    }
    $param["message"] = $sql;
}

?>

