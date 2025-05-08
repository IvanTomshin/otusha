<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_nocache.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_header_rest.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_core.php";

function view(&$param)
{
    $_to_id = (int)$_GET['to_id'];

    if ( $_to_id == 0) {
        $sql = "select id from public.users where id in (select user_id from users_rnd where id = " . (int)rand(1, 200000).")";
        $result = pg_query($GLOBALS['db_postgresql_conn_r2'], $sql);
        if ($result) {
            $row = pg_fetch_array($result);
            $_to_id = (int)$row['id'];
        }
    }


    $_token = $param["token"];

    if (empty($_token)) {
        header('Location: about.php?msg=not%20auth');
        exit();
    }

    $auth_user_id = 0;

    $sql = "select id from public.users where token = '" . $_token . "' and deleted=0 limit 1;";
    $result = pg_query($GLOBALS['db_postgresql_conn_r1'], $sql);
    if ($result) {
        $row = pg_fetch_array($result);
        $auth_user_id = (int)$row['id'];
    }

    if ($auth_user_id == 0) {
        header('Location: about.php?msg=not%20user');
        exit();
    }



    $sql = "select * from public.dialogs where from_id = $auth_user_id and to_id = $_to_id order by dt desc";

    $result = pg_query($GLOBALS['db_postgresql_conn_citus'], $sql);
    if ($result) {
        while ($_post_data = pg_fetch_array($result, null, PGSQL_ASSOC))
            $_posts_data[] = $_post_data;

        $param["message"] = $sql;
        $param["success"] = true;
        $param["data"] = $_posts_data;
    }

}


