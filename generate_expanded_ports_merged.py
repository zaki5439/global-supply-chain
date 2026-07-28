#!/usr/bin/env python3
"""Generate expanded ports JSON file combining existing ports with new countries."""

import json
import os

# New ports for missing UN member states with coastlines
new_ports = [
    # Iceland - 3 ports
    {
        "name": "Port of Reykjavik",
        "country": "Iceland",
        "countryCode": "IS",
        "region": "Europe",
        "lat": 64.1466,
        "lng": -21.9426,
        "status": "operational",
        "containers": 800000,
        "ships": 250,
        "congestion": 9,
        "activity": "Iceland's primary container and fishing port"
    },
    {
        "name": "Port of Akureyri",
        "country": "Iceland",
        "countryCode": "IS",
        "region": "Europe",
        "lat": 65.6831,
        "lng": -18.0883,
        "status": "operational",
        "containers": 300000,
        "ships": 120,
        "congestion": 8,
        "activity": "Northern Iceland's container and cargo facility"
    },
    {
        "name": "Port of Hafnarfjordur",
        "country": "Iceland",
        "countryCode": "IS",
        "region": "Europe",
        "lat": 64.0892,
        "lng": -21.9506,
        "status": "operational",
        "containers": 500000,
        "ships": 180,
        "congestion": 9,
        "activity": "Iceland's fishing and container hub near Reykjavik"
    },
    # Ireland - 3 ports
    {
        "name": "Port of Cork",
        "country": "Ireland",
        "countryCode": "IE",
        "region": "Europe",
        "lat": 51.8933,
        "lng": -8.4667,
        "status": "operational",
        "containers": 900000,
        "ships": 280,
        "congestion": 11,
        "activity": "Southern Ireland's container and pharmaceutical port"
    },
    {
        "name": "Port of Dublin",
        "country": "Ireland",
        "countryCode": "IE",
        "region": "Europe",
        "lat": 53.3498,
        "lng": -6.2603,
        "status": "operational",
        "containers": 1200000,
        "ships": 350,
        "congestion": 13,
        "activity": "Ireland's primary Dublin Bay container hub"
    },
    {
        "name": "Port of Shannon",
        "country": "Ireland",
        "countryCode": "IE",
        "region": "Europe",
        "lat": 52.6500,
        "lng": -8.9333,
        "status": "operational",
        "containers": 600000,
        "ships": 200,
        "congestion": 10,
        "activity": "Western Ireland's container gateway on River Shannon estuary"
    },
    # Monaco - 1 port
    {
        "name": "Port of Monaco Fontvieille",
        "country": "Monaco",
        "countryCode": "MC",
        "region": "Europe",
        "lat": 43.7384,
        "lng": 7.4246,
        "status": "operational",
        "containers": 250000,
        "ships": 100,
        "congestion": 8,
        "activity": "Mediterranean luxury container and yacht port"
    },
    # Bosnia and Herzegovina - 2 ports
    {
        "name": "Port of Ploce",
        "country": "Bosnia and Herzegovina",
        "countryCode": "BA",
        "region": "Europe",
        "lat": 43.0633,
        "lng": 17.4363,
        "status": "operational",
        "containers": 1100000,
        "ships": 320,
        "congestion": 15,
        "activity": "Bosnia's Adriatic container and grain hub"
    },
    {
        "name": "Port of Neum",
        "country": "Bosnia and Herzegovina",
        "countryCode": "BA",
        "region": "Europe",
        "lat": 42.9167,
        "lng": 17.6833,
        "status": "operational",
        "containers": 400000,
        "ships": 140,
        "congestion": 12,
        "activity": "Bosnia's only Adriatic container facility"
    },
    # Montenegro - 3 ports
    {
        "name": "Port of Kotor",
        "country": "Montenegro",
        "countryCode": "ME",
        "region": "Europe",
        "lat": 42.4304,
        "lng": 18.7685,
        "status": "operational",
        "containers": 350000,
        "ships": 130,
        "congestion": 11,
        "activity": "Montenegro's Adriatic container and cruise hub"
    },
    {
        "name": "Port of Bar",
        "country": "Montenegro",
        "countryCode": "ME",
        "region": "Europe",
        "lat": 42.1069,
        "lng": 19.1064,
        "status": "operational",
        "containers": 600000,
        "ships": 200,
        "congestion": 13,
        "activity": "Montenegro's primary container and coal port"
    },
    {
        "name": "Port of Tivat",
        "country": "Montenegro",
        "countryCode": "ME",
        "region": "Europe",
        "lat": 42.4132,
        "lng": 18.6972,
        "status": "operational",
        "containers": 250000,
        "ships": 100,
        "congestion": 10,
        "activity": "Montenegro's yacht and cruise terminal facility"
    },
    # Albania - 2 ports
    {
        "name": "Port of Durrës",
        "country": "Albania",
        "countryCode": "AL",
        "region": "Europe",
        "lat": 41.3167,
        "lng": 19.4500,
        "status": "operational",
        "containers": 800000,
        "ships": 260,
        "congestion": 16,
        "activity": "Albania's Adriatic container gateway"
    },
    {
        "name": "Port of Vlore",
        "country": "Albania",
        "countryCode": "AL",
        "region": "Europe",
        "lat": 40.4667,
        "lng": 19.4833,
        "status": "operational",
        "containers": 350000,
        "ships": 120,
        "congestion": 14,
        "activity": "Southern Albania's secondary container port"
    },
    # Ukraine - 3 ports
    {
        "name": "Port of Varna",
        "country": "Ukraine",
        "countryCode": "UA",
        "region": "Europe",
        "lat": 44.7866,
        "lng": 29.3809,
        "status": "critical",
        "containers": 400000,
        "ships": 150,
        "congestion": 35,
        "activity": "Ukraine's Black Sea container hub (war-affected)"
    },
    {
        "name": "Port of Odesa",
        "country": "Ukraine",
        "countryCode": "UA",
        "region": "Europe",
        "lat": 46.4775,
        "lng": 30.7326,
        "status": "critical",
        "containers": 600000,
        "ships": 200,
        "congestion": 38,
        "activity": "Ukraine's primary Black Sea container gateway (war-affected)"
    },
    {
        "name": "Port of Mariupol",
        "country": "Ukraine",
        "countryCode": "UA",
        "region": "Europe",
        "lat": 47.0987,
        "lng": 37.5405,
        "status": "critical",
        "containers": 300000,
        "ships": 100,
        "congestion": 40,
        "activity": "Ukraine's Azov Sea container port (war-affected)"
    },
    # Turkmenistan - 2 ports
    {
        "name": "Port of Avaza",
        "country": "Turkmenistan",
        "countryCode": "TM",
        "region": "Central Asia",
        "lat": 43.6750,
        "lng": 52.7500,
        "status": "operational",
        "containers": 200000,
        "ships": 80,
        "congestion": 17,
        "activity": "Caspian Sea resort and container facility"
    },
    {
        "name": "Port of Turkmenbashi",
        "country": "Turkmenistan",
        "countryCode": "TM",
        "region": "Central Asia",
        "lat": 43.6442,
        "lng": 52.9606,
        "status": "operational",
        "containers": 350000,
        "ships": 140,
        "congestion": 19,
        "activity": "Caspian Sea's major oil and container hub"
    },
    # Georgia - 2 ports
    {
        "name": "Port of Batumi",
        "country": "Georgia",
        "countryCode": "GE",
        "region": "Middle East",
        "lat": 41.6350,
        "lng": 41.6450,
        "status": "operational",
        "containers": 500000,
        "ships": 180,
        "congestion": 13,
        "activity": "Georgia's Black Sea container and oil gateway"
    },
    {
        "name": "Port of Poti",
        "country": "Georgia",
        "countryCode": "GE",
        "region": "Middle East",
        "lat": 42.1533,
        "lng": 41.6667,
        "status": "operational",
        "containers": 350000,
        "ships": 130,
        "congestion": 12,
        "activity": "Georgia's secondary Black Sea container facility"
    },
    # Azerbaijan - 2 ports
    {
        "name": "Port of Baku",
        "country": "Azerbaijan",
        "countryCode": "AZ",
        "region": "Middle East",
        "lat": 40.3667,
        "lng": 49.6833,
        "status": "operational",
        "containers": 600000,
        "ships": 220,
        "congestion": 18,
        "activity": "Caspian Sea's primary oil and container hub"
    },
    {
        "name": "Port of Sumgait",
        "country": "Azerbaijan",
        "countryCode": "AZ",
        "region": "Middle East",
        "lat": 40.5833,
        "lng": 49.6833,
        "status": "operational",
        "containers": 250000,
        "ships": 100,
        "congestion": 16,
        "activity": "Azerbaijan's secondary Caspian container port"
    },
    # Mauritania - 2 ports
    {
        "name": "Port of Nouakchott",
        "country": "Mauritania",
        "countryCode": "MR",
        "region": "Africa",
        "lat": 18.0735,
        "lng": -15.9582,
        "status": "operational",
        "containers": 400000,
        "ships": 140,
        "congestion": 20,
        "activity": "Mauritania's Atlantic capital container port"
    },
    {
        "name": "Port of Nouadhibou",
        "country": "Mauritania",
        "countryCode": "MR",
        "region": "Africa",
        "lat": 20.9408,
        "lng": -17.0330,
        "status": "operational",
        "containers": 350000,
        "ships": 130,
        "congestion": 19,
        "activity": "Mauritania's fishing and container hub"
    },
    # Cape Verde - 2 ports
    {
        "name": "Port of Praia",
        "country": "Cape Verde",
        "countryCode": "CV",
        "region": "Africa",
        "lat": 14.9150,
        "lng": -23.6372,
        "status": "operational",
        "containers": 300000,
        "ships": 120,
        "congestion": 11,
        "activity": "Cape Verde's Atlantic island container hub"
    },
    {
        "name": "Port of Mindelo",
        "country": "Cape Verde",
        "countryCode": "CV",
        "region": "Africa",
        "lat": 16.8848,
        "lng": -24.9750,
        "status": "operational",
        "containers": 250000,
        "ships": 100,
        "congestion": 10,
        "activity": "Cape Verde's northern Atlantic container facility"
    },
    # Eritrea - 2 ports
    {
        "name": "Port of Assab",
        "country": "Eritrea",
        "countryCode": "ER",
        "region": "Africa",
        "lat": 13.7633,
        "lng": 42.7496,
        "status": "operational",
        "containers": 300000,
        "ships": 110,
        "congestion": 22,
        "activity": "Eritrea's Red Sea container gateway"
    },
    {
        "name": "Port of Massawa",
        "country": "Eritrea",
        "countryCode": "ER",
        "region": "Africa",
        "lat": 15.6519,
        "lng": 39.4457,
        "status": "operational",
        "containers": 350000,
        "ships": 130,
        "congestion": 24,
        "activity": "Eritrea's principal Red Sea container hub"
    },
    # Timor-Leste - 2 ports
    {
        "name": "Port of Dili",
        "country": "Timor-Leste",
        "countryCode": "TL",
        "region": "Southeast Asia",
        "lat": -8.5580,
        "lng": 125.5800,
        "status": "operational",
        "containers": 250000,
        "ships": 100,
        "congestion": 14,
        "activity": "Timor-Leste's capital container and regional hub"
    },
    {
        "name": "Port of Suai",
        "country": "Timor-Leste",
        "countryCode": "TL",
        "region": "Southeast Asia",
        "lat": -9.1633,
        "lng": 124.4667,
        "status": "operational",
        "containers": 150000,
        "ships": 70,
        "congestion": 13,
        "activity": "Timor-Leste's southern container facility"
    },
    # North Korea - 2 ports
    {
        "name": "Port of Pyongyang",
        "country": "North Korea",
        "countryCode": "KP",
        "region": "East Asia",
        "lat": 39.0392,
        "lng": 125.7521,
        "status": "critical",
        "containers": 250000,
        "ships": 100,
        "congestion": 32,
        "activity": "North Korea's capital container port (sanctions-affected)"
    },
    {
        "name": "Port of Chongjin",
        "country": "North Korea",
        "countryCode": "KP",
        "region": "East Asia",
        "lat": 41.7956,
        "lng": 129.8180,
        "status": "critical",
        "containers": 180000,
        "ships": 80,
        "congestion": 30,
        "activity": "North Korea's eastern container port (sanctions-affected)"
    }
]

# Read existing ports
existing_ports_file = r"c:\Users\ACER\supply-chain-app\resources\views\ports-complete.json"
with open(existing_ports_file, 'r', encoding='utf-8') as f:
    existing_ports = json.load(f)

# Combine all ports
all_ports = existing_ports + new_ports

# Write expanded ports file
output_file = r"c:\Users\ACER\supply-chain-app\resources\views\ports-expanded-complete.json"
with open(output_file, 'w', encoding='utf-8') as f:
    json.dump(all_ports, f, indent=2, ensure_ascii=False)

print(f"Successfully created {output_file}")
print(f"Total ports: {len(all_ports)}")
print(f"  - Existing ports: {len(existing_ports)}")
print(f"  - New ports: {len(new_ports)}")
print(f"  - New countries added: 15")
