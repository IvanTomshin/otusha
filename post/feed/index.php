<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_nocache.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_header_rest.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_core.php";

function view(&$param)
{
    $auth_user_id = 0;
    $_token = $param["token"];

    /* Если не задан токен пользователя - отдадим ленту случайного пользователя  */
    if (empty($_token)) {
        $rnd_id = (int)rand(1, 200000);
        $rnd_id = (int)rand(1, 1000);
//        $rnd_id = 1;

        $sql = "select id, token from public.users where id in (select user_id from users_rnd where id = $rnd_id )";
        $result = pg_query($GLOBALS['db_postgresql_conn_r2'], $sql);

        if ($result) {
            $row = pg_fetch_array($result);
            $_token = $row['token'];
            $auth_user_id = (int)$row['id'];
        }
    } else {


        $sql = "select id from public.users where token = '" . $_token . "' and deleted=0 limit 1;";
        $result = pg_query($GLOBALS['db_postgresql_conn_r1'], $sql);
        if ($result) {
            $row = pg_fetch_array($result);
            $auth_user_id = (int)$row['id'];
        }
    }

    if ($auth_user_id == 0) {
        header('Location: about.php?msg=not%20user');
        exit();
    }

//    pg_query($GLOBALS['db_postgresql_conn_w'], 'LISTEN friend_and_post_updated_'.$auth_user_id.';');
//    $notify = pg_get_notify($GLOBALS['db_postgresql_conn_w']);

    $_page = (int)$_GET['page'];
//    $_page = 0;
    $_cache_Key = $auth_user_id . ":" . $_page;

    $_posts_data = array();
    if (1 == 2 and $GLOBALS['redis']->exists($_cache_Key)) {
        // Получение данных из кэша
        $_posts_data = json_decode($GLOBALS['redis']->get($_cache_Key));
        $param["message"] = "In cache!" ;
    } else {
//        $sql = "select * from public.posts where user_id in ( select friend_id from friends where user_id = $auth_user_id ) order by id desc limit 10 offset $_page";
        $sql = "
        select ROW_NUMBER() OVER (ORDER BY P.id desc) AS row_number, P.user_id, P.dt, P.id, P.msg, U.first_name, U.middle_name, U.last_activity from public.posts P 
        join public.users_data U on U.id = P.user_id
        join (select count(*), user_id from public.posts group by user_id) S on S.user_id = P.user_id
        where P.user_id in ( select friend_id from friends where user_id = $auth_user_id ) order by P.id desc limit 10 offset ".(10*$_page);


        $result = pg_query($GLOBALS['db_postgresql_conn_w'], $sql);
        if ($result) {
            while ($_post_data = pg_fetch_array($result, null, PGSQL_ASSOC))
                $_posts_data[] = $_post_data;
            // Запись в кеш
            $GLOBALS['redis']->set($_cache_Key, json_encode($_posts_data));
            $GLOBALS['redis']->expire($_cache_Key, 600);
            $param["message"] = $sql;
        }
    }
    $param["success"] = true;
    $param["data"] = $_posts_data;
}


