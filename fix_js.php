<?php
$file = 'resources/views/country.blade.php';
$lines = file($file);

// Find </script>
$scriptEnd = 0;
foreach($lines as $i => $line) {
    if (strpos($line, '</script>') !== false) {
        $scriptEnd = $i;
        break;
    }
}

// Lines 611 to 650 contain the misinjected functions outside the script tag
// Lines 575 to 607 contain the misinjected DOMContentLoaded inside fetchRiskData
// Let's just fix it automatically

// First, get the bad functions
$badFunctions = implode("", array_slice($lines, 611, 39));

// Next, get the bad DOMContentLoaded
$badDom = implode("", array_slice($lines, 574, 34));

// Now remove them
array_splice($lines, 611, 40); // Remove from line 612 (index 611) to 651
array_splice($lines, 574, 34); // Remove from line 575 (index 574) to 608

// Now find where to properly inject them.
// We want to inject badFunctions just before </script>
$newScriptEnd = 0;
foreach($lines as $i => $line) {
    if (strpos($line, '</script>') !== false) {
        $newScriptEnd = $i;
        break;
    }
}
array_splice($lines, $newScriptEnd, 0, [$badFunctions]);

// We want to inject badDom inside DOMContentLoaded.
// Let's find: `// Initialize with first country`
$domInsert = 0;
foreach($lines as $i => $line) {
    if (strpos($line, '// Initialize with first country') !== false) {
        $domInsert = $i;
        break;
    }
}
array_splice($lines, $domInsert, 0, [$badDom]);

file_put_contents($file, implode("", $lines));
echo "Fixed";
