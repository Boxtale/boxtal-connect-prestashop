<?php

if (file_exists(__DIR__ . '/config/config.inc.php')) {
    require_once __DIR__ . '/config/config.inc.php';
}
require_once __DIR__ . '/modules/boxtalconnect/boxtalconnect.php';
$test = new BoxtalConnect();
foreach (scandir(__DIR__ . '/boxtal-unit-tests-helpers') as $filename) {
    $path = __DIR__ . '/boxtal-unit-tests-helpers/' . $filename;
    if (is_file($path)) {
        require $path;
    }
}
