// Comprehensive Ports Data - All countries with complete port lists
const comprehensivePorts = [
    // SOUTHEAST ASIA - SINGAPORE (2)
    { name: 'Port of Singapore', country: 'Singapore', region: 'Southeast Asia', lat: 1.3521, lng: 103.8198, status: 'operational', containers: 3800, ships: 52, congestion: 8, activity: 'World\'s busiest transshipment hub' },
    { name: 'Port of Jurong', country: 'Singapore', region: 'Southeast Asia', lat: 1.3221, lng: 103.7224, status: 'operational', containers: 800, ships: 12, congestion: 10, activity: 'Oil terminal' },
    
    // MALAYSIA (5)
    { name: 'Port of Tanjung Pelepas', country: 'Malaysia', region: 'Southeast Asia', lat: 1.3522, lng: 103.7401, status: 'operational', containers: 1400, ships: 25, congestion: 20, activity: 'Transshipment hub' },
    { name: 'Port of Port Klang', country: 'Malaysia', region: 'Southeast Asia', lat: 3.0048, lng: 101.5241, status: 'operational', containers: 1100, ships: 18, congestion: 24, activity: 'Kuala Lumpur gateway' },
    { name: 'Port of Penang', country: 'Malaysia', region: 'Southeast Asia', lat: 5.2744, lng: 100.2735, status: 'operational', containers: 800, ships: 14, congestion: 22, activity: 'Northern hub' },
    { name: 'Port of Kuantan', country: 'Malaysia', region: 'Southeast Asia', lat: 3.8235, lng: 103.3256, status: 'operational', containers: 300, ships: 8, congestion: 15, activity: 'East coast port' },
    { name: 'Port of Johor Bahru', country: 'Malaysia', region: 'Southeast Asia', lat: 1.4655, lng: 103.7618, status: 'operational', containers: 250, ships: 6, congestion: 14, activity: 'Southern gateway' },
    
    // INDONESIA (8)
    { name: 'Port of Jakarta', country: 'Indonesia', region: 'Southeast Asia', lat: -6.1256, lng: 106.9651, status: 'operational', containers: 1800, ships: 28, congestion: 25, activity: 'Indonesia main gateway' },
    { name: 'Port of Batam', country: 'Indonesia', region: 'Southeast Asia', lat: 1.1305, lng: 104.0081, status: 'operational', containers: 800, ships: 15, congestion: 18, activity: 'Free trade hub' },
    { name: 'Port of Semarang', country: 'Indonesia', region: 'Southeast Asia', lat: -6.9704, lng: 110.4127, status: 'operational', containers: 600, ships: 12, congestion: 21, activity: 'Central Java' },
    { name: 'Port of Surabaya', country: 'Indonesia', region: 'Southeast Asia', lat: -7.2506, lng: 112.7508, status: 'operational', containers: 700, ships: 14, congestion: 20, activity: 'East Java hub' },
    { name: 'Port of Banjarmasin', country: 'Indonesia', region: 'Southeast Asia', lat: -3.3277, lng: 114.5898, status: 'operational', containers: 200, ships: 5, congestion: 16, activity: 'Kalimantan port' },
    { name: 'Port of Makassar', country: 'Indonesia', region: 'Southeast Asia', lat: -5.1477, lng: 119.4327, status: 'operational', containers: 350, ships: 8, congestion: 17, activity: 'Sulawesi gateway' },
    { name: 'Port of Medan', country: 'Indonesia', region: 'Southeast Asia', lat: 2.1969, lng: 98.6743, status: 'operational', containers: 250, ships: 6, congestion: 18, activity: 'Sumatra port' },
    { name: 'Port of Palembang', country: 'Indonesia', region: 'Southeast Asia', lat: -2.9181, lng: 104.7454, status: 'operational', containers: 280, ships: 7, congestion: 19, activity: 'South Sumatra' },
    
    // VIETNAM (5)
    { name: 'Port of Ho Chi Minh', country: 'Vietnam', region: 'Southeast Asia', lat: 10.7769, lng: 106.6833, status: 'delayed', containers: 1400, ships: 22, congestion: 45, activity: 'Main gateway' },
    { name: 'Port of Hai Phong', country: 'Vietnam', region: 'Southeast Asia', lat: 20.8450, lng: 106.6839, status: 'operational', containers: 700, ships: 16, congestion: 28, activity: 'North Vietnam' },
    { name: 'Port of Da Nang', country: 'Vietnam', region: 'Southeast Asia', lat: 16.0544, lng: 108.2022, status: 'operational', containers: 450, ships: 10, congestion: 25, activity: 'Central hub' },
    { name: 'Port of Cai Lan', country: 'Vietnam', region: 'Southeast Asia', lat: 21.0689, lng: 107.1014, status: 'operational', containers: 250, ships: 7, congestion: 22, activity: 'Coal port' },
    { name: 'Port of Can Tho', country: 'Vietnam', region: 'Southeast Asia', lat: 10.0455, lng: 105.7469, status: 'operational', containers: 200, ships: 5, congestion: 20, activity: 'Mekong gateway' },
    
    // THAILAND (4)
    { name: 'Port of Laem Chabang', country: 'Thailand', region: 'Southeast Asia', lat: 13.0978, lng: 100.8844, status: 'operational', containers: 1300, ships: 26, congestion: 28, activity: 'Main container port' },
    { name: 'Port of Bangkok', country: 'Thailand', region: 'Southeast Asia', lat: 13.1939, lng: 100.5855, status: 'operational', containers: 950, ships: 18, congestion: 35, activity: 'River operations' },
    { name: 'Port of Songkhla', country: 'Thailand', region: 'Southeast Asia', lat: 8.0863, lng: 100.5981, status: 'operational', containers: 350, ships: 8, congestion: 20, activity: 'South Thailand' },
    { name: 'Port of Rayong', country: 'Thailand', region: 'Southeast Asia', lat: 12.6808, lng: 101.2825, status: 'operational', containers: 200, ships: 5, congestion: 18, activity: 'Industrial port' },
    
    // PHILIPPINES (5)
    { name: 'Port of Manila', country: 'Philippines', region: 'Southeast Asia', lat: 14.6091, lng: 120.9824, status: 'critical', containers: 1100, ships: 20, congestion: 72, activity: 'Typhoon recovery' },
    { name: 'Port of Cebu', country: 'Philippines', region: 'Southeast Asia', lat: 10.3157, lng: 123.8854, status: 'operational', containers: 600, ships: 14, congestion: 32, activity: 'Central hub' },
    { name: 'Port of Davao', country: 'Philippines', region: 'Southeast Asia', lat: 7.0731, lng: 125.6432, status: 'operational', containers: 400, ships: 10, congestion: 28, activity: 'Mindanao gateway' },
    { name: 'Port of Subic Bay', country: 'Philippines', region: 'Southeast Asia', lat: 14.8371, lng: 120.2327, status: 'operational', containers: 350, ships: 9, congestion: 25, activity: 'Luzon hub' },
    { name: 'Port of Iloilo', country: 'Philippines', region: 'Southeast Asia', lat: 10.6992, lng: 122.5598, status: 'operational', containers: 200, ships: 6, congestion: 22, activity: 'Visayas port' },
    
    // SOUTH ASIA
    { name: 'Port of Colombo', country: 'Sri Lanka', region: 'South Asia', lat: 6.9271, lng: 79.8412, status: 'operational', containers: 1050, ships: 20, congestion: 26, activity: 'Transshipment hub' },
    { name: 'Port of Trincomalee', country: 'Sri Lanka', region: 'South Asia', lat: 8.5711, lng: 81.2344, status: 'operational', containers: 300, ships: 8, congestion: 18, activity: 'East coast' },
    
    // MIDDLE EAST
    { name: 'Port of Dubai', country: 'UAE', region: 'Middle East', lat: 25.2048, lng: 55.2708, status: 'operational', containers: 3500, ships: 42, congestion: 15, activity: 'Man-made port' },
    { name: 'Port of Jebel Ali', country: 'UAE', region: 'Middle East', lat: 24.9774, lng: 54.9900, status: 'operational', containers: 2800, ships: 40, congestion: 14, activity: 'Container specialist' },
    { name: 'Port of Abu Dhabi', country: 'UAE', region: 'Middle East', lat: 24.4539, lng: 54.3773, status: 'operational', containers: 1200, ships: 22, congestion: 19, activity: 'Abu Dhabi gateway' },
    { name: 'Port of Fujairah', country: 'UAE', region: 'Middle East', lat: 25.1191, lng: 56.3535, status: 'operational', containers: 600, ships: 14, congestion: 16, activity: 'East coast terminal' },
    
    // EUROPE - NETHERLANDS (3)
    { name: 'Port of Rotterdam', country: 'Netherlands', region: 'Europe', lat: 51.9225, lng: 4.1249, status: 'operational', containers: 4800, ships: 55, congestion: 9, activity: 'Europe\'s largest' },
    { name: 'Port of Amsterdam', country: 'Netherlands', region: 'Europe', lat: 52.3676, lng: 4.9041, status: 'operational', containers: 2600, ships: 32, congestion: 14, activity: 'Amsterdam terminal' },
    { name: 'Port of IJmuiden', country: 'Netherlands', region: 'Europe', lat: 52.4581, lng: 4.5651, status: 'operational', containers: 800, ships: 15, congestion: 12, activity: 'North Sea port' },
    
    // GERMANY (3)
    { name: 'Port of Hamburg', country: 'Germany', region: 'Europe', lat: 53.5511, lng: 9.9850, status: 'operational', containers: 3600, ships: 42, congestion: 14, activity: 'Busiest container' },
    { name: 'Port of Bremen', country: 'Germany', region: 'Europe', lat: 53.5136, lng: 8.5833, status: 'operational', containers: 1400, ships: 24, congestion: 16, activity: 'Northern gateway' },
    { name: 'Port of Bremerhaven', country: 'Germany', region: 'Europe', lat: 53.5197, lng: 8.5675, status: 'operational', containers: 1200, ships: 20, congestion: 15, activity: 'Automotive hub' },
    
    // BELGIUM (2)
    { name: 'Port of Antwerp', country: 'Belgium', region: 'Europe', lat: 51.3197, lng: 4.4047, status: 'operational', containers: 3200, ships: 38, congestion: 12, activity: 'Main container' },
    { name: 'Port of Zeebrugge', country: 'Belgium', region: 'Europe', lat: 51.3333, lng: 3.1833, status: 'operational', containers: 1100, ships: 20, congestion: 15, activity: 'Seaport' },
    
    // UK (4)
    { name: 'Port of London', country: 'United Kingdom', region: 'Europe', lat: 51.5074, lng: -0.1278, status: 'operational', containers: 2400, ships: 30, congestion: 20, activity: 'Major container' },
    { name: 'Port of Felixstowe', country: 'United Kingdom', region: 'Europe', lat: 51.9618, lng: 1.3456, status: 'operational', containers: 2100, ships: 28, congestion: 18, activity: 'Busiest container' },
    { name: 'Port of Southampton', country: 'United Kingdom', region: 'Europe', lat: 50.9097, lng: -1.4044, status: 'operational', containers: 1800, ships: 25, congestion: 19, activity: 'Southern UK' },
    { name: 'Port of Liverpool', country: 'United Kingdom', region: 'Europe', lat: 53.4084, lng: -2.9916, status: 'operational', containers: 600, ships: 14, congestion: 17, activity: 'Northwest hub' },
    
    // SPAIN (4)
    { name: 'Port of Barcelona', country: 'Spain', region: 'Europe', lat: 41.3851, lng: 2.1734, status: 'operational', containers: 2800, ships: 35, congestion: 16, activity: 'Mediterranean' },
    { name: 'Port of Valencia', country: 'Spain', region: 'Europe', lat: 39.4699, lng: -0.4417, status: 'operational', containers: 2300, ships: 30, congestion: 17, activity: 'Largest container' },
    { name: 'Port of Algeciras', country: 'Spain', region: 'Europe', lat: 36.1333, lng: -5.4667, status: 'operational', containers: 1900, ships: 28, congestion: 20, activity: 'Gibraltar gateway' },
    { name: 'Port of Bilbao', country: 'Spain', region: 'Europe', lat: 43.3631, lng: -3.0275, status: 'operational', containers: 800, ships: 16, congestion: 15, activity: 'Northern Spain' },
    
    // NORTH AMERICA - USA (10)
    { name: 'Port of Los Angeles', country: 'USA', region: 'North America', lat: 33.7425, lng: -118.2673, status: 'operational', containers: 4500, ships: 48, congestion: 22, activity: 'Busiest container' },
    { name: 'Port of Long Beach', country: 'USA', region: 'North America', lat: 33.7534, lng: -118.2121, status: 'operational', containers: 3800, ships: 42, congestion: 25, activity: 'Second largest' },
    { name: 'Port of New York', country: 'USA', region: 'North America', lat: 40.7128, lng: -74.0060, status: 'operational', containers: 2900, ships: 36, congestion: 28, activity: 'East Coast' },
    { name: 'Port of New Jersey', country: 'USA', region: 'North America', lat: 40.7489, lng: -74.1745, status: 'operational', containers: 2200, ships: 32, congestion: 26, activity: 'NY-NJ complex' },
    { name: 'Port of Houston', country: 'USA', region: 'North America', lat: 29.7589, lng: -95.0830, status: 'operational', containers: 1600, ships: 24, congestion: 21, activity: 'Gulf leader' },
    { name: 'Port of Savannah', country: 'USA', region: 'North America', lat: 32.0809, lng: -81.0912, status: 'operational', containers: 1500, ships: 22, congestion: 19, activity: 'Southeast gateway' },
    { name: 'Port of Seattle', country: 'USA', region: 'North America', lat: 47.6062, lng: -122.3321, status: 'operational', containers: 1200, ships: 20, congestion: 18, activity: 'Pacific Northwest' },
    { name: 'Port of Miami', country: 'USA', region: 'North America', lat: 25.7617, lng: -80.1918, status: 'operational', containers: 1100, ships: 18, congestion: 20, activity: 'Florida gateway' },
    { name: 'Port of Oakland', country: 'USA', region: 'North America', lat: 37.7749, lng: -122.4194, status: 'operational', containers: 900, ships: 15, congestion: 17, activity: 'San Francisco bay' },
    { name: 'Port of Charleston', country: 'USA', region: 'North America', lat: 32.7765, lng: -79.9318, status: 'operational', containers: 850, ships: 14, congestion: 16, activity: 'South Carolina' },
    
    // CANADA (4)
    { name: 'Port of Vancouver', country: 'Canada', region: 'North America', lat: 49.2827, lng: -123.1207, status: 'operational', containers: 2200, ships: 28, congestion: 18, activity: 'Busiest container' },
    { name: 'Port of Prince Rupert', country: 'Canada', region: 'North America', lat: 54.3161, lng: -130.3269, status: 'operational', containers: 900, ships: 16, congestion: 14, activity: 'Deep-sea port' },
    { name: 'Port of Montreal', country: 'Canada', region: 'North America', lat: 45.5017, lng: -73.5673, status: 'operational', containers: 1400, ships: 22, congestion: 20, activity: 'Largest seaport' },
    { name: 'Port of St. John', country: 'Canada', region: 'North America', lat: 45.2769, lng: -66.0895, status: 'operational', containers: 600, ships: 12, congestion: 16, activity: 'Atlantic gateway' },
    
    // SOUTH AMERICA - BRAZIL (5)
    { name: 'Port of Santos', country: 'Brazil', region: 'South America', lat: -23.9645, lng: -46.3332, status: 'operational', containers: 2600, ships: 32, congestion: 24, activity: 'Busiest container' },
    { name: 'Port of Rio de Janeiro', country: 'Brazil', region: 'South America', lat: -22.9068, lng: -43.1729, status: 'operational', containers: 1400, ships: 20, congestion: 22, activity: 'Rio gateway' },
    { name: 'Port of Suape', country: 'Brazil', region: 'South America', lat: -8.3891, lng: -35.0268, status: 'operational', containers: 800, ships: 14, congestion: 20, activity: 'Northeast hub' },
    { name: 'Port of Paranagua', country: 'Brazil', region: 'South America', lat: -25.5180, lng: -48.5140, status: 'operational', containers: 700, ships: 12, congestion: 19, activity: 'South coast' },
    { name: 'Port of Itaqui', country: 'Brazil', region: 'South America', lat: -2.8970, lng: -44.2972, status: 'operational', containers: 600, ships: 10, congestion: 18, activity: 'Northeast gateway' },
    
    // ARGENTINA (3)
    { name: 'Port of Buenos Aires', country: 'Argentina', region: 'South America', lat: -34.6037, lng: -58.3816, status: 'operational', containers: 1800, ships: 24, congestion: 32, activity: 'Main port' },
    { name: 'Port of Rosario', country: 'Argentina', region: 'South America', lat: -32.9468, lng: -60.6393, status: 'operational', containers: 900, ships: 16, congestion: 25, activity: 'Paraná River' },
    { name: 'Port of La Plata', country: 'Argentina', region: 'South America', lat: -34.8627, lng: -57.9159, status: 'operational', containers: 500, ships: 10, congestion: 20, activity: 'Buenos Aires zone' },
    
    // PERU (2)
    { name: 'Port of Callao', country: 'Peru', region: 'South America', lat: -12.0574, lng: -77.1689, status: 'operational', containers: 1400, ships: 20, congestion: 30, activity: 'Main port' },
    { name: 'Port of Matarani', country: 'Peru', region: 'South America', lat: -17.0039, lng: -72.1068, status: 'operational', containers: 300, ships: 6, congestion: 18, activity: 'South Peru' },
    
    // CHILE (2)
    { name: 'Port of Valparaiso', country: 'Chile', region: 'South America', lat: -33.0472, lng: -71.6127, status: 'operational', containers: 1200, ships: 18, congestion: 21, activity: 'Main container' },
    { name: 'Port of Iquique', country: 'Chile', region: 'South America', lat: -20.2136, lng: -70.1538, status: 'operational', containers: 400, ships: 8, congestion: 19, activity: 'Northern port' },
    
    // COLOMBIA (1)
    { name: 'Port of Cartagena', country: 'Colombia', region: 'South America', lat: 10.3910, lng: -75.5144, status: 'operational', containers: 1100, ships: 18, congestion: 24, activity: 'Caribbean gateway' },
    
    // AFRICA - EGYPT (4)
    { name: 'Port of Suez', country: 'Egypt', region: 'Africa', lat: 29.9538, lng: 32.3401, status: 'operational', containers: 1900, ships: 31, congestion: 21, activity: 'Canal throughput' },
    { name: 'Port of Port Said', country: 'Egypt', region: 'Africa', lat: 31.2570, lng: 32.2958, status: 'operational', containers: 1600, ships: 26, congestion: 18, activity: 'Canal gateway' },
    { name: 'Port of Alexandria', country: 'Egypt', region: 'Africa', lat: 31.2001, lng: 29.9187, status: 'operational', containers: 1200, ships: 20, congestion: 22, activity: 'Mediterranean' },
    { name: 'Port of Ain Sokhna', country: 'Egypt', region: 'Africa', lat: 29.6180, lng: 32.3333, status: 'operational', containers: 500, ships: 12, congestion: 16, activity: 'Suez zone' },
    
    // SOUTH AFRICA (2)
    { name: 'Port of Durban', country: 'South Africa', region: 'Africa', lat: -29.8587, lng: 31.0218, status: 'operational', containers: 1300, ships: 22, congestion: 26, activity: 'Sub-Saharan busiest' },
    { name: 'Port of Cape Town', country: 'South Africa', region: 'Africa', lat: -33.9249, lng: 18.4241, status: 'operational', containers: 800, ships: 16, congestion: 20, activity: 'Cape gateway' },
    
    // NIGERIA (1)
    { name: 'Port of Lagos', country: 'Nigeria', region: 'Africa', lat: 6.5244, lng: 3.3792, status: 'delayed', containers: 900, ships: 16, congestion: 48, activity: 'West Africa hub' },
    
    // GHANA (1)
    { name: 'Port of Tema', country: 'Ghana', region: 'Africa', lat: 5.6037, lng: -0.0133, status: 'operational', containers: 600, ships: 12, congestion: 24, activity: 'West Africa' },
    
    // IVORY COAST (1)
    { name: 'Port of Abidjan', country: 'Ivory Coast', region: 'Africa', lat: 4.0211, lng: -7.5898, status: 'operational', containers: 700, ships: 14, congestion: 26, activity: 'Ivory Coast gateway' },
    
    // SENEGAL (1)
    { name: 'Port of Dakar', country: 'Senegal', region: 'Africa', lat: 14.6671, lng: -17.0378, status: 'operational', containers: 500, ships: 10, congestion: 22, activity: 'West Africa entry' },
    
    // DJIBOUTI (1)
    { name: 'Port of Djibouti', country: 'Djibouti', region: 'Africa', lat: 11.5806, lng: 43.1425, status: 'operational', containers: 1100, ships: 19, congestion: 20, activity: 'Red Sea gateway' },
    
    // OCEANIA - AUSTRALIA (5)
    { name: 'Port of Sydney', country: 'Australia', region: 'Oceania', lat: -33.8688, lng: 151.2093, status: 'operational', containers: 2100, ships: 30, congestion: 20, activity: 'Australia gateway' },
    { name: 'Port of Melbourne', country: 'Australia', region: 'Oceania', lat: -37.8136, lng: 144.9631, status: 'operational', containers: 2400, ships: 32, congestion: 17, activity: 'Busiest container' },
    { name: 'Port of Brisbane', country: 'Australia', region: 'Oceania', lat: -27.3818, lng: 153.0007, status: 'operational', containers: 1200, ships: 22, congestion: 18, activity: 'Queensland gateway' },
    { name: 'Port of Fremantle', country: 'Australia', region: 'Oceania', lat: -32.0534, lng: 115.7430, status: 'operational', containers: 900, ships: 16, congestion: 19, activity: 'Western Australia' },
    { name: 'Port of Port Adelaide', country: 'Australia', region: 'Oceania', lat: -34.8494, lng: 138.5117, status: 'operational', containers: 600, ships: 12, congestion: 16, activity: 'South Australia' },
    
    // NEW ZEALAND (3)
    { name: 'Port of Auckland', country: 'New Zealand', region: 'Oceania', lat: -37.0082, lng: 174.7850, status: 'operational', containers: 1200, ships: 18, congestion: 22, activity: 'New Zealand gateway' },
    { name: 'Port of Wellington', country: 'New Zealand', region: 'Oceania', lat: -41.2865, lng: 174.7762, status: 'operational', containers: 600, ships: 12, congestion: 20, activity: 'Second port' },
    { name: 'Port of Christchurch', country: 'New Zealand', region: 'Oceania', lat: -43.5332, lng: 172.6362, status: 'operational', containers: 400, ships: 8, congestion: 18, activity: 'South Island' }
];
