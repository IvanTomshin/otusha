<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "../_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "../common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "../common/_nocache.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "../common/_header_rest.php";
set_time_limit(6000);
/*
$sql = "select msg from public._pst;";
$result = pg_query($GLOBALS['db_postgresql_conn_r1'], $sql);
if ($result) {
    while ( $_users_data = pg_fetch_array($result, null, PGSQL_ASSOC ) )
        pg_query($GLOBALS['db_postgresql_conn_citus'], "insert into public._pst (msg) values ('" . $_users_data['msg'] . "');");
}

exit();
*/




    $sql = "select id from public.users where deleted=0 order by random()";
    $result = pg_query($GLOBALS['db_postgresql_conn_r1'], $sql);
    $_to_id = 1;
    $cnt=0;
    if ($result) {
        while ( $_users_data = pg_fetch_array($result, null, PGSQL_ASSOC ) ) {
            $_user_id = (int)$_users_data['id'];
            $sql1 = "insert into dialogs (from_id, to_id, msg, direction) select $_user_id, $_to_id, msg, random(0,1)::integer from _pst  order by random() limit 1000*random() ";
            pg_query($GLOBALS['db_postgresql_conn_citus'], $sql1);
            $_to_id = $_user_id;
 //           echo $sql1;
            $cnt++;
            echo "\r".$cnt;
        }
    }




