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
    foreach ($param['data'] as $idx => $row) {
        if ($idx == "user_id")
            $user_id = (int)($row);
    }

    if ($user_id == 0) {
        header('Location: about.php?msg=not%20user');
        exit();
    }

    unset($param['data']);

    $sql = "insert into public.posts (user_id , msg) select $user_id, msg from public._pst order by random() limit 1;";
    $result = pg_query($GLOBALS['db_postgresql_conn_w'], $sql);
    if ($result)
        $param["success"] = true;

    $param["message"] = $sql;
}


