
<?php

use db\STOCKFILE_CONFIG;

require_once __DIR__ . "/db.php";
require_once __DIR__ . '/fileHandlers.php';
require_once __DIR__ . "/thumbnailFunctions.php";


function processFiles()
{
    foreach (STOCKFILE_CONFIG::$STOCKFILE_USER_NAMES as $user_name) {
        $path_to_original_files = __DIR__ . "/../data/" . $user_name;
        if (!realpath($path_to_original_files)) shell_exec("mkdir -p " . $path_to_original_files);
        processFile($path_to_original_files, $user_name, true);
    }
}
processFiles();


function processFile($path_to_original_files, $user_name, $isForceRecompute = false)
{

    // Get all original media files
    if (!realpath($path_to_original_files)) throw new Error("" . $path_to_original_files . " is not a real path!!!");
    $media_files = scandir($path_to_original_files);
    sort($media_files);
    reset($media_files); # Resets array pointer

    echo " <hr><h1> Thumbnail Processing For " . strtoupper($user_name) . "</h1> \n <hr>";

    // Extract EXIF date and add to DB
    foreach ($media_files as $file_name) {

        // Skip if file is dir
        if (is_dir($path_to_original_files . "/" . $file_name)) continue;

        // Tests for (re)processing media file
        $tentative_thumbnail_path = __DIR__ . "/../" . ".thumbnails_generated/" . thumbnailfunctions\getThumbnailFileName($file_name);
        $isThumbnailExisting = realpath($tentative_thumbnail_path);
        $isFileInDB = file_handlers\isFileInDB($file_name);

        if (!$isThumbnailExisting || !$isFileInDB || $isForceRecompute) {
            echo "<h3>PROCESSING FILE " . $file_name . "</h3>";
            $file_datetime = getDateFromMediaExif($path_to_original_files . "/" . $file_name);
            $thumbnail_paths = generateThumbNail($file_name, $path_to_original_files, $file_datetime['date']);
            file_handlers\insert_files([["file_name" => $file_name, "thumb_linked_path" => $thumbnail_paths["thumb_linked_path"], "exif_created" => $file_datetime['date'] . " " . $file_datetime['time']]]);
            echo "Retrieved db entry: " . json_encode(file_handlers\get_files($file_name)) . "<br>";
            echo "Linked thumb entry: " . json_encode($thumbnail_paths['thumb_linked_path']) . "<br>";
        }
    }
}


/**
 * Determines path to sym-linked thumbnail image within nested browsing dir; appends '.jpg' if file is video
 * E.g. ('IMG_1000.MOV','2019:01:01') => '/home/magnus/stockfile/.thumbnails_linked/2019/01/img_1000.mov.jpg'
 */
function getThumbLinkedPath($file_name, $file_yyyymmdd)
{
    // Create path based on $file_date 'yyyy:mm:dd'
    $file_date_parts = explode('/', $file_yyyymmdd);
    $year = $file_date_parts[0];
    $month = $file_date_parts[1];

    // Ensure dirs exist for this path
    $path = __DIR__ . "/../.thumbnails_linked/" . $year . "/" . $month;
    if (!realpath($path)) shell_exec('mkdir -p ' . $path);

    $thumb_path = realpath($path) . "/" . thumbnailfunctions\getThumbnailFileName($file_name);
    return $thumb_path;
}

/**
 * Extracts EXIF data from file and returns date and time as object values of form ['date' => yyyy/mm/dd, 'time' => hh:mm:ss]
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


    // Undertake some tedious and error-prone character parsing for different file types
    $file_date_single_spaces = preg_replace('/\s+/', ' ', $file_date_line);
    $file_date_parts = explode(' ', $file_date_single_spaces);

    $date_index = 3;    // Different files require different character-parsing details:
    if (strpos($file_path, '.m4v') !== false) $date_index = 4;

    $file_date_date_parts = explode(':', $file_date_parts[$date_index + 0]);
    $file_date_time_parts = explode(':', $file_date_parts[$date_index + 1]);

    $file_yyyymmdd =  preg_replace('/\s+/', '', $file_date_date_parts[0] . "/" . $file_date_date_parts[1] . "/" . $file_date_date_parts[2]);
    $file_hhmmss =  preg_replace('/\s+/', '', $file_date_time_parts[0] . ":" . $file_date_time_parts[1] . ":" . clean_minutes($file_date_time_parts[2]));

    return ['date' => $file_yyyymmdd, 'time' => $file_hhmmss];
}
// print_r(getDateFromMediaExif(__DIR__ . "/../data/magnus/IMG_5038.jpg"));
// print_r(getDateFromMediaExif(__DIR__ . "/../data/magnus/IMG_5039.m4v"));
// print_r(getDateFromMediaExif(__DIR__ . "/../data/magnus/IMG_7089.MOV"));



/**
 * Function to generate thumbnail and a symlink to it that is nested in a dir marked by yyyy/mm
 * 1. Use imagemagick convert to generate a new JPG in flat dir .thumbnails_generated
 * 2. Create a symlink from newly generated JPG within nested dir in .thumbnails_linked
 * Returns location of 'thumb_generated_path' and 'thumb_linked_path' files generated
 */
function generateThumbNail($file_name, $path_to_original_files, $file_date_yyyymmdd)
{
    // 0. Setup Params
    $thumb_size = 400;
    $file_path = $path_to_original_files . "/" . $file_name;
    // 0.1. Path to 'thumb_generated_path' generated thumbnail
    $thumb_generated_path = __DIR__ . "/../.thumbnails_generated/" . thumbnailfunctions\getThumbnailFileName($file_name);
    // 0.2. Path to thumb_linked_path thumbnail
    $thumb_linked_path = getThumbLinkedPath($file_name, $file_date_yyyymmdd);

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

    // 2. Create thumb_linked_path thumbnails in nested dirs
    $cmd = "ln -fs " . $thumb_generated_path . " " . $thumb_linked_path;
    $rs1 = shell_exec($cmd);

    // 3. Return locations of new files
    $thumbnail_paths = ["thumb_generated_path" => realpath($thumb_generated_path), "thumb_linked_path" => $thumb_linked_path];

    // Finish
    if (!$rs0 || !$rs1) return $thumbnail_paths;
    throw new Error("One of the shell processes failed!");
}


/**
 * Pesky string-parsing function to clean up the +/- hours sometimes tagged onto a timestamp
 * E.g. "05+04:00" => "05"
 * E.g. "05-04:00" => "05"
 * E.g. "05" => "05"
 */
function clean_minutes($input)
{
    $output = explode("+", $input);
    return explode("-", $output[0])[0];
}
// echo clean_minutes("05+04:00");
// echo clean_minutes("05-04:00");
// echo clean_minutes("05");
