<?php
$filePath = "C:\\Users\\ASUS\\.gemini\\antigravity-ide\\brain\\fbe06c63-9180-4e61-ab7d-2ba3cd49ff9e\\.system_generated\\steps\\66\\content.md";
$content = file_get_contents($filePath);
if ($content === false) {
    echo "Failed to read content file\n";
    exit(1);
}
$parts = explode("---", $content, 2);
if (count($parts) < 2) {
    echo "Separator not found\n";
    exit(1);
}
$js = ltrim($parts[1]);
if (!is_dir("public/js")) {
    mkdir("public/js", 0755, true);
}
$result = file_put_contents("public/js/qrcode.min.js", $js);
if ($result === false) {
    echo "Failed to write qrcode.min.js\n";
    exit(1);
}
echo "Successfully extracted and saved qrcode.min.js (" . strlen($js) . " bytes).\n";
unlink(__FILE__); // Delete this script
