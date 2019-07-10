<?php

require_once getcwd() . "/printAllThumbnails.php";

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <base href="stockfile">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.css">
    <link rel="stylesheet" href="static/styles.css">
    <title>Document</title>
    <script src="static/main.js"></script>
</head>

<body>

    <div class="top-bar">
        <h1> Stockfile </h1>
        <!-- <span style>Hola Amigos!</span> -->
        <div class="right-side-toggle-button" onClick="toggleSidePanel('right');"></div>
        <div class="left-side-toggle-button" onClick="toggleSidePanel('left');"></div>
    </div>


    <div class="grid-left-right-panel-container">
        <div id="left-panel">
            <ul>
                <li>A</li>
                <li>B</li>
                <li>C</li>
                <li>D</li>
            </ul>
        </div>
        <div class="grid-wrapper">
            <div class="grid">
                <?php
                printAllThumbnails();
                ?>
            </div>
        </div>
        <div id="right-panel">
            <div class="close-right-panel" onClick="closeRightSidePanel()">X</div>
            <img width="100%" style="width: 100%; height: 300px;" id="loaded-image" src="https://secureservercdn.net/104.238.71.140/3za.cd5.myftpupload.com/wp-content/uploads/2019/06/dog-landing-hero-lg.jpg" alt="">
            <input type="text">
            <input type="text">
        </div>
    </div>

</body>

</html>