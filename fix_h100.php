<?php
$file = 'resources/views/country.blade.php';
$content = file_get_contents($file);

// Add h-100 to the stat-cards in the first part (up to line 330)
// We will split the file at <!-- Comparison View Tab -->
$parts = explode('<!-- Comparison View Tab -->', $content);

if (count($parts) == 2) {
    // In the first part, replace class="stat-card with class="stat-card h-100
    $parts[0] = str_replace('class="stat-card gdp"', 'class="stat-card gdp h-100"', $parts[0]);
    $parts[0] = str_replace('class="stat-card inflation"', 'class="stat-card inflation h-100"', $parts[0]);
    $parts[0] = str_replace('class="stat-card population"', 'class="stat-card population h-100"', $parts[0]);
    $parts[0] = str_replace('class="stat-card currency"', 'class="stat-card currency h-100"', $parts[0]);
    $parts[0] = str_replace('class="stat-card weather"', 'class="stat-card weather h-100"', $parts[0]);
    $parts[0] = str_replace('class="stat-card" style="border-left-color: #6f42c1;"', 'class="stat-card h-100" style="border-left-color: #6f42c1;"', $parts[0]);
    $parts[0] = str_replace('class="stat-card" style="border-left-color: #e83e8c;"', 'class="stat-card h-100" style="border-left-color: #e83e8c;"', $parts[0]);
    
    file_put_contents($file, implode('<!-- Comparison View Tab -->', $parts));
    echo "Fixed";
} else {
    echo "Failed";
}
