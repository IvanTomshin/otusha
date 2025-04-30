<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_nocache.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_header_rest.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_core.php";

function view(&$param)
{

    if (empty($param['data'])) {
        header('Location: about.php');
        exit();
    }

    $user_id = (int)$_GET['user_id'];
    $_token = $param["token"];
    unset($param['data']);
    $auth_user_id = 0;

    $sql = "select token from public.users where id = $user_id and deleted=0 limit 1;";
    $result = pg_query($GLOBALS['db_postgresql_conn_r1'], $sql);
    if ($result) {
        $row = pg_fetch_array($result);
        if ($_token == $row['token'])
            $auth_user_id = $user_id;
    }

    if ($auth_user_id == 0) {
        header('Location: about.php?msg=not%20auth');
        exit();
    }

    $_users_data = array();
    $sql = "select * from public.users_data where id = $auth_user_id;";
    $result = pg_query($GLOBALS['db_postgresql_conn_r1'], $sql);
    if ($result) {
        $_users_data = pg_fetch_array($result, 0, PGSQL_ASSOC );
        $param["success"] = true;
    }
    $param["data"] = $_users_data;
    $param["message"] = $sql;
}

?>

