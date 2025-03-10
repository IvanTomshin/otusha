<?php
$_db_username="c103814_otusha_all_exclusive_ru";
$_db_password="JaLyiFoxgimeg74";
$_db_host="postgres.c103814.h2";
$_db_name="c103814_otusha_all_exclusive_ru";

error_reporting(E_ALL & ~E_NOTICE & ~ E_WARNING);
ini_set("display_errors", "on");

$_db_postgresql_conn = pg_connect("host=$_db_host dbname=$_db_name user=$_db_username password=$_db_password");
?>