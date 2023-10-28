<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_GET['path']) && !empty($_GET['path'])) {
    $path = substr($_GET['path'], 0, 6) == "files/" ? $_GET['path'] : 'files/' . $_GET['path'];

    $extension = pathinfo($_GET['path'], PATHINFO_EXTENSION);

    $contents = file_get_contents($path);

    if ($contents === false) {
        http_response_code(500);
        exit();
    }

    if ($extension == 'js') {
        header('Content-Type: application/javascript');
    } elseif ($extension == 'css') {
        header('Content-Type: text/css');
    } elseif ($extension == 'html') {
        header('Content-Type: text/html');
    } elseif ($extension == 'php') {
        header('Content-Type: text/php');
    } else {
        header('Content-Type: application/json');
    }

    echo $contents;
}

?>