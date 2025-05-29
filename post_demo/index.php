<?php

$_title = "User async post  form";
require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/head.php";
?>
<script src="/common/jquery-3.7.1.min.js"></script>

<h1>Async post</h1>

<?php
$_user_id = (int)$_GET['user_id'];
//$_token = htmlspecialchars($_GET['token']);
?>

<?php # Форма для получения случайного пользователя ?>
<form method="GET" action="index.php">
    <label>Random user id:<input name="user_id"></label><br><br>
    <!-- input type="hidden" name="token" //-->
    <input type="submit" value="Start new user">
</form>
<script>
    var rnd_user_id = 0, rnd_user_token = "";
    $(document).ready(function () {
        $.get("/user/random/", function (data) {
            rnd_user_id = data.data.user_id;
            rnd_user_token = data.data.token;
            $("form input[name='user_id']").val(rnd_user_id);
//            $("form input[name='token']").val(rnd_user_token);
        });
    });
</script>

<br><br><br><br>
<section class="about-me" id="friends_list"></section>
<hr>
<section class="about-me" id="send_msg"></section>
<hr>
<section class="about-me" id="post_list"></section>

<?php
if ($_user_id > 0) { ?>
    <script>
        _frieds = [];
        _user_id = <?php echo $_user_id?>;
//        _user_token = "<?php echo $_token ?>";

        function get_friends(user_id) {
            $.get("/user/friends/", {user_id: user_id}, function (data) {
                __frieds = [];
                $.each(data.data, function (key, value) {
                    __frieds.push(value.user_id);
                });
                _frieds = __frieds;
                _console.html(_console.html() + "<p>Parent list: " + _frieds.join(", ") + "</p>");
            });
        }

        function send_post_pg() {
            random_friends_id = _frieds[Math.floor(Math.random() * _frieds.length)];
            if (random_friends_id == 0) return;
            _send_stat.html("<p>Send post parent user " + random_friends_id + "... </p>");

            $.ajax({
                url: "/post/create_with_friend/",
                method: "POST",
                data: {
                    user_id: random_friends_id
                },
                success: function(response) {
                    _send_stat.html( "<p>Ответ " + response.message + "</p>");
                },
                error: function(xhr, status, error) {
                    _send_stat.html( "<p>❌ Ошибка " + error + "</p>");
                }
            });
        }











        function generateRandomText(length) {
            const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let randomText = '';
            for (let i = 0; i < length; i++) {
                const randomIndex = Math.floor(Math.random() * charset.length);
                randomText += charset[randomIndex];
            }
            return randomText;
        }

        var wsUri = "ws://localhost:8765";
        var websocket = null;

        function initWebSocket() {
            try {
                if (typeof MozWebSocket == 'function')
                    WebSocket = MozWebSocket;
                if (websocket && websocket.readyState == 1)
                    websocket.close();
                websocket = new WebSocket(wsUri);
                websocket.onopen = function (evt) {
                    _console.html(_console.html() + "<p>Websocket CONNECTED ... </p>");
                    websocket.send(JSON.stringify({user_id: "<?php echo $_user_id?>"}));
                };
                websocket.onclose = function (evt) {
                    _console.html(_console.html() + "<p>Websocket DISCONNECTED ... </p>");
                    websocket = null;
                    setTimeout(initWebSocket, 10000);
                };
                websocket.onmessage = function (evt) {
                    console.log(evt.data);
                    const msgObj = JSON.parse(evt.data);
                    _post_list.html(_post_list.html() + "User: " + msgObj.user_id + " [" +  msgObj.dt + "] Friend: " +  msgObj.friend_id + " = " +  msgObj.msg);
                };
                websocket.onerror = function (evt) {
                    _console.html(_console.html() + "<p>Websocket ERROR ... </p>");
                };
            } catch (exception) {
                _console.html(_console.html() + "<p>Websocket ALL ERROR ... </p>");
            }
        }

var _console, _post_list, _send_stat ;

        $(document).ready(function () {
            _console = $("#friends_list");
            _post_list = $("#post_list");
            _send_stat = $("#send_msg");
            initWebSocket();
            get_friends(_user_id);
            setInterval(send_post_pg, 2000);
        });

    </script>

<?php } ?>

<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/common/footer.php"; ?>
