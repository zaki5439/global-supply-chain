<?php
$files = [
    'resources/views/dashboard.blade.php',
    'resources/views/news.blade.php',
    'resources/views/port.blade.php',
    'resources/views/currency.blade.php',
    'resources/views/country.blade.php',
    'resources/views/admin/sidebar.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = preg_replace('/<li class=\"nav-item\">\s*<a href=\"\/compare\"[^>]*>.*?<\/a>\s*<\/li>/is', '', $content);
        file_put_contents($file, $content);
    }
}
echo "Done";
