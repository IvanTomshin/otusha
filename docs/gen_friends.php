<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "../_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "../common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "../common/_nocache.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "../common/_header_rest.php";
set_time_limit(6000);

    $sql = "select id from public.users where deleted=0 order by id asc";
    $result = pg_query($GLOBALS['db_postgresql_conn_r1'], $sql);
    if ($result) {
        while ( $_users_data = pg_fetch_array($result, null, PGSQL_ASSOC ) )
            pg_query($GLOBALS['db_postgresql_conn_w'], "insert into friends (user_id, friend_id) select ".(int)$_users_data['id'].", id from users order by random() limit " . (int)rand(0, 20) );
    }




