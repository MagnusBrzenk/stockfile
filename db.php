<?php

namespace db;

use Dotenv;

// Setup .env read
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

// Class containing configurations
class STOCKFILE_CONFIG
{
    static $STOCKFILE_URL;
    static $STOCKFILE_USER_NAMES;
    static $STOCKFILE_DATA_DIRS;
    static $STOCKFILE_USER_PASSWORDS;
    static $STOCKFILE_DB_HOST;
    static $STOCKFILE_DB_NAME;
    static $STOCKFILE_DB_USER;
    static $STOCKFILE_DB_PASSWORD;
    static $MYSQLCONN;

    static function getConnection()
    {
        // Explode .env vars with multiple comma-separated entries into arrays
        self::$STOCKFILE_USER_NAMES = explode(',', getenv('STOCKFILE_USER_NAMES'));
        self::$STOCKFILE_USER_PASSWORDS = explode(',', getenv('STOCKFILE_USER_PASSWORDS'));
        self::$STOCKFILE_DATA_DIRS = explode(',', getenv('STOCKFILE_DATA_DIRS'));

        // Simple .env variables
        self::$STOCKFILE_URL = getenv('STOCKFILE_URL');
        self::$STOCKFILE_DB_HOST = getenv('STOCKFILE_DB_HOST');
        self::$STOCKFILE_DB_NAME = getenv('STOCKFILE_DB_NAME');
        self::$STOCKFILE_DB_USER = getenv('STOCKFILE_DB_USER');
        self::$STOCKFILE_DB_PASSWORD = getenv('STOCKFILE_DB_PASSWORD');

        if (!self::$MYSQLCONN) {
            self::$MYSQLCONN = mysqli_connect(
                self::$STOCKFILE_DB_HOST,
                self::$STOCKFILE_DB_USER,
                self::$STOCKFILE_DB_PASSWORD,
                self::$STOCKFILE_DB_NAME
            );
        }
        return self::$MYSQLCONN;
    }
}


// Create tables if not existing
function setupDB()
{
    // Initialize mysql connection
    $CONN = STOCKFILE_CONFIG::getConnection();

    $QUERY = "CREATE TABLE IF NOT EXISTS files (
        file_name varchar(16) NOT NULL UNIQUE PRIMARY KEY,
        file_date varchar(350)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

    // echo "<hr>" . $QUERY . "<hr>";

    $query_result = mysqli_query($CONN, $QUERY);
    // echo "<hr>";
    // print_r($query_result);
    return;
}
setupDB();


function addFileToDB($file_name, $file_date)
{
    $CONN = STOCKFILE_CONFIG::getConnection();

    $QUERY = "INSERT INTO files (file_name, file_date)
        VALUES('{$file_name}', '{$file_date}') ON DUPLICATE KEY
        UPDATE file_name='{$file_name}', file_date='{$file_date}'; ";

    echo "<h3>ADD FILE RESULT</h3>";

    echo "<hr>" . $QUERY . "<hr>";

    $query_result = mysqli_query($CONN, $QUERY);
    print_r($query_result);
    echo "<hr>";
}


function getFileData($file_name = "")
{
    $CONN = STOCKFILE_CONFIG::getConnection();

    $QUERY = "SELECT * FROM files ";
    if (!!$file_name) $QUERY .= " WHERE file_name='" . $file_name . "';";

    $query_result = mysqli_query($CONN, $QUERY);

    // echo "<h3>RETRIEVED FILE DATA</h3>";

    $file_data = array();
    while ($row = mysqli_fetch_array($query_result)) {
        $file_data[] = $row;
    }
    // echo "file_data: <br>";
    // print_r($file_data);
    // echo ">>>" . !!$file_data . "<<< <br>";
    // if (!$file_data) echo "<h5>XXX</h5>";
    // echo "<hr>";
    return $file_data;
}
