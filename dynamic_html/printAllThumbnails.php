<?php


require_once __DIR__ . "/../utils/db.php";
require_once __DIR__ . "/../utils/thumbnailFunctions.php";

function printAllThumbnails()
{
    $file_data_rows = db\getFileData();
    $path_to_original_files = db\STOCKFILE_CONFIG::$STOCKFILE_DATA_DIRS[0];

    foreach ($file_data_rows as $row) {
        // echo "<hr>";
        // print_r($row);
        // $file = $path_to_original_files . "/" . $row['file_name'];
        // $file = $row['file_name'];
        $file = "/data/magnus/" . $row['file_name'];
        $thumb_file = "/stockfile/.thumbnails_generated/" . thumbnailfunctions\getThumbnailFileName($row['file_name']);

        echo '
            <div class="gallery-wrapper" onCLick="loadImage(\'' . $file . '\')">
                    <div
                        class="gallery"
                        style="background-image:url(' . $thumb_file . ');"
                    >
                        <div class="gallery-tint">
                        </div>
                        <div class="gallery-name">
                        ' . $thumb_file . '<br><span style="font-size: 16px;">
                        </div>
                    </div>
            </div>
        ';
    }
}



function generateHtml()
{
    $output = '<div class="grid">';
    foreach (get_dir_files() as $file) {
        $output .=
            '
                <div class="gallery-wrapper">
                    <a href="' . $file . '">
                        <div
                            class="gallery"
                            style="background-image:url(' . $file . '/cover-image/image.jpg);"
                        >
                            <div class="gallery-tint">
                            </div>
                            <div class="gallery-name">
                            ' . str_replace("_", " ", $file) . '<br><span style="font-size: 16px;">
                            ' . get_total_images($file) . ' images
                            ' . get_total_videos($file) . ' videos  </span>
                            </div>
                        </div>
                    </a>
                </div>
            ';
    }
    echo $output . '</div>';
}
