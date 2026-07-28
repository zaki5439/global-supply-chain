<?php

$countryFile = 'resources/views/country.blade.php';
$compareFile = 'resources/views/comparison.blade.php';

$cLines = file($countryFile);
$compLines = file($compareFile);

$compHtmlStart = 0;
$compHtmlEnd = 0;
foreach($compLines as $i => $line) {
    if (strpos($line, '<div class="row">') !== false && $compHtmlStart == 0) $compHtmlStart = $i;
    if (strpos($line, '</main>') !== false && $compHtmlEnd == 0) $compHtmlEnd = $i;
}
$compHtml = implode("", array_slice($compLines, $compHtmlStart, $compHtmlEnd - $compHtmlStart));

$cHtmlStart = 0;
$cHtmlEnd = 0;
foreach($cLines as $i => $line) {
    if (strpos($line, '<!-- Country Selector -->') !== false && $cHtmlStart == 0) $cHtmlStart = $i;
    if (strpos($line, '</main>') !== false && $cHtmlEnd == 0) $cHtmlEnd = $i;
}
$cHtml = implode("", array_slice($cLines, $cHtmlStart, $cHtmlEnd - $cHtmlStart));

$newHtml = '
        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4 mt-3" id="countryTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="single-tab" data-bs-toggle="pill" data-bs-target="#single-view" type="button" role="tab" aria-controls="single-view" aria-selected="true">
                    <i class="bi bi-person-bounding-box me-1"></i> Single Country View
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="compare-tab" data-bs-toggle="pill" data-bs-target="#compare-view" type="button" role="tab" aria-controls="compare-view" aria-selected="false">
                    <i class="bi bi-arrow-left-right me-1"></i> Country Comparison Engine
                </button>
            </li>
        </ul>

        <div class="tab-content" id="countryTabsContent">
            <!-- Single View Tab -->
            <div class="tab-pane fade show active" id="single-view" role="tabpanel" aria-labelledby="single-tab">
' . $cHtml . '
            </div>
            
            <!-- Comparison View Tab -->
            <div class="tab-pane fade" id="compare-view" role="tabpanel" aria-labelledby="compare-tab">
                <div class="mb-3 p-3 bg-light rounded border-start border-4 border-primary">
                    <h5 class="fw-bold text-dark mb-1"><i class="bi bi-layout-split me-2"></i>Compare Countries</h5>
                    <p class="text-muted mb-0" style="font-size: 14px;">Select two countries to compare their macroeconomic and risk factors side-by-side.</p>
                </div>
' . $compHtml . '
            </div>
        </div>
    </main>
';

array_splice($cLines, $cHtmlStart, $cHtmlEnd - $cHtmlStart + 1, [$newHtml]);


$jsInjectIdx = 0;
foreach($cLines as $i => $line) {
    if (strpos($line, '});') !== false && $i > 300) {
        $jsInjectIdx = $i;
    }
}

$jsInjection = "
        const selectA = document.getElementById('countrySelectA');
        const selectB = document.getElementById('countrySelectB');
        
        countryData.forEach(country => {
            const optA = document.createElement('option');
            optA.value = country.id;
            optA.textContent = country.name;
            if(selectA) selectA.appendChild(optA);

            const optB = document.createElement('option');
            optB.value = country.id;
            optB.textContent = country.name;
            if(selectB) selectB.appendChild(optB);
        });

        if(selectA) selectA.addEventListener('change', (e) => {
            const selected = countryData.find(c => c.id === e.target.value);
            if(selected) updateUICompare(selected, 'A');
        });

        if(selectB) selectB.addEventListener('change', (e) => {
            const selected = countryData.find(c => c.id === e.target.value);
            if(selected) updateUICompare(selected, 'B');
        });

        if(countryData.length > 1 && selectA && selectB) {
            let cA = countryData.find(c => c.name === 'Germany') || countryData[0];
            let cB = countryData.find(c => c.name === 'Australia') || countryData[1];
            selectA.value = cA.id;
            selectB.value = cB.id;
            updateUICompare(cA, 'A');
            updateUICompare(cB, 'B');
        }
";

array_splice($cLines, $jsInjectIdx, 0, [$jsInjection]);


$funcInjection = "
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

array_splice($cLines, count($cLines) - 2, 0, [$funcInjection]);

file_put_contents($countryFile, implode("", $cLines));
echo 'Success';
