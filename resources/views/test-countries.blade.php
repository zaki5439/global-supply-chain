<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Countries Dropdown Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; padding: 40px 20px; }
        .test-container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .test-section { margin-bottom: 20px; }
        .debug-log { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 15px; margin-top: 15px; max-height: 300px; overflow-y: auto; font-family: monospace; font-size: 12px; }
        .log-entry { margin: 5px 0; }
        .log-success { color: #28a745; }
        .log-error { color: #dc3545; }
        .log-info { color: #0066cc; }
        .log-warn { color: #ff9800; }
    </style>
</head>
<body>
<div class="test-container">
    <h2 class="mb-4">🌍 Countries Dropdown Test</h2>
    
    <div class="test-section">
        <label class="form-label"><strong>Select a Country:</strong></label>
        <select id="countrySelect" class="form-select" data-auto-populate="true">
            <option value="" selected disabled>-- Loading Countries... --</option>
        </select>
    </div>

    <div class="test-section">
        <button class="btn btn-primary" id="testBtn" onclick="testSelect()">Test Selection</button>
        <button class="btn btn-secondary" id="refreshBtn" onclick="refreshCountries()">Refresh</button>
    </div>

    <div class="test-section">
        <h5>Debug Console:</h5>
        <div class="debug-log" id="debugLog">
            <div class="log-entry log-info">Waiting for initialization...</div>
        </div>
    </div>

    <div class="test-section" id="selectionResult" style="display: none;">
        <h5>Selected Country Info:</h5>
        <pre id="selectionData" style="background: #f8f9fa; padding: 10px; border-radius: 5px;"></pre>
    </div>
</div>

<script src="/js/countries-dropdown.js"></script>
<script>
const debugLog = document.getElementById('debugLog');

function addLog(message, type = 'info') {
    const entry = document.createElement('div');
    entry.className = `log-entry log-${type}`;
    entry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
    debugLog.appendChild(entry);
    debugLog.scrollTop = debugLog.scrollHeight;
    console.log(`[${type.toUpperCase()}] ${message}`);
}

// Override console methods to show in debug log
const originalLog = console.log;
const originalError = console.error;
const originalWarn = console.warn;

console.log = function(...args) {
    originalLog.apply(console, args);
    addLog(args.join(' '), 'info');
};

console.error = function(...args) {
    originalError.apply(console, args);
    addLog(args.join(' '), 'error');
};

console.warn = function(...args) {
    originalWarn.apply(console, args);
    addLog(args.join(' '), 'warn');
};

function testSelect() {
    const dropdown = new CountriesDropdown(document.getElementById('countrySelect'));
    const selected = dropdown.getSelectedCountry();
    
    if (selected) {
        document.getElementById('selectionResult').style.display = 'block';
        document.getElementById('selectionData').textContent = JSON.stringify(selected, null, 2);
        addLog(`Selected: ${selected.name} (${selected.iso3})`, 'success');
    } else {
        addLog('No country selected', 'warn');
    }
}

function refreshCountries() {
    const dropdown = new CountriesDropdown(document.getElementById('countrySelect'));
    dropdown.refresh().then(success => {
        if (success) {
            addLog('Countries refreshed successfully!', 'success');
        } else {
            addLog('Failed to refresh countries', 'error');
        }
    });
}

// Start test when page loads
window.addEventListener('load', () => {
    addLog('Page loaded, initializing dropdown...', 'info');
});
</script>
</body>
</html>
