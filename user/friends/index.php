<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_nocache.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_header_rest.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_core.php";

function view(&$param)
{
    $user_id = (int)$_GET['user_id'];

    if ($user_id == 0) {
        exit();
    }

    $_friends_data = array();
    $sql = "select user_id from friends where friend_id = $user_id";
    $result = pg_query($GLOBALS['db_postgresql_conn_r1'], $sql);
    if ($result) {
        while ($_post_data = pg_fetch_array($result, null, PGSQL_ASSOC))
            $_friends_data[] = $_post_data;
    }
    $param["success"] = true;
    $param["data"] = $_friends_data;
    $param["message"] = $sql;
}


