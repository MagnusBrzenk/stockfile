
<?php

use db\STOCKFILE_CONFIG;
// use thumbnailFunctions\getTh

require_once getcwd() . "/db.php";
require_once getcwd() . "/thumbnailFunctions.php";

// require_once getcwd() . '/addFileToDB.php';

function processFiles($path_to_original_files)
{
    // Get all original media files
    $media_files = scandir($path_to_original_files);
    sort($media_files);
    reset($media_files); # Resets array pointer

    echo "<h1> Thumbnails</h1>";

    // Extract EXIF date and add to DB
    foreach ($media_files as $file_name) {

        // Skip if file is dir
        if (is_dir($path_to_original_files . "/" . $file_name)) continue;

        // Create 'real' and 'symlinked' thumbnail if not existing

        // Test if (i) thumbnail already exists for this file, and (ii) if the file is represented in DB

        // Create thumbnail and ensure file is represented in DB
        $tentative_thumbnail_path = getcwd() . "/" . ".thumbnails_generated/" . thumbnailfunctions\getThumbFileName($file_name);
        $file_data = db\getFileData($file_name);
        $isThumbnailExisting = realpath($tentative_thumbnail_path);
        $isFileInDB = !!$file_data;
        if (!$isThumbnailExisting || !$isFileInDB) {
            echo "<h3>PROCESSING FILE " . $file_name . "</h3>";
            $file_date_yyyymmdd = getDateFromMediaExif($path_to_original_files . "/" . $file_name);
            $thumbnail_paths = generateThumbNail($file_name, $path_to_original_files, $file_date_yyyymmdd);
            echo "<hr>";
            print_r($thumbnail_paths);
            db\addFileToDB($file_name, $file_date_yyyymmdd);
        }
    }
}
processFiles(STOCKFILE_CONFIG::$STOCKFILE_DATA_DIRS[0]);



/**
 * Determines path to sym-linked thumbnail image within nested browsing dir; appends '.jpg' if file is video
 * E.g. ('IMG_1000.MOV','2019:01:01') => '/home/magnus/stockfile/.thumbnails_linked/2019/01/img_1000.mov.jpg'
 */
function getThumbLinkedPath($file_name, $file_yyyymmdd)
{
    // Create path based on $file_date 'yyyy:mm:dd'
    $file_date_parts = explode(':', $file_yyyymmdd);
    $year = $file_date_parts[0];
    $month = $file_date_parts[1];
    $thumb_path = getcwd() . "/.thumbnails_linked/" . $year . "/" . $month . "/" . thumbnailfunctions\getThumbFileName($file_name);

    // Ensure dirs exist for this path
    $path = getcwd() . "/.thumbnails_linked/" . $year . "/" . $month;
    if (!realpath($path)) shell_exec('mkdir -p ' . $path);

    return $thumb_path;
}



/**
 * Extracts EXIF data from file and returns date as string 'yyyy:mm:dd'
 * E.g. '/home/magnus/mydata/IMG_1000.MOV' => '2019:01:01'
 * Requires exiftool (Mac: `brew install exiftool`, Debian: `sudo apt install libimage-exiftool-perl`)
 */
function getDateFromMediaExif($file_path)
{
    // Set grep pattern based on file type
    $date_pattern = "Date/Time Original";   // jpg default
    if (strpos(strtolower($file_path), '.mov')) $date_pattern = 'Creation Date';
    if (strpos(strtolower($file_path), '.m4v')) $date_pattern = 'Content Create Date';

    // Extract date with exiftool
    $cmd = "
        export PATH=\$PATH:/usr/local/bin;
        exiftool " . $file_path . " |
        grep -m1 '" . $date_pattern . "'
    ";
    $file_date_line = shell_exec($cmd);
    if ($file_date_line == "") throw new Error("Can't extract EXIF date from file " . $file_path);

    // Parse returned string and assemble 'yyyy:mm' string with whitespace stripped out
    $file_date_parts = explode(":", $file_date_line);
    $file_yyyymmdd =  preg_replace('/\s+/', '', $file_date_parts[1] . ":" . $file_date_parts[2] . ":" . $file_date_parts[3]);

    return $file_yyyymmdd;
}


/**
 * Function to generate thumbnail and a symlink to it that is nested in a dir marked by yyyy/mm
 * 1. Use imagemagick convert to generate a new JPG in flat dir .thumbnails_generated
 * 2. Create a symlink from newly generated JPG within nested dir in .thumbnails_linked
 * Returns location of 'real' and 'symlinked' files generated
 */
function generateThumbNail($file_name, $path_to_original_files, $file_date_yyyymmdd)
{
    // 0. Setup Params
    $thumb_size = 400;
    $file_path = $path_to_original_files . "/" . $file_name;
    // 0.1. Path to 'real' generated thumbnail
    $thumb_generated_path = getcwd() . "/.thumbnails_generated/" . thumbnailfunctions\getThumbFileName($file_name);
    // 0.2. Path to symlinked thumbnail
    $thumb_linked_path = getThumbLinkedPath($file_name, $file_date_yyyymmdd);
    // 0.3 Return locations of new files
    $thumbnail_paths = ["real" => $thumb_generated_path, "symlinked" => $thumb_linked_path];


    // 1. Imagemagick convert original media file to thumbnail jpg; Mac needs PATH augment
    $cmd = "
        export PATH=\$PATH:/usr/local/bin;
        convert -define jpeg:size=400x400 " .
        $file_path . "[1] -thumbnail " .
        $thumb_size . "x" . $thumb_size .
        "^ -gravity center -extent " .
        $thumb_size . "x" . $thumb_size .
        " " . $thumb_generated_path;
    $rs0 = shell_exec($cmd);

    // 2. Create symlinked thumbnails in nested dirs
    $cmd = "ln -fs " . $thumb_generated_path . " " . $thumb_linked_path;
    $rs1 = shell_exec($cmd);

    // Finish
    if (!$rs0 || !$rs1) return $thumbnail_paths;
    throw new Error("One of the shell processes failed!");
}
