<?php
$url = "https://raw.githubusercontent.com/davidshimjs/qrcodejs/master/qrcode.min.js";
echo "Fetching $url...\n";
$content = file_get_contents($url);
if ($content === false) {
    echo "Failed to fetch content.\n";
    exit(1);
}
if (!is_dir("public/js")) {
    mkdir("public/js", 0755, true);
}
$result = file_put_contents("public/js/qrcode.min.js", $content);
if ($result === false) {
    echo "Failed to write content to file.\n";
    exit(1);
}
echo "Successfully saved to public/js/qrcode.min.js (" . strlen($content) . " bytes).\n";
unlink(__FILE__); // Delete this script
