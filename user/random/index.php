<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_nocache.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_header_rest.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_core.php";

function view(&$param)
{
    $sql = "select id, token from public.users where deleted = 0 order by random() limit 1;";
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

