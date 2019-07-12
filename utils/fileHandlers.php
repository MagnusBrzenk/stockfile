<?php

namespace file_handlers;

use db\STOCKFILE_CONFIG;

require_once __DIR__ . "/db.php";

$connection = STOCKFILE_CONFIG::getConnection();

/**
 * CRUD GET
 * Returns OBJECT of single row if $file_name is specified and found
 * Returns OBJECT of status-failure message if error occurs
 * Returns ARRAY of all rows if $file_name is NOT specified (table with no rows returns empty array)
 */
function get_files($file_name = null)
{
    global $connection;
    $query = "SELECT * FROM files";
    $query .= $file_name !== null ? " WHERE file_name='" . $file_name . "' LIMIT 1;" : ";";

    $response = array();
    $result = mysqli_query($connection, $query);

    if (!$result) {
        $response[] = array(
            'status' => 0,
            'status_message' => 'file retrieval failed.'
        );
    } else {
        while ($row = mysqli_fetch_array($result)) {
            $response[] = array(
                'file_name' => $row['file_name'],
                'exif_created' => $row['exif_created']
            );
        }
        $response = count($response) === 1 ? $response[0] : $response;
    }
    return  $response;
}


/**
 * CRUD POST
 * Takes items from POST body and creates/updates row(s) in files table
 * If one post fails to create/update, it does not affect the rest
 * Returns an ARRAY of status-message objects
 */
function insert_files($post_body_array)
{
    global $connection;
    $response = array();
    foreach ($post_body_array as $DATA) {

        // Extract params
        $file_name = isset($DATA['file_name']) ? $DATA['file_name'] : null;
        $thumb_linked_path = isset($DATA['thumb_linked_path']) ? $DATA['thumb_linked_path'] : null;
        $exif_created = isset($DATA['exif_created']) ? $DATA['exif_created'] : null;

        // Fail if not all params present
        if (!$file_name || !$thumb_linked_path || !$exif_created) {
            $response[] = ['status' => 0, 'status_message' => 'Insufficient file-creation data!'];
            continue;
        }

        // Execute insert/update query
        $query =
            "INSERT INTO files (file_name, thumb_linked_path, exif_created)
            VALUES('{$file_name}', '{$thumb_linked_path}', '{$exif_created}') ON DUPLICATE KEY
            UPDATE file_name='{$file_name}', thumb_linked_path='{$thumb_linked_path}', exif_created='{$exif_created}';";

        if (mysqli_query($connection, $query)) {
            $response[] = array(
                'status' => 1,
                'status_message' => 'file Added Successfully.'
            );
        } else {
            $response[] = array(
                'status' => 0,
                'status_message' => 'file Addition Failed for ' . $file_name
            );
        }
    }
    return  $response;
}

/**
 * CRUD PUT
 * Updates the field(s) of a row specified by file_name
 * Returns a status-message OBJECT
 */
function update_file($file_name, $put_body)
{
    global $connection;

    if (!$file_name) $file_name = isset($put_body["file_name"]) ? $put_body["file_name"] : null;
    $exif_created = isset($put_body["exif_created"]) ? $put_body["exif_created"] : null;
    $thumb_linked_path = isset($put_body["thumb_linked_path"]) ? $put_body["thumb_linked_path"] : null;

    if (!$file_name || !$thumb_linked_path || !$exif_created) {
        return array("message" =>  "Insufficient field data provided for update!");
    }

    $query = "  UPDATE files SET 
                exif_created='{$exif_created}',
                thumb_linked_path='{$thumb_linked_path}'
                WHERE file_name='" . $file_name . "';";

    $result = mysqli_query($connection, $query);
    $rowsAffected = explode(":", $connection->info)[1];

    if ($result) {
        $response = array(
            'status' => 1,
            'status_message' => 'Query executed successfully; ' .  $rowsAffected . "."
        );
    } else {
        $response = array(
            'status' => 0,
            'status_message' => 'File Updating Failed.'
        );
    }
    return  $response;
}

/**
 * CRUD DELETE
 * Deletes a row as specified by file_name in files table
 * Returns a status-message OBJECT
 */
function delete_file($file_name)
{
    global $connection;
    $query =  "DELETE FROM files WHERE file_name='" . $file_name . "';";

    if (mysqli_query($connection, $query)) {
        $response = array(
            'status' => 1,
            'status_message' => 'Row for ' . $file_name . ' deleted successfully.'
        );
    } else {
        $response = array(
            'status' => 0,
            'status_message' => 'Row for ' . $file_name . ' NOT deleted successfully.'
        );
    }
    return  $response;
}


//////////////////////////////////
//////////////////////////////////
// HANDY CRUD-WRAPPER FUNCTIONS //
//////////////////////////////////
//////////////////////////////////


/**
 * Simply checks if there is a row in the files table with name $file_name
 */
function isFileInDB($file_name, $throw_errors = true)
{
    if (!$file_name) throw new \Error("isFileInDB requires truthy \$file_name");

    $data = get_files($file_name);

    if (!!$throw_errors && isset($data['status']) && $data['status'] === 0) throw new Error('DB Error :(');

    if (count($data) === 0) return false;

    return true;
}
