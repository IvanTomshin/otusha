<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_nocache.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_header_rest.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_core.php";

function view(&$param)
{
        header('Location: about.php');
        exit();
}

function create(&$param)
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

    $_values = "";
    $_direction = 0;
    foreach ($param['data'] as $idx => $row) {
        if ($idx == "msg")
            $_values = htmlspecialchars($row);
        if ($idx == "direction")
            $_direction = (int)($row);
    }

    if ($_values == "") {
        header('Location: about.php?msg=not%20message');
        exit();
    }

    unset($param['data']);

    $sql = "insert into public.dialogs (from_id , to_id, msg, direction ) values ( $auth_user_id, $_to_id, '".$_values."', $_direction);";
    $result = pg_query($GLOBALS['db_postgresql_conn_citus'], $sql);
    if ($result)
        $param["success"] = true;

    $param["message"] = $sql;
}


