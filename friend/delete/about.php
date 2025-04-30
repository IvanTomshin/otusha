<?php

$_title = "User friendship form";

require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/head.php";
?>


<h1>Form of friendship</h1>

<form method="POST" action="/friend/add/">
    <label>FriendID:<input name="friend_id"></label><br><br>
    <input type="submit">
</form>


<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/common/footer.php"; ?>
