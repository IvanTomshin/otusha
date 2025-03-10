<?php
error_reporting(E_ALL & ~E_NOTICE & ~ E_WARNING);
ini_set("display_errors", "on");

$_db_postgresql_conn = pg_connect("host=$_db_host dbname=$_db_name user=$_db_username password=$_db_password");
?>