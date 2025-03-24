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

    $_login = substr(trim(htmlspecialchars($param['data']['login'])), 0, 64);
    $_password = md5(substr(trim(htmlspecialchars($param['data']['password'])), 0, 64));

    $sql = "select id as user_id, token from public.users where deleted = 0 and login = '$_login' and password = '$_password' limit 1;";
    $param["message"] = $sql;

    $result = pg_query($GLOBALS['db_postgresql_conn'], $sql);
    unset($param['data']);
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

    if (!$param["success"])
        header('Location: about.php?msg=error');
}

?>

