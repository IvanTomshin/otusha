<?php

$_title = "Messages post form";

require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/head.php";
?>


<h1>Form of friendship</h1>

<form method="POST" action="/post/create/">
    <label>Message:<input name="msg"></label><br><br>
    <input type="submit">
</form>


<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/common/footer.php"; ?>
