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
    foreach ($param['data'] as $idx => $row) {
        if ($idx == "msg")
            $_values = htmlspecialchars($row);
    }

    if ($_values == "") {
        header('Location: about.php?msg=not%20message');
        exit();
    }

    unset($param['data']);

    $sql = "insert into public.posts (user_id , msg ) values ( $auth_user_id, '".$_values."');";
    $result = pg_query($GLOBALS['db_postgresql_conn_w'], $sql);
    if ($result)
        $param["success"] = true;

    $iterator = null;
    while ($keys = $GLOBALS['redis']->scan($iterator, $auth_user_id . ":*"))
        if ($keys !== false)
            $GLOBALS['redis']->del($keys);

    $param["message"] = $sql;
}


