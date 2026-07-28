<!DOCTYPE html>
<html>
<head>
    <title>Simple Countries Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        #log { background: #f0f0f0; padding: 15px; border-radius: 5px; min-height: 200px; white-space: pre-wrap; }
        select { padding: 10px; font-size: 16px; }
    </style>
</head>
<body>
    <h1>🌍 Countries API Test</h1>
    
    <div>
        <h3>Select Country:</h3>
        <select id="countrySelect">
            <option value="">-- Loading... --</option>
        </select>
    </div>

    <div>
        <h3>Debug Log:</h3>
        <div id="log"></div>
    </div>

    <script>
        const log = document.getElementById('log');
        
        function addLog(msg) {
            log.textContent += '[' + new Date().toLocaleTimeString() + '] ' + msg + '\n';
            log.scrollTop = log.scrollHeight;
            console.log(msg);
        }

        async function loadCountries() {
            addLog('Starting to load countries...');
            
            try {
                addLog('Fetching from /api/countries...');
                const response = await fetch('/api/countries');
                addLog(`Response status: ${response.status}`);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                
                const data = await response.json();
                addLog(`✓ Received data: ${JSON.stringify({status: data.status, count: data.count})}`);
                
                if (!data.data || data.data.length === 0) {
                    addLog('ERROR: No countries in response');
                    return;
                }
                
                const select = document.getElementById('countrySelect');
                select.innerHTML = '<option value="">-- Select a Country --</option>';
                
                addLog(`Adding ${data.data.length} countries to dropdown...`);
                
                data.data.forEach((country, index) => {
                    const option = document.createElement('option');
                    option.value = `${country.name},${country.iso3},${country.latitude || 0},${country.longitude || 0}`;
                    option.textContent = `${country.name} (${country.iso3})`;
                    select.appendChild(option);
                    
                    if (index % 50 === 0) {
                        addLog(`  Added ${index + 1} countries...`);
                    }
                });
                
                addLog(`✅ SUCCESS! Dropdown populated with ${data.data.length} countries`);
                
            } catch (error) {
                addLog(`❌ ERROR: ${error.message}`);
                addLog(`Stack: ${error.stack}`);
            }
        }

        // Load on page load
        document.addEventListener('DOMContentLoaded', loadCountries);
    </script>
</body>
</html>
