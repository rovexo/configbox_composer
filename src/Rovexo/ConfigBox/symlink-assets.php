<?php 

$libDir = __DIR__ . "/../../../../../../lib/web/";
$targetDir = $libDir . "rovexo/configbox/";
if (file_exists($libDir) && !file_exists($targetDir)) {
	mkdir($targetDir, 0775, true);
}

if (file_exists($targetDir)) {
	custom_copy(__DIR__ . '/assets', $targetDir . 'assets');
}

function custom_copy($src, $dst)
{
    // open the source directory
    $dir = opendir($src);

    // Make the destination directory if not exist
    mkdir($dst);

    // Loop through the files in source directory
    foreach (scandir($src) as $file) {
        if (($file != '.') && ($file != '..')) {
            if ( is_dir($src . '/' . $file) ) {
                // Recursively calling custom copy function for sub directory
                custom_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }

    closedir($dir);
}