<?php

namespace thumbnailfunctions;

/**
 * SSOT for thumb file names absed on file type of original
 */
function getThumbFileName($file_name)
{
    $new_file_name = strtolower($file_name);
    if (strpos($new_file_name, ".mov")) $new_file_name .= '.jpg';
    if (strpos($new_file_name, ".m4v")) $new_file_name .= '.jpg';
    return $new_file_name;
}
