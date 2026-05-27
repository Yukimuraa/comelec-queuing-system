<?php
$files = [
    'resources/views/admin/dashboard.blade.php',
    'resources/views/client/scan.blade.php',
    'resources/views/display/tv.blade.php',
];

$replacements = [
    'text-white' => 'text-gray-900',
    'text-gray-400' => 'text-gray-600',
    'text-gray-300' => 'text-gray-700',
    'bg-gray-950' => 'bg-gray-50',
    'bg-gray-900' => 'bg-white',
    'border-gray-800' => 'border-gray-200',
    'border-gray-900' => 'border-gray-300',
    'text-gray-200' => 'text-gray-800',
    'bg-gray-800' => 'bg-white',
    'border-gray-700' => 'border-gray-300',
    'text-gray-500' => 'text-gray-500', // Keep some grays
    'bg-gray-950/50' => 'bg-gray-50/50',
    'bg-gray-900/40' => 'bg-white/60',
    'border-gray-900/60' => 'border-gray-200/60',
    'bg-gray-900/20' => 'bg-white/40',
    'border-gray-900/40' => 'border-gray-200/40',
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        
        // Some specific fixes
        // For TV display
        if ($file === 'resources/views/display/tv.blade.php') {
            $content = str_replace(
                'background: radial-gradient(circle at 50% 50%, #030712 0%, #000000 100%) !important;',
                'background: radial-gradient(circle at 50% 50%, #ffffff 0%, #f3f4f6 100%) !important;',
                $content
            );
        }
        
        file_put_contents($path, $content);
        echo "Updated $file\n";
    }
}
