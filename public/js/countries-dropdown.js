/**
 * Countries Dropdown Populator
 * Automatically fetches and populates all countries from the API
 * 
 * Usage:
 * <select id="countrySelect" data-auto-populate="true">
 *     <option value="">-- Select a Country --</option>
 * </select>
 */

class CountriesDropdown {
    constructor(selectElement) {
        this.selectElement = selectElement;
        this.isLoading = false;
        this.cache = null;
    }

    /**
     * Initialize and populate the dropdown
     */
    async init() {
        try {
            console.log('🚀 CountriesDropdown Initializing...');
            
            if (!this.selectElement) {
                console.error('❌ CountriesDropdown: Select element not found');
                return false;
            }

            console.log('✓ Select element found:', this.selectElement.id || 'unnamed');
            
            this.showLoadingState();
            const countries = await this.fetchCountries();
            
            if (countries && countries.length > 0) {
                console.log(`✅ ${countries.length} countries fetched, populating dropdown...`);
                this.populateDropdown(countries);
                this.setupEventListeners();
                console.log('✓ Dropdown setup complete!');
                return true;
            } else {
                this.showError('No countries found');
                return false;
            }

        } catch (error) {
            console.error('❌ CountriesDropdown Error:', error);
            this.showError(error.message);
            return false;
        }
    }

    /**
     * Fetch countries from API
     */
    async fetchCountries() {
        try {
            if (this.cache) {
                console.log('✓ Using cached countries data');
                return this.cache;
            }

            console.log('🔄 Fetching countries from /api/countries...');
            
            const response = await fetch('/api/countries', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            console.log(`📡 API Response Status: ${response.status}`);

            if (!response.ok) {
                throw new Error(`API Error: ${response.status} ${response.statusText}`);
            }

            const data = await response.json();
            
            console.log('📦 API Response:', data);

            if (data.status !== 'success' || !Array.isArray(data.data)) {
                throw new Error(data.message || 'Invalid API response format');
            }

            // Cache the results
            this.cache = data.data;
            console.log(`✅ Fetched ${data.count} countries successfully!`);
            
            return data.data;

        } catch (error) {
            console.error('❌ Failed to fetch countries:', error);
            throw error;
        }
    }

    /**
     * Populate dropdown with countries
     */
    populateDropdown(countries) {
        console.log(`📝 Populating dropdown with ${countries.length} countries...`);
        
        // Remove loading option if exists
        const loadingOption = this.selectElement.querySelector('option[data-loading]');
        if (loadingOption) {
            loadingOption.remove();
        }

        // Add countries as options
        let added = 0;
        countries.forEach(country => {
            const option = document.createElement('option');
            
            // Use ISO3 code and coordinates format for compatibility with existing code
            // Format: "CountryName,ISO3,Latitude,Longitude"
            const lat = country.latitude || '0';
            const lon = country.longitude || '0';
            option.value = `${country.name},${country.iso3},${lat},${lon}`;
            
            // Display name with flag emoji if available
            const flagEmoji = country.flag || '🌍';
            option.textContent = `${flagEmoji} ${country.name}`;
            option.setAttribute('data-iso2', country.iso2);
            option.setAttribute('data-iso3', country.iso3);
            option.setAttribute('data-region', country.region || '');
            
            this.selectElement.appendChild(option);
            added++;
        });

        console.log(`✅ Successfully added ${added} countries to dropdown`);
    }

    /**
     * Show loading state
     */
    showLoadingState() {
        this.isLoading = true;
        this.selectElement.disabled = true;
        
        // Add loading option
        if (!this.selectElement.querySelector('option[data-loading]')) {
            const loadingOption = document.createElement('option');
            loadingOption.setAttribute('data-loading', 'true');
            loadingOption.textContent = '⏳ Loading countries...';
            loadingOption.selected = true;
            this.selectElement.appendChild(loadingOption);
        }
    }

    /**
     * Show error state
     */
    showError(errorMessage) {
        this.isLoading = false;
        this.selectElement.disabled = true;
        
        console.error('❌ CountriesDropdown Error:', errorMessage);
        
        // Update option text to show error
        const errorOption = this.selectElement.querySelector('option[data-loading]');
        if (errorOption) {
            errorOption.textContent = `⚠️ Error: ${errorMessage}`;
            errorOption.removeAttribute('data-loading');
            errorOption.setAttribute('data-error', 'true');
        }
    }

    /**
     * Setup change event listeners
     */
    setupEventListeners() {
        this.selectElement.disabled = false;
        
        // Add change event if callback is provided via data attribute
        if (this.selectElement.dataset.onchange) {
            this.selectElement.addEventListener('change', (e) => {
                const [countryName, iso3, lat, lon] = e.target.value.split(',');
                console.log(`Selected: ${countryName} (${iso3})`);
                
                // Trigger custom event
                window.dispatchEvent(new CustomEvent('countrySelected', {
                    detail: { countryName, iso3, lat, lon }
                }));
            });
        }
    }

    /**
     * Refresh/reload countries from API
     */
    async refresh() {
        this.cache = null; // Clear cache
        this.selectElement.innerHTML = '<option value="">-- Select a Country --</option>';
        return this.init();
    }

    /**
     * Get currently selected country data
     */
    getSelectedCountry() {
        const value = this.selectElement.value;
        if (!value) return null;

        const [name, iso3, lat, lon] = value.split(',');
        return { name, iso3, latitude: parseFloat(lat), longitude: parseFloat(lon) };
    }

    /**
     * Set selected country by name or ISO code
     */
    selectCountry(identifier) {
        const option = Array.from(this.selectElement.options).find(opt => {
            return opt.value.includes(identifier) || 
                   opt.getAttribute('data-iso3') === identifier ||
                   opt.getAttribute('data-iso2') === identifier;
        });

        if (option) {
            this.selectElement.value = option.value;
            this.selectElement.dispatchEvent(new Event('change', { bubbles: true }));
            return true;
        }

        console.warn(`Country not found: ${identifier}`);
        return false;
    }
}

/**
 * Auto-initialize dropdowns on DOM ready
 */
document.addEventListener('DOMContentLoaded', () => {
    const selectElements = document.querySelectorAll('select[data-auto-populate="true"]');
    
    if (selectElements.length === 0) {
        // Try to find by ID if no auto-populate attribute
        const countrySelect = document.getElementById('countrySelect');
        if (countrySelect) {
            new CountriesDropdown(countrySelect).init();
        }
    } else {
        selectElements.forEach(select => {
            new CountriesDropdown(select).init();
        });
    }
});

// Export for use in modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CountriesDropdown;
}
