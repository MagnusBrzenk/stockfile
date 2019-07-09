<?php

namespace addfiletodb;

use db\STOCKFILE_CONFIG;

require_once getcwd() . '/db.php';



function addFileToDB($file_name, $file_thumb, $file_date)
{
    $CONN = STOCKFILE_CONFIG::getConnection();

    // $QUERY = "CREATE TABLE IF NOT EXISTS files (
    //     file_id varchar(12) NOT NULL UNIQUE PRIMARY KEY,
    //     file_path varchar(350),
    //     file_date varchar(350)
    // ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

    $QUERY = "INSERT INTO files (file_name, file_thumb, file_date)
        VALUES('{$file_name}', '{$file_thumb}', '{$file_date}') ON DUPLICATE KEY
        UPDATE file_name='{$file_name}', file_thumb='{$file_thumb}', file_date='{$file_date}'; ";

    echo "<hr>" . $QUERY . "<hr>";

    $query_result = mysqli_query($CONN, $QUERY);
    echo "<hr>";
    print_r($query_result);
}
