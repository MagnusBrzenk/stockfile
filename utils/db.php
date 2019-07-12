<?php

namespace db;

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv;

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
    static $STOCKFILE_SECRET_AUTH_KEY;
    static $STOCKFILE_ADMIN_USERNAME;
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
        self::$STOCKFILE_SECRET_AUTH_KEY = getenv('STOCKFILE_SECRET_AUTH_KEY');
        self::$STOCKFILE_ADMIN_USERNAME = getenv('STOCKFILE_ADMIN_USERNAME');

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

    // Check that admin username is one of the user names
    if (!in_array(STOCKFILE_CONFIG::$STOCKFILE_ADMIN_USERNAME, STOCKFILE_CONFIG::$STOCKFILE_USER_NAMES)) throw new \Error('Admin username is NOT in list of users!');

    // Ensure files table exists
    $QUERY = "CREATE TABLE IF NOT EXISTS files (
        file_name VARCHAR(16) NOT NULL UNIQUE PRIMARY KEY,
        thumb_linked_path VARCHAR(250) NOT NULL UNIQUE,
        owner_user_name VARCHAR(20) NOT NULL,
        exif_created DATETIME DEFAULT NULL,
        record_created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

    $query_result = mysqli_query($CONN, $QUERY);

    return;
}
setupDB();


// function addFileToDB($file_name, $file_date)
// {
//     $CONN = STOCKFILE_CONFIG::getConnection();

//     $QUERY = "INSERT INTO files (file_name, file_date)
//         VALUES('{$file_name}', '{$file_date}') ON DUPLICATE KEY
//         UPDATE file_name='{$file_name}', file_date='{$file_date}'; ";

//     echo "<h3>ADD FILE RESULT</h3>";

//     echo "<hr>" . $QUERY . "<hr>";

//     $query_result = mysqli_query($CONN, $QUERY);
//     print_r($query_result);
//     echo "<hr>";
// }


// function getFileData($file_name = "")
// {
//     $CONN = STOCKFILE_CONFIG::getConnection();

//     $QUERY = "SELECT * FROM files ";
//     if (!!$file_name) $QUERY .= " WHERE file_name='" . $file_name . "';";

//     $query_result = mysqli_query($CONN, $QUERY);

//     // echo "<h3>RETRIEVED FILE DATA</h3>";

//     $file_data = array();
//     while ($row = mysqli_fetch_array($query_result)) {
//         $file_data[] = $row;
//     }
//     // echo "file_data: <br>";
//     // print_r($file_data);
//     // echo ">>>" . !!$file_data . "<<< <br>";
//     // if (!$file_data) echo "<h5>XXX</h5>";
//     // echo "<hr>";
//     return $file_data;
// }
