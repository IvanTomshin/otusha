<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_nocache.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_header_rest.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_core.php";

function view(&$param)
{
    $_where = "";
    foreach ($param['data'] as $idx => $row) {
        $_where .= " and " . htmlspecialchars($idx) . " like '%" . htmlspecialchars($row) . "%'";
	}

    $sql = "select * from public.users_data where 1=1 $_where ;";
    $result = pg_query($GLOBALS['db_postgresql_conn_r2'], $sql);
    $_res_row = array();
    if ($result) {
        while ( $_users_data = pg_fetch_array($result, null, PGSQL_ASSOC ) ) {
	    $_res_row[] = $_users_data;
	}
        $param["success"] = true;
	$param["data"] = $_res_row;
    }
    $param["message"] = $sql;
}
?>

