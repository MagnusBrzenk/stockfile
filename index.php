<?php

require_once getcwd() . "/printAllThumbnails.php";

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <!-- <base href="stockfile/"> -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="styles.css">
    <!-- <style src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.4/css/bootstrap.css"></style> -->
    <title>Document</title>
</head>

<body>
    <h1>Hola Amigos!</h1>

    <?php
    // echo "Hello World";

    printAllThumbnails();
    ?>

</body>

</html>