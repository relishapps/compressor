<?php
require_once '../vendor/autoload.php';

use RelishMedia\Compressor;

// Change this to your web server root
define('COMPRESSOR_ROOT', dirname(dirname(__DIR__)));

// Path to assets dir, relative to `COMPRESSOR_ROOT`
define('COMPRESSOR_URL', '/compressor/examples/');
?>

<head>
    <?php Compressor::start() ?>
    <link rel="stylesheet" href="/compressor/examples/css/file1.css">
    <link rel="stylesheet" href="/compressor/examples/css/file2.css">
    <script src="/compressor/examples/js/file1.js"></script>
    <script src="/compressor/examples/js/file2.js"></script>
    <?php Compressor::stop() ?>
</head>
