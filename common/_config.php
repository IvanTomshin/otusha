<?php
error_reporting(E_ALL & ~E_NOTICE & ~ E_WARNING);
ini_set("display_errors", "on");

$GLOBALS['db_postgresql_conn_w'] = pg_connect("host=$_db_host_w dbname=$_db_name user=$_db_username password=$_db_password");
$GLOBALS['db_postgresql_conn_r1'] = pg_connect("host=$_db_host_w dbname=$_db_name user=$_db_username password=$_db_password");
$GLOBALS['db_postgresql_conn_r2'] = pg_connect("host=$_db_host_w dbname=$_db_name user=$_db_username password=$_db_password");
// $GLOBALS['db_postgresql_conn_r1'] = pg_connect("host=$_db_host_r1 dbname=$_db_name user=$_db_username password=$_db_password");
// $GLOBALS['db_postgresql_conn_r2'] = pg_connect("host=$_db_host_r2 dbname=$_db_name user=$_db_username password=$_db_password");
$GLOBALS['redis_conn'] = $_redis_host;
?>