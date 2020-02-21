<?php
defined('CB_VALID_ENTRY') or die();
// Deleting domPDF font cache file since older versions use a constant in the file that isn't defined in newer versions.
$domPdfCacheFiles = array(
    KenedoPlatform::p()->getDirDataStore().'/private/dompdf/dompdf_font_family_cache.php',
    KenedoPlatform::p()->getComponentDir('com_configbox').'/external/dompdf/lib/fonts/dompdf_font_family_cache.php',
);

foreach ($domPdfCacheFiles as $cacheFile) {
    if (is_writable($cacheFile) && is_writable(dirname($cacheFile))) {
        unlink($cacheFile);
    }
}

