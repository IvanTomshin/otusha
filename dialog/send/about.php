<?php

$_title = "User dialogs form";

require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/head.php";
?>


<h1>Form of dialogs</h1>

<form method="POST" action="/dialog/send/">
    <label>Message:<input name="msg"></label><br><br>
    <input type="submit">
</form>


<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/common/footer.php"; ?>
