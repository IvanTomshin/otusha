<?php

$_title = "User login form";

require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/head.php";
?>


<h1>User login form</h1>

<form method="GET" action="/login">
    <label>Login:<input name="login"></label><br><br>
    <label>Password:<input name="password"></label><br><br>
    <input type="submit">
</form>


<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/common/footer.php"; ?>
