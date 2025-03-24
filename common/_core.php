<?php

$method = $_SERVER["REQUEST_METHOD"];
$id = null;
$param["success"] = false;
$param["message"] = '';
$param["data"] = array();
$param["data"] = preparedata($method);
$_headers = getallheaders();
if ($_headers['Token'] > "")
    $param["token"] = substr(trim(htmlspecialchars($_headers['Token'])), 0, 36);

switch ($method) {
    case 'GET':
        view($param);
        break;
    case 'POST':
        create($param);
        break;
    case 'PUT':
        update($id, $param);
        break;
    case 'DELETE':
        delete($id);
        break;
}
echo to_json($param);


function preparedata($method)
{
    global $id;
    $params = array();
    if ($method == 'PUT' or $method == 'POST') {   // <-- Have to jump through hoops to get PUT data
        $raw = '';
        $httpContent = fopen('php://input', 'r');
        while ($kb = fread($httpContent, 1024)) {
            $raw .= $kb;
        }
        fclose($httpContent);
        parse_str($raw, $params);
    } else {
        foreach ($_REQUEST as $idx => $row) {
            $params[$idx] = stripslashes($row);
        }
    }

    if (isset($_SERVER["PATH_INFO"])) {
        $cai = '/^\/([a-z]+\w)\/([a-z]+\w)\/([0-9]+)$/';  // /controller/action/id
        $ca = '/^\/([a-z]+\w)\/([a-z]+)$/';              // /controller/action
        $ci = '/^\/([a-z]+\w)\/([0-9]+)$/';               // /controller/id
        $c = '/^\/([a-z]+\w)$/';                             // /controller
        $i = '/^\/([0-9]+)$/';                             // /id
        $matches = array();
        if (preg_match($i, $_SERVER["PATH_INFO"], $matches)) {
            $id = $matches[1];
        }
    }


    return $params;
}

function to_json($param)
{
    return json_encode(array(
        'success' => $param["success"],
        'message' => $param["message"],
        'data' => $param["data"]
    ), JSON_UNESCAPED_UNICODE);
}

?>