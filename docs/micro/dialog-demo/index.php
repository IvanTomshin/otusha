<?php
$_title = "Users dialog async router";
require_once $_SERVER['DOCUMENT_ROOT'] . "/_config_db.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/_config.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/common/head.php";
?>
<script src="/common/jquery-3.7.1.min.js"></script>
<h1>Async router</h1>
<table width="100%">
    <tr>
        <td><label>From user id:<input name="from_user_id"></label></td>
        <td><label>to user id:<input name="to_user_id"></label></td>
    </tr>
</table>

<table width="100%">
    <tr>
        <td><input type="button" value="new user dest" onclick="new_user();"></td>
        <td><input type="button" value="send msg" onclick="send_msg();"></td>
        <td><input type="button" value="read msg" onclick="read_msg();"></td>
    </tr>
</table>

<br><br><br><br>
<section class="about-me" id="friends_list"></section>
<hr>
<section class="about-me" id="send_msg"></section>

<script>
    function new_user() {
        _to_user_id = Math.floor(Math.random() * 10000000);
        $("input[name='to_user_id']").val(_to_user_id);

    }

    function send_msg() {
        _msg = generateRandomText(32);
        _send_stat.html("<p>отправка от id " + _from_user_id + " пользователю id " + _to_user_id + ". Сообщение:" + _msg + "</p>");
        websocket.send(JSON.stringify({from_user_id: _from_user_id, to_user_id: _to_user_id, text: _msg}));
    }

    function read_msg() {
        _send_stat.html("<p>Чтение сообщения от id " + _from_user_id + " пользователю id " + _to_user_id + "</p>");
        websocket.send(JSON.stringify({from_user_id: _from_user_id, to_user_id: _to_user_id}));
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
                websocket.send(JSON.stringify({from_user_id: _from_user_id}));
            };
            websocket.onclose = function (evt) {
                _console.html(_console.html() + "<p>Websocket DISCONNECTED ... </p>");
                websocket = null;
                setTimeout(initWebSocket, 10000);
            };
            websocket.onmessage = function (evt) {
                console.log(evt.data);
                const msgObj = JSON.parse(evt.data);
                _post_list.html(_post_list.html() + "получено сообщение от: " + msgObj.from_user_id + " | " + msgObj.msg);
            };
            websocket.onerror = function (evt) {
                _console.html(_console.html() + "<p>Websocket ERROR ... </p>");
            };
        } catch (exception) {
            _console.html(_console.html() + "<p>Websocket ALL ERROR ... </p>");
        }
    }

    var _console, _post_list, _send_stat, _from_user_id;

    _from_user_id = Math.floor(Math.random() * 10000000);
    $("input[name='from_user_id']").val(_from_user_id);

    $(document).ready(function () {
        _console = $("#friends_list");
        _post_list = $("#post_list");
        _send_stat = $("#send_msg");
        initWebSocket();
    });

</script>


<?php require_once $_SERVER['DOCUMENT_ROOT'] . "/common/footer.php"; ?>
