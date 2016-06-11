<?php
require_once 'compressor.php';
?>

<head>
    <?php Compressor::start() ?>
    <link rel="stylesheet" href="css/file1.css">
    <link rel="stylesheet" href="css/file2.css">
    <script src="js/file1.js"></script>
    <script src="js/file2.js"></script>
    <?php Compressor::stop() ?>
</head>
