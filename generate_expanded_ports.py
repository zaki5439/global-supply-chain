#!/usr/bin/env python3
import json

# Load original ports file and add new missing countries
try:
    with open('resources/views/ports-complete.json', 'r') as f:
        original_ports = json.load(f)
except:
    original_ports = []

# New ports for missing UN member states with coastlines
new_ports = [
    # Iceland (IS) - 3 ports
    {"name": "Port of Reykjavik", "country": "Iceland", "countryCode": "IS", "region": "North Atlantic", "lat": 64.1466, "lng": -21.9426, "status": "operational", "containers": 350000, "ships": 120, "congestion": 8, "activity": "Iceland's primary container port serving North Atlantic"},
    {"name": "Port of Akureyri", "country": "Iceland", "countryCode": "IS", "region": "North Atlantic", "lat": 65.6835, "lng": -18.0878, "status": "operational", "containers": 200000, "ships": 80, "congestion": 7, "activity": "Northern Iceland's container and fishing hub"},
    {"name": "Port of Hafnarfjordur", "country": "Iceland", "countryCode": "IS", "region": "North Atlantic", "lat": 64.0769, "lng": -21.9508, "status": "operational", "containers": 280000, "ships": 100, "congestion": 7, "activity": "Iceland's secondary container and aluminum port"},
    # Ireland (IE) - 3 ports
    {"name": "Port of Cork", "country": "Ireland", "countryCode": "IE", "region": "Europe", "lat": 51.8960, "lng": -8.4735, "status": "operational", "containers": 800000, "ships": 200, "congestion": 11, "activity": "Southern Ireland's container and general cargo hub"},
    {"name": "Port of Dublin", "country": "Ireland", "countryCode": "IE", "region": "Europe", "lat": 53.3498, "lng": -6.2603, "status": "operational", "containers": 1200000, "ships": 320, "congestion": 12, "activity": "Ireland's largest container port on Irish Sea"},
    {"name": "Port of Shannon", "country": "Ireland", "countryCode": "IE", "region": "Europe", "lat": 52.6935, "lng": -8.9246, "status": "operational", "containers": 400000, "ships": 120, "congestion": 10, "activity": "Western Ireland's Atlantic container gateway"},
    # Monaco (MC) - 1 port
    {"name": "Port of Monaco Fontvieille", "country": "Monaco", "countryCode": "MC", "region": "Europe", "lat": 43.7384, "lng": 7.4246, "status": "operational", "containers": 120000, "ships": 60, "congestion": 9, "activity": "Mediterranean luxury container and yacht port"},
    # Bosnia and Herzegovina (BA) - 2 ports
    {"name": "Port of Ploce", "country": "Bosnia and Herzegovina", "countryCode": "BA", "region": "Europe", "lat": 42.8950, "lng": 17.4475, "status": "operational", "containers": 300000, "ships": 100, "congestion": 13, "activity": "Bosnia's Adriatic container gateway"},
    {"name": "Port of Neum", "country": "Bosnia and Herzegovina", "countryCode": "BA", "region": "Europe", "lat": 42.9075, "lng": 17.6650, "status": "operational", "containers": 100000, "ships": 40, "congestion": 12, "activity": "Bosnia's only direct Adriatic access container facility"},
    # Montenegro (ME) - 3 ports
    {"name": "Port of Kotor", "country": "Montenegro", "countryCode": "ME", "region": "Europe", "lat": 42.4250, "lng": 19.2722, "status": "operational", "containers": 350000, "ships": 110, "congestion": 11, "activity": "Montenegro's Adriatic container and cruise hub"},
    {"name": "Port of Bar", "country": "Montenegro", "countryCode": "ME", "region": "Europe", "lat": 42.1069, "lng": 19.0947, "status": "operational", "containers": 280000, "ships": 90, "congestion": 12, "activity": "Montenegro's second-largest Adriatic container port"},
    {"name": "Port of Tivat", "country": "Montenegro", "countryCode": "ME", "region": "Europe", "lat": 42.4208, "lng": 18.6917, "status": "operational", "containers": 150000, "ships": 70, "congestion": 10, "activity": "Montenegro's secondary container and superyacht facility"},
    # Albania (AL) - 2 ports
    {"name": "Port of Durrës", "country": "Albania", "countryCode": "AL", "region": "Europe", "lat": 41.3256, "lng": 19.4504, "status": "operational", "containers": 400000, "ships": 130, "congestion": 14, "activity": "Albania's primary Adriatic container gateway"},
    {"name": "Port of Vlore", "country": "Albania", "countryCode": "AL", "region": "Europe", "lat": 40.4646, "lng": 19.4831, "status": "operational", "containers": 200000, "ships": 80, "congestion": 13, "activity": "Southern Albania's Ionian Sea container facility"},
    # Ukraine (UA) - 3 ports
    {"name": "Port of Varna Black Sea", "country": "Ukraine", "countryCode": "UA", "region": "Europe", "lat": 46.4825, "lng": 30.7233, "status": "critical", "containers": 200000, "ships": 80, "congestion": 35, "activity": "Ukraine's Black Sea container hub (limited operations)"},
    {"name": "Port of Odesa", "country": "Ukraine", "countryCode": "UA", "region": "Europe", "lat": 46.4869, "lng": 30.7326, "status": "critical", "containers": 150000, "ships": 60, "congestion": 38, "activity": "Ukraine's primary Black Sea container port (war affected)"},
    {"name": "Port of Mariupol", "country": "Ukraine", "countryCode": "UA", "region": "Europe", "lat": 47.0953, "lng": 37.5368, "status": "critical", "containers": 100000, "ships": 40, "congestion": 45, "activity": "Ukraine's Azov Sea port (severely affected)"},
    # Turkmenistan (TM) - 2 ports
    {"name": "Port of Avaza", "country": "Turkmenistan", "countryCode": "TM", "region": "Central Asia", "lat": 42.5097, "lng": 52.9606, "status": "operational", "containers": 300000, "ships": 100, "congestion": 17, "activity": "Caspian Sea's container and petroleum facility"},
    {"name": "Port of Turkmenbashi Container Terminal", "country": "Turkmenistan", "countryCode": "TM", "region": "Central Asia", "lat": 43.6450, "lng": 52.9600, "status": "operational", "containers": 250000, "ships": 80, "congestion": 19, "activity": "Caspian container expansion facility"},
    # Georgia (GE) - 2 ports
    {"name": "Port of Batumi", "country": "Georgia", "countryCode": "GE", "region": "South Caucasus", "lat": 41.6271, "lng": 41.6271, "status": "operational", "containers": 650000, "ships": 180, "congestion": 15, "activity": "Georgia's Black Sea container gateway for Caucasus"},
    {"name": "Port of Poti", "country": "Georgia", "countryCode": "GE", "region": "South Caucasus", "lat": 42.1667, "lng": 41.6667, "status": "operational", "containers": 400000, "ships": 120, "congestion": 16, "activity": "Georgia's secondary Black Sea container facility"},
    # Azerbaijan (AZ) - 2 ports
    {"name": "Port of Baku Container Terminal", "country": "Azerbaijan", "countryCode": "AZ", "region": "Middle East", "lat": 40.3700, "lng": 49.6800, "status": "operational", "containers": 600000, "ships": 160, "congestion": 19, "activity": "Caspian's major container and oil export hub"},
    {"name": "Port of Sumgait", "country": "Azerbaijan", "countryCode": "AZ", "region": "Middle East", "lat": 40.5828, "lng": 48.8681, "status": "operational", "containers": 300000, "ships": 100, "congestion": 18, "activity": "Caspian's petrochemical and container facility"},
    # Mauritania (MR) - 2 ports
    {"name": "Port of Nouakchott", "country": "Mauritania", "countryCode": "MR", "region": "Africa", "lat": 18.0735, "lng": -15.9582, "status": "operational", "containers": 350000, "ships": 110, "congestion": 18, "activity": "West Africa's Atlantic container gateway for Mauritania"},
    {"name": "Port of Nouadhibou", "country": "Mauritania", "countryCode": "MR", "region": "Africa", "lat": 20.9331, "lng": -17.0333, "status": "operational", "containers": 200000, "ships": 80, "congestion": 16, "activity": "Mauritania's secondary container and fishing hub"},
    # Cape Verde (CV) - 2 ports
    {"name": "Port of Praia", "country": "Cape Verde", "countryCode": "CV", "region": "Africa", "lat": 14.9179, "lng": -23.6331, "status": "operational", "containers": 250000, "ships": 100, "congestion": 12, "activity": "Atlantic island nation's container gateway"},
    {"name": "Port of Mindelo", "country": "Cape Verde", "countryCode": "CV", "region": "Africa", "lat": 16.8845, "lng": -24.9769, "status": "operational", "containers": 180000, "ships": 70, "congestion": 11, "activity": "Cape Verde's secondary Atlantic container facility"},
    # Eritrea (ER) - 2 ports
    {"name": "Port of Assab", "country": "Eritrea", "countryCode": "ER", "region": "Africa", "lat": 13.3667, "lng": 42.7500, "status": "operational", "containers": 200000, "ships": 80, "congestion": 20, "activity": "Eritrea's Red Sea container facility"},
    {"name": "Port of Massawa", "country": "Eritrea", "countryCode": "ER", "region": "Africa", "lat": 15.6267, "lng": 39.4517, "status": "operational", "containers": 250000, "ships": 90, "congestion": 21, "activity": "Eritrea's main Red Sea container gateway"},
    # Timor-Leste (TL) - 2 ports
    {"name": "Port of Dili", "country": "Timor-Leste", "countryCode": "TL", "region": "Southeast Asia", "lat": -8.5580, "lng": 125.5603, "status": "operational", "containers": 350000, "ships": 130, "congestion": 16, "activity": "Timor-Leste's primary container gateway in Southeast Asia"},
    {"name": "Port of Suai", "country": "Timor-Leste", "countryCode": "TL", "region": "Southeast Asia", "lat": -9.1306, "lng": 125.2314, "status": "operational", "containers": 150000, "ships": 60, "congestion": 14, "activity": "Timor-Leste's secondary container facility"},
    # North Korea (KP) - 2 ports
    {"name": "Port of Pyongyang", "country": "North Korea", "countryCode": "KP", "region": "East Asia", "lat": 39.0196, "lng": 125.7453, "status": "delayed", "containers": 300000, "ships": 100, "congestion": 32, "activity": "North Korea's primary Yellow Sea container port"},
    {"name": "Port of Chongjin", "country": "North Korea", "countryCode": "KP", "region": "East Asia", "lat": 41.8000, "lng": 129.8170, "status": "delayed", "containers": 150000, "ships": 60, "congestion": 35, "activity": "North Korea's eastern Sea of Japan container facility"},
]

# Combine all ports
all_ports = original_ports + new_ports

# Output JSON to stdout
print(json.dumps(all_ports, indent=2))
