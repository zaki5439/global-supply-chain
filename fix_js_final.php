<?php

$file = 'resources/views/country.blade.php';
$content = file_get_contents($file);

// Remove the weird syntax error at line 554
$content = str_replace(
    "        }\r\n            });\r\n        // Initialize with first country",
    "        }\r\n\r\n        // Initialize with first country",
    $content
);
$content = str_replace(
    "        }\n            });\n        // Initialize with first country",
    "        }\n\n        // Initialize with first country",
    $content
);

$func_str = "
    function updateUICompare(country, side) {
        document.getElementById(`flagContainer\${side}`).textContent = country.flag;
        document.getElementById(`countryNameTitle\${side}`).textContent = country.name;
        
        document.getElementById(`valGdp\${side}`).textContent = country.gdp;
        document.getElementById(`valInfl\${side}`).textContent = country.infl;
        document.getElementById(`valCurr\${side}`).textContent = country.curr;
        document.getElementById(`valWeath\${side}`).textContent = country.weather;
        
        const weathIcon = document.getElementById(`weathIcon\${side}`);
        weathIcon.className = `bi \${country.w_icon} stat-icon weath-icon`;

        fetchRiskDataCompare(country.id, side);
    }

    function fetchRiskDataCompare(iso2, side) {
        document.getElementById(`valRiskScore\${side}`).textContent = '...';
        document.getElementById(`riskLabel\${side}`).textContent = 'Calculating...';
        document.getElementById(`valSentiment\${side}`).textContent = '...';

        fetch(`/api/risk/score/\${iso2}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    const score = data.data.total_score;
                    const label = data.data.label;
                    const sentiment = data.data.components.news_sentiment;

                    document.getElementById(`valRiskScore\${side}`).textContent = score + ' / 100';
                    document.getElementById(`riskLabel\${side}`).innerHTML = `<strong>Status:</strong> \${label}`;
                    document.getElementById(`valSentiment\${side}`).textContent = sentiment;
                }
            })
            .catch(err => {
                document.getElementById(`valRiskScore\${side}`).textContent = 'N/A';
                document.getElementById(`riskLabel\${side}`).textContent = 'Failed to load';
                document.getElementById(`valSentiment\${side}`).textContent = 'N/A';
            });
    }
";

// If they are missing, append them to the end before </script>
if (strpos($content, 'function updateUICompare') === false) {
    // Also we need to fix the broken end of catch block in fetchRiskData
    $broken_catch = "            .catch(err => {\r\n                document.getElementById('valRiskScore').textContent = 'N/A';\r\n                document.getElementById('riskLabel').textContent = 'Failed to load';\r\n                document.getElementById('valSentiment').textContent = 'N/A';\r\n\r\n    }\r\n</script>";
    
    $fixed_catch = "            .catch(err => {\r\n                document.getElementById('valRiskScore').textContent = 'N/A';\r\n                document.getElementById('riskLabel').textContent = 'Failed to load';\r\n                document.getElementById('valSentiment').textContent = 'N/A';\r\n            });\r\n    }\r\n" . $func_str . "\r\n</script>\r\n</body>\r\n";

    $broken_catch2 = "            .catch(err => {\n                document.getElementById('valRiskScore').textContent = 'N/A';\n                document.getElementById('riskLabel').textContent = 'Failed to load';\n                document.getElementById('valSentiment').textContent = 'N/A';\n\n    }\n</script>";
    
    $fixed_catch2 = "            .catch(err => {\n                document.getElementById('valRiskScore').textContent = 'N/A';\n                document.getElementById('riskLabel').textContent = 'Failed to load';\n                document.getElementById('valSentiment').textContent = 'N/A';\n            });\n    }\n" . $func_str . "\n</script>\n</body>\n";

    if (strpos($content, $broken_catch) !== false) {
        $content = str_replace($broken_catch, $fixed_catch, $content);
    } else if (strpos($content, $broken_catch2) !== false) {
        $content = str_replace($broken_catch2, $fixed_catch2, $content);
    } else {
        // Fallback: just put it before </script>
        $content = str_replace('</script>', $func_str . "\n</script>\n</body>\n", $content);
    }
}

file_put_contents($file, $content);
echo "Fixed again";

