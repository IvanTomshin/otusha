<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_nocache.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_header_rest.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_core.php";

function create(&$param)
{
    if (empty($param['data'])) {
        header('Location: about.php?msg=error');
        exit();
    }

    $_fields = "";
    $_values = "";
    $_sep = "";

    foreach ($param['data'] as $idx => $row) {
        $_fields .= $_sep . htmlspecialchars($idx);
        $_values .= $_sep . "'" . ($idx == 'password' ? md5($row) : $row) . "'";
        $_sep = ",";
    }
    unset($param['data']);

    $sql = "insert into public.users ($_fields) values ($_values) RETURNING id as user_id, token;";
    $result = pg_query($GLOBALS['db_postgresql_conn'], $sql);
    $param["message"] = $sql;
    if ($result) {
        $row = pg_fetch_array($result);
        $res = array(
            "token" => $row['token'],
            "user_id" => $row['user_id']
        );
        if ($row['user_id'] > 0)
            $param["success"] = true;
        $param["data"] = $res;
    }
}


function Update($id, &$param)
{
    $user_id = (int)$_GET['user_id'];

    if ($user_id == 0) {
        header('Location: about.php?msg=error');
        exit();
    }

    $_fields = "";
    $_sep = "";

    foreach ($param['data'] as $idx => $row) {
        $_fields .= $_sep . htmlspecialchars($idx) . "='" . htmlspecialchars($row) . "'";
        $_sep = ",";
    }

    unset($param["data"]);

    if ($_fields != "") {
        $sql = "update public.users_data set $_fields where id=$user_id;";
        $param["message"] = $sql;
        $result = pg_query($GLOBALS['db_postgresql_conn'], $sql);
        if ($result)
            $param["success"] = true;
    }
}

?>